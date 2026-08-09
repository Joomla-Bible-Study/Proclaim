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

echo "-- [1/16] reset test site(s) to a clean slate"
php build/reset-testsite.php

echo "-- [2/16] fetch released baseline ${BASEVER}"
bash build/build-baseline.sh "$BASEVER"

echo "-- [3/16] install baseline ${BASEVER} (the 'before' state)"
"$BIN/cwm-install-zip" --zip "$BASEZIP"

echo "-- [4/16] seed a downloaded translation + provider cache (upgrade must not eat them)"
php build/verify-scripture-upgrade.php seed

echo "-- [5/16] build new full package ${NEWVER}"
bash build/build-package.sh "$NEWVER"

if [ ! -f "$NEWZIP" ]; then
    echo "ERROR: expected build artifact not found: $NEWZIP" >&2
    exit 1
fi

echo "-- [6/16] install ${NEWVER} over ${BASEVER} (triggers update() + migrations)"
"$BIN/cwm-install-zip" --zip "$NEWZIP"

echo "-- [7/16] verify registration + migrations"
"$BIN/cwm-verify" --target test
php build/verify-migrations.php "$NEWVER"

echo "-- [8/16] verify the downloaded translation and cached passages survived"
php build/verify-scripture-upgrade.php verify

echo "-- [9/16] verify the consumer registry schema landed and the shipped extensions registered"
php build/verify-scripture-uninstall.php schema
php build/verify-scripture-uninstall.php assert-first-party-registered

# The remaining phases uninstall things, so they run last — the site is expendable
# from here on and reset-testsite.php rebuilds it on the next run. Phase 16 puts
# ${NEWVER} back at the end, because test:e2e runs against this install straight
# after and must not be handed the baseline.
# tail -n1: these modes print one value, but any PHP notice would land on stdout
# ahead of it. Taking the last line keeps a stray warning from corrupting the
# path or id — which would otherwise make the CLI fail for the wrong reason and
# turn phase 10 into a false pass.
SITE="$(php build/verify-scripture-uninstall.php site-path | tail -n1)"
JCLI="${SITE}/cli/joomla.php"

if [ ! -f "$JCLI" ]; then
    echo "ERROR: Joomla CLI not found at '$JCLI'." >&2
    echo "       Without it the uninstall phases cannot prove anything." >&2
    exit 1
fi

# Phase 11 asserts that removing pkg_proclaim drops the shared scripture tables,
# which is only true when nothing else uses them. Any other consumer -- Living
# Word is the one that actually bit us (lib_cwmscripture#37) -- makes keeping
# them correct, so the assertion would be testing for a bug.
#
# Clear them here rather than in phase 11 itself: phases 12 and 13 install their
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
    echo "   clearing other scripture consumers before the uninstall phases:"

    for CID in $OTHER_CONSUMERS; do
        if php "$JCLI" extension:remove -n "$CID" >/dev/null 2>&1; then
            echo "     removed extension id ${CID}"
        else
            echo "ERROR: could not remove scripture consumer id ${CID}." >&2
            echo "       Phase 11 would then fail for the wrong reason." >&2
            exit 1
        fi
    done
fi

echo "-- [10/16] STILL NEEDED: removing the library alone must be refused"
LIBID="$(php build/verify-scripture-uninstall.php ext-id library cwmscripture | tail -n1)"

if php "$JCLI" extension:remove -n "$LIBID" >/dev/null 2>&1; then
    echo "ERROR: extension:remove reported success for lib_cwmscripture." >&2
    echo "       preflight() should have refused it while com_proclaim is installed." >&2
    exit 1
fi

echo "   extension:remove exited non-zero, as expected"
php build/verify-scripture-uninstall.php assert-library-present

echo "-- [11/16] COMPLETE UNINSTALL: package removal with no other consumer drops the tables"
# The precondition has to be checked BEFORE the removal — afterwards the registry
# and extension rows are gone and "was anything else using these?" is unanswerable.
# lib_cwmscripture#37 was filed because this phase asserted the drop on a site with
# com_livingword installed, where keeping the tables is the correct behaviour.
php build/verify-scripture-uninstall.php assert-no-other-consumer
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"

# No `|| true`. A removal that failed and a removal that succeeded then declined to
# drop look identical in the table check, and that ambiguity is what turned correct
# behaviour into a filed bug.
if ! php "$JCLI" extension:remove -n "$PKGID"; then
    echo "ERROR: extension:remove failed for pkg_proclaim (id ${PKGID})." >&2
    echo "       The table assertions below would be meaningless, so stopping here." >&2
    exit 1
fi

php build/verify-scripture-uninstall.php assert-tables-gone

echo "-- [12/16] STILL NEEDED: a registered third-party consumer keeps the tables"
"$BIN/cwm-install-zip" --zip "$NEWZIP"
php build/verify-scripture-uninstall.php seed-consumer
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"
php "$JCLI" extension:remove -n "$PKGID" || true
php build/verify-scripture-uninstall.php assert-tables-present
# Only meaningful in this phase: the registry survives here because the tables
# were kept, so it is the one place a stale row can outlive its extension (#1662).
php build/verify-scripture-uninstall.php assert-registry-pruned

echo "-- [13/16] STILL NEEDED: an UNregistered consumer keeps the tables (detection, not registration)"
"$BIN/cwm-install-zip" --zip "$NEWZIP"
php build/verify-scripture-uninstall.php seed-detected-consumer
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"
php "$JCLI" extension:remove -n "$PKGID" || true
php build/verify-scripture-uninstall.php assert-detected-consumer

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

LIBZIP="build/dist/lib_cwmscripture-only.zip"
rm -rf build/dist/_libonly && mkdir -p build/dist/_libonly
unzip -qo "$NEWZIP" -d build/dist/_libonly
cp build/dist/_libonly/packages/lib_cwmscripture.zip "$LIBZIP"

# The baseline no longer supplies the hazard: lib_cwmscripture has shipped a
# disarmed uninstall SQL since 1.1.5, so these two phases plant the pre-1.1.5
# file themselves and then assert it really is armed before relying on it.
echo "-- [14/16] NEGATIVE CONTROL: library-only update with no disarm must destroy data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php arm
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-destroyed

echo "-- [15/16] library-only update AFTER the disarm must preserve data"
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
# uninstall phases retain the Proclaim data tables by design, so phase 11's
# removal leaves ${NEWVER}-shaped tables behind, and the baseline reinstalls in
# 14/15 then rewrite #__schemas back to ${BASEVER} over them. An update from
# there re-runs migrations whose work is already present, and
# 10.5.6-20260807.sql's unguarded `ALTER TABLE ... DROP INDEX idx_study_topic`
# fails with MySQL 1091. A clean install is also what the API acceptance spec
# ("REST API acceptance (package install)") is written against.
echo "-- [16/16] reset and clean-install ${NEWVER} for anything that runs after this"
php build/reset-testsite.php
"$BIN/cwm-install-zip" --zip "$NEWZIP" >/dev/null
"$BIN/cwm-verify" --target test
php build/verify-migrations.php "$NEWVER"

echo "UPGRADE TEST PASSED ${BASEVER} -> ${NEWVER}."
