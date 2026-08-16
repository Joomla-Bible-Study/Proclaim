#!/usr/bin/env bash
#
# Upgrade + migration release test.
#
# Installs the last released package (the real artifact users are on) into a
# clean test site, then installs the freshly-built package over it. Joomla routes
# the second install to update() — running the install scriptfile's update() path
# and every admin/sql/updates migration newer than the recorded #__schemas
# version. Finally confirms the new version is registered and the migration
# columns/tables/indexes exist (i.e. the ADD COLUMN / CREATE TABLE steps ran
# without collision).
#
# It also plants a locally downloaded Bible translation and live provider-cache
# rows in the baseline and checks they are still there afterwards —
# lib_cwmscripture's uninstall SQL used to run on every update and drop all three
# scripture tables, verses and cached passages alike.
#
# The final phases then exercise the uninstall guards, which is destructive and
# so runs last: that the consumer-registry schema landed, that removing the
# library while Proclaim is installed is refused, and that a registered
# third-party consumer stops a package uninstall from dropping the shared tables,
# and that an extension which never registered is still protected because the
# library finds its namespace references on disk.
#
# Run via: composer test:upgrade
#
# @since __DEPLOY_VERSION__

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

BIN="libraries/vendor/bin"

NEWVER="$(php -r '$v=json_decode(file_get_contents("build/versions.json"),true); echo $v["active_development"]["version"] ?? "";')"
BASEVER="$(php -r '$v=json_decode(file_get_contents("build/versions.json"),true); echo $v["current"]["version"] ?? "";')"

if [ -z "$NEWVER" ] || [ -z "$BASEVER" ]; then
    echo "ERROR: could not resolve versions (active_development / current) from build/versions.json" >&2
    exit 1
fi

if [ "$NEWVER" = "$BASEVER" ]; then
    echo "ERROR: active-development version ($NEWVER) equals the last release ($BASEVER)." >&2
    echo "       The upgrade path only fires when the new build is newer — bump before testing." >&2
    exit 1
fi

BASEZIP="build/dist/pkg_proclaim-${BASEVER}.zip"
NEWZIP="build/dist/pkg_proclaim-${NEWVER}.zip"

echo "========================================================================"
echo " UPGRADE TEST — ${BASEVER}  ->  ${NEWVER}"
echo "========================================================================"

echo "-- [1/17] reset test site(s) to a clean slate"
php build/reset-testsite.php

echo "-- [2/17] fetch released baseline ${BASEVER}"
bash build/build-baseline.sh "$BASEVER"

echo "-- [3/17] install baseline ${BASEVER} (the 'before' state)"
"$BIN/cwm-install-zip" --zip "$BASEZIP"

echo "-- [4/17] seed a downloaded translation + provider cache (upgrade must not eat them)"
php build/verify-scripture-upgrade.php seed

echo "-- [5/17] build new full package ${NEWVER}"
bash build/build-package.sh "$NEWVER"

if [ ! -f "$NEWZIP" ]; then
    echo "ERROR: expected build artifact not found: $NEWZIP" >&2
    exit 1
fi

echo "-- [6/17] install ${NEWVER} over ${BASEVER} (triggers update() + migrations)"
"$BIN/cwm-install-zip" --zip "$NEWZIP"

# Immediately after the upgrade, before anything else installs over it: the log
# describes one install, and phase 17 reinstalls on top. What the update decided
# about the legacy migrations is recorded nowhere else — the CLI installer drops
# the on-screen report, and both a gated and an ungated update leave the same
# schema behind for every other assertion here to agree on.
echo "-- [7/17] verify the update reported what it did (and gated the migrations)"
php build/verify-install-log.php update "$BASEVER"

echo "-- [8/17] verify registration + migrations"
"$BIN/cwm-verify" --target test
php build/verify-migrations.php "$NEWVER"
# The upgraded state is what a real site is in, and it is the state that
# reported a Database Maintenance error on a live site after 10.5.6 while
# every other assertion here passed.
php build/verify-schema-check.php

echo "-- [9/17] verify the downloaded translation and cached passages survived"
php build/verify-scripture-upgrade.php verify

echo "-- [10/17] verify the consumer registry schema landed and the shipped extensions registered"
php build/verify-scripture-uninstall.php schema
php build/verify-scripture-uninstall.php assert-first-party-registered

# The remaining phases uninstall things, so they run last — the site is expendable
# from here on and reset-testsite.php rebuilds it on the next run. Phase 17 puts
# ${NEWVER} back at the end, because test:e2e runs against this install straight
# after and must not be handed the baseline.
# tail -n1: these modes print one value, but any PHP notice would land on stdout
# ahead of it. Taking the last line keeps a stray warning from corrupting the
# path or id — which would otherwise make the CLI fail for the wrong reason and
# turn phase 11 into a false pass.
SITE="$(php build/verify-scripture-uninstall.php site-path | tail -n1)"
JCLI="${SITE}/cli/joomla.php"

if [ ! -f "$JCLI" ]; then
    echo "ERROR: Joomla CLI not found at '$JCLI'." >&2
    echo "       Without it the uninstall phases cannot prove anything." >&2
    exit 1
fi

# Phase 12 asserts that removing pkg_proclaim drops the shared scripture tables,
# which is only true when nothing else uses them. Any other consumer -- Living
# Word is the one that actually bit us (lib_cwmscripture#37) -- makes keeping
# them correct, so the assertion would be testing for a bug.
#
# Clear them here rather than in phase 12 itself: phases 13 and 14 install their
# own consumers to prove the opposite case, and an unrelated one already present
# would let those pass without their fixtures doing anything.
#
# NOTE: reset-testsite.php only removes the Proclaim family, so anything taken
# out here stays out. That is fine for a role=test site -- everything from this
# point is destructive by design -- but it does mean a consumer must be
# reinstalled by hand if a later run needs it.
# grep to digits only: this mode prints bare ids with no header, so a stray PHP
# notice on stdout would otherwise be passed to extension:remove as an id.
OTHER_CONSUMERS="$(php build/verify-scripture-uninstall.php other-consumer-ids | grep -E '^[0-9]+$' || true)"

if [ -n "$OTHER_CONSUMERS" ]; then
    # ⚠️ Removing a consumer package takes the shared scripture stack with it,
    # and correcting package_id cannot prevent that. PackageAdapter::
    # removeExtensionFiles() reads the consumer's *installed* manifest from
    # administrator/manifests/packages and resolves every <file> it declares
    # through _getExtensionId($type, $element, $client, $group) -- by element.
    # package_id is never consulted on that path.
    #
    # pkg_livingword declares lib_cwmscripture, plg_content_scripturelinks and
    # plg_task_cwmscripture as its own children, so all three go with it.
    # plg_system_cwmscripture, which it does not declare, survives -- the
    # correlation is exact (#1860, and the same damage as #1820).
    #
    # So the stack is restored afterwards rather than defended beforehand. That
    # holds for any consumer whose manifest overlaps ours, not just LivingWord.
    echo "   clearing other scripture consumers before the uninstall phases:"

    for CID in $OTHER_CONSUMERS; do
        if php "$JCLI" extension:remove -n "$CID" >/dev/null 2>&1; then
            echo "     removed extension id ${CID}"
        else
            echo "ERROR: could not remove scripture consumer id ${CID}." >&2
            echo "       Phase 12 would then fail for the wrong reason." >&2
            exit 1
        fi
    done

    # Read the version rather than globbing for the newest zip on disk: several
    # pkg_cwmscripture builds accumulate in build/dist, and picking the newest
    # file is how a package once shipped a zip that was not the pinned one.
    SCRIPTUREVER="$(php -r '
        $m = @simplexml_load_file("plugins/content/scripturelinks/build/pkg_cwmscripture.xml");
        echo $m === false ? "" : (string) $m->version;
    ')"
    SCRIPTUREZIP="plugins/content/scripturelinks/build/dist/pkg_cwmscripture-${SCRIPTUREVER}.zip"

    if [ -z "$SCRIPTUREVER" ] || [ ! -f "$SCRIPTUREZIP" ]; then
        echo "ERROR: pkg_cwmscripture build artifact not found: '${SCRIPTUREZIP}'." >&2
        echo "       Phase 5 builds it; without it the stack cannot be restored." >&2
        exit 1
    fi

    echo "   restoring the scripture stack those removals may have taken:"

    if ! php "$JCLI" extension:install --path="$SCRIPTUREZIP" >/dev/null 2>&1; then
        echo "ERROR: could not reinstall ${SCRIPTUREZIP}." >&2
        exit 1
    fi

    echo "     reinstalled pkg_cwmscripture ${SCRIPTUREVER}"

    # ⚠️ Assert here, not at the next phase's first use. Every phase from 11 on
    # needs a library to test against, so without this the damage surfaces as
    # "No library 'cwmscripture' registered" against whichever assertion ran
    # first -- reporting a missing fixture as a failure of the code under test.
    php build/verify-scripture-uninstall.php assert-library-present

    # ⚠️ The reinstall above is a *library* install, and LibraryAdapter::install()
    # calls checkExtensionInFilesystem() -> uninstall() before it installs, which
    # is the exact path that used to run lib_cwmscripture's uninstall SQL and wipe
    # every locally downloaded translation. It ships disarmed, but a library
    # version that shipped it armed again would destroy the downloaded bibles here
    # and the run would still report PASSED: phase 9 verified this seed *before*
    # the reinstall, and phase 12 seeds its own data *after* it, so the window in
    # between is checked by nothing else.
    php build/verify-scripture-upgrade.php verify
fi

echo "-- [11/17] STILL NEEDED: removing the library alone must be refused"
LIBID="$(php build/verify-scripture-uninstall.php ext-id library cwmscripture | tail -n1)"

if php "$JCLI" extension:remove -n "$LIBID" >/dev/null 2>&1; then
    echo "ERROR: extension:remove reported success for lib_cwmscripture." >&2
    echo "       preflight() should have refused it while com_proclaim is installed." >&2
    exit 1
fi

echo "   extension:remove exited non-zero, as expected"
php build/verify-scripture-uninstall.php assert-library-present

echo "-- [12/17] A package removal must leave the whole scripture stack alone"
# Inverted deliberately at 10.5.7 (#1675). This used to assert that removing
# pkg_proclaim DROPPED the shared tables when nothing else used them. Proclaim no
# longer owns any of it: pkg_cwmscripture is not a child of pkg_proclaim, so the
# uninstaller cannot reach the library, the plugin, or the data. Removing that is
# the administrator's deliberate act.
#
# The old "no other consumer" precondition is gone with the drop it guarded.
# Proclaim keeps the stack unconditionally now, so who else uses it is no longer
# part of the question — and since 10.5.7 the bundled pkg_cwmscripture always
# brings plg_task_cwmscripture, so there is always another consumer anyway.
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"

# No `|| true`. A removal that failed and a removal that succeeded then left
# everything alone look identical in the assertions below, and that ambiguity is
# what turned correct behaviour into a filed bug once already.
if ! php "$JCLI" extension:remove -n "$PKGID"; then
    echo "ERROR: extension:remove failed for pkg_proclaim (id ${PKGID})." >&2
    echo "       The assertions below would be meaningless, so stopping here." >&2
    exit 1
fi

php build/verify-scripture-uninstall.php assert-library-present
php build/verify-scripture-uninstall.php assert-tables-present
# com_proclaim's own uninstall unregisters it, and that now sticks: postflight
# was re-registering it immediately afterwards, because Joomla calls postflight
# on uninstall too and every install-time step in it ran unguarded (#1679).
#
# The registry outlives every Proclaim uninstall now that the scripture stack is
# not removed with it, so a stale row would persist rather than vanish with the
# dropped table (#1662).
php build/verify-scripture-uninstall.php assert-registry-pruned

echo "-- [13/17] STILL NEEDED: a registered consumer blocks a STANDALONE library uninstall"
# Repointed at the standalone path (#1675). The consumer guard used to be
# exercised by a package removal; now that Proclaim never removes the library,
# lib_cwmscripture's own dropTablesIfOrphaned() is the only code that can drop
# these tables, and only on a genuine standalone uninstall.
"$BIN/cwm-install-zip" --zip "$NEWZIP"
php build/verify-scripture-uninstall.php seed-consumer
php build/verify-scripture-uninstall.php seed-translation
LIBID="$(php build/verify-scripture-uninstall.php ext-id library cwmscripture | tail -n1)"

if php "$JCLI" extension:remove -n "$LIBID" >/dev/null 2>&1; then
    echo "ERROR: the library was removed while a registered consumer was present." >&2
    exit 1
fi

echo "   extension:remove exited non-zero, as expected"
php build/verify-scripture-uninstall.php assert-tables-present
php build/verify-scripture-uninstall.php assert-library-present

echo "-- [14/17] SKIPPED: unregistered-consumer detection is no longer isolatable here"
# Retired at 10.5.7 (#1675), not silently dropped.
#
# This asserted that a consumer found only by ConsumerScanner — never registered
# — was the reason the tables survived. That required no first-party consumer to
# be installed, and since Proclaim now bundles pkg_cwmscripture, plg_task_cwmscripture
# is always present and always keeps them. The probe correctly refuses to claim
# detection was the cause when something else already is.
#
# Isolating it would mean removing the bundled stack's children individually,
# which Joomla forbids for package children. The detection logic itself is unit
# tested in CWMScriptureLinks, which is where it belongs.
echo "   (see the comment above — covered by unit tests in CWMScriptureLinks)"

# --- library-only update path ------------------------------------------------
#
# Joomla lists lib_cwmscripture separately in the Update Manager (it carries its
# own update server), so an admin can update the library without touching any
# package. No package code runs, and nothing inside the incoming library can help
# either: InstallerAdapter::install() calls checkExtensionInFilesystem() — which
# triggers the OLD library's uninstall — before triggerManifestScript('preflight')
# loads the new script file.
#
# The only defence is code already resident on the site: plg_system_proclaim, or
# plg_system_cwmscripture for non-Proclaim stacks, blanking the legacy SQL on an
# admin com_installer page load. These phases prove the mechanism both ways.
#
# NOTE: this simulates the disarm rather than driving a browser request, so it
# proves the disarm *works*, not that the plugin's event wiring fires. See the
# unit tests in CWMScriptureLinks for the detection logic itself.

# The library zip is no longer a top-level entry in the package: since 10.5.7 the
# scripture stack ships as packages/pkg_cwmscripture.zip, with the library nested
# one level further in (#1675). Take it from the submodule's own dist instead —
# build/build-subpackages.sh has just rebuilt it, and that is the same artifact
# the package wraps.
LIBZIP="build/dist/lib_cwmscripture-only.zip"
LIBSRC="$(ls -1t libraries/lib_cwmscripture/build/dist/lib_cwmscripture-*.zip 2>/dev/null | head -n1)"

if [ -z "$LIBSRC" ] || [ ! -f "$LIBSRC" ]; then
    echo "ERROR: no lib_cwmscripture zip in the submodule dist — phases 14/15 cannot run." >&2
    exit 1
fi

cp "$LIBSRC" "$LIBZIP"

# The baseline no longer supplies the hazard: lib_cwmscripture has shipped a
# disarmed uninstall SQL since 1.1.5, so these two phases plant the pre-1.1.5
# file themselves and then assert it really is armed before relying on it.
echo "-- [15/17] NEGATIVE CONTROL: library-only update with no disarm must destroy data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php arm
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-destroyed

echo "-- [16/17] library-only update AFTER the disarm must preserve data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php arm
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php build/verify-scripture-uninstall.php disarm
php build/verify-scripture-uninstall.php assert-sql-disarmed
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-survived

# Phases 14 and 15 use the released baseline as their fixture, so the site is
# left on ${BASEVER} with a newer library over it. Nothing here needs the site
# afterwards, but composer test:release runs test:e2e next against this same
# install — and it would then exercise ${BASEVER}, quietly reporting a missing
# ${NEWVER} feature as a product failure. Leave the site on what was tested.
#
# Reset first rather than installing ${NEWVER} straight over ${BASEVER}. The
# uninstall phases retain the Proclaim data tables by design, so phase 12's
# removal leaves ${NEWVER}-shaped tables behind, and the baseline reinstalls in
# 14/15 then rewrite #__schemas back to ${BASEVER} over them. An update from
# there re-runs migrations whose work is already present, and
# 10.5.6-20260807.sql's unguarded `ALTER TABLE ... DROP INDEX idx_study_topic`
# fails with MySQL 1091. A clean install is also what the API acceptance spec
# ("REST API acceptance (package install)") is written against.
echo "-- [17/17] reset and clean-install ${NEWVER} for anything that runs after this"
php build/reset-testsite.php
"$BIN/cwm-install-zip" --zip "$NEWZIP" >/dev/null
"$BIN/cwm-verify" --target test
php build/verify-migrations.php "$NEWVER"
php build/verify-schema-check.php

echo "UPGRADE TEST PASSED ${BASEVER} -> ${NEWVER}."
