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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
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
     * @param object $media_form Media files form
     * @param bool $new If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    abstract public function renderGeneral($media_form, bool $new): string;

    /**
     * Render Layout and fields (full tab with addTab/endTab wrappers)
     *
     * @param object $media_form Media files form
     * @param bool $new If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    abstract public function render($media_form, bool $new): string;

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
    public function renderOptionsFields($media_form, bool $new): string
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
     * The 'fetchStats' action is handled by the base class for all stats-capable addons.
     *
     * @param   string  $action  The action name to handle
     *
     * @return  array  Response data array with 'success' key and additional data
     *
     * @throws  \RuntimeException  If the action is not supported
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
     * Prepare environment for AJAX response (suppress errors, clear buffers)
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
        /** @var DatabaseDriver $db */
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('server_name'), $db->quoteName('type'), $db->quoteName('stats_synced_at')])
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $servers = $db->loadAssocList() ?? [];

        return array_values(array_filter($servers, function ($srv) {
            try {
                $addon = static::getInstance($srv['type']);

                return $addon->supportsStats();
            } catch (\RuntimeException) {
                return false;
            }
        }));
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
        /** @var DatabaseDriver $db */
        $db  = Factory::getContainer()->get('DatabaseDriver');
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

        $db->setQuery($sql);
        $db->execute();
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
        /** @var DatabaseDriver $db */
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $now   = Factory::getDate()->toSql();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_servers'))
            ->set($db->quoteName('stats_synced_at') . ' = ' . $db->quote($now))
            ->where($db->quoteName('id') . ' = ' . (int) $serverId);
        $db->setQuery($query);
        $db->execute();
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
        string $platform = ''
    ): array {
        /** @var DatabaseDriver $db */
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([$db->quoteName('m.id'), $db->quoteName('m.params')])
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->where($db->quoteName('m.server_id') . ' = ' . (int) $serverId)
            ->where($db->quoteName('m.published') . ' = 1');

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

        $db->setQuery($query);
        $rows = $db->loadAssocList() ?? [];

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
     * Count total published media files linked to a server (for remaining calculation).
     *
     * @param   int  $serverId  The server record ID
     *
     * @return  int
     *
     * @since   10.1.0
     */
    protected static function getMediaVideoCount(int $serverId): int
    {
        /** @var DatabaseDriver $db */
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('server_id') . ' = ' . (int) $serverId)
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Load addon language file
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
