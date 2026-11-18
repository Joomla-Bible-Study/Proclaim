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

echo "-- [1/12] reset test site(s) to a clean slate"
"$BIN/cwm-reset-testsite"
# The reset leaves Joomla's namespace map listing Proclaim, which a site that
# never had it would not, and that hid install steps that could not load
# Proclaim's classes. Joomla rebuilds the map from #__extensions when the file
# is missing, so the install below starts as cold as a new site's.
php -r 'require "libraries/vendor/autoload.php"; foreach ((new CWM\BuildTools\Dev\PropertiesReader("build.properties"))->installsFor("test") as $i) { @unlink($i->path . "/administrator/cache/autoload_psr4.php"); }'

echo "-- [2/12] build full package ${VERSION}"
bash build/build-package.sh "$VERSION"

if [ ! -f "$ZIP" ]; then
    echo "ERROR: expected build artifact not found: $ZIP" >&2
    exit 1
fi

echo "-- [3/12] install ${ZIP} (fresh)"
"$BIN/cwm-install-zip" --zip "$ZIP"

# Demo content is opt-in now (#2145/#2176) — install.mysql.utf8.sql no longer
# seeds a sample study. This imports the demo fixture through proclaim:import,
# the path the setup wizard uses, and aborts the run like reset/build/install
# above rather than joining the soft-fail block below: a broken import path is
# a broken install, not one finding among many.
echo "-- [4/12] seed demo content through proclaim:import (#2176)"
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

# A postflight step that fails is reported and the install carries on, so this
# is the only check that sees one.
echo "-- [5/12] verify the install reported what it did"
php build/verify-install-log.php install || FAILURES+=("install log (verify-install-log)")

echo "-- [6/12] verify extension registration"
"$BIN/cwm-verify" --target test || FAILURES+=("extension registration (cwm-verify)")

echo "-- [7/12] verify migrations landed"
php build/verify-migrations.php "$VERSION" || FAILURES+=("migrations (verify-migrations)")

echo "-- [8/12] verify the REST API landed (#1309/#1310/#1331 guards)"
php build/verify-api-install.php || FAILURES+=("REST API (verify-api-install)")

echo "-- [9/12] verify the scripture library landed (tables, seed, plugin enabled)"
php build/verify-scripture-install.php || FAILURES+=("scripture library (verify-scripture-install)")

echo "-- [10/12] seed the site menu items the front end is reached through (#1701)"
php build/seed/servers.php apply || FAILURES+=("server seeding (build/seed/servers.php)")
php build/seed/servers.php check || FAILURES+=("server seed check (build/seed/servers.php check)")
php build/seed/content.php apply || FAILURES+=("content seeding (build/seed/content.php)")
php build/seed/content.php check || FAILURES+=("content seed check (build/seed/content.php check)")
php build/seed/templates.php apply || FAILURES+=("template seeding (build/seed/templates.php)")
php build/seed/templates.php check || FAILURES+=("template seed check (build/seed/templates.php check)")
php build/seed/menus.php apply || FAILURES+=("menu seeding (build/seed/menus.php)")
php build/seed/modules.php apply || FAILURES+=("module seeding (build/seed/modules.php)")
php build/seed/modules.php check || FAILURES+=("module seed check (build/seed/modules.php check)")
php build/seed/plugins.php apply || FAILURES+=("plugin seeding (build/seed/plugins.php)")
php build/seed/plugins.php check || FAILURES+=("plugin seed check (build/seed/plugins.php check)")
php build/seed/accounts.php apply || FAILURES+=("account seeding (build/seed/accounts.php)")
php build/seed/accounts.php check || FAILURES+=("account seed check (build/seed/accounts.php check)")
php build/seed/tasks.php apply || FAILURES+=("scheduled tasks (build/seed/tasks.php)")
php build/seed/tasks.php check || FAILURES+=("scheduled task check (build/seed/tasks.php check)")
php build/seed/finder.php apply || FAILURES+=("finder indexing (build/seed/finder.php)")
php build/seed/finder.php check || FAILURES+=("finder index check (build/seed/finder.php check)")
# A fresh install has no verses until the Download Core Translations task runs,
# and the seeded study cites a book. Without this the front-end check below only
# ever exercises the unresolvable path.
php build/seed-scripture-fixture.php || FAILURES+=("scripture fixture (seed-scripture-fixture)")

echo "-- [11/12] verify the front end renders (#1701 guards)"
php build/verify-frontend.php || FAILURES+=("front end (verify-frontend)")

echo "-- [12/12] verify Joomla's own schema check is clean"
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
