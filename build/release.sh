#!/bin/bash
#
# Full Proclaim release workflow.
#
# Runs on the current branch (should be main). Handles:
#   1. Version bump (interactive if no version given)
#   2. Build zip package
#   3. Commit version bump and push
#   4. Create GitHub release with zip
#   5. Publish to ARS on christianwebministries.org
#   6. Update versions.json on development branch
#
# Usage:
#   composer release -- 10.2.3              # Release specific version
#   composer release -- 10.3.0-beta1        # Pre-release
#   composer release                        # Prompted for version
#
# Prerequisites:
#   - On main branch with clean working tree
#   - 1Password CLI (op) authenticated
#   - gh CLI authenticated
#
# @since 10.2.2

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_DIR"

# --- Pre-checks ---
BRANCH=$(git branch --show-current)
if [ "$BRANCH" != "main" ]; then
    echo "Error: Must be on main branch (currently on '$BRANCH')."
    echo "Switch with: git checkout main && git pull"
    exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
    echo "Error: Working tree is not clean. Commit or stash changes first."
    exit 1
fi

# --- Get version ---
if [ -n "${1:-}" ]; then
    VERSION="$1"
else
    CURRENT=$(grep -oP '(?<=<version>)[^<]+' proclaim.xml 2>/dev/null || echo "unknown")
    echo "Current version: ${CURRENT}"
    printf "Enter new version (e.g., 10.2.3): "
    read -r VERSION
fi

if [ -z "$VERSION" ]; then
    echo "Error: No version provided."
    exit 1
fi

if [[ "$VERSION" == *-dev* ]]; then
    echo "Error: Development versions cannot be released. Use -alpha, -beta, or -rc for testing."
    exit 1
fi

TAG="v${VERSION}"
ZIP_NAME="com_proclaim-${VERSION}.zip"

# Check if this is a pre-release
PRERELEASE_FLAG=""
if [[ "$VERSION" == *-* ]]; then
    PRERELEASE_FLAG="--prerelease"
fi

echo ""
echo "=== Proclaim Release ${VERSION} ==="
echo ""

# --- Step 1: Version bump ---
echo "[1/7] Bumping version to ${VERSION}..."
composer version -- -v "$VERSION"
echo ""

# --- Step 2: Build ---
echo "[2/7] Building package..."
composer build
echo ""

if [ ! -f "build/${ZIP_NAME}" ]; then
    echo "Error: Build failed — build/${ZIP_NAME} not found."
    exit 1
fi

# --- Step 3: Commit and push ---
echo "[3/7] Committing version bump..."
git add -A
git commit -m "chore: bump version to ${VERSION}"
git push
echo ""

# --- Step 4: GitHub release ---
echo "[4/7] Creating GitHub release ${TAG}..."

# Get release notes from the latest commits since last tag
PREV_TAG=$(git describe --tags --abbrev=0 HEAD~1 2>/dev/null || echo "")
if [ -n "$PREV_TAG" ]; then
    NOTES=$(gh api repos/Joomla-Bible-Study/Proclaim/releases/generate-notes \
        -f tag_name="$TAG" -f target_commitish=main -f previous_tag_name="$PREV_TAG" \
        --jq '.body' 2>/dev/null || echo "Release ${VERSION}")
else
    NOTES="Release ${VERSION}"
fi

gh release create "$TAG" "build/${ZIP_NAME}" \
    --repo Joomla-Bible-Study/Proclaim \
    --target main \
    --title "${TAG}" \
    --notes "$NOTES" \
    $PRERELEASE_FLAG

echo ""

# --- Step 5: Generate changelog entry ---
echo "[5/7] Updating changelog..."
bash build/generate-changelog-entry.sh "$VERSION"
if git diff --quiet build/proclaim-changelog.xml 2>/dev/null; then
    echo "  (no changes — entry already existed)"
else
    git add build/proclaim-changelog.xml
    git commit -m "chore: add changelog entry for ${VERSION}"
    git push
    # Update the tag to include the changelog commit
    git tag -f "$TAG"
    git push origin "$TAG" --force
fi
echo ""

# --- Step 6: Publish to ARS ---
echo "[6/7] Publishing to ARS..."
bash build/ars-release.sh "$VERSION"
echo ""

# --- Step 7: Update development versions.json ---
echo "[7/7] Updating development branch..."

# Parse version parts for next patch
IFS='.' read -r MAJOR MINOR PATCH <<< "${VERSION%%-*}"
NEXT_PATCH="${MAJOR}.${MINOR}.$((PATCH + 1))"

git stash 2>/dev/null || true
git checkout development
git pull

# Update versions.json
python3 -c "
import json
with open('build/versions.json', 'r') as f:
    v = json.load(f)
v['_updated'] = '$(date +%Y-%m-%d)'
v['current']['version'] = '${VERSION}'
v['next']['patch'] = '${NEXT_PATCH}'
with open('build/versions.json', 'w') as f:
    json.dump(v, f, indent=4)
    f.write('\n')
"

git add build/versions.json
git commit -m "chore: update versions.json for ${TAG} release"
git push

git checkout main
git stash pop 2>/dev/null || true

echo ""
echo "=== Release ${VERSION} complete! ==="
echo "  GitHub: https://github.com/Joomla-Bible-Study/Proclaim/releases/tag/${TAG}"
echo "  ARS:    https://www.christianwebministries.org/downloads/proclaim.html"
echo ""
echo "Don't forget to:"
echo "  - Update wiki release notes (What's-New-${MAJOR}.${MINOR}.md)"
echo "  - Comment on related GitHub Discussions"
