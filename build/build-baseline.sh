#!/usr/bin/env bash
#
# Fetch the last-released pkg_proclaim package to use as the "before" state in
# the upgrade test (composer test:upgrade). The upgrade path only fires when the
# installed version is older than the new build, so we install this baseline
# first and then install the freshly-built package over it.
#
# The baseline is the ACTUAL released artifact (the zip users installed), pulled
# from its GitHub release — more faithful than rebuilding an old tree, and it
# guarantees the old install.sql (without the newer migrations) so the upgrade
# genuinely exercises the ADD COLUMN / CREATE TABLE update SQL.
#
# Version resolution: argument > current.version in build/versions.json.
# Output: build/dist/pkg_proclaim-<version>.zip
#
# @since __DEPLOY_VERSION__

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="${ROOT}/build/dist"

VERSION="${1:-}"
if [ -z "${VERSION}" ]; then
    VERSION="$(php -r '$v=json_decode(file_get_contents("'"${ROOT}"'/build/versions.json"),true); echo $v["current"]["version"] ?? "";')"
fi

if [ -z "${VERSION}" ]; then
    echo "ERROR: could not resolve baseline version (pass one as arg 1, or set current.version in build/versions.json)." >&2
    exit 1
fi

ASSET="pkg_proclaim-${VERSION}.zip"
TAG="v${VERSION}"
OUT="${DIST}/${ASSET}"

mkdir -p "${DIST}"

if [ -f "${OUT}" ]; then
    echo "Baseline already present: ${OUT}"
    exit 0
fi

if ! command -v gh >/dev/null 2>&1; then
    echo "ERROR: gh CLI not found — needed to download the ${TAG} release asset." >&2
    exit 1
fi

echo "Downloading baseline ${ASSET} from release ${TAG} ..."
gh release download "${TAG}" --pattern "${ASSET}" --dir "${DIST}" --clobber

if [ ! -f "${OUT}" ]; then
    echo "ERROR: ${ASSET} not found on release ${TAG}." >&2
    echo "Check the release assets: gh release view ${TAG}" >&2
    exit 1
fi

echo "Baseline ready: ${OUT}"
