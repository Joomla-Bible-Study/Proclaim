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

use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

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

    /**
     * @dataProvider contentTypeProvider
     */
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

            // Resi URLs
            'resi stream URL' => [
                'https://control.resi.io/webplayer/video.html?id=abc', '', '', '', 'resi',
            ],
            'resi rfrn.tv URL' => [
                'https://rfrn.tv/embed/abc123', '', '', '', 'resi',
            ],

            // SoundCloud URLs
            'soundcloud track URL' => [
                'https://soundcloud.com/artist/track-name', '', '', '', 'soundcloud',
            ],
            'soundcloud embed URL' => [
                '//w.soundcloud.com/player/?url=https%3A//soundcloud.com/artist/track', '', '', '', 'soundcloud',
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

            // Rumble URLs
            'rumble embed URL' => [
                'https://rumble.com/embed/v1abc23/', '', '', '', 'rumble',
            ],
            'rumble standard URL' => [
                'https://rumble.com/v1abc23-some-video-title.html', '', '', '', 'rumble',
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

            // Unknown
            'empty everything' => [
                '', '', '', '', 'unknown',
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
    // extractYoutubeId() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider youtubeIdProvider
     */
    public function testExtractYoutubeId(string $text, ?string $expected): void
    {
        $result = CwmserverMigrationHelper::extractYoutubeId($text);
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
    // extractVimeoId() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider vimeoIdProvider
     */
    public function testExtractVimeoId(string $text, ?string $expected): void
    {
        $result = CwmserverMigrationHelper::extractVimeoId($text);
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
    // extractWistiaHash() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider wistiaHashProvider
     */
    public function testExtractWistiaHash(string $text, ?string $expected): void
    {
        $result = CwmserverMigrationHelper::extractWistiaHash($text);
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
    // extractDailymotionId() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider dailymotionIdProvider
     */
    public function testExtractDailymotionId(string $text, ?string $expected): void
    {
        $result = CwmserverMigrationHelper::extractDailymotionId($text);
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
    // extractRumbleId() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider rumbleIdProvider
     */
    public function testExtractRumbleId(string $text, ?string $expected): void
    {
        $result = CwmserverMigrationHelper::extractRumbleId($text);
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
    // Constants tests
    // -------------------------------------------------------------------------

    public function testTargetTypesIncludesAllExpected(): void
    {
        $expected = [
            'youtube', 'vimeo', 'wistia', 'resi',
            'soundcloud', 'dailymotion', 'rumble', 'embed',
            'article', 'virtuemart', 'docman', 'local',
        ];

        self::assertSame($expected, CwmserverMigrationHelper::TARGET_TYPES);
    }

    public function testTypeLabelsCoversAllTargetTypes(): void
    {
        foreach (CwmserverMigrationHelper::TARGET_TYPES as $type) {
            self::assertArrayHasKey($type, CwmserverMigrationHelper::TYPE_LABELS);
        }

        // Also includes 'unknown'
        self::assertArrayHasKey('unknown', CwmserverMigrationHelper::TYPE_LABELS);
    }
}
