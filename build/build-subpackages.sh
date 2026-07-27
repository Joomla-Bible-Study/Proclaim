#!/usr/bin/env bash
#
# Build the sibling extension packages that pkg_proclaim bundles, fresh from the
# current submodule checkouts, into each submodule's build/dist. Proclaim's
# cwm-build.config.json consumes the results via `prebuilt` includes.
#
# The two siblings are independent repos with different build shapes:
#   - lib_cwmscripture      → cwm-build   (component-shaped cwm-build.config.json)
#   - content/scripturelinks → cwm-package (package-shaped cwm-build.config.json)
#
# Both are driven with Proclaim's own build-tools binary (it reads the CWD's
# config), non-interactively, at each submodule's own manifest version. Their
# build/dist output is gitignored, so this leaves the submodules git-clean.
#
# @since __DEPLOY_VERSION__

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BIN="${ROOT}/libraries/vendor/bin"

export PATH="${BIN}:${PATH}"
export CWM_NONINTERACTIVE=1

LIB_DIR="${ROOT}/libraries/lib_cwmscripture"
PLG_DIR="${ROOT}/plugins/content/scripturelinks"
NESTED_LIB_DIR="${PLG_DIR}/libraries/lib_cwmscripture"

if [ ! -f "${LIB_DIR}/cwm-build.config.json" ] || [ ! -f "${PLG_DIR}/cwm-build.config.json" ]; then
    echo "ERROR: submodules not populated — run 'git submodule update --init --recursive' first." >&2
    exit 1
fi

# Build a submodule's npm assets when it has them. The min files are
# gitignored, so a fresh clone (CI, a new contributor) has none, and each
# submodule's ensure-minified gate rightly refuses to package without them.
# Local working trees usually carry them already — then this is just the
# submodule's incremental `npm run build`. npm ci only when node_modules is
# absent, so the local loop stays fast.
build_npm_assets() {
    local dir="$1"

    if [ ! -f "${dir}/package.json" ]; then
        return 0
    fi

    if [ ! -d "${dir}/node_modules" ]; then
        echo "   npm ci (${dir#"${ROOT}"/})"
        ( cd "${dir}" && npm ci --no-audit --no-fund --silent )
    fi

    ( cd "${dir}" && npm run --silent build )
}

echo "-- building lib_cwmscripture (npm + cwm-build)"
build_npm_assets "${LIB_DIR}"
( cd "${LIB_DIR}" && "${BIN}/cwm-build" )

# scripturelinks bundles its own nested lib_cwmscripture checkout as a
# `prebuilt` include — so that nested submodule must be built first, or a
# clean clone dies with "no zip matched" (or worse, silently bundles a
# stale zip a local tree happened to retain).
echo "-- building scripturelinks' nested lib_cwmscripture (npm + cwm-build)"
build_npm_assets "${NESTED_LIB_DIR}"
( cd "${NESTED_LIB_DIR}" && "${BIN}/cwm-build" )

echo "-- building content/scripturelinks (cwm-package)"
( cd "${PLG_DIR}" && "${BIN}/cwm-package" )

echo "Sub-packages built."
