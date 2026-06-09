#!/usr/bin/env bash
#
# sync-wiki-version.sh — keep the Proclaim wiki Home.md version table in sync
# with the actual released version + the active development version.
#
# WHY: `composer release` (cwm-release) updates versions.json / package.json /
# manifests, but never touches the wiki (a separate repo). The Home.md table
# silently drifted three releases behind (showed 10.2.0 when 10.3.2 had shipped)
# because nothing automated it and no checklist named it. This is the missing
# release step. Run it right after `composer release`.
#
# Sources of truth (branch-independent on purpose):
#   - Released version + date : the highest semver git tag and its commit date
#   - Development version      : active_development.version in build/versions.json
#                                on origin/development (where release step 8
#                                commits it). Falls back to next.patch when
#                                active_development still equals the released
#                                version (i.e. immediately post-release, before
#                                the next `composer bump`).
#
# Usage:
#   composer release:wiki            # update + commit + push the wiki
#   composer release:wiki -- --dry-run   # print the new rows, change nothing
#
# Wiki location resolution: $PROCLAIM_WIKI_DIR  >  $1 (if not a flag)  >  ../Proclaim.wiki
#
# Idempotent: if Home.md already matches, it reports "already current" and exits 0.
# Safe in headless/CI: if the wiki clone is absent, it warns and exits 0.

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
VERSIONS_JSON="${REPO_ROOT}/build/versions.json"
DEV_BRANCH="development"

DRY_RUN=0
WIKI_ARG=""
for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=1 ;;
        --*)       echo "Unknown flag: $arg" >&2; exit 2 ;;
        *)         WIKI_ARG="$arg" ;;
    esac
done

# --- Resolve wiki directory ---
WIKI_DIR="${PROCLAIM_WIKI_DIR:-${WIKI_ARG:-${REPO_ROOT}/../Proclaim.wiki}}"
if [ ! -d "$WIKI_DIR/.git" ]; then
    echo "⚠  Wiki clone not found at: $WIKI_DIR"
    echo "   (set PROCLAIM_WIKI_DIR or pass the path as an argument)"
    echo "   Skipping wiki sync — nothing to do."
    exit 0
fi
WIKI_DIR="$(cd "$WIKI_DIR" && pwd)"
HOME_MD="${WIKI_DIR}/Home.md"
if [ ! -f "$HOME_MD" ]; then
    echo "⚠  $HOME_MD not found — skipping wiki sync." >&2
    exit 0
fi

# --- Released version + date from the highest semver tag ---
RELEASE_TAG="$(git -C "$REPO_ROOT" tag --sort=-v:refname | head -1)"
if [ -z "$RELEASE_TAG" ]; then
    echo "⚠  No git tags found — cannot determine released version. Skipping." >&2
    exit 0
fi
RELEASE_VERSION="${RELEASE_TAG#v}"
RELEASE_DATE_ISO="$(git -C "$REPO_ROOT" log -1 --format=%cs "$RELEASE_TAG")"

# --- Development version from versions.json on origin/development ---
git -C "$REPO_ROOT" fetch --quiet origin "$DEV_BRANCH" 2>/dev/null || true
VERSIONS_CONTENT="$(git -C "$REPO_ROOT" show "origin/${DEV_BRANCH}:build/versions.json" 2>/dev/null || cat "$VERSIONS_JSON")"

# --- Compute the rows + rewrite Home.md (python3: robust JSON + date + table edit) ---
# python prints ROW_MAIN= / ROW_DEV= / STATUS= lines to stdout and always exits 0;
# bash parses them. STATUS is one of: unchanged | would-change | changed | error.
RESULT="$(
    RELEASE_VERSION="$RELEASE_VERSION" \
    RELEASE_DATE_ISO="$RELEASE_DATE_ISO" \
    VERSIONS_CONTENT="$VERSIONS_CONTENT" \
    HOME_MD="$HOME_MD" \
    DRY_RUN="$DRY_RUN" \
    python3 - <<'PY'
import os, json, datetime

release_version = os.environ["RELEASE_VERSION"]
release_iso     = os.environ["RELEASE_DATE_ISO"]
versions        = json.loads(os.environ["VERSIONS_CONTENT"])
home_md         = os.environ["HOME_MD"]
dry_run         = os.environ["DRY_RUN"] == "1"

# Released date -> "May 9, 2026" (abbrev month, no leading-zero day)
d = datetime.date.fromisoformat(release_iso)
release_date = f"{d.strftime('%b')} {d.day}, {d.year}"

current   = (versions.get("current") or {}).get("version")
active    = (versions.get("active_development") or {}).get("version")
nextpatch = (versions.get("next") or {}).get("patch")

# Development row: prefer active_development, but if it still equals the just
# released version (pre next-bump), show next.patch as the upcoming target.
dev_version = active
if active and current and active == current:
    dev_version = nextpatch or active
if not dev_version:
    dev_version = nextpatch or active or current or "-"

with open(home_md, encoding="utf-8") as fh:
    lines = fh.readlines()

def split_row(line):
    # "| Main | 10.3.2 | May 9, 2026 | 5.1+ / 6.0 | 8.3.0+ |" -> cells list
    return [c.strip() for c in line.strip().strip("|").split("|")]

changed = False
new_rows = {}
for i, line in enumerate(lines):
    if not line.strip().startswith("|"):
        continue
    cells = split_row(line)
    if len(cells) < 5:
        continue
    label = cells[0]
    if label == "Main":
        rebuilt = f"| Main | {release_version} | {release_date} | {cells[3]} | {cells[4]} |\n"
    elif label == "Development":
        rebuilt = f"| Development | {dev_version} | - | {cells[3]} | {cells[4]} |\n"
    else:
        continue
    new_rows[label] = rebuilt.strip()
    if rebuilt != line:
        changed = True
        lines[i] = rebuilt

print("ROW_MAIN=" + new_rows.get("Main", "(Main row not found)"))
print("ROW_DEV="  + new_rows.get("Development", "(Development row not found)"))

if "Main" not in new_rows or "Development" not in new_rows:
    print("STATUS=error")
elif not changed:
    print("STATUS=unchanged")
elif dry_run:
    print("STATUS=would-change")
else:
    with open(home_md, "w", encoding="utf-8") as fh:
        fh.writelines(lines)
    print("STATUS=changed")
PY
)"

# Surface the resolved rows + status
printf '%s\n' "$RESULT" | sed -n 's/^ROW_MAIN=/  Main:        /p; s/^ROW_DEV=/  Development: /p'
STATUS="$(printf '%s\n' "$RESULT" | sed -n 's/^STATUS=//p')"

case "$STATUS" in
    unchanged)
        echo "✓ Home.md version table already current ($RELEASE_VERSION released, dev ahead). Nothing to do."
        exit 0
        ;;
    would-change)
        echo "ℹ [dry-run] Home.md would be updated to the rows above. No changes written."
        exit 0
        ;;
    changed)
        : # fall through to commit
        ;;
    error)
        echo "✗ Could not locate the Main/Development rows in $HOME_MD — table format changed?" >&2
        exit 1
        ;;
    *)
        echo "✗ Unexpected status from rewrite step: '$STATUS'" >&2
        exit 1
        ;;
esac

# --- Commit + push the wiki ---
echo "→ Updating wiki at $WIKI_DIR"
git -C "$WIKI_DIR" pull --quiet --ff-only 2>/dev/null || true
git -C "$WIKI_DIR" add Home.md
git -C "$WIKI_DIR" commit --quiet -m "docs(home): sync version table for ${RELEASE_TAG} release

Released ${RELEASE_VERSION} (${RELEASE_DATE_ISO}); development line set from versions.json.
Automated by build/sync-wiki-version.sh."
git -C "$WIKI_DIR" push --quiet
echo "✓ Wiki Home.md version table synced and pushed for ${RELEASE_TAG}."