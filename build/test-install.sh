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

echo "-- [1/11] reset test site(s) to a clean slate"
"$BIN/cwm-reset-testsite"

echo "-- [2/11] build full package ${VERSION}"
bash build/build-package.sh "$VERSION"

if [ ! -f "$ZIP" ]; then
    echo "ERROR: expected build artifact not found: $ZIP" >&2
    exit 1
fi

echo "-- [3/11] install ${ZIP} (fresh)"
"$BIN/cwm-install-zip" --zip "$ZIP"

# Demo content is opt-in now (#2145/#2176) — install.mysql.utf8.sql no longer
# seeds a sample study, so several checks below have nothing to look at unless
# something imports it first. This aborts the run like reset/build/install
# above, rather than joining the soft-fail block below: a missing study makes
# seed-testsite-menus.php throw anyway, and letting the checks after it run
# against an empty site would report the wrong failure, or none at all.
echo "-- [4/11] seed demo content through proclaim:import (#2176)"
php build/seed-demo-content.php

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

echo "-- [5/11] verify extension registration"
"$BIN/cwm-verify" --target test || FAILURES+=("extension registration (cwm-verify)")

echo "-- [6/11] verify migrations landed"
php build/verify-migrations.php "$VERSION" || FAILURES+=("migrations (verify-migrations)")

echo "-- [7/11] verify the REST API landed (#1309/#1310/#1331 guards)"
php build/verify-api-install.php || FAILURES+=("REST API (verify-api-install)")

echo "-- [8/11] verify the scripture library landed (tables, seed, plugin enabled)"
php build/verify-scripture-install.php || FAILURES+=("scripture library (verify-scripture-install)")

echo "-- [9/11] seed the site menu items the front end is reached through (#1701)"
php build/seed-testsite-menus.php || FAILURES+=("menu seeding (seed-testsite-menus)")
# A fresh install has no verses until the Download Core Translations task runs,
# and the seeded study cites a book. Without this the front-end check below only
# ever exercises the unresolvable path.
php build/seed-scripture-fixture.php || FAILURES+=("scripture fixture (seed-scripture-fixture)")

echo "-- [10/11] verify the front end renders (#1701 guards)"
php build/verify-frontend.php || FAILURES+=("front end (verify-frontend)")

echo "-- [11/11] verify Joomla's own schema check is clean"
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
