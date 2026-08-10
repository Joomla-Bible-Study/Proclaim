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

echo "-- [1/9] reset test site(s) to a clean slate"
php build/reset-testsite.php

echo "-- [2/9] build full package ${VERSION}"
bash build/build-package.sh "$VERSION"

if [ ! -f "$ZIP" ]; then
    echo "ERROR: expected build artifact not found: $ZIP" >&2
    exit 1
fi

echo "-- [3/9] install ${ZIP} (fresh)"
"$BIN/cwm-install-zip" --zip "$ZIP"

echo "-- [4/9] verify extension registration"
"$BIN/cwm-verify" --target test

echo "-- [5/9] verify migrations landed"
php build/verify-migrations.php "$VERSION"

echo "-- [6/9] verify the REST API landed (#1309/#1310/#1331 guards)"
php build/verify-api-install.php

echo "-- [7/9] verify the scripture library landed (tables, seed, plugin enabled)"
php build/verify-scripture-install.php

echo "-- [8/9] seed the site menu items the front end is reached through (#1701)"
php build/seed-testsite-menus.php

echo "-- [9/9] verify the front end renders (#1701 guards)"
php build/verify-frontend.php

echo "CLEAN-INSTALL TEST PASSED for ${VERSION}."
