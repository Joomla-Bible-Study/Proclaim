#!/bin/bash
#
# Publish a Proclaim release to Akeeba Release System on christianwebministries.org
#
# Usage: bash build/ars-release.sh [version]
#   version: e.g. "10.2.1" (without "v" prefix). If omitted, reads from proclaim.xml.
#
# Prerequisites:
#   - 1Password CLI (op) authenticated
#   - "CWM ARS API Token" item in CWM vault
#   - GitHub release already created with zip attached
#   - ARS webservices plugin enabled on CWM site
#
# @since 10.2.1

set -euo pipefail

SITE_URL="https://www.christianwebministries.org"
API_BASE="${SITE_URL}/api/index.php/v1/ars"
ARS_CATEGORY_ID=1
ARS_UPDATE_STREAM_ID=2
ZIP_PREFIX="com_proclaim"

# ARS environment IDs for Proclaim 10.x
# Joomla 5.x=45, 6.x=46, PHP 8.3=48, PHP 8.4=49, PHP 8.5=50
ARS_ENVIRONMENTS='["45","46","48","49","50"]'

# --- Resolve version ---
if [ -n "${1:-}" ]; then
    VERSION="$1"
else
    VERSION=$(grep -oP '(?<=<version>)[^<]+' proclaim.xml 2>/dev/null || echo "")
    if [ -z "$VERSION" ]; then
        echo "Error: Could not determine version. Pass it as an argument or run from repo root."
        exit 1
    fi
fi

TAG="v${VERSION}"
ALIAS=$(echo "proclaim-${VERSION}" | tr '.' '-')
ZIP_NAME="${ZIP_PREFIX}-${VERSION}.zip"
GITHUB_DOWNLOAD_URL="https://github.com/Joomla-Bible-Study/Proclaim/releases/download/${TAG}/${ZIP_NAME}"

# Determine ARS maturity from version string
# beta/beta1/beta2 → beta, rc/rc1/rc2 → rc, alpha → alpha, else → stable
if [[ "$VERSION" == *-alpha* ]]; then
    ARS_MATURITY="alpha"
elif [[ "$VERSION" == *-beta* ]]; then
    ARS_MATURITY="beta"
elif [[ "$VERSION" == *-rc* ]]; then
    ARS_MATURITY="rc"
else
    ARS_MATURITY="stable"
fi

echo "Publishing Proclaim ${VERSION} to ARS (maturity: ${ARS_MATURITY})..."
echo "  GitHub release: ${TAG}"
echo "  Download URL:   ${GITHUB_DOWNLOAD_URL}"

# --- Get API token from 1Password ---
echo "Retrieving API token from 1Password..."
TOKEN=$(op item get "CWM ARS API Token" --vault CWM --fields label=credential --reveal 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo "Error: Could not retrieve API token from 1Password."
    echo "Make sure 'CWM ARS API Token' exists in the CWM vault."
    exit 1
fi

# --- Verify GitHub release exists ---
echo "Verifying GitHub release ${TAG}..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://api.github.com/repos/Joomla-Bible-Study/Proclaim/releases/tags/${TAG}")

if [ "$HTTP_CODE" != "200" ]; then
    echo "Error: GitHub release ${TAG} not found (HTTP ${HTTP_CODE})."
    echo "Create the release first: gh release create ${TAG} build/${ZIP_NAME}"
    exit 1
fi

# --- Get file size and checksums from GitHub ---
echo "Fetching release asset info from GitHub..."
ASSET_INFO=$(gh release view "$TAG" --repo Joomla-Bible-Study/Proclaim --json assets --jq ".assets[] | select(.name==\"${ZIP_NAME}\")")

if [ -z "$ASSET_INFO" ]; then
    echo "Error: Asset ${ZIP_NAME} not found in GitHub release ${TAG}."
    exit 1
fi

FILESIZE=$(echo "$ASSET_INFO" | python3 -c "import json,sys; print(json.load(sys.stdin)['size'])" 2>/dev/null || echo "0")

# Compute checksums from local build if available
MD5=""
SHA1=""
SHA256=""
SHA384=""
SHA512=""

if [ -f "build/${ZIP_NAME}" ]; then
    echo "Computing checksums from local build..."
    MD5=$(md5 -q "build/${ZIP_NAME}" 2>/dev/null || md5sum "build/${ZIP_NAME}" 2>/dev/null | cut -d' ' -f1 || echo "")
    SHA1=$(shasum -a 1 "build/${ZIP_NAME}" 2>/dev/null | cut -d' ' -f1 || echo "")
    SHA256=$(shasum -a 256 "build/${ZIP_NAME}" 2>/dev/null | cut -d' ' -f1 || echo "")
    SHA384=$(shasum -a 384 "build/${ZIP_NAME}" 2>/dev/null | cut -d' ' -f1 || echo "")
    SHA512=$(shasum -a 512 "build/${ZIP_NAME}" 2>/dev/null | cut -d' ' -f1 || echo "")
fi

# --- Get GitHub release notes ---
echo "Fetching release notes from GitHub..."
RELEASE_NOTES=$(gh release view "$TAG" --repo Joomla-Bible-Study/Proclaim --json body --jq '.body' 2>/dev/null || echo "")

# --- Check if ARS release already exists ---
echo "Checking for existing ARS release..."
EXISTING=$(curl -s \
    -H "X-Joomla-Token: ${TOKEN}" \
    -H "Accept: application/vnd.api+json" \
    "${API_BASE}/releases?filter%5Bcategory_id%5D=${ARS_CATEGORY_ID}&filter%5Bsearch%5D=${VERSION}")

EXISTING_ID=$(echo "$EXISTING" | python3 -c "
import json,sys
d=json.load(sys.stdin)
for r in d.get('data',[]):
    if r['attributes']['version'] == '${VERSION}':
        print(r['attributes']['id'])
        break
" 2>/dev/null || echo "")

if [ -n "$EXISTING_ID" ]; then
    echo "ARS release already exists (ID: ${EXISTING_ID}). Updating..."
    RELEASE_ID="$EXISTING_ID"

    # Update existing release
    curl -s -X PATCH \
        -H "X-Joomla-Token: ${TOKEN}" \
        -H "Accept: application/vnd.api+json" \
        -H "Content-Type: application/json" \
        -d "{
            \"id\": ${RELEASE_ID},
            \"category_id\": ${ARS_CATEGORY_ID},
            \"version\": \"${VERSION}\",
            \"alias\": \"${ALIAS}\",
            \"maturity\": \"${ARS_MATURITY}\",
            \"notes\": $(echo "$RELEASE_NOTES" | python3 -c 'import json,sys; print(json.dumps(sys.stdin.read()))'),
            \"published\": 1
        }" \
        "${API_BASE}/releases/${RELEASE_ID}" > /dev/null

    echo "Release updated."
else
    echo "Creating new ARS release..."
    RESPONSE=$(curl -s -X POST \
        -H "X-Joomla-Token: ${TOKEN}" \
        -H "Accept: application/vnd.api+json" \
        -H "Content-Type: application/json" \
        -d "{
            \"category_id\": ${ARS_CATEGORY_ID},
            \"version\": \"${VERSION}\",
            \"alias\": \"${ALIAS}\",
            \"maturity\": \"${ARS_MATURITY}\",
            \"notes\": $(echo "$RELEASE_NOTES" | python3 -c 'import json,sys; print(json.dumps(sys.stdin.read()))'),
            \"published\": 1,
            \"access\": 1,
            \"show_unauth_links\": 0,
            \"redirect_unauth\": \"\",
            \"language\": \"*\"
        }" \
        "${API_BASE}/releases")

    RELEASE_ID=$(echo "$RESPONSE" | python3 -c "import json,sys; print(json.load(sys.stdin)['data']['attributes']['id'])" 2>/dev/null || echo "")

    if [ -z "$RELEASE_ID" ]; then
        echo "Error: Failed to create ARS release."
        echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
        exit 1
    fi

    echo "Release created (ID: ${RELEASE_ID})."
fi

# --- Create download item ---
echo "Adding download item..."

# Check if item already exists for this release
EXISTING_ITEM=$(curl -s \
    -H "X-Joomla-Token: ${TOKEN}" \
    -H "Accept: application/vnd.api+json" \
    "${API_BASE}/items?filter%5Brelease_id%5D=${RELEASE_ID}")

EXISTING_ITEM_ID=$(echo "$EXISTING_ITEM" | python3 -c "
import json,sys
d=json.load(sys.stdin)
for i in d.get('data',[]):
    if i['attributes'].get('url','').endswith('${ZIP_NAME}'):
        print(i['attributes']['id'])
        break
" 2>/dev/null || echo "")

ITEM_PAYLOAD="{
    \"release_id\": ${RELEASE_ID},
    \"title\": \"${ZIP_NAME%.zip}\",
    \"alias\": \"${ZIP_NAME%.zip}\",
    \"description\": \"\",
    \"type\": \"link\",
    \"url\": \"${GITHUB_DOWNLOAD_URL}\",
    \"updatestream\": ${ARS_UPDATE_STREAM_ID},
    \"md5\": \"${MD5}\",
    \"sha1\": \"${SHA1}\",
    \"sha256\": \"${SHA256}\",
    \"sha384\": \"${SHA384}\",
    \"sha512\": \"${SHA512}\",
    \"filesize\": ${FILESIZE},
    \"published\": 1,
    \"access\": 1,
    \"show_unauth_links\": 0,
    \"redirect_unauth\": \"\",
    \"language\": \"*\",
    \"environments\": ${ARS_ENVIRONMENTS}
}"

if [ -n "$EXISTING_ITEM_ID" ]; then
    echo "Item already exists (ID: ${EXISTING_ITEM_ID}). Updating..."
    curl -s -X PATCH \
        -H "X-Joomla-Token: ${TOKEN}" \
        -H "Accept: application/vnd.api+json" \
        -H "Content-Type: application/json" \
        -d "$ITEM_PAYLOAD" \
        "${API_BASE}/items/${EXISTING_ITEM_ID}" > /dev/null
    echo "Item updated."
else
    ITEM_RESPONSE=$(curl -s -X POST \
        -H "X-Joomla-Token: ${TOKEN}" \
        -H "Accept: application/vnd.api+json" \
        -H "Content-Type: application/json" \
        -d "$ITEM_PAYLOAD" \
        "${API_BASE}/items")

    ITEM_ID=$(echo "$ITEM_RESPONSE" | python3 -c "import json,sys; print(json.load(sys.stdin)['data']['attributes']['id'])" 2>/dev/null || echo "")

    if [ -z "$ITEM_ID" ]; then
        echo "Error: Failed to create download item."
        echo "$ITEM_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$ITEM_RESPONSE"
        exit 1
    fi

    echo "Download item created (ID: ${ITEM_ID})."
fi

echo ""
echo "Done! Proclaim ${VERSION} published to ARS."
echo "  ARS Release: ${SITE_URL}/index.php?option=com_ars&view=items&release_id=${RELEASE_ID}"
echo "  GitHub:      https://github.com/Joomla-Bible-Study/Proclaim/releases/tag/${TAG}"
