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

echo "-- [1/15] reset test site(s) to a clean slate"
php build/reset-testsite.php

echo "-- [2/15] fetch released baseline ${BASEVER}"
bash build/build-baseline.sh "$BASEVER"

echo "-- [3/15] install baseline ${BASEVER} (the 'before' state)"
"$BIN/cwm-install-zip" --zip "$BASEZIP"

echo "-- [4/15] seed a downloaded translation + provider cache (upgrade must not eat them)"
php build/verify-scripture-upgrade.php seed

echo "-- [5/15] build new full package ${NEWVER}"
bash build/build-package.sh "$NEWVER"

if [ ! -f "$NEWZIP" ]; then
    echo "ERROR: expected build artifact not found: $NEWZIP" >&2
    exit 1
fi

echo "-- [6/15] install ${NEWVER} over ${BASEVER} (triggers update() + migrations)"
"$BIN/cwm-install-zip" --zip "$NEWZIP"

echo "-- [7/15] verify registration + migrations"
"$BIN/cwm-verify" --target test
php build/verify-migrations.php "$NEWVER"

echo "-- [8/15] verify the downloaded translation and cached passages survived"
php build/verify-scripture-upgrade.php verify

echo "-- [9/15] verify the consumer registry schema landed and the shipped extensions registered"
php build/verify-scripture-uninstall.php schema
php build/verify-scripture-uninstall.php assert-first-party-registered

# The remaining phases uninstall things, so they run last — the site is expendable
# from here on and reset-testsite.php rebuilds it on the next run.
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

echo "-- [10/15] STILL NEEDED: removing the library alone must be refused"
LIBID="$(php build/verify-scripture-uninstall.php ext-id library cwmscripture | tail -n1)"

if php "$JCLI" extension:remove -n "$LIBID" >/dev/null 2>&1; then
    echo "ERROR: extension:remove reported success for lib_cwmscripture." >&2
    echo "       preflight() should have refused it while com_proclaim is installed." >&2
    exit 1
fi

echo "   extension:remove exited non-zero, as expected"
php build/verify-scripture-uninstall.php assert-library-present

echo "-- [11/15] COMPLETE UNINSTALL: package removal with no other consumer drops the tables"
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

echo "-- [12/15] STILL NEEDED: a registered third-party consumer keeps the tables"
"$BIN/cwm-install-zip" --zip "$NEWZIP"
php build/verify-scripture-uninstall.php seed-consumer
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"
php "$JCLI" extension:remove -n "$PKGID" || true
php build/verify-scripture-uninstall.php assert-tables-present

echo "-- [13/15] STILL NEEDED: an UNregistered consumer keeps the tables (detection, not registration)"
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

echo "-- [14/15] NEGATIVE CONTROL: library-only update with no disarm must destroy data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-destroyed

echo "-- [15/15] library-only update AFTER the disarm must preserve data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php build/verify-scripture-uninstall.php disarm
php build/verify-scripture-uninstall.php assert-sql-disarmed
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-survived

echo "UPGRADE TEST PASSED ${BASEVER} -> ${NEWVER}."
