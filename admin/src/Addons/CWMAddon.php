<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Abstract Server class
 *
 * @since  9.0.0
 */
abstract class CWMAddon
{
    /**
     * Addon configuration
     *
     * @var     bool|null|\SimpleXMLElement
     * @since   9.0.0
     */
    protected bool|null|\SimpleXMLElement $xml = null;

    /**
     * Name of Add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $name = '';

    /**
     * Description of add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $description = '';

    /**
     * Config information
     *
     * @var     string
     * @since   9.0.0
     */
    protected $config = '';

    /**
     * The type of server
     *
     * @var     string
     * @since   9.0.0
     */
    protected $type = '';

    /**
     * Construct
     *
     * @param array $config Array of Obtains
     *
     * @throws \Exception
     *
     * @since 9.0.0
     */
    public function __construct(array $config = [])
    {
        if (empty($this->type)) {
            if (\array_key_exists('type', $config)) {
                $this->type = $config['type'];
            } else {
                $this->type = $this->getType();
            }
        }

        if (empty($this->xml)) {
            $this->xml = $this->getXml();

            if ($this->xml) {
                $this->name        = $this->xml->name->__toString();
                $this->description = $this->xml->description->__toString();
                $this->config      = $this->xml->config;
            }
        }
    }

    /**
     * Gets the type of addon loaded based on the class name
     *
     * @return  string
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function getType(): string
    {
        if (empty($this->type)) {
            $r = null;

            if (!preg_match('/CWMAddon(.*)/i', \get_class($this), $r)) {
                throw new \RuntimeException(Text::sprintf('JBS_CMN_CANT_ADDON_CLASS_NAME', $this->type), 500);
            }

            $this->type = strtolower($r[1]);
        }

        return $this->type;
    }

    /**
     * Loads the addon configuration from the XML file
     *
     * @return  \SimpleXMLElement  The parsed XML configuration
     *
     * @throws  \RuntimeException  If the configuration file cannot be found or parsed
     * @since   9.0.0
     */
    public function getXml(): \SimpleXMLElement
    {
        $path = Path::find(BIBLESTUDY_PATH_ADMIN . '/src/Addons/Servers/' . ucfirst($this->type), $this->type . '.xml');

        if (!$path) {
            throw new \RuntimeException(Text::_('JBS_CMN_COULD_NOT_LOAD_ADDON_CONFIGURATION'), 404);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException(Text::sprintf('JBS_CMN_COULD_NOT_READ_ADDON_FILE', $path), 500);
        }

        $xml = simplexml_load_string($contents);

        if ($xml === false) {
            throw new \RuntimeException(Text::sprintf('JBS_CMN_COULD_NOT_PARSE_ADDON_XML', $path), 500);
        }

        return $xml;
    }

    /**
     * Returns an Addon object, always creating it
     *
     * @param   string  $type    The addon type to instantiate
     * @param   array   $config  Configuration options for the addon
     *
     * @return  static  The addon instance
     *
     * @throws  \RuntimeException  If the addon class does not exist
     * @since   9.0.0
     */
    public static function getInstance(string $type, array $config = []): static
    {
        $type  = ucfirst(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));
        $class = "CWM\\Component\\Proclaim\\Administrator\\Addons\\Servers\\" . ucfirst($type) . "\\CWMAddon" . ucfirst(
            $type
        );

        if (!class_exists($class)) {
            throw new \RuntimeException(Text::sprintf('JBS_CMN_ADDON_CLASS_NOT_FOUND', $type), 404);
        }

        return new $class($config);
    }

    /**
     * Delete a physical file managed by this addon
     *
     * Base implementation returns false (not supported). Only addons
     * that manage local files should override this method.
     *
     * @param   string    $filename      The filename or relative path to delete
     * @param   Registry  $serverParams  The server configuration parameters
     *
     * @return  bool  True if the file was deleted or already absent, false if not supported
     *
     * @since   10.1.0
     */
    public function deleteFile(string $filename, Registry $serverParams): bool
    {
        return false;
    }

    /**
     * Render Fields for a general view.
     *
     * @param   object  $media_form  Media files form
     * @param bool      $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    abstract public function renderGeneral(object $media_form, bool $new): string;

    /**
     * Render Layout and fields (full tab with addTab/endTab wrappers)
     *
     * @param   object  $media_form  Media files form
     * @param bool      $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    abstract public function render(object $media_form, bool $new): string;

    /**
     * Render non-general fieldset fields WITHOUT tab wrappers.
     *
     * Used by AJAX addon loading to return just the options HTML content
     * that gets injected into an existing tab shell.
     *
     * @param   object  $media_form  Media files form
     * @param   bool    $new         If media is new
     *
     * @return  string  The rendered HTML
     *
     * @since   10.1.0
     */
    public function renderOptionsFields(object $media_form, bool $new): string
    {
        $html = '<div class="row">';

        foreach ($media_form->getFieldsets('params') as $name => $fieldset) {
            if ($name !== 'general') {
                $html .= '<div class="col-6">';

                foreach ($media_form->getFieldset($name) as $field) {
                    if ($new) {
                        $s_name = $field->fieldname;

                        if (isset($media_form->s_params[$s_name])) {
                            $field->setValue($media_form->s_params[$s_name]);
                        }
                    }

                    $html .= $field->renderField();
                }

                $html .= '</div>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Upload
     *
     * @param ?array $data Data to upload
     *
     * @return mixed
     *
     * @since 9.0.0
     */
    abstract protected function upload(?array $data): mixed;

    /**
     * Get available AJAX actions for this addon
     *
     * Override in child classes to register available AJAX actions.
     * Return an array of action names that map to handle{ActionName}Action methods.
     *
     * @return  array  List of available action names (e.g., ['testApi', 'fetchVideos'])
     *
     * @since   10.0.0
     */
    public function getAjaxActions(): array
    {
        return [];
    }

    /**
     * Handle an AJAX action request
     *
     * This method dispatches to the appropriate handler method based on the action name.
     * Handler methods should be named handle{ActionName}Action (e.g., handleTestApiAction).
     * The base class handles the 'fetchStats' action for all stats-capable addons.
     *
     * @param   string  $action  The action name to handle
     *
     * @return  array  Response data array with a 'success' key and additional data
     *
     * @throws  \RuntimeException|\Exception  If the action is not supported
     * @since   10.0.0
     */
    public function handleAjaxAction(string $action): array
    {
        // Base class handles fetchStats for all stats-capable addons
        if ($action === 'fetchStats') {
            if (!$this->supportsStats()) {
                return [
                    'success' => false,
                    'error'   => Text::sprintf('JBS_CMN_ADDON_ACTION_NOT_SUPPORTED', $action, $this->type),
                ];
            }

            $app        = Factory::getApplication();
            $serverId   = $app->getInput()->getInt('server_id', 0);
            $batchLimit = $app->getInput()->getInt('batch_limit', 50);

            if (!$serverId) {
                return ['success' => false, 'error' => 'No server ID provided'];
            }

            return $this->fetchPlatformStats($serverId, $batchLimit);
        }

        $availableActions = $this->getAjaxActions();

        if (!\in_array($action, $availableActions, true)) {
            return [
                'success' => false,
                'error'   => Text::sprintf('JBS_CMN_ADDON_ACTION_NOT_SUPPORTED', $action, $this->type),
            ];
        }

        // Convert action name to method name (e.g., 'testApi' -> 'handleTestApiAction')
        $methodName = 'handle' . ucfirst($action) . 'Action';

        if (!method_exists($this, $methodName)) {
            return [
                'success' => false,
                'error'   => Text::sprintf('JBS_CMN_ADDON_ACTION_METHOD_NOT_FOUND', $methodName, $this->type),
            ];
        }

        return $this->$methodName();
    }

    /**
     * Prepare the environment for AJAX response (suppress errors, clear buffers)
     *
     * Call this at the start of an AJAX handler to ensure clean JSON output.
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public static function prepareAjaxEnvironment(): void
    {
        // Suppress any error output that might corrupt JSON
        @ini_set('display_errors', '0');
        @error_reporting(0);

        // Clear any output buffers completely
        while (@ob_get_level()) {
            @ob_end_clean();
        }
    }

    /**
     * Output JSON response and terminate
     *
     * @param   array  $data  The data to encode as JSON
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public static function outputJson(array $data): void
    {
        // Clear all output buffers
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Send headers before any output
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        // Encode and output JSON
        $json = json_encode($data);

        if ($json === false) {
            $json = '{"success":false,"error":"JSON encoding failed"}';
        }

        echo $json;

        // Force flush and terminate
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        exit;
    }

    /**
     * Handle a generic AJAX request for any addon
     *
     * This static method is the main entry point for addon AJAX requests from the controller.
     * It loads the appropriate addon, prepares the environment, and dispatches to the action handler.
     *
     * @param   string  $addonType  The addon type (e.g., 'youtube', 'vimeo')
     * @param   string  $action     The action to perform
     *
     * @return  void  Outputs JSON and exits
     *
     * @since   10.0.0
     */
    public static function handleAjaxRequest(string $addonType, string $action): void
    {
        self::prepareAjaxEnvironment();

        try {
            // Load the addon
            $addon = self::getInstance($addonType);

            // Start buffering to capture any deprecation warnings from vendor code
            ob_start();

            // Handle the action
            $result = @$addon->handleAjaxAction($action);

            // Discard any output from vendor code (deprecation warnings, etc.)
            ob_end_clean();

            self::outputJson($result);
        } catch (\Exception $e) {
            self::outputJson([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Whether this addon supports pushing descriptions to the platform via API.
     * Override in a child class and return true to enable description sync.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function supportsDescriptionSync(): bool
    {
        return false;
    }

    /**
     * Push a description to a video on the platform.
     * Override in child class to implement platform-specific API calls.
     *
     * @param   int     $mediaId      The media file ID (to look up platform video ID)
     * @param   string  $description  The description text to push
     *
     * @return  array{success: bool, error?: string}
     *
     * @since   10.1.0
     */
    public function syncDescription(int $mediaId, string $description): array
    {
        return ['success' => false, 'error' => 'Not supported by this addon'];
    }

    /**
     * Whether this addon supports importing chapters from the platform.
     *
     * When true, the Chapters & Tracks tab shows a platform-specific
     * "Import Chapters" button. Override in child class for platforms
     * that expose chapter/timestamp data (e.g. YouTube descriptions).
     *
     * @return  bool
     *
     * @since   10.2.0
     */
    public function supportsChapters(): bool
    {
        return false;
    }

    /**
     * Whether this addon supports downloading captions from the platform.
     *
     * When true, the Chapters & Tracks tab shows a "Download Captions"
     * button. Override in child class for platforms that expose caption
     * tracks (e.g. YouTube Captions API via OAuth).
     *
     * @return  bool
     *
     * @since   10.2.0
     */
    public function supportsCaptions(): bool
    {
        return false;
    }

    /**
     * Format chapters array as a timestamp block for video descriptions.
     *
     * The `0:00 Label` format is recognized by YouTube, Vimeo, and most
     * video platforms for automatic chapter markers.
     *
     * @param   array  $chapters  Chapters from media params
     *
     * @return  string  Formatted timestamp block
     *
     * @since   10.2.0
     */
    public static function formatChaptersForDescription(array $chapters): string
    {
        $lines = [];

        foreach ($chapters as $ch) {
            $ch      = (array) $ch;
            $time    = $ch['time'] ?? '0:00';
            $label   = $ch['label'] ?? '';
            $lines[] = $time . ' ' . $label;
        }

        return implode("\n", $lines);
    }

    /**
     * Whether this addon supports fetching external platform statistics.
     * Override in child class and return true to enable stats sync.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function supportsStats(): bool
    {
        return false;
    }

    /**
     * Fetch platform statistics for media files linked to the given server.
     * Override in child class to implement platform-specific API calls.
     *
     * When $batchLimit > 0, only the N least-recently-synced videos are processed
     * per invocation. Never-synced videos are prioritised. This prevents API quota
     * exhaustion on large libraries; the scheduled task gradually covers all videos
     * over successive runs.
     *
     * @param   int  $serverId    The server record ID
     * @param   int  $batchLimit  Max videos to sync this run (0 = unlimited)
     *
     * @return  array{success: bool, synced: int, remaining: int, errors: string[]}
     *
     * @since   10.1.0
     */
    public function fetchPlatformStats(int $serverId, int $batchLimit = 0): array
    {
        return ['success' => true, 'synced' => 0, 'remaining' => 0, 'errors' => []];
    }

    /**
     * Get all published servers whose addon supports stats retrieval.
     * Used by both the manual sync button and the scheduled task.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public static function getStatsCapableServers(): array
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $columns = $db->getTableColumns('#__bsms_servers');
        $select  = [$db->quoteName('id'), $db->quoteName('server_name'), $db->quoteName('type')];

        if (isset($columns['stats_synced_at'])) {
            $select[] = $db->quoteName('stats_synced_at');
        }

        $query = $db->getQuery(true)
            ->select($select)
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('published') . ' = 1');
        $servers = $db->setQuery($query)->loadAssocList() ?? [];

        return array_values(array_filter($servers, function ($srv) {
            try {
                return static::getInstance($srv['type'])->supportsStats();
            } catch (\RuntimeException) {
                return false;
            }
        }));
    }

    /**
     * Detect metadata for a file.
     *
     * @param   Registry    $params      Media params (modified in place)
     * @param   object      $server      Server object
     * @param   string      $set_path    Server path prefix
     * @param   Registry    $path        Server params
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function detectMetadata(Registry $params, object $server, string $set_path, Registry $path, Cwmpodcast $jbspodcast): void
    {
        // Default implementation does nothing
    }

    /**
     * Get MIME type from file extension.
     *
     * @param   string  $filename  Filename or URL
     *
     * @return  string|null
     *
     * @since   10.1.0
     */
    protected function getMimeTypeFromExtension(string $filename): ?string
    {
        $path      = parse_url($filename, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mimeTypes = [
            'mp3'  => 'audio/mpeg',
            'mp4'  => 'video/mp4',
            'm4a'  => 'audio/mp4',
            'm4v'  => 'video/mp4',
            'ogg'  => 'audio/ogg',
            'oga'  => 'audio/ogg',
            'ogv'  => 'video/ogg',
            'wav'  => 'audio/wav',
            'webm' => 'video/webm',
            'flac' => 'audio/flac',
            'aac'  => 'audio/aac',
            'pdf'  => 'application/pdf',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /**
     * Set duration params using FFprobe.
     *
     * @param   Registry    $params      Media params (modified in place)
     * @param   string      $url         File URL or path
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function setDurationFromFFprobe(Registry $params, string $url, Cwmpodcast $jbspodcast): void
    {
        $durationSeconds = $jbspodcast->getDurationWithFFprobe($url);

        // Fallback to getMediaDuration for local files
        if ($durationSeconds <= 0 && is_file($url)) {
            $durationSeconds = $jbspodcast->getMediaDuration($url);
        }

        if ($durationSeconds > 0) {
            $duration = $jbspodcast->formatTime($durationSeconds);
            $params->set('media_hours', str_pad((string) $duration->hours, 2, '0', STR_PAD_LEFT));
            $params->set('media_minutes', str_pad((string) $duration->minutes, 2, '0', STR_PAD_LEFT));
            $params->set('media_seconds', str_pad((string) $duration->seconds, 2, '0', STR_PAD_LEFT));
        }
    }

    /**
     * Check which metadata fields need detection.
     *
     * @param   Registry  $params  Media params
     *
     * @return  array{needsSize: bool, needsMime: bool, needsDuration: bool}
     *
     * @since   10.1.0
     */
    protected function needsDetection(Registry $params): array
    {
        $hours   = $params->get('media_hours', '00');
        $minutes = $params->get('media_minutes', '00');
        $seconds = $params->get('media_seconds', '00');

        return [
            'needsSize'     => empty($params->get('size', 0)) || (int) $params->get('size', 0) < 1000,
            'needsMime'     => empty($params->get('mime_type')),
            'needsDuration' => ($hours === '00' && $minutes === '00' && $seconds === '00'),
        ];
    }

    /**
     * Detect metadata for a remote file via HTTP headers and FFprobe.
     *
     * Shared helper for addons that host downloadable remote files (Legacy, etc.).
     * Detects file size via HTTP Content-Length, MIME type from extension, and
     * duration via FFprobe.
     *
     * @param   Registry    $params      Media params (modified in place)
     * @param   string      $remoteUrl   File URL (protocol is added if missing)
     * @param   Cwmpodcast  $jbspodcast  Podcast helper
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function detectRemoteMetadata(Registry $params, string $remoteUrl, Cwmpodcast $jbspodcast): void
    {
        if (empty($remoteUrl)) {
            return;
        }

        // Ensure URL has protocol
        if (!str_contains($remoteUrl, '://')) {
            $remoteUrl = 'https://' . ltrim($remoteUrl, '/');
        }

        ['needsSize' => $needsSize, 'needsMime' => $needsMime, 'needsDuration' => $needsDuration] = $this->needsDetection($params);

        if (!$needsSize && !$needsMime && !$needsDuration) {
            return;
        }

        // Get file size via HTTP Content-Length header
        if ($needsSize) {
            $size = Cwmhelper::getRemoteFileSize($remoteUrl);

            if ($size > 0) {
                $params->set('size', $size);
            }
        }

        // MIME type from file extension
        if ($needsMime) {
            $mime = $this->getMimeTypeFromExtension($remoteUrl);

            if ($mime) {
                $params->set('mime_type', $mime);
            }
        }

        // Duration via FFprobe
        if ($needsDuration) {
            $this->setDurationFromFFprobe($params, $remoteUrl, $jbspodcast);
        }
    }

    /**
     * UPSERT a platform stats row. Used by addon fetchPlatformStats() implementations.
     *
     * @param   int     $mediaId     Media file ID
     * @param   int     $serverId    Server ID
     * @param   string  $platform    Platform type (youtube, vimeo, wistia)
     * @param   string  $platformId  Video ID/hash on the platform
     * @param   array   $stats       Associative array of stat columns to set
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected static function upsertPlatformStats(
        int $mediaId,
        int $serverId,
        string $platform,
        string $platformId,
        array $stats
    ): void {
        $db  = Factory::getContainer()->get(DatabaseInterface::class);
        $now = Factory::getDate()->toSql();

        $columns = ['media_id', 'server_id', 'platform', 'platform_id', 'synced_at'];
        $values  = [
            (int) $mediaId,
            (int) $serverId,
            $db->quote($platform),
            $db->quote($platformId),
            $db->quote($now),
        ];

        $updates = [$db->quoteName('synced_at') . ' = ' . $db->quote($now)];

        $statColumns = [
            'view_count', 'play_count', 'like_count', 'comment_count',
            'load_count', 'hours_watched', 'engagement',
        ];

        foreach ($statColumns as $col) {
            if (isset($stats[$col])) {
                $columns[] = $col;

                if ($stats[$col] === null) {
                    $values[] = 'NULL';
                } elseif (\is_float($stats[$col])) {
                    $values[] = $db->quote(number_format($stats[$col], 2, '.', ''));
                } else {
                    $values[] = (int) $stats[$col];
                }

                $updates[] = $db->quoteName($col) . ' = VALUES(' . $db->quoteName($col) . ')';
            }
        }

        $sql = 'INSERT INTO ' . $db->quoteName('#__bsms_platform_stats')
            . ' (' . implode(', ', array_map([$db, 'quoteName'], $columns)) . ')'
            . ' VALUES (' . implode(', ', $values) . ')'
            . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);

        $db->setQuery($sql)->execute();
    }

    /**
     * Update the stats_synced_at timestamp on a server record.
     *
     * @param   int  $serverId  The server ID
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected static function updateServerSyncTimestamp(int $serverId): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_servers'))
            ->set($db->quoteName('stats_synced_at') . ' = ' . $db->quote($now))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query)->execute();
    }

    /**
     * Get media files linked to a server with their video IDs extracted from params.
     *
     * When $batchLimit > 0 and $platform is set, results are ordered by sync
     * priority: never-synced media first, then oldest-synced. This allows
     * incremental syncing over multiple scheduled task runs without hitting
     * API quotas.
     *
     * @param   int     $serverId    The server record ID
     * @param   string  $paramKey    The params key holding the video identifier (default: 'filename')
     * @param   int     $batchLimit  Max rows to return (0 = unlimited)
     * @param   string  $platform    Platform name for sync-priority ordering (e.g. 'youtube')
     *
     * @return  array  Array of [media_id, video_id] pairs
     *
     * @since   10.1.0
     */
    protected static function getMediaVideoIds(
        int $serverId,
        string $paramKey = 'filename',
        int $batchLimit = 0,
        string $platform = '',
        bool $includeArchived = true
    ): array {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('m.id'), $db->quoteName('m.params')])
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->where($db->quoteName('m.server_id') . ' = ' . (int) $serverId);

        if ($includeArchived) {
            $query->whereIn($db->quoteName('m.published'), [1, 2]);
        } else {
            $query->where($db->quoteName('m.published') . ' = 1');
        }

        // When batching, LEFT JOIN platform_stats for sync-priority ordering
        if ($batchLimit > 0 && $platform !== '') {
            $query->leftJoin(
                $db->quoteName('#__bsms_platform_stats', 'ps')
                . ' ON ' . $db->quoteName('ps.media_id') . ' = ' . $db->quoteName('m.id')
                . ' AND ' . $db->quoteName('ps.platform') . ' = ' . $db->quote($platform)
            );

            // Never-synced first (NULL synced_at), then oldest-synced
            $query->order($db->quoteName('ps.synced_at') . ' IS NULL DESC')
                ->order($db->quoteName('ps.synced_at') . ' ASC');

            // Fetch extra rows to account for media with invalid/missing video IDs
            $query->setLimit((int) ceil($batchLimit * 1.5));
        }

        $rows = $db->setQuery($query)->loadAssocList() ?? [];

        $result = [];

        foreach ($rows as $row) {
            $params  = new Registry($row['params'] ?? '');
            $videoId = trim((string) $params->get($paramKey, ''));

            if ($videoId !== '') {
                $result[] = [
                    'media_id' => (int) $row['id'],
                    'video_id' => $videoId,
                ];
            }
        }

        return $result;
    }

    /**
     * Count published (and optionally archived) media files linked to a server.
     *
     * @param   int   $serverId         The server record ID
     * @param   bool  $includeArchived  Include archived media (state 2) — default true
     *
     * @return  int
     *
     * @since   10.1.0
     */
    protected static function getMediaVideoCount(int $serverId, bool $includeArchived = true): int
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('server_id') . ' = ' . (int) $serverId);

        if ($includeArchived) {
            $query->whereIn($db->quoteName('published'), [1, 2]);
        } else {
            $query->where($db->quoteName('published') . ' = 1');
        }

        return (int) $db->setQuery($query)->loadResult();
    }

    /**
     * URL regex patterns this addon can render. Override in child classes.
     * Used by resolveForUrl() to find the correct addon for a given URL.
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public function getUrlPatterns(): array
    {
        return [];
    }

    /**
     * Migration detection metadata for this addon.
     *
     * Override in child classes to declare the URL patterns, AllVideos tags,
     * and display label used by the server migration helper to classify
     * legacy media files.
     *
     * Return format:
     * [
     *     'type'           => 'youtube',
     *     'label'          => 'YouTube',
     *     'patterns'       => ['/youtu(be\.com|\.be)\//i'],
     *     'allVideosTags'  => ['youtube', 'youtubewide'],
     * ]
     *
     * @return  array  Migration metadata, or empty array if this addon
     *                 does not participate in migration detection.
     *
     * @since   10.1.0
     */
    public function getMigrationPatterns(): array
    {
        return [];
    }

    /**
     * Transform legacy media params into this addon's format during migration.
     *
     * Receives the full legacy params array and the legacy server params.
     * Returns an array with addon-specific keys (filename, player, mediacode,
     * plus any embed option fields).
     *
     * The caller (CwmserverMigrationHelper::transformParams) handles
     * preserving shared display params and merging them with this result.
     *
     * @param   array  $params              Legacy media file params
     * @param   string $mediacode           The raw mediacode value
     * @param   string $filename            The raw filename value
     * @param   string $avContent           Extracted AllVideos content (bare ID between tags)
     * @param   string $combined            Combined "$filename $mediacode" for pattern matching
     * @param   array  $legacyServerParams  Legacy server params (path, protocol)
     *
     * @return  array  Transformed params for this addon
     *
     * @since   10.1.0
     */
    public function transformMigrationParams(
        array $params,
        string $mediacode,
        string $filename,
        string $avContent,
        string $combined,
        array $legacyServerParams = []
    ): array {
        // Default: preserve filename and player settings (local/passthrough behavior)
        return [
            'filename'  => $filename,
            'player'    => $params['player'] ?? '',
            'mediacode' => $mediacode,
        ];
    }

    /**
     * Extract the platform-specific media ID from a URL or embed string.
     *
     * Each addon overrides this to extract its platform's ID
     * (e.g., YouTube video ID, Vimeo numeric ID, Wistia hash).
     *
     * @param   string  $text  URL, embed code, or combined text to search
     *
     * @return  string|null  The extracted ID, or null if not found
     *
     * @since   10.1.0
     */
    public static function extractMediaId(string $text): ?string
    {
        return null;
    }

    /**
     * Normalize a user-entered URL to the canonical embed format for storage.
     * Called on save to ensure consistent URLs in the database.
     * Override in each platform addon.
     *
     * @param   string  $filename  The raw user-entered URL or filename
     *
     * @return  string  The normalized URL (or original if no normalization needed)
     *
     * @since   10.1.0
     */
    public function normalizeFilename(string $filename): string
    {
        return $filename;
    }

    /**
     * Build the complete embed URL with all form field params applied.
     * Override in each platform addon.
     *
     * @param   string    $filename     The raw URL/filename
     * @param   Registry  $mediaParams  Merged template + media params
     *
     * @return  string  The embed-ready URL
     *
     * @since   10.1.0
     */
    public function buildEmbedUrl(string $filename, Registry $mediaParams): string
    {
        return $filename;
    }

    /**
     * Render inline player HTML (responsive iframe/embed).
     * Override in each platform addon.
     *
     * @param   string    $url          The raw URL/filename
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   int       $mediaId      The media file ID (for play tracking)
     *
     * @return  string  Complete player HTML, or empty to fall back to CWMHtml5Inline
     *
     * @since   10.1.0
     */
    public function renderInlinePlayer(string $url, Registry $mediaParams, int $mediaId): string
    {
        return '';
    }

    /**
     * Render fancybox/squeezebox link HTML.
     * The default implementation works for most platforms — only overrides if custom behavior is needed.
     *
     * @param   string    $url          The raw URL/filename
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   int       $mediaId      The media file ID
     * @param   string    $image        The thumbnail image HTML
     * @param   string    $headerText   Popup header text (already escaped)
     * @param   string    $footerText   Popup footer text (already escaped)
     *
     * @return  string  Complete fancybox link HTML
     *
     * @since   10.1.0
     */
    public function renderFancyboxLink(
        string $url,
        Registry $mediaParams,
        int $mediaId,
        string $image,
        string $headerText,
        string $footerText
    ): string {
        $embedUrl = $this->buildEmbedUrl($url, $mediaParams);

        return '<a class="fancybox_player playhit" data-id="' . $mediaId
            . '" aria-hidden="false" data-src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8')
            . '" data-header="' . $headerText . '" data-footer="' . $footerText
            . '" data-options=\'{"autoplay":"' . (int) $mediaParams->get('autostart', false)
            . '","controls":"' . (int) $mediaParams->get('controls') . '"}\' href="javascript:;">'
            . $image . '</a>';
    }

    /**
     * Render popup player HTML (iframe with explicit dimensions).
     * Default implementation works for most platforms.
     *
     * @param   string    $url          The raw URL/filename
     * @param   Registry  $mediaParams  Merged template + media params
     * @param   string    $width        Player width
     * @param   string    $height       Player height
     *
     * @return  string  Complete popup player HTML
     *
     * @since   10.1.0
     */
    public function renderPopupPlayer(string $url, Registry $mediaParams, string $width, string $height): string
    {
        $embedUrl = $this->buildEmbedUrl($url, $mediaParams);

        return '<iframe width="' . $width . '" height="' . $height
            . '" src="' . htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8')
            . '" style="border:0;" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
    }

    /**
     * Cached addon instances for URL resolution.
     *
     * @var   array|null
     * @since 10.1.0
     */
    private static ?array $urlAddonCache = null;

    /**
     * Resolve which addon handles a URL by scanning all Servers/ subdirectories
     * and checking getUrlPatterns() on each addon. Results are cached per-request.
     *
     * @param   string  $url  The URL to match against addon patterns
     *
     * @return  static|null  The matching addon instance, or null
     *
     * @since   10.1.0
     */
    public static function resolveForUrl(string $url): ?static
    {
        if (self::$urlAddonCache === null) {
            self::$urlAddonCache = [];
            $serverDir           = BIBLESTUDY_PATH_ADMIN . '/src/Addons/Servers';

            if (is_dir($serverDir)) {
                foreach (scandir($serverDir) as $dir) {
                    if ($dir === '.' || $dir === '..' || !is_dir($serverDir . '/' . $dir)) {
                        continue;
                    }

                    try {
                        $addon    = static::getInstance(strtolower($dir));
                        $patterns = $addon->getUrlPatterns();

                        if (!empty($patterns)) {
                            self::$urlAddonCache[] = ['addon' => $addon, 'patterns' => $patterns];
                        }
                    } catch (\RuntimeException) {
                        continue;
                    }
                }
            }
        }

        foreach (self::$urlAddonCache as $entry) {
            foreach ($entry['patterns'] as $pattern) {
                if (preg_match($pattern, $url)) {
                    return $entry['addon'];
                }
            }
        }

        return null;
    }

    /**
     * Load the addon language file.
     *
     * Loads from Servers/{Type}/language/{tag}/{tag}.jbs_addon_{type}.ini.
     * Falls back to en-GB automatically when the active locale file is missing
     * (Joomla Language::load $default parameter).
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function loadLanguage(): void
    {
        $lang = Factory::getApplication()->getLanguage();
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($this->type);
        $lang->load('jbs_addon_' . strtolower($this->type), $path);
    }
}
