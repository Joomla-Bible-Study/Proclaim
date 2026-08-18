#!/usr/bin/env bash
#
# Clean-install release test.
#
# Resets every role=test install, builds the package at the active-development
# version, installs it into a Proclaim-free site (a true fresh install — the
# install() scriptfile path + full install.sql), then confirms the extension is
# registered and every migration column/table/index actually landed.
#
# Run via: composer test:install
#
# @since __DEPLOY_VERSION__

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

BIN="libraries/vendor/bin"
VERSION="$(php -r '$v=json_decode(file_get_contents("build/versions.json"),true); echo $v["active_development"]["version"] ?? ($v["current"]["version"] ?? "");')"

if [ -z "$VERSION" ]; then
    echo "ERROR: could not resolve active-development version from build/versions.json" >&2
    exit 1
fi

ZIP="build/dist/pkg_proclaim-${VERSION}.zip"

echo "========================================================================"
echo " CLEAN-INSTALL TEST — pkg_proclaim ${VERSION}"
echo "========================================================================"

echo "-- [1/10] reset test site(s) to a clean slate"
"$BIN/cwm-reset-testsite"

echo "-- [2/10] build full package ${VERSION}"
bash build/build-package.sh "$VERSION"

if [ ! -f "$ZIP" ]; then
    echo "ERROR: expected build artifact not found: $ZIP" >&2
    exit 1
fi

echo "-- [3/10] install ${ZIP} (fresh)"
"$BIN/cwm-install-zip" --zip "$ZIP"

# Verification steps record their result and carry on; the reset/build/install
# steps above still abort, because there is nothing to verify if the package
# never landed.
#
# Under `set -e` a bare check aborts the run, so the first failure hid the other
# six: a known registration drift would mask an unrelated schema regression, and
# the fix for one would only reveal the next. Adopted from CWMLivingWord's copy
# of this script, which already worked this way -- comparing the two for
# Joomla-Bible-Study/cwm-build-tools#142 is what surfaced the difference.
FAILURES=()

echo "-- [4/10] verify extension registration"
"$BIN/cwm-verify" --target test || FAILURES+=("extension registration (cwm-verify)")

echo "-- [5/10] verify migrations landed"
php build/verify-migrations.php "$VERSION" || FAILURES+=("migrations (verify-migrations)")

echo "-- [6/10] verify the REST API landed (#1309/#1310/#1331 guards)"
php build/verify-api-install.php || FAILURES+=("REST API (verify-api-install)")

echo "-- [7/10] verify the scripture library landed (tables, seed, plugin enabled)"
php build/verify-scripture-install.php || FAILURES+=("scripture library (verify-scripture-install)")

echo "-- [8/10] seed the site menu items the front end is reached through (#1701)"
php build/seed-testsite-menus.php || FAILURES+=("menu seeding (seed-testsite-menus)")

echo "-- [9/10] verify the front end renders (#1701 guards)"
php build/verify-frontend.php || FAILURES+=("front end (verify-frontend)")

echo "-- [10/10] verify Joomla's own schema check is clean"
php build/verify-schema-check.php || FAILURES+=("Joomla schema check (verify-schema-check)")

echo
if [ ${#FAILURES[@]} -gt 0 ]; then
    echo "CLEAN-INSTALL TEST FAILED for ${VERSION}:"
    for f in "${FAILURES[@]}"; do
        echo "  - ${f}"
    done
    exit 1
fi

echo "CLEAN-INSTALL TEST PASSED for ${VERSION}."
