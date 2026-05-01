#!/bin/bash
#
# Generate a Joomla changelog XML entry from a GitHub release and insert it
# into build/proclaim-changelog.xml.
#
# Parses the release body markdown into Joomla changelog types:
#   ### Fixes / ### Bug Fixes       -> <fix>
#   ### New Features / ### Features  -> <addition>
#   ### Changes / ### Changed        -> <change>
#   ### Security                     -> <security>
#   ### Removed                      -> <remove>
#   ### Language                     -> <language>
#   ### Notes / ### Upgrade notes    -> <note>
#
# Usage:
#   bash build/generate-changelog-entry.sh [version]
#   composer changelog -- [version]
#
# Options:
#   --dry-run   Print the entry to stdout without modifying the changelog file
#
# If version is omitted, reads from proclaim.xml.
#
# @since 10.3.0

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
CHANGELOG_FILE="$SCRIPT_DIR/proclaim-changelog.xml"

# --- Parse flags ---
DRY_RUN=false
VERSION=""

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=true ;;
        *)         VERSION="$arg" ;;
    esac
done

# --- Resolve version ---
if [ -z "$VERSION" ]; then
    VERSION=$(grep -oP '(?<=<version>)[^<]+' "$REPO_DIR/proclaim.xml" 2>/dev/null || echo "")
    if [ -z "$VERSION" ]; then
        echo "Error: Could not determine version. Pass it as an argument." >&2
        exit 1
    fi
fi

TAG="v${VERSION}"

# --- Check if entry already exists ---
if grep -q "<version>${VERSION}</version>" "$CHANGELOG_FILE" 2>/dev/null; then
    echo "Changelog entry for ${VERSION} already exists in $(basename "$CHANGELOG_FILE")." >&2
    exit 0
fi

# --- Fetch release notes from GitHub ---
BODY=$(gh release view "$TAG" --repo Joomla-Bible-Study/Proclaim --json body --jq '.body' 2>/dev/null || echo "")
RELEASE_DATE=$(gh release view "$TAG" --repo Joomla-Bible-Study/Proclaim --json publishedAt --jq '.publishedAt' 2>/dev/null | cut -dT -f1 || echo "")

if [ -z "$BODY" ]; then
    echo "Error: Could not fetch release notes for ${TAG}." >&2
    echo "Make sure the GitHub release exists: gh release view ${TAG}" >&2
    exit 1
fi

if [ -z "$RELEASE_DATE" ]; then
    RELEASE_DATE=$(date +%Y-%m-%d)
fi

# --- Parse markdown into changelog XML ---
# Uses Python for reliable markdown parsing. Body passed via environment variable
# since the heredoc consumes stdin.
export CHANGELOG_BODY="$BODY"
ENTRY=$(python3 - "$VERSION" "$RELEASE_DATE" <<'PYTHON_SCRIPT'
import os
import sys
import re
from html import escape

version = sys.argv[1]
date = sys.argv[2]
body = os.environ.get('CHANGELOG_BODY', '')

# Map markdown headings to Joomla changelog types
HEADING_MAP = {
    'fixes': 'fix',
    'fix': 'fix',
    'bug fixes': 'fix',
    'bug fix': 'fix',
    'new features': 'addition',
    'features': 'addition',
    'additions': 'addition',
    'added': 'addition',
    'changes': 'change',
    'changed': 'change',
    'modifications': 'change',
    'security': 'security',
    'security fixes': 'security',
    'removed': 'remove',
    'remove': 'remove',
    'deprecated': 'remove',
    'language': 'language',
    'translations': 'language',
    'notes': 'note',
    'note': 'note',
    'upgrade notes': 'note',
    'requirements': 'note',
    'testing': 'note',
}

# Parse sections
sections = {}
current_type = None
current_items = []

for line in body.split('\n'):
    line = line.strip()

    # Match ### or ## headings
    heading_match = re.match(r'^#{2,3}\s+(.+)', line)
    if heading_match:
        # Save previous section
        if current_type and current_items:
            sections.setdefault(current_type, []).extend(current_items)

        heading = heading_match.group(1).strip().rstrip(':').lower()
        # Remove version suffix like "v10.2.2 — Bug Fix Release"
        heading = re.sub(r'^v?\d+\.\d+\S*\s*[—–-]\s*', '', heading)
        heading = heading.strip().lower()

        current_type = HEADING_MAP.get(heading)
        current_items = []
        continue

    # Match list items (- or *)
    item_match = re.match(r'^[-*]\s+(.+)', line)
    if item_match and current_type:
        text = item_match.group(1).strip()
        # Strip markdown formatting
        text = re.sub(r'\*\*([^*]+)\*\*', r'\1', text)  # bold
        text = re.sub(r'`([^`]+)`', r'\1', text)         # inline code
        # Escape XML entities
        text = escape(text)
        current_items.append(text)

# Save last section
if current_type and current_items:
    sections.setdefault(current_type, []).extend(current_items)

# If no sections were parsed, put everything as <note>
if not sections:
    items = []
    for line in body.split('\n'):
        line = line.strip()
        item_match = re.match(r'^[-*]\s+(.+)', line)
        if item_match:
            text = re.sub(r'\*\*([^*]+)\*\*', r'\1', item_match.group(1).strip())
            text = re.sub(r'`([^`]+)`', r'\1', text)
            items.append(escape(text))
    if items:
        sections['note'] = items

# Output XML
# Desired order for readability
TYPE_ORDER = ['security', 'fix', 'addition', 'change', 'remove', 'language', 'note']

print(f'    <!-- ============================================================ -->')
print(f'    <!-- {version:<57s}-->')
print(f'    <!-- ============================================================ -->')
print(f'    <changelog>')
print(f'        <element>com_proclaim</element>')
print(f'        <type>component</type>')
print(f'        <version>{version}</version>')
print(f'        <date>{date}</date>')

for change_type in TYPE_ORDER:
    if change_type in sections:
        print(f'        <{change_type}>')
        for item in sections[change_type]:
            print(f'            <item>{item}</item>')
        print(f'        </{change_type}>')

print(f'    </changelog>')
PYTHON_SCRIPT
)

if [ -z "$ENTRY" ]; then
    echo "Error: Failed to generate changelog entry." >&2
    exit 1
fi

# --- Dry run: just print ---
if [ "$DRY_RUN" = true ]; then
    echo "$ENTRY"
    exit 0
fi

# --- Insert entry into changelog file (after <changelogs> opening tag) ---
# Uses Python to insert after the first line matching <changelogs>
export CHANGELOG_ENTRY="$ENTRY"
python3 - "$CHANGELOG_FILE" <<'INSERT_SCRIPT'
import os
import sys

changelog_file = sys.argv[1]
entry = os.environ.get('CHANGELOG_ENTRY', '')

with open(changelog_file, 'r') as f:
    content = f.read()

# Find the insertion point: right after <changelogs> and its trailing newline
marker = '<changelogs>'
pos = content.find(marker)
if pos == -1:
    print("Error: Could not find <changelogs> tag in changelog file.", file=sys.stderr)
    sys.exit(1)

insert_pos = content.index('\n', pos) + 1

new_content = content[:insert_pos] + '\n' + entry + '\n' + content[insert_pos:]

with open(changelog_file, 'w') as f:
    f.write(new_content)
INSERT_SCRIPT

echo "Changelog entry for ${VERSION} added to $(basename "$CHANGELOG_FILE")."
