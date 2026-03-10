<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Helper for migrating media files from Legacy servers to core server types.
 *
 * Scans legacy server media files, detects the actual content platform,
 * and provides batch migration to the appropriate core server addon.
 *
 * @since  10.1.0
 */
class CwmserverMigrationHelper
{
    /**
     * Get supported target server types for migration.
     *
     * Built dynamically from the addon registry. Each addon declares its type
     * via getMigrationPatterns().
     *
     * @return  string[]
     *
     * @since   10.1.0
     */
    public static function getTargetTypes(): array
    {
        $types = [];

        foreach (self::getMigrationRegistry() as $entry) {
            $type = $entry['meta']['type'] ?? null;

            if ($type !== null && $type !== 'legacy') {
                $types[] = $type;
            }
        }

        return array_values(array_unique($types));
    }

    /**
     * Get display-friendly labels per detected type.
     *
     * Built dynamically from the addon registry. Each addon declares its label
     * via getMigrationPatterns().
     *
     * @return  array<string, string>
     *
     * @since   10.1.0
     */
    public static function getTypeLabels(): array
    {
        $labels = [];

        foreach (self::getMigrationRegistry() as $entry) {
            $type  = $entry['meta']['type'] ?? null;
            $label = $entry['meta']['label'] ?? null;

            if ($type !== null && $label !== null && $type !== 'legacy') {
                $labels[$type] = $label;
            }
        }

        // Always include non-addon types
        $labels['unknown'] = 'Unknown';
        $labels['empty']   = 'Empty';

        return $labels;
    }

    /**
     * Cached addon migration metadata, built on first call to getMigrationRegistry().
     *
     * @var    array|null
     * @since  10.1.0
     */
    private static ?array $migrationRegistry = null;

    /**
     * Build the migration pattern registry from all addon classes.
     *
     * Iterates all Servers/ subdirectories, instantiates each addon, and collects
     * their getMigrationPatterns() metadata. Cached per-request.
     *
     * @return  array  Array of ['addon' => CWMAddon, 'meta' => array] entries
     *
     * @since   10.1.0
     */
    private static function getMigrationRegistry(): array
    {
        if (self::$migrationRegistry !== null) {
            return self::$migrationRegistry;
        }

        self::$migrationRegistry = [];

        $serverDir = BIBLESTUDY_PATH_ADMIN . '/src/Addons/Servers';

        if (!is_dir($serverDir)) {
            return self::$migrationRegistry;
        }

        foreach (scandir($serverDir) as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($serverDir . '/' . $dir)) {
                continue;
            }

            try {
                $addon = CWMAddon::getInstance(strtolower($dir));
                $meta  = $addon->getMigrationPatterns();

                if (!empty($meta) && !empty($meta['type'])) {
                    self::$migrationRegistry[] = ['addon' => $addon, 'meta' => $meta];
                }
            } catch (\RuntimeException) {
                continue;
            }
        }

        return self::$migrationRegistry;
    }

    /**
     * Scan all legacy servers and classify their media files by detected type.
     *
     * @return  array  Array of legacy servers with media file counts per detected type.
     *                 Each entry: [id, server_name, params, types => [type => count]]
     *
     * @since   10.1.0
     */
    public static function scanLegacyServers(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Get all legacy servers (published or unpublished)
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.server_name'),
                $db->quoteName('s.params'),
                $db->quoteName('s.published'),
            ])
            ->from($db->quoteName('#__bsms_servers', 's'))
            ->where($db->quoteName('s.type') . ' = ' . $db->quote('legacy'))
            ->order($db->quoteName('s.server_name') . ' ASC');
        $db->setQuery($query);
        $servers = $db->loadObjectList();

        if (empty($servers)) {
            return [];
        }

        $serverIds = array_column($servers, 'id');

        // Get all media files for these servers
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.server_id'),
                $db->quoteName('m.params'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->whereIn($db->quoteName('m.server_id'), $serverIds);
        $db->setQuery($query);
        $mediaFiles = $db->loadObjectList();

        // Classify each media file
        $result = [];

        foreach ($servers as $server) {
            $serverParams = (new Registry($server->params))->toArray();
            $entry        = [
                'id'          => (int) $server->id,
                'server_name' => $server->server_name,
                'published'   => (int) $server->published,
                'params'      => $serverParams,
                'types'       => [],
                'total'       => 0,
            ];

            foreach ($mediaFiles as $media) {
                if ((int) $media->server_id !== (int) $server->id) {
                    continue;
                }

                try {
                    $mediaParams = json_decode($media->params ?? '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
                } catch (\JsonException) {
                    continue;
                }
                $filename    = $mediaParams['filename'] ?? '';
                $mediacode   = $mediaParams['mediacode'] ?? '';
                $mimeType    = $mediaParams['mime_type'] ?? '';
                $player      = $mediaParams['player'] ?? '';

                $detected = self::detectContentType($filename, $mediacode, $mimeType, $player, $mediaParams);

                if (!isset($entry['types'][$detected])) {
                    $entry['types'][$detected] = 0;
                }

                $entry['types'][$detected]++;
                $entry['total']++;
            }

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * Get detailed media file information for a legacy server filtered by detected type.
     *
     * Used by the drill-down feature so administrators can investigate
     * unknown, empty, or any other classified media files.
     *
     * @param   int     $legacyServerId  The legacy server ID
     * @param   string  $detectedType    The detected content type to filter by
     * @param   int     $limit           Maximum rows to return
     *
     * @return  array  Array of associative arrays with media file details
     *
     * @since   10.1.0
     */
    public static function getMediaFileDetails(int $legacyServerId, string $detectedType, int $limit = 100): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.study_id'),
                $db->quoteName('m.published'),
                $db->quoteName('m.params'),
                $db->quoteName('s.studytitle'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->join('LEFT', $db->quoteName('#__bsms_studies', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id'))
            ->where($db->quoteName('m.server_id') . ' = :serverId')
            ->bind(':serverId', $legacyServerId, \Joomla\Database\ParameterType::INTEGER);
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $results = [];

        foreach ($rows as $row) {
            try {
                $params = json_decode($row->params ?? '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
            } catch (\JsonException) {
                continue;
            }

            $filename  = $params['filename'] ?? '';
            $mediacode = $params['mediacode'] ?? '';
            $mimeType  = $params['mime_type'] ?? '';
            $player    = $params['player'] ?? '';

            if (self::detectContentType($filename, $mediacode, $mimeType, $player, $params) !== $detectedType) {
                continue;
            }

            $results[] = [
                'id'         => (int) $row->id,
                'study_id'   => (int) $row->study_id,
                'published'  => (int) $row->published,
                'studytitle' => $row->studytitle ?? '',
                'filename'   => $filename,
                'mediacode'  => mb_substr($mediacode, 0, 120),
                'mime_type'  => $mimeType,
                'player'     => $player,
                'icon'       => $params['media_icon_type'] ?? '',
            ];

            if (\count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    /**
     * AllVideos shortcode tags that represent local media files.
     *
     * @var    string[]
     * @since  10.1.0
     */
    private const array ALLVIDEOS_LOCAL_TAGS = [
        'mp3', 'mp4', 'flv', 'wmv', 'wma', 'ogg', 'ogv', 'webm', 'wav',
        'divx', 'mov', 'swf', 'avi', 'aac',
    ];

    /**
     * Detect the actual content platform from media file data.
     *
     * Detection phases:
     *  1. Legacy player/ID overrides (player 4/5/6 or docMan_id/article_id/virtueMart_id)
     *  2. AllVideos shortcodes ({youtube}…{/youtube}, {mp3}…{/mp3}, etc.)
     *  3. URL pattern matching via addon registry (YouTube, Vimeo, Wistia, etc.)
     *  4. Heuristic fallbacks (iframe→embed, file extension→local, S3/CloudFront→local)
     *
     * @param   string  $filename    The filename/URL from media params
     * @param   string  $mediacode   The embed code from media params
     * @param   string  $mimeType    The MIME type
     * @param   string  $player      The player setting
     * @param   array   $allParams   Full media params array for legacy ID field detection
     *
     * @return  string  A target type from the addon registry (e.g. youtube, vimeo, embed,
     *                  local, direct) or 'unknown'
     *
     * @since   10.1.0
     */
    public static function detectContentType(
        string $filename,
        string $mediacode,
        string $mimeType,
        string $player,
        array $allParams = []
    ): string {
        // Phase 1: Legacy player type / ID overrides
        $legacyIdFields = [
            ['4', 'docMan_id',     'docman'],
            ['5', 'article_id',    'article'],
            ['6', 'virtueMart_id', 'virtuemart'],
        ];

        foreach ($legacyIdFields as [$legacyPlayer, $paramKey, $type]) {
            if ($player === $legacyPlayer) {
                return $type;
            }

            $val = $allParams[$paramKey] ?? '';

            if ($val !== '' && $val !== '0' && $val !== '-1') {
                return $type;
            }
        }

        // Early exit: genuinely empty records (no filename, no mediacode, no legacy IDs)
        if (empty($filename) && empty($mediacode)) {
            return 'empty';
        }

        // Load the addon registry at once for phases 2 and 3
        $registry = self::getMigrationRegistry();

        // Phase 2: AllVideos shortcodes ({tag}content{/tag})
        if (!empty($mediacode) && preg_match('/\{(\w+)\}/', $mediacode, $avMatch)) {
            $tag = strtolower($avMatch[1]);

            foreach ($registry as $entry) {
                if (\in_array($tag, $entry['meta']['allVideosTags'] ?? [], true)) {
                    return $entry['meta']['type'];
                }
            }

            if (\in_array($tag, self::ALLVIDEOS_LOCAL_TAGS, true)) {
                return 'local';
            }
        }

        // Phase 3: URL pattern detection via addon registry
        $combined = $filename . ' ' . $mediacode;

        foreach ($registry as $entry) {
            foreach ($entry['meta']['patterns'] ?? [] as $pattern) {
                if (preg_match($pattern, $combined)) {
                    return $entry['meta']['type'];
                }
            }
        }

        // Phase 4: Heuristic fallbacks

        // Embed code detected by HTML tags or player type
        if (!empty($mediacode)) {
            if (preg_match('/<(iframe|embed|object)\b/i', $mediacode)
                || $player === '8'
                || $player === '2'
                || $player === '3'
            ) {
                return 'embed';
            }
        }

        // Player 7 = legacy simple audio player
        if ($player === '7') {
            return 'local';
        }

        // Local file detection
        if (!empty($filename)) {
            if (preg_match('/\.(mp3|mp4|m4a|m4v|ogg|ogv|webm|wav|flac|aac|pdf|doc|docx|ppt|pptx|zip)$/i', $filename)) {
                return 'local';
            }

            if (!preg_match('/^(https?:)?\/\//i', $filename) && !str_contains($filename, '://')) {
                return 'local';
            }

            if (preg_match('/\.s3[\.-].*?amazonaws\.com|cloudfront\.net/i', $filename)) {
                return 'local';
            }

            // Player 0 with an external URL and no recognized platform
            if ($player === '0' && preg_match('/^(https?:)?\/\//i', $filename)) {
                return 'direct';
            }
        }

        return 'unknown';
    }

    /**
     * Get existing published core servers grouped by type.
     *
     * @return  array<string, array>  Type => list of [id, server_name]
     *
     * @since   10.1.0
     */
    public static function getExistingServersByType(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('server_name'),
                $db->quoteName('type'),
            ])
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('type') . ' != ' . $db->quote('legacy'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('type') . ', ' . $db->quoteName('server_name'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $grouped = [];

        foreach ($rows as $row) {
            $type = strtolower($row->type);

            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }

            $grouped[$type][] = [
                'id'          => (int) $row->id,
                'server_name' => $row->server_name,
            ];
        }

        return $grouped;
    }

    /**
     * Create a new server record of the given type.
     *
     * @param   string    $type        Server addon type (e.g., 'youtube', 'vimeo')
     * @param   string    $name        Server name
     * @param   int|null  $locationId  Optional location ID
     *
     * @return  int  The new server ID
     *
     * @throws  \RuntimeException|\Exception  On failure
     *
     * @since   10.1.0
     */
    public static function createServerForType(string $type, string $name, ?int $locationId = null): int
    {
        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $user = Factory::getApplication()->getIdentity();

        $data = (object) [
            'server_name' => $name,
            'type'        => strtolower($type),
            'published'   => 1,
            'access'      => 1,
            'params'      => '{}',
            'media'       => '{}',
            'location_id' => $locationId,
            'created'     => (new \Joomla\CMS\Date\Date())->toSql(),
            'created_by'  => (int) $user->id,
        ];

        $db->insertObject('#__bsms_servers', $data, 'id');

        if (empty($data->id)) {
            throw new \RuntimeException('Failed to create server record');
        }

        return (int) $data->id;
    }

    /**
     * Get a batch of media file IDs for a given legacy server and detected type.
     *
     * @param   int     $legacyServerId  The legacy server ID
     * @param   string  $detectedType    The detected content type
     * @param   int     $offset          Query offset
     * @param   int     $limit           Batch size
     *
     * @return  int[]  Array of media file IDs
     *
     * @since   10.1.0
     */
    public static function getLegacyMediaFileIds(
        int $legacyServerId,
        string $detectedType,
        int $offset = 0,
        int $limit = 25
    ): array {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);

        // Load ALL media files for this server (no SQL LIMIT) because the type
        // filter is applied in PHP after parsing params.  Applying SQL LIMIT
        // before filtering caused early exit when a batch contained mixed types
        // and returned fewer matching IDs than the batch size.
        // ORDER BY id ensures deterministic ordering so the offset-based
        // failure-skipping logic in the JS batch loop works correctly.
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('params')])
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('server_id') . ' = :serverId')
            ->bind(':serverId', $legacyServerId, \Joomla\Database\ParameterType::INTEGER)
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Filter by detected type in PHP, then apply offset/limit
        $allIds = [];

        foreach ($rows as $row) {
            try {
                $params = json_decode($row->params ?? '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
            } catch (\JsonException) {
                continue;
            }

            $filename  = $params['filename'] ?? '';
            $mediacode = $params['mediacode'] ?? '';
            $mimeType  = $params['mime_type'] ?? '';
            $player    = $params['player'] ?? '';

            if (self::detectContentType($filename, $mediacode, $mimeType, $player, $params) === $detectedType) {
                $allIds[] = (int) $row->id;
            }
        }

        return \array_slice($allIds, $offset, $limit);
    }

    /**
     * Migrate a batch of media files to a target server.
     *
     * Updates server_id and transforms params for each media file.
     *
     * @param   int[]   $mediaIds          Array of media file IDs
     * @param   int     $targetServerId    Target server ID
     * @param   string  $targetType        Target server type (e.g., 'youtube')
     * @param   array   $legacyServerParams  Legacy server params (path, protocol)
     *
     * @return  array{migrated: int, errors: string[]}
     *
     * @since   10.1.0
     */
    public static function migrateMediaBatch(
        array $mediaIds,
        int $targetServerId,
        string $targetType,
        array $legacyServerParams = []
    ): array {
        if (empty($mediaIds)) {
            return ['migrated' => 0, 'errors' => []];
        }

        $db       = Factory::getContainer()->get(DatabaseInterface::class);
        $migrated = 0;
        $errors   = [];

        foreach ($mediaIds as $mediaId) {
            try {
                $query = $db->getQuery(true)
                    ->select([$db->quoteName('id'), $db->quoteName('params')])
                    ->from($db->quoteName('#__bsms_mediafiles'))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $mediaId, \Joomla\Database\ParameterType::INTEGER);
                $db->setQuery($query);
                $row = $db->loadObject();

                if (!$row) {
                    $errors[] = 'Media ID ' . $mediaId . ' not found';

                    continue;
                }

                $params        = json_decode($row->params ?? '{}', true, 512, JSON_THROW_ON_ERROR) ?: [];
                $newParams     = self::transformParams($params, $targetType, $legacyServerParams);
                $newParamsJson = json_encode($newParams, JSON_THROW_ON_ERROR);

                $update = $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_mediafiles'))
                    ->set($db->quoteName('server_id') . ' = :serverId')
                    ->set($db->quoteName('params') . ' = :params')
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':serverId', $targetServerId, \Joomla\Database\ParameterType::INTEGER)
                    ->bind(':params', $newParamsJson)
                    ->bind(':id', $mediaId, \Joomla\Database\ParameterType::INTEGER);
                $db->setQuery($update);
                $db->execute();
                $migrated++;
            } catch (\Exception $e) {
                $errors[] = 'Media ID ' . $mediaId . ': ' . $e->getMessage();
            }
        }

        return ['migrated' => $migrated, 'errors' => $errors];
    }

    /**
     * Transform media file params for the target server type.
     *
     * Extracts platform-specific IDs, sets proper embed URLs,
     * and adjusts player settings while preserving display params.
     *
     * @param   array   $params             Current media file params
     * @param   string  $targetType         Target server type
     * @param   array   $legacyServerParams Legacy server params (path, protocol)
     *
     * @return  array  Transformed params
     *
     * @since   10.1.0
     */
    public static function transformParams(
        array $params,
        string $targetType,
        array $legacyServerParams = []
    ): array {
        $filename  = $params['filename'] ?? '';
        $mediacode = $params['mediacode'] ?? '';

        // Display params to always preserve
        $preserved = [
            'media_image',
            'media_use_button_icon',
            'media_button_text',
            'media_button_type',
            'media_button_color',
            'media_icon_type',
            'media_custom_icon',
            'media_icon_text_size',
            'mime_type',
            'size',
            'duration',
            'autostart',
            'popup',
            'link_type',
            'playerwidth',
            'playerheight',
            'itempopuptitle',
            'itempopupfooter',
            'popupmargin',
        ];

        $result = [];

        foreach ($preserved as $key) {
            if (isset($params[$key])) {
                $result[$key] = $params[$key];
            }
        }

        // Extract AllVideos shortcode content (bare ID between tags)
        $avContent = self::extractAllVideosContent($mediacode);
        $combined  = $filename . ' ' . $mediacode;

        // Delegate to addon's transformMigrationParams()
        $addon       = CWMAddon::getInstance($targetType);
        $addonResult = $addon->transformMigrationParams(
            $params,
            $mediacode,
            $filename,
            $avContent,
            $combined,
            $legacyServerParams
        );

        // Merge addon result into preserved display params (addon values override)
        return array_merge($result, $addonResult);
    }

    /**
     * Unpublish legacy servers that have zero remaining media files.
     *
     * @return  array{unpublished: int, skipped: int}
     *
     * @since   10.1.0
     */
    public static function unpublishEmptyLegacyServers(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Get published legacy servers
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('legacy'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $legacyIds = $db->loadColumn();

        if (empty($legacyIds)) {
            return ['unpublished' => 0, 'skipped' => 0];
        }

        $unpublished = 0;
        $skipped     = 0;

        foreach ($legacyIds as $serverId) {
            $countQuery = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('server_id') . ' = :serverId')
                ->bind(':serverId', $serverId, \Joomla\Database\ParameterType::INTEGER);
            $db->setQuery($countQuery);
            $count = (int) $db->loadResult();

            if ($count === 0) {
                $update = $db->getQuery(true)
                    ->update($db->quoteName('#__bsms_servers'))
                    ->set($db->quoteName('published') . ' = 0')
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $serverId, \Joomla\Database\ParameterType::INTEGER);
                $db->setQuery($update);
                $db->execute();
                $unpublished++;
            } else {
                $skipped++;
            }
        }

        return ['unpublished' => $unpublished, 'skipped' => $skipped];
    }

    /**
     * Extract URL query parameters from a platform-specific URL in the source text.
     *
     * Finds the first URL matching the given platform and parses its query string.
     * This preserves user-configured embed params (autoplay, start, loop, color, etc.)
     * that would otherwise be lost when rebuilding the embed URL from just the video ID.
     *
     * @param   string  $text      Combined filename + mediacode text
     * @param   string  $platform  Platform identifier (youtube, vimeo, wistia, dailymotion, rumble, soundcloud)
     *
     * @return  array  Associative array of query parameters, empty if none found
     *
     * @since   10.1.0
     */
    public static function extractSourceUrlParams(string $text, string $platform): array
    {
        $patterns = [
            'youtube'     => '/(?:https?:)?\/\/(?:www\.)?(?:youtube\.com|youtu\.be)\/[^\s"\'<>]+/',
            'vimeo'       => '/(?:https?:)?\/\/(?:player\.)?vimeo\.com\/[^\s"\'<>]+/',
            'wistia'      => '/(?:https?:)?\/\/(?:fast\.)?wistia\.(?:com|net)\/[^\s"\'<>]+/',
            'dailymotion' => '/(?:https?:)?\/\/(?:www\.)?dailymotion\.com\/[^\s"\'<>]+/',
            'rumble'      => '/(?:https?:)?\/\/(?:www\.)?rumble\.com\/[^\s"\'<>]+/',
            'soundcloud'  => '/(?:https?:)?\/\/(?:w\.)?soundcloud\.com\/[^\s"\'<>]+/',
            'facebook'    => '/(?:https?:)?\/\/(?:www\.)?(?:facebook\.com|fb\.watch)\/[^\s"\'<>]+/',
        ];

        if (!isset($patterns[$platform]) || empty($text)) {
            return [];
        }

        if (!preg_match($patterns[$platform], $text, $match)) {
            return [];
        }

        $url   = $match[0];
        $parts = parse_url($url);

        if (empty($parts['query'])) {
            return [];
        }

        parse_str($parts['query'], $queryParams);

        return $queryParams;
    }

    /**
     * Extract the content from an AllVideos shortcode.
     *
     * Parses shortcodes like `{youtube}dQw4w9WgXcQ{/youtube}` and returns
     * the content between the opening and closing tags (the bare video ID).
     * Returns empty string for dash-only content (`{youtube}-{/youtube}`)
     * since the dash is a placeholder meaning "use the filename field".
     *
     * @param   string  $mediacode  The mediacode field value
     *
     * @return  string  The shortcode content, or empty string if not found
     *
     * @since   10.1.0
     */
    public static function extractAllVideosContent(string $mediacode): string
    {
        if (empty($mediacode)) {
            return '';
        }

        // Match {tagname}CONTENT{/tagname}
        if (preg_match('/\{(\w+)\}([^{]*)\{\/\1\}/', $mediacode, $matches)) {
            $content = trim($matches[2]);

            // Dash is a placeholder meaning "use filename" — not a real ID
            if ($content === '-') {
                return '';
            }

            return $content;
        }

        return '';
    }

    /**
     * Strip legacy server protocol+domain prefix from a filename.
     *
     * @param   string  $filename           The filename/URL
     * @param   array   $legacyServerParams Legacy server params (path, protocol)
     *
     * @return  string  Cleaned filename (relative path)
     *
     * @since   10.1.0
     */
    public static function stripLegacyPrefix(string $filename, array $legacyServerParams): string
    {
        $path     = $legacyServerParams['path'] ?? '';
        $protocol = $legacyServerParams['protocol'] ?? '';

        if (empty($filename)) {
            return $filename;
        }

        // If the legacy server had a path, strip protocol + path prefix
        if (!empty($path)) {
            $fullPrefix = rtrim($protocol . $path, '/');

            if (!empty($fullPrefix) && str_starts_with($filename, $fullPrefix)) {
                return ltrim(substr($filename, \strlen($fullPrefix)), '/');
            }

            // Try without protocol
            if (str_starts_with($filename, $path)) {
                return ltrim(substr($filename, \strlen($path)), '/');
            }
        }

        // Strip any http(s):// prefix for local files
        $stripped = preg_replace('/^https?:\/\/[^\/]+\//i', '', $filename);

        return $stripped ?? $filename;
    }
}
