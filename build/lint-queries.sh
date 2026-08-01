#!/bin/sh
#
# Reject $db->getQuery(true) in favour of $db->createQuery().
#
# Both return the same DatabaseQuery and neither is deprecated in Joomla 5 or 6,
# so this is a consistency guard rather than a correctness one. It exists because
# consistency does not hold on its own: the codebase reached 666 uses of the old
# spelling against 9 of the documented one (#1394), simply because new code copies
# what it finds nearby. Without a check, it grows back.
#
# Only the no-argument form is legal here. `getQuery()` returns the *current*
# query rather than a new one — a different operation, and deliberately untouched.
#
# The scripturelinks submodule is a separate repository and is not our standard
# to enforce.
#
# Usage: sh build/lint-queries.sh
# Exit:  0 clean, 1 offenders found.

set -eu

PATHS='admin api site modules plugins'
NEEDLE='->getQuery(true)'

# grep exits 1 when it finds nothing, which is the success case here.
if matches=$(grep -rn --include='*.php' --exclude-dir=scripturelinks -F -- "$NEEDLE" $PATHS 2>/dev/null); then
    count=$(printf '%s\n' "$matches" | wc -l | tr -d ' ')

    printf '%s\n' "$matches"
    printf '\n'
    printf 'Found %s use(s) of getQuery(true).\n' "$count" >&2
    printf 'Build queries with $db->createQuery() instead — see CLAUDE.md and #1394.\n' >&2
    printf 'The no-argument $db->getQuery() is a different operation and is allowed.\n' >&2

    exit 1
fi

echo 'No getQuery(true) found.'
