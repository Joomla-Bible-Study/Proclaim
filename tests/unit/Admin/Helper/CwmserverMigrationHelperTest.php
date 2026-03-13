<?php

/**
 * Unit tests for CwmserverMigrationHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Addons\Servers\Dailymotion\CWMAddonDailymotion;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Rumble\CWMAddonRumble;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Vimeo\CWMAddonVimeo;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Wistia\CWMAddonWistia;
use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for CwmserverMigrationHelper — focuses on pure static methods
 * that don't require a database connection.
 *
 * @since  10.1.0
 */
class CwmserverMigrationHelperTest extends ProclaimTestCase
{
    // -------------------------------------------------------------------------
    // detectContentType() tests
    // -------------------------------------------------------------------------

    #[DataProvider('contentTypeProvider')]
    public function testDetectContentType(
        string $filename,
        string $mediacode,
        string $mimeType,
        string $player,
        string $expected
    ): void {
        $result = CwmserverMigrationHelper::detectContentType($filename, $mediacode, $mimeType, $player);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, string, string, string, string}>
     */
    public static function contentTypeProvider(): array
    {
        return [
            // YouTube URLs
            'youtube watch URL' => [
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '', '', '', 'youtube',
            ],
            'youtube embed URL' => [
                '//www.youtube.com/embed/dQw4w9WgXcQ', '', '', '', 'youtube',
            ],
            'youtube short URL' => [
                'https://youtu.be/dQw4w9WgXcQ', '', '', '', 'youtube',
            ],
            'youtube live URL' => [
                'https://www.youtube.com/live/abc123xyz', '', '', '', 'youtube',
            ],
            'youtube in mediacode' => [
                '', '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>', '', '', 'youtube',
            ],

            // Vimeo URLs
            'vimeo standard URL' => [
                'https://vimeo.com/123456789', '', '', '', 'vimeo',
            ],
            'vimeo player embed' => [
                '//player.vimeo.com/video/123456789', '', '', '', 'vimeo',
            ],
            'vimeo in mediacode' => [
                '', '<iframe src="https://player.vimeo.com/video/987654"></iframe>', '', '', 'vimeo',
            ],

            // Wistia URLs
            'wistia medias URL' => [
                'https://home.wistia.com/medias/abc123xyz', '', '', '', 'wistia',
            ],
            'wistia embed URL' => [
                'https://fast.wistia.net/embed/iframe/abc123xyz', '', '', '', 'wistia',
            ],
            'wistia .com URL' => [
                'https://myaccount.wistia.com/medias/abc123', '', '', '', 'wistia',
            ],
            'wistia in mediacode' => [
                '', '<iframe src="https://fast.wistia.net/embed/iframe/abc123xyz"></iframe>', '', '', 'wistia',
            ],

            // Resi URLs
            'resi stream URL' => [
                'https://control.resi.io/webplayer/video.html?id=abc', '', '', '', 'resi',
            ],
            'resi rfrn.tv URL' => [
                'https://rfrn.tv/embed/abc123', '', '', '', 'resi',
            ],
            'resi in mediacode' => [
                '', '<iframe src="https://control.resi.io/webplayer/video.html?id=abc"></iframe>', '', '', 'resi',
            ],

            // SoundCloud URLs
            'soundcloud track URL' => [
                'https://soundcloud.com/artist/track-name', '', '', '', 'soundcloud',
            ],
            'soundcloud embed URL' => [
                '//w.soundcloud.com/player/?url=https%3A//soundcloud.com/artist/track', '', '', '', 'soundcloud',
            ],
            'soundcloud in mediacode' => [
                '', '<iframe src="https://w.soundcloud.com/player/?url=https%3A//soundcloud.com/artist/track"></iframe>', '', '', 'soundcloud',
            ],

            // Dailymotion URLs
            'dailymotion video URL' => [
                'https://www.dailymotion.com/video/x7tgad0', '', '', '', 'dailymotion',
            ],
            'dailymotion short URL' => [
                'https://dai.ly/x7tgad0', '', '', '', 'dailymotion',
            ],
            'dailymotion embed URL' => [
                '//www.dailymotion.com/embed/video/x7tgad0', '', '', '', 'dailymotion',
            ],
            'dailymotion in mediacode' => [
                '', '<iframe src="https://www.dailymotion.com/embed/video/x7tgad0"></iframe>', '', '', 'dailymotion',
            ],

            // Rumble URLs
            'rumble embed URL' => [
                'https://rumble.com/embed/v1abc23/', '', '', '', 'rumble',
            ],
            'rumble standard URL' => [
                'https://rumble.com/v1abc23-some-video-title.html', '', '', '', 'rumble',
            ],
            'rumble in mediacode' => [
                '', '<iframe src="https://rumble.com/embed/v1abc23/"></iframe>', '', '', 'rumble',
            ],

            // VirtueMart URLs
            'virtuemart component URL' => [
                'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=5', '', '', '', 'virtuemart',
            ],
            'virtuemart download URL' => [
                'https://example.com/virtuemart/download/sermon-audio.mp3', '', '', '', 'virtuemart',
            ],
            'virtuemart in mediacode' => [
                '', '<a href="index.php?option=com_virtuemart&view=productdetails">Download</a>', '', '', 'virtuemart',
            ],

            // DOCman URLs
            'docman component URL' => [
                'index.php?option=com_docman&view=document&id=42', '', '', '', 'docman',
            ],
            'docman document URL' => [
                'https://example.com/docman/documents/sermon-notes.pdf', '', '', '', 'docman',
            ],
            'docman in mediacode' => [
                '', '<a href="index.php?option=com_docman&task=document.download">Get</a>', '', '', 'docman',
            ],

            // Joomla Article URLs
            'article component URL' => [
                'index.php?option=com_content&view=article&id=42', '', '', '', 'article',
            ],
            'article in mediacode' => [
                '', '<a href="index.php?option=com_content&view=article&id=5">Read</a>', '', '', 'article',
            ],

            // Legacy player type overrides
            'player type 4 = docman' => [
                '', '', '', '4', 'docman',
            ],
            'player type 5 = article' => [
                '', '', '', '5', 'article',
            ],
            'player type 6 = virtuemart' => [
                '', '', '', '6', 'virtuemart',
            ],

            // Generic embed
            'iframe embed code' => [
                '', '<iframe src="https://example.com/player/123" width="640" height="360"></iframe>', '', '', 'embed',
            ],
            'embed tag in mediacode' => [
                '', '<embed src="https://example.com/video.swf">', '', '', 'embed',
            ],
            'object tag in mediacode' => [
                '', '<object data="https://example.com/video.swf"></object>', '', '', 'embed',
            ],
            'player type 8 with mediacode' => [
                'some-url', 'custom embed code here', '', '8', 'embed',
            ],

            // Local files
            'mp3 file' => [
                '/images/biblestudy/media/sermon.mp3', '', 'audio/mpeg', '', 'local',
            ],
            'mp4 file' => [
                'media/videos/sermon.mp4', '', '', '', 'local',
            ],
            'relative path' => [
                'sermons/2024/week1.mp3', '', '', '', 'local',
            ],
            'pdf document' => [
                'documents/notes.pdf', '', '', '', 'local',
            ],
            's3 URL' => [
                'https://my-bucket.s3.amazonaws.com/sermons/audio.mp3', '', '', '', 'local',
            ],
            's3 regional URL' => [
                'https://my-bucket.s3.us-east-1.amazonaws.com/audio.mp3', '', '', '', 'local',
            ],
            'cloudfront URL' => [
                'https://d123456.cloudfront.net/media/sermon.mp4', '', '', '', 'local',
            ],

            // Player type 7 = legacy audio
            'player type 7 = local' => [
                'sermon.mp3', '', 'audio/mpeg', '7', 'local',
            ],

            // Empty (no filename, no mediacode)
            'empty everything' => [
                '', '', '', '', 'empty',
            ],
            'unknown domain URL' => [
                'https://unknownplatform.com/video/123', '', '', '', 'unknown',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // detectContentType() with legacy ID params
    // -------------------------------------------------------------------------

    public function testDetectContentTypeWithArticleIdParam(): void
    {
        $result = CwmserverMigrationHelper::detectContentType(
            '',
            '',
            '',
            '',
            ['article_id' => '42', 'docMan_id' => '0', 'virtueMart_id' => '0']
        );
        self::assertSame('article', $result);
    }

    public function testDetectContentTypeWithDocmanIdParam(): void
    {
        $result = CwmserverMigrationHelper::detectContentType(
            '',
            '',
            '',
            '',
            ['article_id' => '', 'docMan_id' => '15', 'virtueMart_id' => '0']
        );
        self::assertSame('docman', $result);
    }

    public function testDetectContentTypeWithVirtuemartIdParam(): void
    {
        $result = CwmserverMigrationHelper::detectContentType(
            '',
            '',
            '',
            '',
            ['article_id' => '', 'docMan_id' => '0', 'virtueMart_id' => '7']
        );
        self::assertSame('virtuemart', $result);
    }

    // -------------------------------------------------------------------------
    // detectContentType() with AllVideos shortcodes
    // -------------------------------------------------------------------------

    #[DataProvider('allVideosShortcodeProvider')]
    public function testDetectContentTypeWithAllVideosShortcode(
        string $mediacode,
        string $player,
        string $expected
    ): void {
        $result = CwmserverMigrationHelper::detectContentType('', $mediacode, '', $player);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function allVideosShortcodeProvider(): array
    {
        return [
            'youtube shortcode' => [
                '{youtube}dQw4w9WgXcQ{/youtube}', '2', 'youtube',
            ],
            'youtubewide shortcode' => [
                '{youtubewide}dQw4w9WgXcQ{/youtubewide}', '3', 'youtube',
            ],
            'youtubehd shortcode' => [
                '{youtubehd}dQw4w9WgXcQ{/youtubehd}', '2', 'youtube',
            ],
            'vimeo shortcode' => [
                '{vimeo}123456789{/vimeo}', '2', 'vimeo',
            ],
            'dailymotion shortcode' => [
                '{dailymotion}x7tgad0{/dailymotion}', '2', 'dailymotion',
            ],
            'soundcloud shortcode' => [
                '{soundcloud}artist/track{/soundcloud}', '3', 'soundcloud',
            ],
            'rumble shortcode' => [
                '{rumble}v1abc23{/rumble}', '2', 'rumble',
            ],
            'mp3 shortcode = local' => [
                '{mp3}sermon.mp3{/mp3}', '2', 'local',
            ],
            'mp4 shortcode = local' => [
                '{mp4}sermon.mp4{/mp4}', '3', 'local',
            ],
            'flv shortcode = local' => [
                '{flv}video.flv{/flv}', '2', 'local',
            ],
            'player 2 unrecognized shortcode = embed' => [
                '{customtag}something{/customtag}', '2', 'embed',
            ],
            'player 3 unrecognized shortcode = embed' => [
                '{unknowntag}data{/unknowntag}', '3', 'embed',
            ],
            'youtube dash placeholder (use filename)' => [
                '{youtube}-{/youtube}', '2', 'youtube',
            ],
            'vimeo dash placeholder' => [
                '{vimeo}-{/vimeo}', '3', 'vimeo',
            ],
        ];
    }

    public function testAllVideosShortcodeOverridesUrlInFilename(): void
    {
        // Shortcode in mediacode says YouTube, even though filename is empty
        $result = CwmserverMigrationHelper::detectContentType(
            '',
            '{youtube}dQw4w9WgXcQ{/youtube}',
            '',
            '2'
        );
        self::assertSame('youtube', $result);
    }

    public function testPlayerType7DetectsAsLocal(): void
    {
        $result = CwmserverMigrationHelper::detectContentType(
            'sermon.mp3',
            '',
            'audio/mpeg',
            '7'
        );
        self::assertSame('local', $result);
    }

    public function testDetectContentTypeLegacyIdTakesPriorityOverUrl(): void
    {
        // A media file with article_id=42 but also a YouTube URL in filename —
        // the legacy ID should win because player overrides come first
        $result = CwmserverMigrationHelper::detectContentType(
            'https://www.youtube.com/watch?v=abc123',
            '',
            '',
            '5',
            ['article_id' => '42']
        );
        self::assertSame('article', $result);
    }

    // -------------------------------------------------------------------------
    // extractMediaId() tests — YouTube
    // -------------------------------------------------------------------------

    #[DataProvider('youtubeIdProvider')]
    public function testExtractYoutubeMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonYoutube::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function youtubeIdProvider(): array
    {
        return [
            'watch URL' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'embed URL' => ['//www.youtube.com/embed/dQw4w9WgXcQ?enablejsapi=1', 'dQw4w9WgXcQ'],
            'short URL' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'live URL'  => ['https://www.youtube.com/live/abc123xyz', 'abc123xyz'],
            'no match'  => ['https://vimeo.com/123456', null],
            'empty'     => ['', null],
        ];
    }

    // -------------------------------------------------------------------------
    // extractMediaId() tests — Vimeo
    // -------------------------------------------------------------------------

    #[DataProvider('vimeoIdProvider')]
    public function testExtractVimeoMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonVimeo::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function vimeoIdProvider(): array
    {
        return [
            'standard URL' => ['https://vimeo.com/123456789', '123456789'],
            'player URL'   => ['//player.vimeo.com/video/987654', '987654'],
            'iframe embed' => ['<iframe src="https://player.vimeo.com/video/555666"></iframe>', '555666'],
            'no match'     => ['https://youtube.com/watch?v=abc', null],
        ];
    }

    // -------------------------------------------------------------------------
    // extractMediaId() tests — Wistia
    // -------------------------------------------------------------------------

    #[DataProvider('wistiaHashProvider')]
    public function testExtractWistiaMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonWistia::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function wistiaHashProvider(): array
    {
        return [
            'medias URL' => ['https://home.wistia.com/medias/abc123xyz', 'abc123xyz'],
            'embed URL'  => ['https://fast.wistia.net/embed/iframe/def456', 'def456'],
            'no match'   => ['https://youtube.com/watch?v=abc', null],
        ];
    }

    // -------------------------------------------------------------------------
    // extractMediaId() tests — Dailymotion
    // -------------------------------------------------------------------------

    #[DataProvider('dailymotionIdProvider')]
    public function testExtractDailymotionMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonDailymotion::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function dailymotionIdProvider(): array
    {
        return [
            'standard URL' => ['https://www.dailymotion.com/video/x7tgad0', 'x7tgad0'],
            'embed URL'    => ['//www.dailymotion.com/embed/video/x7tgad0', 'x7tgad0'],
            'short URL'    => ['https://dai.ly/x7tgad0', 'x7tgad0'],
            'no match'     => ['https://youtube.com/video/abc', null],
        ];
    }

    // -------------------------------------------------------------------------
    // extractMediaId() tests — Rumble
    // -------------------------------------------------------------------------

    #[DataProvider('rumbleIdProvider')]
    public function testExtractRumbleMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonRumble::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function rumbleIdProvider(): array
    {
        return [
            'embed URL'    => ['https://rumble.com/embed/v1abc23/', 'v1abc23'],
            'standard URL' => ['https://rumble.com/v1abc23-some-video.html', 'v1abc23'],
            'no match'     => ['https://youtube.com/watch?v=abc', null],
        ];
    }

    // -------------------------------------------------------------------------
    // transformParams() tests
    // -------------------------------------------------------------------------

    public function testTransformParamsYoutube(): void
    {
        $params = [
            'filename'           => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'mediacode'          => '',
            'player'             => '0',
            'media_image'        => 'images/sermon.jpg',
            'media_button_text'  => 'Watch',
            'media_button_color' => 'red',
            'mime_type'          => 'video/mp4',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'youtube');

        self::assertSame('//www.youtube.com/embed/dQw4w9WgXcQ?enablejsapi=1', $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
        // Preserved display params
        self::assertSame('images/sermon.jpg', $result['media_image']);
        self::assertSame('Watch', $result['media_button_text']);
        self::assertSame('red', $result['media_button_color']);
        self::assertSame('video/mp4', $result['mime_type']);
    }

    public function testTransformParamsVimeo(): void
    {
        $params = [
            'filename'  => 'https://vimeo.com/123456789',
            'mediacode' => '',
            'player'    => '0',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'vimeo');

        self::assertSame('//player.vimeo.com/video/123456789', $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsWistia(): void
    {
        $params = [
            'filename'  => 'https://home.wistia.com/medias/abc123xyz',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'wistia');

        self::assertSame('https://fast.wistia.net/embed/iframe/abc123xyz', $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsLocal(): void
    {
        $params = [
            'filename'  => '//example.com/sermons/audio.mp3',
            'mediacode' => '',
            'player'    => '0',
        ];

        $legacyParams = [
            'path'     => '//example.com/sermons',
            'protocol' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'local', $legacyParams);

        self::assertSame('audio.mp3', $result['filename']);
        self::assertSame('0', $result['player']);
    }

    public function testTransformParamsEmbed(): void
    {
        $params = [
            'filename'  => 'https://example.com/video',
            'mediacode' => '<iframe src="https://example.com/embed/123"></iframe>',
            'player'    => '8',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'embed');

        self::assertSame('https://example.com/video', $result['filename']);
        self::assertSame('8', $result['player']);
        self::assertSame('<iframe src="https://example.com/embed/123"></iframe>', $result['mediacode']);
    }

    public function testTransformParamsDailymotion(): void
    {
        $params = [
            'filename'  => 'https://www.dailymotion.com/video/x7tgad0',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'dailymotion');

        self::assertSame('//www.dailymotion.com/embed/video/x7tgad0', $result['filename']);
        self::assertSame('1', $result['player']);
    }

    public function testTransformParamsRumble(): void
    {
        $params = [
            'filename'  => 'https://rumble.com/v1abc23-some-video.html',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'rumble');

        self::assertSame('//rumble.com/embed/v1abc23/', $result['filename']);
        self::assertSame('1', $result['player']);
    }

    public function testTransformParamsSoundcloud(): void
    {
        $params = [
            'filename'  => 'https://soundcloud.com/artist/track',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'soundcloud');

        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
        // Should contain embed URL
        self::assertStringContainsString('w.soundcloud.com/player', $result['filename']);
    }

    public function testTransformParamsArticle(): void
    {
        $params = [
            'filename'  => 'index.php?option=com_content&view=article&id=42',
            'mediacode' => '',
            'player'    => '0',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'article');

        self::assertSame($params['filename'], $result['filename']);
        self::assertSame('100', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsArticleFromLegacyId(): void
    {
        // Legacy media files store the article reference in article_id param
        $params = [
            'filename'   => '',
            'mediacode'  => '',
            'player'     => '5',
            'article_id' => '42',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'article');

        self::assertSame('index.php?option=com_content&view=article&id=42', $result['filename']);
        self::assertSame('100', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsVirtuemart(): void
    {
        $params = [
            'filename'  => 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=5',
            'mediacode' => '',
            'player'    => '0',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'virtuemart');

        self::assertSame($params['filename'], $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsVirtuemartFromLegacyId(): void
    {
        $params = [
            'filename'      => '',
            'mediacode'     => '',
            'player'        => '6',
            'virtueMart_id' => '7',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'virtuemart');

        self::assertSame(
            'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=7',
            $result['filename']
        );
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsDocman(): void
    {
        $params = [
            'filename'  => 'index.php?option=com_docman&view=document&id=42',
            'mediacode' => '',
            'player'    => '0',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'docman');

        self::assertSame($params['filename'], $result['filename']);
        self::assertSame('100', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsDocmanFromLegacyId(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '',
            'player'    => '4',
            'docMan_id' => 'sermon-notes-2024',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'docman');

        self::assertSame(
            'index.php?option=com_docman&view=document&slug=sermon-notes-2024',
            $result['filename']
        );
        self::assertSame('100', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsPreservesDisplayParams(): void
    {
        $params = [
            'filename'              => 'https://vimeo.com/123',
            'mediacode'             => '',
            'media_image'           => 'images/thumb.jpg',
            'media_use_button_icon' => '3',
            'media_button_text'     => 'Watch Video',
            'media_button_type'     => 'btn-primary',
            'media_button_color'    => '#FF0000',
            'media_icon_type'       => 'fas fa-play',
            'media_custom_icon'     => '',
            'media_icon_text_size'  => '24',
            'mime_type'             => 'video/mp4',
            'size'                  => '1024',
            'duration'              => '300',
            'autostart'             => '2',
            'popup'                 => '1',
            'link_type'             => '1',
            'playerwidth'           => '640',
            'playerheight'          => '360',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'vimeo');

        // All display params should be preserved
        self::assertSame('images/thumb.jpg', $result['media_image']);
        self::assertSame('3', $result['media_use_button_icon']);
        self::assertSame('Watch Video', $result['media_button_text']);
        self::assertSame('btn-primary', $result['media_button_type']);
        self::assertSame('#FF0000', $result['media_button_color']);
        self::assertSame('fas fa-play', $result['media_icon_type']);
        self::assertSame('24', $result['media_icon_text_size']);
        self::assertSame('video/mp4', $result['mime_type']);
        self::assertSame('1024', $result['size']);
        self::assertSame('300', $result['duration']);
        self::assertSame('2', $result['autostart']);
        self::assertSame('1', $result['popup']);
        self::assertSame('1', $result['link_type']);
        self::assertSame('640', $result['playerwidth']);
        self::assertSame('360', $result['playerheight']);
    }

    // -------------------------------------------------------------------------
    // Dynamic type/label methods tests
    // -------------------------------------------------------------------------

    public function testGetTargetTypesIncludesAllExpected(): void
    {
        $expected = [
            'youtube', 'vimeo', 'wistia', 'resi',
            'soundcloud', 'dailymotion', 'rumble', 'facebook',
            'embed', 'article', 'virtuemart', 'docman', 'local',
            'direct', 'googledrive',
        ];

        $actual = CwmserverMigrationHelper::getTargetTypes();
        sort($expected);
        sort($actual);
        self::assertSame($expected, $actual);
    }

    public function testGetTypeLabelsCoversAllTargetTypes(): void
    {
        foreach (CwmserverMigrationHelper::getTargetTypes() as $type) {
            self::assertArrayHasKey($type, CwmserverMigrationHelper::getTypeLabels());
        }

        // Also includes 'unknown'
        self::assertArrayHasKey('unknown', CwmserverMigrationHelper::getTypeLabels());
    }

    // -------------------------------------------------------------------------
    // extractAllVideosContent() tests
    // -------------------------------------------------------------------------

    #[DataProvider('allVideosContentProvider')]
    public function testExtractAllVideosContent(string $mediacode, string $expected): void
    {
        $result = CwmserverMigrationHelper::extractAllVideosContent($mediacode);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function allVideosContentProvider(): array
    {
        return [
            'youtube bare ID' => [
                '{youtube}dQw4w9WgXcQ{/youtube}', 'dQw4w9WgXcQ',
            ],
            'vimeo numeric ID' => [
                '{vimeo}123456789{/vimeo}', '123456789',
            ],
            'dailymotion ID' => [
                '{dailymotion}x7tgad0{/dailymotion}', 'x7tgad0',
            ],
            'mp3 filename' => [
                '{mp3}sermon.mp3{/mp3}', 'sermon.mp3',
            ],
            'dash placeholder returns empty' => [
                '{youtube}-{/youtube}', '',
            ],
            'vimeo dash placeholder returns empty' => [
                '{vimeo}-{/vimeo}', '',
            ],
            'empty mediacode' => [
                '', '',
            ],
            'no shortcode tags' => [
                '<iframe src="https://example.com"></iframe>', '',
            ],
            'URL inside shortcode' => [
                '{youtube}https://youtu.be/dQw4w9WgXcQ{/youtube}', 'https://youtu.be/dQw4w9WgXcQ',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // transformParams() with AllVideos bare IDs
    // -------------------------------------------------------------------------

    public function testTransformParamsYoutubeFromAllVideos(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '{youtube}dQw4w9WgXcQ{/youtube}',
            'player'    => '2',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'youtube');

        self::assertSame('//www.youtube.com/embed/dQw4w9WgXcQ?enablejsapi=1', $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsVimeoFromAllVideos(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '{vimeo}123456789{/vimeo}',
            'player'    => '2',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'vimeo');

        self::assertSame('//player.vimeo.com/video/123456789', $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsDailymotionFromAllVideos(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '{dailymotion}x7tgad0{/dailymotion}',
            'player'    => '3',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'dailymotion');

        self::assertSame('//www.dailymotion.com/embed/video/x7tgad0', $result['filename']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsYoutubeAllVideosDashUsesFilename(): void
    {
        // Dash placeholder means "use filename field" — should fall back
        $params = [
            'filename'  => 'https://www.youtube.com/watch?v=abc123',
            'mediacode' => '{youtube}-{/youtube}',
            'player'    => '2',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'youtube');

        self::assertSame('//www.youtube.com/embed/abc123?enablejsapi=1', $result['filename']);
        self::assertSame('1', $result['player']);
    }

    // -------------------------------------------------------------------------
    // transformParams() preserves embed URL query params
    // -------------------------------------------------------------------------

    public function testTransformParamsYoutubePreservesIframeParams(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&start=30&loop=1&rel=0" allowfullscreen></iframe>',
            'player'    => '8',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'youtube');

        self::assertStringContainsString('dQw4w9WgXcQ', $result['filename']);
        self::assertStringContainsString('enablejsapi=1', $result['filename']);
        // URL params are now mapped to form fields, not kept in filename
        self::assertSame('true', $result['autostart']);
        self::assertSame('30', $result['yt_start']);
        self::assertSame('1', $result['yt_loop']);
        self::assertSame('0', $result['yt_rel']);
        self::assertSame('1', $result['player']);
        self::assertSame('', $result['mediacode']);
    }

    public function testTransformParamsYoutubePreservesWatchUrlParams(): void
    {
        $params = [
            'filename'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=45&list=PLabc',
            'mediacode' => '',
            'player'    => '0',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'youtube');

        self::assertStringContainsString('dQw4w9WgXcQ', $result['filename']);
        self::assertStringContainsString('enablejsapi=1', $result['filename']);
        // 'v' param should not be in the rebuilt embed URL
        self::assertStringNotContainsString('v=dQw4w9WgXcQ', $result['filename']);
        // Note: 't' and 'list' are YouTube watch URL params, not standard embed params,
        // so they are not mapped to form fields (only standard embed API params are mapped)
    }

    public function testTransformParamsVimeoPreservesIframeParams(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '<iframe src="https://player.vimeo.com/video/123456789?color=FF0000&title=0&byline=0&portrait=0" allowfullscreen></iframe>',
            'player'    => '8',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'vimeo');

        self::assertStringContainsString('123456789', $result['filename']);
        // URL params are now mapped to form fields
        self::assertSame('FF0000', $result['vm_color']);
        self::assertSame('0', $result['vm_title']);
        self::assertSame('0', $result['vm_byline']);
        self::assertSame('0', $result['vm_portrait']);
    }

    public function testTransformParamsWistiaPreservesParams(): void
    {
        $params = [
            'filename'  => 'https://fast.wistia.net/embed/iframe/abc123xyz?autoPlay=true&controlsVisibleOnLoad=false',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'wistia');

        self::assertStringContainsString('abc123xyz', $result['filename']);
        // URL params are now mapped to form fields
        self::assertSame('true', $result['autostart']);
        self::assertSame('false', $result['ws_controls_visible']);
    }

    public function testTransformParamsDailymotionPreservesParams(): void
    {
        $params = [
            'filename'  => '',
            'mediacode' => '<iframe src="https://www.dailymotion.com/embed/video/x7tgad0?autoplay=1&mute=1&startTime=15"></iframe>',
            'player'    => '8',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'dailymotion');

        self::assertStringContainsString('x7tgad0', $result['filename']);
        // URL params are now mapped to form fields
        self::assertSame('true', $result['autostart']);
        self::assertSame('1', $result['dm_mute']);
        self::assertSame('15', $result['dm_start']);
    }

    public function testTransformParamsRumblePreservesParams(): void
    {
        $params = [
            'filename'  => 'https://rumble.com/embed/v1abc23/?pub=abc&autoplay=2',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'rumble');

        self::assertStringContainsString('v1abc23', $result['filename']);
        // URL params are now mapped to form fields
        self::assertSame('abc', $result['rb_pub']);
        self::assertSame('true', $result['autostart']);
    }

    public function testTransformParamsSoundcloudPreservesEmbedUrl(): void
    {
        // If someone had a full SoundCloud embed URL in an iframe, preserve it
        $embedUrl = '//w.soundcloud.com/player/?url=https%3A//soundcloud.com/artist/track&color=%23ff5500&auto_play=true&hide_related=true';
        $params   = [
            'filename'  => '',
            'mediacode' => '<iframe src="' . $embedUrl . '"></iframe>',
            'player'    => '8',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'soundcloud');

        self::assertSame($embedUrl, $result['filename']);
        self::assertSame('1', $result['player']);
    }

    public function testTransformParamsCleanUrlNoExtraParams(): void
    {
        // Clean URLs without params should still produce clean output (no trailing ?)
        $params = [
            'filename'  => 'https://vimeo.com/123456789',
            'mediacode' => '',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'vimeo');

        self::assertSame('//player.vimeo.com/video/123456789', $result['filename']);
        self::assertStringNotContainsString('?', $result['filename']);
    }

    // -------------------------------------------------------------------------
    // extractSourceUrlParams() tests
    // -------------------------------------------------------------------------

    public function testExtractSourceUrlParamsYoutubeWatch(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            'https://www.youtube.com/watch?v=abc123&start=30&autoplay=1',
            'youtube'
        );

        self::assertSame('abc123', $params['v']);
        self::assertSame('30', $params['start']);
        self::assertSame('1', $params['autoplay']);
    }

    public function testExtractSourceUrlParamsYoutubeEmbed(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            '<iframe src="https://www.youtube.com/embed/abc123?autoplay=1&loop=1&rel=0"></iframe>',
            'youtube'
        );

        self::assertSame('1', $params['autoplay']);
        self::assertSame('1', $params['loop']);
        self::assertSame('0', $params['rel']);
    }

    public function testExtractSourceUrlParamsVimeo(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            '<iframe src="https://player.vimeo.com/video/123?color=FF0000&title=0"></iframe>',
            'vimeo'
        );

        self::assertSame('FF0000', $params['color']);
        self::assertSame('0', $params['title']);
    }

    public function testExtractSourceUrlParamsNoParams(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            'https://vimeo.com/123456789',
            'vimeo'
        );

        self::assertSame([], $params);
    }

    public function testExtractSourceUrlParamsUnknownPlatform(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            'https://example.com/video?id=123',
            'unknown'
        );

        self::assertSame([], $params);
    }

    public function testTransformParamsAllVideosPlayer2ToEmbed(): void
    {
        // Unrecognized AllVideos shortcode → embed, preserve mediacode
        $params = [
            'filename'  => 'https://example.com/video',
            'mediacode' => '{customtag}something{/customtag}',
            'player'    => '2',
        ];

        $result = CwmserverMigrationHelper::transformParams($params, 'embed');

        self::assertSame('https://example.com/video', $result['filename']);
        self::assertSame('8', $result['player']);
        self::assertSame('{customtag}something{/customtag}', $result['mediacode']);
    }
}
