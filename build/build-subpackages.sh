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

if [ ! -f "${LIB_DIR}/cwm-build.config.json" ] || [ ! -f "${PLG_DIR}/cwm-build.config.json" ]; then
    echo "ERROR: submodules not populated — run 'git submodule update --init --recursive' first." >&2
    exit 1
fi

echo "-- building lib_cwmscripture (cwm-build)"
( cd "${LIB_DIR}" && "${BIN}/cwm-build" )

echo "-- building content/scripturelinks (cwm-package)"
( cd "${PLG_DIR}" && "${BIN}/cwm-package" )

echo "Sub-packages built."
