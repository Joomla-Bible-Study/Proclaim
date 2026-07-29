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
# It also plants a locally downloaded Bible translation in the baseline and
# checks it is still there afterwards — lib_cwmscripture's uninstall SQL used to
# run on every update and drop the verse tables.
#
# The final phases then exercise the uninstall guards, which is destructive and
# so runs last: that the consumer-registry schema landed, that removing the
# library while Proclaim is installed is refused, and that a registered
# third-party consumer stops a package uninstall from dropping the shared tables.
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

echo "-- [1/8] reset test site(s) to a clean slate"
php build/reset-testsite.php

echo "-- [2/8] fetch released baseline ${BASEVER}"
bash build/build-baseline.sh "$BASEVER"

echo "-- [3/8] install baseline ${BASEVER} (the 'before' state)"
"$BIN/cwm-install-zip" --zip "$BASEZIP"

echo "-- [4/8] seed a locally downloaded translation (upgrade must not eat it)"
php build/verify-scripture-upgrade.php seed

echo "-- [5/8] build new full package ${NEWVER}"
bash build/build-package.sh "$NEWVER"

if [ ! -f "$NEWZIP" ]; then
    echo "ERROR: expected build artifact not found: $NEWZIP" >&2
    exit 1
fi

echo "-- [6/8] install ${NEWVER} over ${BASEVER} (triggers update() + migrations)"
"$BIN/cwm-install-zip" --zip "$NEWZIP"

echo "-- [7/8] verify registration + migrations"
"$BIN/cwm-verify" --target test
php build/verify-migrations.php "$NEWVER"

echo "-- [8/14] verify the downloaded translation survived"
php build/verify-scripture-upgrade.php verify

echo "-- [9/14] verify the consumer registry schema landed"
php build/verify-scripture-uninstall.php schema

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

echo "-- [10/14] STILL NEEDED: removing the library alone must be refused"
LIBID="$(php build/verify-scripture-uninstall.php ext-id library cwmscripture | tail -n1)"

if php "$JCLI" extension:remove -n "$LIBID" >/dev/null 2>&1; then
    echo "ERROR: extension:remove reported success for lib_cwmscripture." >&2
    echo "       preflight() should have refused it while com_proclaim is installed." >&2
    exit 1
fi

echo "   extension:remove exited non-zero, as expected"
php build/verify-scripture-uninstall.php assert-library-present

echo "-- [11/14] COMPLETE UNINSTALL: package removal with no other consumer drops the tables"
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"
php "$JCLI" extension:remove -n "$PKGID" || true
php build/verify-scripture-uninstall.php assert-tables-gone

echo "-- [12/14] STILL NEEDED: a registered third-party consumer keeps the tables"
"$BIN/cwm-install-zip" --zip "$NEWZIP"
php build/verify-scripture-uninstall.php seed-consumer
php build/verify-scripture-uninstall.php seed-translation
PKGID="$(php build/verify-scripture-uninstall.php ext-id package pkg_proclaim | tail -n1)"
php "$JCLI" extension:remove -n "$PKGID" || true
php build/verify-scripture-uninstall.php assert-tables-present

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

echo "-- [13/14] NEGATIVE CONTROL: library-only update with no disarm must destroy data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-destroyed

echo "-- [14/14] library-only update AFTER the disarm must preserve data"
"$BIN/cwm-install-zip" --zip "$BASEZIP" >/dev/null
php build/verify-scripture-uninstall.php assert-sql-armed
php build/verify-scripture-uninstall.php seed-translation
php build/verify-scripture-uninstall.php disarm
php build/verify-scripture-uninstall.php assert-sql-disarmed
php "$JCLI" extension:install -n --path="$(pwd)/${LIBZIP}" >/dev/null 2>&1 || true
php build/verify-scripture-uninstall.php assert-translation-survived

echo "UPGRADE TEST PASSED ${BASEVER} -> ${NEWVER}."
