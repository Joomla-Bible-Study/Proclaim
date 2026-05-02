#!/bin/bash
#
# Post a "Proclaim X.Y.Z Released" announcement to christianwebministries.org
# in the standard CWM article format and feature it on the front page,
# automatically un-featuring the previous release announcement.
#
# Usage:
#   bash build/cwm-article.sh                       # version from proclaim.xml
#   bash build/cwm-article.sh 10.3.1                # specific version
#   bash build/cwm-article.sh 10.3.1 path/to/bullets.txt
#
# Optional env vars:
#   CATEGORY_ID=2         # com_content category for the new article
#                         # default 2 = "General News and Information" on
#                         # christianwebministries.org
#   PREV_ARTICLE_ID=42    # explicit previous article to un-feature
#                         # (otherwise auto-detected via featured filter)
#   BULLETS_FILE=...      # one bullet per line; alternative to positional arg.
#                         # Markdown bold (**word**) and links (<url>) supported.
#   LEAD="…"              # one-line lead override (replaces "now available for
#                         # production use!" wording — useful for hotfixes)
#   DRY_RUN=1             # print the would-be article HTML and the JSON
#                         # payloads, but do not call the API or 1Password
#
# Bullets file format (plain text, one bullet per line, blank lines ignored):
#     Visual layout editor and redesigned scripture entry
#     YouTube OAuth integration and quota management
#     **Media chapters** and per-media description copying
#
# If neither a positional bullets path nor BULLETS_FILE is supplied, the
# script reads bullets from stdin.
#
# Prerequisites:
#   - 1Password CLI (op) authenticated
#   - "CWM ARS API Token" item in CWM vault — same Joomla API token used
#     by ars-release.sh; no separate token needed for the content API
#   - GitHub release for the version exists (used for the download link)
#   - jq + gh CLI installed
#
# Setup (one-time, on christianwebministries.org):
#   The 1Password item is shared with ars-release.sh, so if ARS publish
#   already works for you, the token is already in place. Just make sure:
#   1. "Web Services - Content" plugin is enabled (System → Manage → Plugins).
#   2. The user that owns the API token has Edit / Edit State / Create
#      permission on com_content articles (Super Users do by default).
#   3. Pick a category for release announcements; pass id via CATEGORY_ID.
#
# @since 10.3.2

set -euo pipefail

SITE_URL="https://www.christianwebministries.org"
ARTICLES_URL="${SITE_URL}/api/index.php/v1/content/articles"
GITHUB_REPO="Joomla-Bible-Study/Proclaim"
GITHUB_BASE="https://github.com/${GITHUB_REPO}"

CATEGORY_ID="${CATEGORY_ID:-2}"   # 2 = "General News and Information" on christianwebministries.org

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

# Major version for "What's new in Proclaim 10:" heading
MAJOR="${VERSION%%.*}"
TAG="v${VERSION}"
ALIAS=$(echo "proclaim-${VERSION}" | tr '.' '-')
TITLE="Proclaim ${VERSION} Released"
LEAD="${LEAD:-now available for production use!}"

DOWNLOAD_URL="${GITHUB_BASE}/releases/tag/${TAG}"
ISSUES_URL="${GITHUB_BASE}/issues"

# --- Resolve bullets source ---
BULLETS_PATH="${2:-${BULLETS_FILE:-}}"

if [ -n "$BULLETS_PATH" ]; then
    if [ ! -f "$BULLETS_PATH" ]; then
        echo "Error: bullets file not found: $BULLETS_PATH"
        exit 1
    fi
    RAW_BULLETS=$(cat "$BULLETS_PATH")
else
    if [ -t 0 ]; then
        echo "Error: no bullets provided. Pass a file path or pipe bullets on stdin."
        echo "Example:"
        echo "  bash build/cwm-article.sh ${VERSION} release-bullets.txt"
        exit 1
    fi
    RAW_BULLETS=$(cat)
fi

if [ -z "$(echo "$RAW_BULLETS" | tr -d '[:space:]')" ]; then
    echo "Error: bullets source is empty."
    exit 1
fi

echo "Posting CWM release article for Proclaim ${VERSION}..."
echo "  Title:    ${TITLE}"
echo "  Alias:    ${ALIAS}"
echo "  Category: ${CATEGORY_ID}"
echo "  Tag:      ${TAG}"
echo ""

# --- Tooling check ---
REQUIRED_TOOLS=(jq curl)
[ "${DRY_RUN:-0}" != "1" ] && REQUIRED_TOOLS+=(op gh)

for cmd in "${REQUIRED_TOOLS[@]}"; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        echo "Error: '$cmd' not found in PATH."
        exit 1
    fi
done

# --- API token ---
if [ "${DRY_RUN:-0}" = "1" ]; then
    TOKEN="(dry-run-no-token)"
    echo "DRY RUN: skipping 1Password lookup."
else
    echo "Retrieving Joomla API token from 1Password..."
    # `|| true` so a non-zero op exit doesn't abort the script via set -e
    # before we can print the friendly diagnostic below.
    TOKEN=$(op item get "CWM ARS API Token" --vault CWM --fields label=credential --reveal 2>&1 || true)

    if [ -z "$TOKEN" ] || echo "$TOKEN" | grep -q '\[ERROR\]'; then
        echo ""
        echo "Error: Could not retrieve API token from 1Password."
        if echo "$TOKEN" | grep -q '\[ERROR\]'; then
            echo "1Password said: ${TOKEN}"
        fi
        echo ""
        echo "This script reuses the same 1Password item as ars-release.sh."
        echo "If ARS publish works for you, the token should already exist."
        echo "Item lookup: vault 'CWM', title 'CWM ARS API Token', field 'credential'."
        echo "Re-run after fixing: composer cwm-article -- ${VERSION} ${BULLETS_PATH:-bullets.txt}"
        exit 1
    fi
fi

AUTH_HEADER="Authorization: Bearer ${TOKEN}"
JSON_HEADER="Content-Type: application/json"
ACCEPT_HEADER="Accept: application/vnd.api+json"

# --- HTML helpers ---
# Escape <, >, & for safe inclusion as text content
html_escape() {
    sed 's/&/\&amp;/g; s/</\&lt;/g; s/>/\&gt;/g'
}

# Convert one-line markdown to inline HTML:
#   **text**       → <strong>text</strong>
#   <https://…>    → <a href="…">…</a>
#   bare https://… → <a href="…">…</a>
inline_md() {
    local s="$1"
    # **bold**
    s=$(echo "$s" | sed -E 's@\*\*([^*]+)\*\*@<strong>\1</strong>@g')
    # <https://…>  → keep as proper link
    s=$(echo "$s" | sed -E 's@<(https?://[^>]+)>@<a href="\1">\1</a>@g')
    # bare URLs not already inside an anchor — best-effort
    s=$(echo "$s" | sed -E 's@(^|[[:space:]])(https?://[^[:space:]<]+)@\1<a href="\2">\2</a>@g')
    echo "$s"
}

# Build <li>…</li> entries from RAW_BULLETS (one per non-empty line)
build_li() {
    local out=""
    while IFS= read -r line; do
        # Trim leading dashes/spaces and a leading "- " marker if present
        line=$(echo "$line" | sed -E 's@^[[:space:]]*[-*][[:space:]]+@@; s@^[[:space:]]+@@; s@[[:space:]]+$@@')
        [ -z "$line" ] && continue
        # Escape HTML, then re-apply minimal markdown (escape happens BEFORE inline_md
        # would otherwise reintroduce angle-bracket links — so escape only here for text).
        local esc
        esc=$(printf '%s' "$line" | html_escape)
        local rendered
        rendered=$(inline_md "$esc")
        out+="<li>${rendered}</li>"$'\n'
    done <<< "$RAW_BULLETS"
    printf '%s' "$out"
}

LIST_ITEMS=$(build_li)

# --- Compose article HTML (mirrors the 10.2.2 announcement structure) ---
# Everything goes in introtext so the article shows in full on category /
# featured listings without a "Read More" button. fulltext stays empty.
INTRO_HTML=$(cat <<HTML
<p>The Christian Web Ministries Team is proud to announce <strong>Proclaim ${VERSION}</strong> — ${LEAD}</p>
<p><strong>What&#39;s new in Proclaim ${MAJOR}:</strong></p>
<ul>
${LIST_ITEMS}</ul>
<p>You can download the latest release here: <a href="${DOWNLOAD_URL}">${DOWNLOAD_URL}</a></p>
<p>Report issues on GitHub here: <a href="${ISSUES_URL}">${ISSUES_URL}</a></p>
<p>Visit the Documentation area for helpful articles and videos. We will continue to work on new articles as time permits.</p>
<p>May God richly bless you as you continue to spread the gospel and build Christians using this tool.</p>
<p>The CWM team (Brent and Tom)</p>
HTML
)

FULL_HTML=""

# --- Helper: PATCH article ---
api_patch() {
    local id="$1"
    local payload="$2"
    curl -sS -g -w "\n%{http_code}" \
        -X PATCH \
        -H "$AUTH_HEADER" -H "$JSON_HEADER" -H "$ACCEPT_HEADER" \
        "${ARTICLES_URL}/${id}" \
        -d "$payload"
}

# --- Build the POST payload up front (used for both dry-run preview and live POST) ---
POST_PAYLOAD=$(jq -n \
    --arg title    "$TITLE" \
    --arg alias    "$ALIAS" \
    --arg intro    "$INTRO_HTML" \
    --arg fulltext "$FULL_HTML" \
    --argjson catid "$CATEGORY_ID" \
    '{
        title: $title,
        alias: $alias,
        catid: $catid,
        introtext: $intro,
        fulltext: $fulltext,
        featured: 1,
        state: 1,
        language: "*",
        access: 1,
        publish_up: "2000-01-01 00:00:00",
        publish_down: null
    }')

if [ "${DRY_RUN:-0}" = "1" ]; then
    PREVIEW_FILE="${PREVIEW_FILE:-build/cwm-article-preview-${VERSION}.html}"

    cat > "$PREVIEW_FILE" <<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview — ${TITLE}</title>
    <!--
      Minimal CSS that approximates the typical Joomla article rendering
      (Cassiopeia-ish): readable serif body, generous line-height, neutral
      link colour, comfortable max-width. Adjust if your CWM template has
      a notably different look.
    -->
    <style>
        :root {
            --fg: #222;
            --muted: #666;
            --link: #1c63b7;
            --rule: #e3e3e3;
            --frame: #f5f5f5;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--frame);
            color: var(--fg);
            font: 17px/1.7 -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
        }
        .frame { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
        .meta {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        article {
            background: #fff;
            border: 1px solid var(--rule);
            border-radius: 6px;
            padding: 36px 44px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        article h1 {
            font-size: 30px;
            line-height: 1.25;
            margin: 0 0 24px;
        }
        article p { margin: 0 0 16px; }
        article ul { padding-left: 28px; margin: 0 0 16px; }
        article li { margin-bottom: 8px; }
        article a { color: var(--link); text-decoration: none; }
        article a:hover { text-decoration: underline; }
        article code {
            background: #f3f3f3;
            padding: 0 4px;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .divider {
            border: 0;
            border-top: 1px dashed var(--rule);
            margin: 24px 0;
        }
        .footer-note {
            color: var(--muted);
            font-size: 13px;
            margin-top: 32px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="meta">Preview · category ${CATEGORY_ID} · alias <code>${ALIAS}</code></div>
        <article>
            <h1>${TITLE}</h1>
            ${INTRO_HTML}
        </article>
        <p class="footer-note">Local preview — generated by build/cwm-article.sh DRY_RUN. Nothing has been posted to christianwebministries.org.</p>
    </div>
</body>
</html>
HTML

    echo "==================== DRY RUN ===================="
    echo "Preview written to: ${PREVIEW_FILE}"
    echo ""
    if command -v open >/dev/null 2>&1; then
        open "$PREVIEW_FILE"
        echo "Opened in your default browser."
    else
        echo "Open it in a browser to inspect the layout."
    fi
    echo ""
    echo "If a previous featured article exists, the script would also PATCH it to {\"featured\":0}."
    echo "POST payload that would be sent (also dumped here for diff/log purposes):"
    echo "-------------------------------------------------"
    echo "$POST_PAYLOAD" | jq .
    echo "================================================="
    exit 0
fi

# --- Step 1: un-feature current featured article(s) ---
echo "Looking for currently-featured article(s) to un-feature..."

UNFEATURE_IDS=()

if [ -n "${PREV_ARTICLE_ID:-}" ]; then
    UNFEATURE_IDS+=("$PREV_ARTICLE_ID")
else
    LIST=$(curl -sS -g \
        -H "$AUTH_HEADER" -H "$ACCEPT_HEADER" \
        "${ARTICLES_URL}?filter[featured]=1&fields[articles]=title,featured")

    while IFS= read -r row_id; do
        [ -n "$row_id" ] && UNFEATURE_IDS+=("$row_id")
    done < <(echo "$LIST" | jq -r '.data[]?.id // empty')
fi

if [ "${#UNFEATURE_IDS[@]}" -eq 0 ]; then
    echo "  (none found)"
else
    UNFEATURE_PAYLOAD='{"featured":0}'
    for id in "${UNFEATURE_IDS[@]}"; do
        echo "  Un-featuring article ${id}..."
        RESP=$(api_patch "$id" "$UNFEATURE_PAYLOAD")
        CODE=$(echo "$RESP" | tail -n1)

        if [ "$CODE" != "200" ] && [ "$CODE" != "204" ]; then
            echo "    Warning: PATCH returned HTTP ${CODE}; continuing anyway."
            echo "$RESP" | sed '$d' | jq '.errors // .' 2>/dev/null || true
        fi
    done
fi
echo ""

# --- Step 2: POST new article ---
echo "Creating new article in category ${CATEGORY_ID}..."

RESPONSE=$(curl -sS -g -w "\n%{http_code}" \
    -X POST \
    -H "$AUTH_HEADER" -H "$JSON_HEADER" -H "$ACCEPT_HEADER" \
    "$ARTICLES_URL" \
    -d "$POST_PAYLOAD")

HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
HTTP_BODY=$(echo "$RESPONSE" | sed '$d')

if [ "$HTTP_CODE" != "200" ] && [ "$HTTP_CODE" != "201" ]; then
    echo ""
    echo "Error: POST failed with HTTP ${HTTP_CODE}."
    echo "$HTTP_BODY" | jq '.errors // .' 2>/dev/null || echo "$HTTP_BODY"
    exit 1
fi

NEW_ID=$(echo "$HTTP_BODY" | jq -r '.data.id // empty')
echo ""
echo "Article created."
[ -n "$NEW_ID" ] && echo "  ID:    ${NEW_ID}"
echo "  Alias: ${ALIAS}"
echo "  URL:   ${SITE_URL}/index.php?option=com_content&view=article&id=${NEW_ID:-?}"