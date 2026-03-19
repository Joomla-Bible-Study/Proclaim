<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Googledrive;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Google Drive Server Addon
 *
 * Provides integration with Google Drive for document sharing including:
 * - URL parsing and embed conversion for Drive file links
 * - Support for Google Docs, Sheets, Slides, and generic Drive files
 * - Iframe-based preview embedding (no API key required)
 *
 * @since 10.2.0
 */
class CWMAddonGoogledrive extends CWMAddon
{
    /**
     * Addon name
     *
     * @var string
     * @since 10.2.0
     */
    protected $name = 'Google Drive';

    /**
     * Addon description
     *
     * @var string
     * @since 10.2.0
     */
    protected $description = 'Used for Google Drive document sharing and embedding';

    /**
     * URL patterns that identify Google Drive content.
     *
     * @return  string[]
     *
     * @since   10.2.0
     */
    public function getUrlPatterns(): array
    {
        return [
            '/drive\.google\.com/i',
            '/docs\.google\.com/i',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.2.0
     */
    public function getMigrationPatterns(): array
    {
        return [
            'type'     => 'googledrive',
            'label'    => 'Google Drive',
            'patterns' => [
                '/drive\.google\.com/i',
                '/docs\.google\.com/i',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.2.0
     */
    public function transformMigrationParams(
        array $params,
        string $mediacode,
        string $filename,
        string $avContent,
        string $combined,
        array $legacyServerParams = []
    ): array {
        return [
            'filename'  => $this->normalizeFilename($filename),
            'player'    => '1',
            'mediacode' => '',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since   10.2.0
     */
    #[\Override]
    public function normalizeFilename(string $filename): string
    {
        return $this->convertGoogledrive($filename);
    }

    /**
     * Extract the Google Drive file ID from various URL formats.
     *
     * Supported formats:
     * - https://drive.google.com/file/d/{ID}/view
     * - https://drive.google.com/file/d/{ID}/edit
     * - https://drive.google.com/file/d/{ID}/preview
     * - https://drive.google.com/open?id={ID}
     * - https://docs.google.com/document/d/{ID}/edit
     * - https://docs.google.com/spreadsheets/d/{ID}/edit
     * - https://docs.google.com/presentation/d/{ID}/edit
     *
     * @param   string  $url  The Google Drive URL
     *
     * @return  string|null  The file ID or null if not found
     *
     * @since   10.2.0
     */
    public static function extractMediaId(string $url): ?string
    {
        // Pattern 1: /d/{ID}/ in path (file, document, spreadsheet, presentation)
        if (preg_match('#/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        // Pattern 2: ?id={ID} query parameter (drive.google.com/open?id=...)
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Detect the Google document type from a URL.
     *
     * @param   string  $url  The Google Drive/Docs URL
     *
     * @return  string  One of: document, spreadsheets, presentation, file
     *
     * @since   10.2.0
     */
    public static function detectDocType(string $url): string
    {
        if (str_contains($url, 'docs.google.com/document/')) {
            return 'document';
        }

        if (str_contains($url, 'docs.google.com/spreadsheets/')) {
            return 'spreadsheets';
        }

        if (str_contains($url, 'docs.google.com/presentation/')) {
            return 'presentation';
        }

        return 'file';
    }

    /**
     * Convert a Google Drive URL to its embed/preview format.
     *
     * @param   string  $url  The Google Drive URL
     *
     * @return  string  The embed-ready URL
     *
     * @since   10.2.0
     */
    public function convertGoogledrive(string $url = ''): string
    {
        if (empty($url)) {
            return '';
        }

        $fileId = self::extractMediaId($url);

        if ($fileId === null) {
            return $url;
        }

        $docType = self::detectDocType($url);

        // Google Docs/Sheets/Slides use their own embed format
        if ($docType === 'document') {
            return 'https://docs.google.com/document/d/' . $fileId . '/preview';
        }

        if ($docType === 'spreadsheets') {
            return 'https://docs.google.com/spreadsheets/d/' . $fileId . '/preview';
        }

        if ($docType === 'presentation') {
            return 'https://docs.google.com/presentation/d/' . $fileId . '/embed';
        }

        // Generic Drive file — use Drive file preview
        return 'https://drive.google.com/file/d/' . $fileId . '/preview';
    }

    /**
     * Build the embed URL with form field params applied.
     *
     * @param   string    $filename     The raw Google Drive URL
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL
     *
     * @since   10.2.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        return $this->convertGoogledrive($filename);
    }

    /**
     * Render inline Google Drive preview with responsive iframe.
     *
     * @param   string    $url          The raw Google Drive URL
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   int       $mediaId      The media file ID
     *
     * @return  string  Complete player HTML
     *
     * @since   10.2.0
     */
    public function renderInlinePlayer(string $url, Registry $mediaParams, int $mediaId): string
    {
        $embedUrl = $this->buildEmbedUrl($url, $mediaParams);
        $fileId   = self::extractMediaId($url);
        $docType  = self::detectDocType($url);

        // Presentations use 16:9, documents/spreadsheets use taller aspect ratio
        if ($docType === 'presentation') {
            $paddingBottom = '56.25%';
        } else {
            $paddingBottom = '75%';
        }

        return '<div class="proclaim-doc-wrap" style="position:relative;padding-bottom:' . $paddingBottom . ';overflow:hidden;max-width:100%;">'
            . '<iframe class="playhit" data-id="' . $mediaId . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') . '"'
            . ' allow="autoplay" allowfullscreen loading="lazy"'
            . ' style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"></iframe>'
            . '</div>';
    }

    /**
     * Render general fieldset fields
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  Rendered HTML
     *
     * @since   10.2.0
     */
    public function renderGeneral(object $media_form, bool $new): string
    {
        $html = '';

        foreach ($media_form->getFieldset('general') as $field) {
            if ($new && isset($media_form->s_params[$field->fieldname])) {
                $field->setValue($media_form->s_params[$field->fieldname]);
            }

            $html .= $field->renderField();
        }

        return $html;
    }

    /**
     * Render full tab with addTab/endTab wrappers
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  Rendered HTML
     *
     * @since   10.2.0
     */
    public function render(object $media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('JBS_ADDON_MEDIA_OPTIONS_LABEL'));
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }

    /**
     * Upload method (not supported for Google Drive URLs)
     *
     * @param   array|null  $data  Data to upload
     *
     * @return  mixed
     *
     * @since   10.2.0
     */
    protected function upload(?array $data): mixed
    {
        // Google Drive files are referenced by URL, not uploaded
        return false;
    }

    /**
     * Detect metadata for a Google Drive file.
     *
     * Sets a reasonable MIME type default based on the document type detected from the URL.
     * No API calls needed — Google Drive embeds work without authentication.
     *
     * @param   Registry    $params      Media params (modified in place)
     * @param   object      $server      Server object
     * @param   string      $set_path    Server path prefix
     * @param   Registry    $path        Server params
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.2.0
     */
    #[\Override]
    public function detectMetadata(Registry $params, object $server, string $set_path, Registry $path, Cwmpodcast $jbspodcast): void
    {
        if (!empty($params->get('mime_type'))) {
            return;
        }

        $filename = $params->get('filename', '');

        if (empty($filename)) {
            return;
        }

        $docType = self::detectDocType($filename);

        $mimeMap = [
            'document'     => 'application/vnd.google-apps.document',
            'spreadsheets' => 'application/vnd.google-apps.spreadsheet',
            'presentation' => 'application/vnd.google-apps.presentation',
            'file'         => 'application/octet-stream',
        ];

        $params->set('mime_type', $mimeMap[$docType] ?? 'application/octet-stream');
    }
}
