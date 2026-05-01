#!/bin/bash
#
# Generate JED (Joomla Extensions Directory) submission text.
#
# Outputs formatted text ready to paste into the JED update form,
# including version, compatibility, changelog, and description.
#
# Usage: composer jed-prep [-- version]
#   version: e.g. "10.2.2" (optional, reads from proclaim.xml if omitted)
#
# @since 10.2.2

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$REPO_DIR"

# --- Resolve version ---
if [ -n "${1:-}" ]; then
    VERSION="$1"
else
    VERSION=$(sed -n 's/.*<version>\([^<]*\)<.*/\1/p' proclaim.xml 2>/dev/null || echo "")
    if [ -z "$VERSION" ]; then
        echo "Error: Could not determine version."
        exit 1
    fi
fi

TAG="v${VERSION}"

# --- Get release notes from GitHub ---
RELEASE_NOTES=$(gh release view "$TAG" --repo Joomla-Bible-Study/Proclaim --json body --jq '.body' 2>/dev/null || echo "No release notes found for ${TAG}")

# --- Get release date ---
RELEASE_DATE=$(gh release view "$TAG" --repo Joomla-Bible-Study/Proclaim --json publishedAt --jq '.publishedAt' 2>/dev/null | cut -d'T' -f1 || date +%Y-%m-%d)

# --- Output ---
cat << JEDEOF

================================================================================
  JED SUBMISSION — Proclaim ${VERSION}
================================================================================

--- VERSION INFO (paste into JED version field) ---

Version: ${VERSION}
Release Date: ${RELEASE_DATE}
Joomla Compatibility: 5.1, 5.2, 5.3, 6.0
PHP Compatibility: 8.3, 8.4, 8.5

--- DOWNLOAD URL ---

https://github.com/Joomla-Bible-Study/Proclaim/releases/download/${TAG}/com_proclaim-${VERSION}.zip

--- CHANGELOG (paste into JED changelog field) ---

${RELEASE_NOTES}

--- SHORT DESCRIPTION (200 chars max) ---

Proclaim is a Joomla component for managing and displaying Bible studies, sermons, media files, podcasts, teachers, series, and topics with customizable templates.

--- FULL DESCRIPTION ---

Proclaim (CWM Proclaim) is a comprehensive Joomla component for churches and ministries to manage and display Bible studies and sermons.

Features:
- Sermon/message management with multiple teachers, scriptures, and media files
- Teacher and series management with landing pages
- YouTube integration with live stream support and quota management
- Podcast RSS feed generation with Apple/Spotify/Google compatibility
- Podcasting 2.0 namespace support (chapters, transcripts)
- Interactive VTT-powered searchable transcripts
- Schema.org structured data (CreativeWork, Person, CreativeWorkSeries)
- Visual Layout Editor for template customization
- Multi-campus location-based access control
- CSV import/export for bulk content management
- Analytics dashboard with platform stats (YouTube views, etc.)
- Smart Search (Finder) integration
- Joomla 5.x and 6.x native support (no backward compatibility layer needed)

Requirements:
- Joomla 5.1.0+ or Joomla 6.x
- PHP 8.3+

Documentation: https://github.com/Joomla-Bible-Study/Proclaim/wiki
Support: https://github.com/Joomla-Bible-Study/Proclaim/issues
Downloads: https://www.christianwebministries.org/downloads/proclaim.html

--- UPDATE XML URL (for JED auto-update configuration) ---

https://www.christianwebministries.org/index.php?option=com_ars&view=update&type=components&id=2

================================================================================
  Copy the sections above into the JED update form.
  JED: https://extensions.joomla.org/manage/
================================================================================

JEDEOF
