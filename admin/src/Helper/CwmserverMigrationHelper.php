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
     * Supported target server types for migration.
     *
     * @var    string[]
     * @since  10.1.0
     */
    public const TARGET_TYPES = [
        'youtube',
        'vimeo',
        'wistia',
        'resi',
        'soundcloud',
        'dailymotion',
        'rumble',
        'facebook',
        'embed',
        'article',
        'virtuemart',
        'docman',
        'local',
    ];

    /**
     * Display-friendly labels per detected type.
     *
     * @var    array<string, string>
     * @since  10.1.0
     */
    public const TYPE_LABELS = [
        'youtube'     => 'YouTube',
        'vimeo'       => 'Vimeo',
        'wistia'      => 'Wistia',
        'resi'        => 'Resi',
        'soundcloud'  => 'SoundCloud',
        'dailymotion' => 'Dailymotion',
        'rumble'      => 'Rumble',
        'facebook'    => 'Facebook',
        'embed'       => 'Embed / iFrame',
        'article'     => 'Article',
        'virtuemart'  => 'VirtueMart',
        'docman'      => 'DOCman',
        'local'       => 'Local',
        'unknown'     => 'Unknown',
    ];

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

                $mediaParams = json_decode($media->params ?? '{}', true) ?: [];
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
     * AllVideos shortcode tag-to-type map.
     *
     * Maps AllVideos Reloaded shortcode names to migration target types.
     *
     * @var    array<string, string>
     * @since  10.1.0
     */
    private const ALLVIDEOS_TAG_MAP = [
        'youtube'     => 'youtube',
        'youtubewide' => 'youtube',
        'youtubehd'   => 'youtube',
        'vimeo'       => 'vimeo',
        'dailymotion' => 'dailymotion',
        'soundcloud'  => 'soundcloud',
        'rumble'      => 'rumble',
    ];

    /**
     * AllVideos shortcode tags that represent local media files.
     *
     * @var    string[]
     * @since  10.1.0
     */
    private const ALLVIDEOS_LOCAL_TAGS = [
        'mp3', 'mp4', 'flv', 'wmv', 'wma', 'ogg', 'ogv', 'webm', 'wav',
        'divx', 'mov', 'swf', 'avi', 'aac',
    ];

    /**
     * Detect the actual content platform from media file data.
     *
     * Priority: Legacy player overrides (4/5/6) > AllVideos shortcodes (player 2/3)
     *           > YouTube > Vimeo > Wistia > Resi > SoundCloud > Dailymotion > Rumble
     *           > VirtueMart > DOCman > Article > Embed (iframe) > Player 7 (audio)
     *           > Local > Unknown
     *
     * @param   string  $filename    The filename/URL from media params
     * @param   string  $mediacode   The embed code from media params
     * @param   string  $mimeType    The MIME type
     * @param   string  $player      The player setting
     * @param   array   $allParams   Full media params array for legacy ID field detection
     *
     * @return  string  One of: youtube, vimeo, wistia, resi, soundcloud,
     *                  dailymotion, rumble, virtuemart, docman, article, embed, local, unknown
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
        // Legacy player type overrides (set via docMan_id/article_id/virtueMart_id params)
        if ($player === '4' || (!empty($allParams['docMan_id']) && $allParams['docMan_id'] !== '0' && $allParams['docMan_id'] !== '-1')) {
            return 'docman';
        }

        if ($player === '5' || (!empty($allParams['article_id']) && $allParams['article_id'] !== '0' && $allParams['article_id'] !== '-1' && $allParams['article_id'] !== '')) {
            return 'article';
        }

        if ($player === '6' || (!empty($allParams['virtueMart_id']) && $allParams['virtueMart_id'] !== '0' && $allParams['virtueMart_id'] !== '-1')) {
            return 'virtuemart';
        }

        // AllVideos shortcodes: {youtube}ID{/youtube}, {vimeo}ID{/vimeo}, etc.
        // Used with player types 2/3 (AllVideos Reloaded popup/inline)
        if (!empty($mediacode) && preg_match('/\{(\w+)\}/', $mediacode, $avMatch)) {
            $tag = strtolower($avMatch[1]);

            if (isset(self::ALLVIDEOS_TAG_MAP[$tag])) {
                return self::ALLVIDEOS_TAG_MAP[$tag];
            }

            if (\in_array($tag, self::ALLVIDEOS_LOCAL_TAGS, true)) {
                return 'local';
            }
        }

        // Player types 2/3 without recognized shortcode — check URL patterns first,
        // then fall back to embed
        $isAllVideosPlayer = ($player === '2' || $player === '3');

        $combined = $filename . ' ' . $mediacode;

        // YouTube
        if (preg_match('/youtu(be\.com|\.be)\//i', $combined)
            || preg_match('/youtube\.com\/embed\//i', $combined)
        ) {
            return 'youtube';
        }

        // Vimeo
        if (preg_match('/vimeo\.com/i', $combined)
            || preg_match('/player\.vimeo\.com/i', $combined)
        ) {
            return 'vimeo';
        }

        // Wistia
        if (preg_match('/wistia\.(com|net)/i', $combined)
            || preg_match('/fast\.wistia/i', $combined)
        ) {
            return 'wistia';
        }

        // Resi
        if (preg_match('/rfrn\.(tv|stream)|resi\.(io|media)/i', $combined)
            || preg_match('/control\.resi\.io/i', $combined)
        ) {
            return 'resi';
        }

        // SoundCloud
        if (preg_match('/soundcloud\.com/i', $combined)
            || preg_match('/w\.soundcloud\.com/i', $combined)
        ) {
            return 'soundcloud';
        }

        // Dailymotion
        if (preg_match('/dailymotion\.com/i', $combined)
            || preg_match('/dai\.ly/i', $combined)
        ) {
            return 'dailymotion';
        }

        // Rumble
        if (preg_match('/rumble\.com/i', $combined)) {
            return 'rumble';
        }

        // Facebook
        if (preg_match('/facebook\.com/i', $combined)
            || preg_match('/fb\.watch/i', $combined)
        ) {
            return 'facebook';
        }

        // VirtueMart (Joomla e-commerce digital downloads)
        if (preg_match('/com_virtuemart/i', $combined)
            || preg_match('/virtuemart.*download/i', $combined)
        ) {
            return 'virtuemart';
        }

        // DOCman (Joomlatools document management)
        if (preg_match('/com_docman/i', $combined)
            || preg_match('/docman.*document/i', $combined)
        ) {
            return 'docman';
        }

        // Joomla core articles (com_content)
        if (preg_match('/com_content/i', $combined)
            || preg_match('/option=com_content/i', $combined)
        ) {
            return 'article';
        }

        // Generic embed code (has iframe/embed/object tag in mediacode)
        if (!empty($mediacode) && preg_match('/<(iframe|embed|object)\b/i', $mediacode)) {
            return 'embed';
        }

        // Player type 8 = embed code (even without iframe tag in mediacode)
        if ($player === '8' && !empty($mediacode)) {
            return 'embed';
        }

        // AllVideos player types 2/3 with unrecognized content → embed
        if ($isAllVideosPlayer && !empty($mediacode)) {
            return 'embed';
        }

        // Player type 7 = legacy simple audio player → local
        if ($player === '7') {
            return 'local';
        }

        // Local file detection: relative paths, no known platform domain
        if (!empty($filename)) {
            // Has a file extension typical of audio/video/document
            if (preg_match('/\.(mp3|mp4|m4a|m4v|ogg|ogv|webm|wav|flac|aac|pdf|doc|docx|ppt|pptx|zip)$/i', $filename)) {
                return 'local';
            }

            // Relative path (no protocol, starts with / or doesn't have ://)
            if (!preg_match('/^(https?:)?\/\//i', $filename) && !str_contains($filename, '://')) {
                return 'local';
            }

            // S3/CloudFront URLs treated as local (direct file hosting)
            if (preg_match('/\.s3[\.-].*?amazonaws\.com/i', $filename)
                || preg_match('/cloudfront\.net/i', $filename)
            ) {
                return 'local';
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
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('params')])
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('server_id') . ' = :serverId')
            ->bind(':serverId', $legacyServerId, \Joomla\Database\ParameterType::INTEGER)
            ->setLimit($limit, $offset);
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $ids = [];

        foreach ($rows as $row) {
            $params    = json_decode($row->params ?? '{}', true) ?: [];
            $filename  = $params['filename'] ?? '';
            $mediacode = $params['mediacode'] ?? '';
            $mimeType  = $params['mime_type'] ?? '';
            $player    = $params['player'] ?? '';

            if (self::detectContentType($filename, $mediacode, $mimeType, $player, $params) === $detectedType) {
                $ids[] = (int) $row->id;
            }
        }

        return $ids;
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

                $params        = json_decode($row->params ?? '{}', true) ?: [];
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

        switch ($targetType) {
            case 'youtube':
                $videoId     = self::extractYoutubeId($combined);
                $sourceQuery = self::extractSourceUrlParams($combined, 'youtube');

                // Fall back to AllVideos bare ID: {youtube}dQw4w9WgXcQ{/youtube}
                if ($videoId === null && !empty($avContent) && preg_match('/^[a-zA-Z0-9_-]+$/', $avContent)) {
                    $videoId = $avContent;
                }

                if ($videoId) {
                    // Clean embed URL — form fields handle params now
                    $result['filename'] = '//www.youtube.com/embed/' . $videoId . '?enablejsapi=1';
                } else {
                    $result['filename'] = $filename;
                }

                // Map extracted URL params → embed option form fields
                $ytParamMap = [
                    'mute'           => 'yt_mute',
                    'start'          => 'yt_start',
                    'end'            => 'yt_end',
                    'loop'           => 'yt_loop',
                    'controls'       => 'yt_controls',
                    'rel'            => 'yt_rel',
                    'cc_load_policy' => 'yt_cc',
                    'playsinline'    => 'yt_playsinline',
                ];

                foreach ($ytParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                // Map autoplay → autostart (existing field)
                if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === '1') {
                    $result['autostart'] = 'true';
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'vimeo':
                $videoId     = self::extractVimeoId($combined);
                $sourceQuery = self::extractSourceUrlParams($combined, 'vimeo');

                // Fall back to AllVideos bare ID: {vimeo}123456789{/vimeo}
                if ($videoId === null && !empty($avContent) && preg_match('/^\d+$/', $avContent)) {
                    $videoId = $avContent;
                }

                if ($videoId) {
                    // Clean embed URL — form fields handle params now
                    $result['filename'] = '//player.vimeo.com/video/' . $videoId;
                } else {
                    $result['filename'] = $filename;
                }

                // Map extracted URL params → embed option form fields
                $vmParamMap = [
                    'muted'      => 'vm_muted',
                    'loop'       => 'vm_loop',
                    'controls'   => 'vm_controls',
                    'color'      => 'vm_color',
                    'title'      => 'vm_title',
                    'byline'     => 'vm_byline',
                    'portrait'   => 'vm_portrait',
                    'dnt'        => 'vm_dnt',
                    'background' => 'vm_background',
                    'speed'      => 'vm_speed',
                ];

                foreach ($vmParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === '1') {
                    $result['autostart'] = 'true';
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'wistia':
                $hash        = self::extractWistiaHash($combined);
                $sourceQuery = self::extractSourceUrlParams($combined, 'wistia');

                if ($hash) {
                    // Clean embed URL — form fields handle params now
                    $result['filename'] = 'https://fast.wistia.net/embed/iframe/' . $hash;
                } else {
                    $result['filename'] = $filename;
                }

                // Map extracted URL params → embed option form fields
                $wsParamMap = [
                    'muted'                 => 'ws_muted',
                    'playerColor'           => 'ws_player_color',
                    'controlsVisibleOnLoad' => 'ws_controls_visible',
                    'playbar'               => 'ws_playbar',
                    'endVideoBehavior'      => 'ws_end_behavior',
                    'doNotTrack'            => 'ws_dnt',
                    'time'                  => 'ws_time',
                    'resumable'             => 'ws_resumable',
                    'playbackRateControl'   => 'ws_speed',
                ];

                foreach ($wsParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                if (isset($sourceQuery['autoPlay']) && $sourceQuery['autoPlay'] === 'true') {
                    $result['autostart'] = 'true';
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'resi':
                $result['filename']  = $filename;
                $result['player']    = '1';
                $result['mediacode'] = '';

                // Extract query params from Resi embed URL if present
                $resiParts = parse_url($filename);

                if (!empty($resiParts['query'])) {
                    $resiQuery  = [];
                    parse_str($resiParts['query'], $resiQuery);

                    $riParamMap = [
                        'controls'   => 'ri_controls',
                        'loop'       => 'ri_loop',
                        'startPos'   => 'ri_start_pos',
                        'background' => 'ri_background',
                    ];

                    foreach ($riParamMap as $urlParam => $formField) {
                        if (isset($resiQuery[$urlParam]) && $resiQuery[$urlParam] !== '') {
                            $result[$formField] = $resiQuery[$urlParam];
                        }
                    }

                    if (isset($resiQuery['autoplay']) && $resiQuery['autoplay'] === '1') {
                        $result['autostart'] = 'true';
                    }
                }

                break;

            case 'soundcloud':
                $sourceQuery         = self::extractSourceUrlParams($combined, 'soundcloud');
                $result['filename']  = $filename;
                $result['player']    = '1';
                $result['mediacode'] = '';

                // Map extracted URL params → embed option form fields
                $scParamMap = [
                    'color'         => 'sc_color',
                    'hide_related'  => 'sc_hide_related',
                    'show_comments' => 'sc_show_comments',
                    'show_user'     => 'sc_show_user',
                    'visual'        => 'sc_visual',
                ];

                foreach ($scParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                if (isset($sourceQuery['auto_play']) && $sourceQuery['auto_play'] === 'true') {
                    $result['autostart'] = 'true';
                }

                // If already an embed player URL with params, preserve it
                if (str_contains(strtolower($filename), 'w.soundcloud.com/player')) {
                    break;
                }

                // If iframe in mediacode had an embed URL, extract and use it
                if (!empty($mediacode) && preg_match('/src=["\']([^"\']*w\.soundcloud\.com\/player[^"\']*)["\']/', $mediacode, $scMatch)) {
                    $result['filename'] = $scMatch[1];

                    break;
                }

                // Convert track URL to embed, using defaults
                if (str_contains(strtolower($filename), 'soundcloud.com')) {
                    $result['filename'] = '//w.soundcloud.com/player/?url=' . urlencode($filename)
                        . '&color=%23ff5500&auto_play=false&hide_related=true&show_comments=false&show_user=true&show_reposts=false&show_teaser=false';
                }

                break;

            case 'dailymotion':
                $videoId     = self::extractDailymotionId($combined);
                $sourceQuery = self::extractSourceUrlParams($combined, 'dailymotion');

                // Fall back to AllVideos bare ID: {dailymotion}x7tgad0{/dailymotion}
                if ($videoId === null && !empty($avContent) && preg_match('/^[a-z0-9]+$/i', $avContent)) {
                    $videoId = $avContent;
                }

                if ($videoId) {
                    // Clean embed URL — form fields handle params now
                    $result['filename'] = '//www.dailymotion.com/embed/video/' . $videoId;
                } else {
                    $result['filename'] = $filename;
                }

                // Map extracted URL params → embed option form fields
                $dmParamMap = [
                    'mute'      => 'dm_mute',
                    'startTime' => 'dm_start',
                    'loop'      => 'dm_loop',
                    'scaleMode' => 'dm_scale',
                ];

                foreach ($dmParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === '1') {
                    $result['autostart'] = 'true';
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'rumble':
                $embedId     = self::extractRumbleId($combined);
                $sourceQuery = self::extractSourceUrlParams($combined, 'rumble');

                // Fall back to AllVideos bare ID: {rumble}v1abc23{/rumble}
                if ($embedId === null && !empty($avContent) && preg_match('/^v[a-z0-9]+$/i', $avContent)) {
                    $embedId = $avContent;
                }

                if ($embedId) {
                    // Clean embed URL — form fields handle params now
                    $result['filename'] = '//rumble.com/embed/' . $embedId . '/';
                } else {
                    $result['filename'] = $filename;
                }

                // Map extracted URL params → embed option form fields
                $rbParamMap = [
                    'rel' => 'rb_rel',
                    'pub' => 'rb_pub',
                ];

                foreach ($rbParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === '2') {
                    $result['autostart'] = 'true';
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'facebook':
                $sourceQuery = self::extractSourceUrlParams($combined, 'facebook');

                // If already an embed URL, extract the href param as the source URL
                if (str_contains(strtolower($filename), 'facebook.com/plugins/video.php')) {
                    $embedParts = parse_url($filename);
                    $embedQuery = [];

                    if (!empty($embedParts['query'])) {
                        parse_str($embedParts['query'], $embedQuery);
                    }

                    // Use href param as the clean source, or keep the embed URL
                    $sourceUrl          = !empty($embedQuery['href']) ? urldecode($embedQuery['href']) : $filename;
                    $result['filename'] = 'https://www.facebook.com/plugins/video.php?href='
                        . urlencode($sourceUrl) . '&show_text=false';

                    // Map embed params to form fields
                    $sourceQuery = $embedQuery;
                } else {
                    // Convert any Facebook URL to embed format
                    $result['filename'] = 'https://www.facebook.com/plugins/video.php?href='
                        . urlencode($filename) . '&show_text=false';
                }

                // Map extracted URL params → embed option form fields
                $fbParamMap = [
                    'show_text' => 'fb_show_text',
                    'muted'     => 'fb_muted',
                    'lazy'      => 'fb_lazy',
                    't'         => 'fb_t',
                ];

                foreach ($fbParamMap as $urlParam => $formField) {
                    if (isset($sourceQuery[$urlParam]) && $sourceQuery[$urlParam] !== '') {
                        $result[$formField] = $sourceQuery[$urlParam];
                    }
                }

                if (isset($sourceQuery['autoplay']) && $sourceQuery['autoplay'] === 'true') {
                    $result['autostart'] = 'true';
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'embed':
                $result['filename']  = $filename;
                $result['player']    = '8';
                $result['mediacode'] = $mediacode;
                break;

            case 'article':
                // Build URL from legacy article_id param if available
                $articleId = $params['article_id'] ?? '';

                if (!empty($articleId) && $articleId !== '0' && $articleId !== '') {
                    $result['filename'] = 'index.php?option=com_content&view=article&id=' . (int) $articleId;
                } else {
                    $result['filename'] = $filename;
                }

                $result['player']    = '100';
                $result['mediacode'] = '';
                break;

            case 'virtuemart':
                // Build URL from legacy virtueMart_id param if available
                $vmId = $params['virtueMart_id'] ?? '';

                if (!empty($vmId) && $vmId !== '0') {
                    $result['filename'] = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . (int) $vmId;
                } else {
                    $result['filename'] = $filename;
                }

                $result['player']    = '1';
                $result['mediacode'] = '';
                break;

            case 'docman':
                // Build URL from legacy docMan_id param if available
                $docmanId = $params['docMan_id'] ?? '';

                if (!empty($docmanId) && $docmanId !== '0') {
                    $result['filename'] = 'index.php?option=com_docman&view=document&slug=' . $docmanId;
                } else {
                    $result['filename'] = $filename;
                }

                $result['player']    = '100';
                $result['mediacode'] = '';
                break;

            case 'local':
            default:
                // Strip protocol+domain using legacy server path/protocol
                $result['filename']  = self::stripLegacyPrefix($filename, $legacyServerParams);
                $result['player']    = $params['player'] ?? '';
                $result['mediacode'] = $mediacode;
                break;
        }

        return $result;
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
     * Extract YouTube video ID from a string (URL or embed).
     *
     * @param   string  $text  Text containing a YouTube URL
     *
     * @return  string|null  Video ID or null
     *
     * @since   10.1.0
     */
    public static function extractYoutubeId(string $text): ?string
    {
        $patterns = [
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/live\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract Vimeo video ID from a string (URL or embed).
     *
     * @param   string  $text  Text containing a Vimeo URL
     *
     * @return  string|null  Numeric video ID or null
     *
     * @since   10.1.0
     */
    public static function extractVimeoId(string $text): ?string
    {
        $patterns = [
            '/vimeo\.com\/(\d+)/',
            '/player\.vimeo\.com\/video\/(\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract Wistia media hash from a string.
     *
     * @param   string  $text  Text containing a Wistia URL
     *
     * @return  string|null  Media hash or null
     *
     * @since   10.1.0
     */
    public static function extractWistiaHash(string $text): ?string
    {
        $patterns = [
            '/wistia\.com\/medias\/([a-z0-9]+)/i',
            '/fast\.wistia\.net\/embed\/iframe\/([a-z0-9]+)/i',
            '/wistia\.net\/medias\/([a-z0-9]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract Dailymotion video ID from a string.
     *
     * @param   string  $text  Text containing a Dailymotion URL
     *
     * @return  string|null  Video ID or null
     *
     * @since   10.1.0
     */
    public static function extractDailymotionId(string $text): ?string
    {
        $patterns = [
            '/dailymotion\.com\/video\/([a-z0-9]+)/i',
            '/dailymotion\.com\/embed\/video\/([a-z0-9]+)/i',
            '/dai\.ly\/([a-z0-9]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract Rumble embed ID from a string.
     *
     * @param   string  $text  Text containing a Rumble URL
     *
     * @return  string|null  Embed ID or null
     *
     * @since   10.1.0
     */
    public static function extractRumbleId(string $text): ?string
    {
        // Rumble embed URLs: rumble.com/embed/v1abc23/
        if (preg_match('/rumble\.com\/embed\/(v[a-z0-9]+)/i', $text, $matches)) {
            return $matches[1];
        }

        // Standard URLs: rumble.com/v1abc23-title.html
        if (preg_match('/rumble\.com\/(v[a-z0-9]+)/i', $text, $matches)) {
            return $matches[1];
        }

        return null;
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
    private static function stripLegacyPrefix(string $filename, array $legacyServerParams): string
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
