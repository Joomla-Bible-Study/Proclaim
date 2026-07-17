#!/usr/bin/env bash
#
# Build the full installable pkg_proclaim-<version>.zip end to end:
#   1. build the sibling sub-packages (lib_cwmscripture, scripturelinks) fresh
#   2. build com_proclaim's assets + component zip  (cwm-build)
#   3. assemble the package wrapper                 (cwm-package)
#
# This is the single command the release-test harness and the release runbook
# use to produce the artifact users install. Non-interactive.
#
# Usage: build/build-package.sh <version>
#
# @since __DEPLOY_VERSION__

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

BIN="libraries/vendor/bin"
VERSION="${1:-}"

if [ -z "$VERSION" ]; then
    echo "ERROR: version required — usage: build/build-package.sh <version>" >&2
    exit 1
fi

echo "-- [pkg 1/3] build sibling sub-packages"
bash build/build-subpackages.sh

echo "-- [pkg 2/3] build com_proclaim assets + component (${VERSION})"
CWM_NONINTERACTIVE=1 "$BIN/cwm-build" --version "$VERSION"

echo "-- [pkg 3/3] assemble pkg_proclaim-${VERSION}.zip"
CWM_NONINTERACTIVE=1 "$BIN/cwm-package" --version "$VERSION"

ZIP="build/dist/pkg_proclaim-${VERSION}.zip"

if [ ! -f "$ZIP" ]; then
    echo "ERROR: expected package not produced: $ZIP" >&2
    exit 1
fi

echo "Package ready: ${ZIP}"
