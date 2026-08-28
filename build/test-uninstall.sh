#!/usr/bin/env bash
#
# Uninstall test.
#
# Resets every role=test install, builds and installs the package, then runs the
# real uninstall with drop_tables enabled and asserts what it removed — and,
# more importantly, what it left alone.
#
# ⚠️ DESTRUCTIVE, and separate from test-install.sh on purpose. It leaves the
# site Proclaim-free, so it cannot be a phase of test:install: test:release
# chains install -> upgrade -> e2e, and those two would find nothing installed.
#
# Run via: composer test:uninstall
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
echo " UNINSTALL TEST — pkg_proclaim ${VERSION}"
echo "========================================================================"

echo "-- [1/4] reset test site(s) to a clean slate"
"$BIN/cwm-reset-testsite"

# ⚠️ Always rebuilt, never reused. An earlier version reused an existing
# artifact on the reasoning that the uninstall path does not depend on what is
# in the zip. It does: the uninstall runs proclaim.script.php *from the
# installed copy*. Reusing a two-day-old zip tested two-day-old uninstall code
# and reported a failure that had already been fixed — and would just as
# happily report a pass for a fix that had not been made.
echo "-- [2/4] build full package ${VERSION}"
rm -f "$ZIP"
bash build/build-package.sh "$VERSION"

echo "-- [3/4] install ${ZIP}"
"$BIN/cwm-install-zip" --zip "$ZIP"

echo "-- [4/4] uninstall and verify"
php build/verify-uninstall.php
