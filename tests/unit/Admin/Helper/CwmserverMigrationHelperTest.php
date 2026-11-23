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
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
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

    /**
     * detectContentType() picks the expected type for each case, from the filename, the
     * media code, the MIME type or the legacy player number.
     *
     * @param   string  $filename   Media filename or URL
     * @param   string  $mediacode  Stored media code
     * @param   string  $mimeType   MIME type
     * @param   string  $player     Legacy player number
     * @param   string  $expected   The type it should detect
     *
     * @return  void
     *
     * @since   10.1.0
     */
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
     * Filenames, media codes, MIME types and player numbers, each with the content type
     * they should be detected as.
     *
     * @return  array<string, array{string, string, string, string, string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * An article_id in the legacy params makes the type article.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A docMan_id in the legacy params makes the type docman.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A virtueMart_id in the legacy params makes the type virtuemart.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * When more than one legacy ID field is populated at once (a realistic
     * state for hand-edited legacy records), detectContentType() must match
     * the live renderer's precedence in Cwmmedia.php: docMan_id is checked
     * first but unconditionally overridden by article_id, which is in turn
     * unconditionally overridden by virtueMart_id -- i.e. last-match-wins,
     * so virtuemart beats article beats docman. Checking in
     * [docman, article, virtuemart] order and returning on first match was
     * the exact inverse -- see #1540.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testDetectContentTypeLegacyIdPrecedenceMatchesLiveRendererWhenMultipleSet(): void
    {
        $result = CwmserverMigrationHelper::detectContentType(
            '',
            '',
            '',
            '',
            ['docMan_id' => '15', 'article_id' => '42', 'virtueMart_id' => '0']
        );
        self::assertSame(
            'article',
            $result,
            'article_id overrides docMan_id in the renderer -- must not return docman just because it was checked first'
        );

        $result = CwmserverMigrationHelper::detectContentType(
            '',
            '',
            '',
            '',
            ['docMan_id' => '15', 'article_id' => '42', 'virtueMart_id' => '7']
        );
        self::assertSame(
            'virtuemart',
            $result,
            'virtueMart_id overrides both docMan_id and article_id in the renderer -- must win when all three are set'
        );
    }

    // -------------------------------------------------------------------------
    // detectContentType() with AllVideos shortcodes
    // -------------------------------------------------------------------------

    /**
     * detectContentType() reads each AllVideos shortcode as the expected type.
     *
     * @param   string  $mediacode  Media code holding the shortcode
     * @param   string  $player     Legacy player number
     * @param   string  $expected   The type it should detect
     *
     * @return  void
     *
     * @since   10.1.0
     */
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
     * AllVideos shortcodes with a player number, each with the type they should be
     * detected as.
     *
     * @return  array<string, array{string, string, string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * A YouTube shortcode in the media code is detected as youtube when the filename is empty.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * Player type 7 with an mp3 filename is detected as local.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * An article_id with player type 5 wins over a YouTube URL in the filename.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * CWMAddonYoutube::extractMediaId() reads the video id from a watch, embed, short or
     * live URL, and returns null for anything else.
     *
     * @param   string       $text      URL or embed code
     * @param   string|null  $expected  The id it should yield, or null
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('youtubeIdProvider')]
    public function testExtractYoutubeMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonYoutube::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * YouTube URLs and others, each with the video id that should be extracted, or null.
     *
     * @return  array<string, array{string, ?string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * CWMAddonVimeo::extractMediaId() reads the video id from a vimeo.com URL, a player
     * URL or an iframe, and returns null for anything else.
     *
     * @param   string       $text      URL or embed code
     * @param   string|null  $expected  The id it should yield, or null
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('vimeoIdProvider')]
    public function testExtractVimeoMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonVimeo::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * Vimeo URLs and others, each with the video id that should be extracted, or null.
     *
     * @return  array<string, array{string, ?string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * CWMAddonWistia::extractMediaId() reads the media hash from a medias or embed URL,
     * and returns null for anything else.
     *
     * @param   string       $text      URL or embed code
     * @param   string|null  $expected  The id it should yield, or null
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('wistiaHashProvider')]
    public function testExtractWistiaMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonWistia::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * Wistia URLs and others, each with the media hash that should be extracted, or null.
     *
     * @return  array<string, array{string, ?string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * CWMAddonDailymotion::extractMediaId() reads the video id from a video, embed or
     * dai.ly URL, and returns null for anything else.
     *
     * @param   string       $text      URL or embed code
     * @param   string|null  $expected  The id it should yield, or null
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('dailymotionIdProvider')]
    public function testExtractDailymotionMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonDailymotion::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * Dailymotion URLs and others, each with the video id that should be extracted, or null.
     *
     * @return  array<string, array{string, ?string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * CWMAddonRumble::extractMediaId() reads the video id from an embed or video page URL,
     * and returns null for anything else.
     *
     * @param   string       $text      URL or embed code
     * @param   string|null  $expected  The id it should yield, or null
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('rumbleIdProvider')]
    public function testExtractRumbleMediaId(string $text, ?string $expected): void
    {
        $result = CWMAddonRumble::extractMediaId($text);
        self::assertSame($expected, $result);
    }

    /**
     * Rumble URLs and others, each with the video id that should be extracted, or null.
     *
     * @return  array<string, array{string, ?string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * A YouTube watch URL becomes an embed URL with enablejsapi=1 and player 1, and the
     * display params are kept.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Vimeo URL becomes a player embed URL with player 1 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Wistia medias URL becomes an embed iframe URL with player 1 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A local file loses the legacy server's path prefix and keeps player 0.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * stripLegacyPrefix() must not strip a host that doesn't belong to the
     * legacy server being migrated. The old fallback stripped ANY
     * http(s)://host/ prefix unconditionally, turning an S3/CDN URL into a
     * bare relative path that mediaBuildUrl() then silently concatenated
     * onto the *new* local server's own base path -- pointing at a file
     * that was never actually moved there. See #1540.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testStripLegacyPrefixDoesNotStripAnUnrelatedHost(): void
    {
        $legacyParams = [
            'path'     => 'legacy.example.com/legacy-media',
            'protocol' => 'https://',
        ];

        $result = CwmserverMigrationHelper::stripLegacyPrefix(
            'https://cdn.otherhost.com/podcasts/sermon123.mp3',
            $legacyParams
        );

        self::assertSame(
            'https://cdn.otherhost.com/podcasts/sermon123.mp3',
            $result,
            'a foreign host must be left untouched, not stripped into a bare relative path -- see #1540'
        );
    }

    /**
     * Sanity check the fix didn't also break the case it's meant to
     * preserve: a filename actually served by the legacy server being
     * migrated must still have its prefix stripped.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testStripLegacyPrefixStillStripsTheConfiguredLegacyServerPrefix(): void
    {
        $legacyParams = [
            'path'     => 'legacy.example.com/legacy-media',
            'protocol' => 'https://',
        ];

        $result = CwmserverMigrationHelper::stripLegacyPrefix(
            'https://legacy.example.com/legacy-media/sermons/audio.mp3',
            $legacyParams
        );

        self::assertSame('sermons/audio.mp3', $result);
    }

    /**
     * An embed keeps its filename, player 8 and media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Dailymotion video URL becomes an embed URL with player 1.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Rumble video page URL becomes an embed URL with player 1.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A SoundCloud track URL becomes a player embed URL with player 1 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * An article URL is kept, with player 100 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A legacy article_id becomes an article URL, with player 100.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A VirtueMart URL is kept, with player 1 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A legacy virtueMart_id becomes a VirtueMart product URL, with player 1.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A DOCman URL is kept, with player 100 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A legacy docMan_id becomes a DOCman document URL by slug, with player 100.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * transformParams() keeps every display param: image, button, icon, MIME type, size,
     * duration, autostart, popup, link type and player size.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * getTargetTypes() returns exactly the fifteen expected types.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * getTypeLabels() has a label for every target type, and for unknown.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * extractAllVideosContent() returns what an AllVideos shortcode wraps, and an empty
     * string for a dash placeholder or when there is no shortcode.
     *
     * @param   string  $mediacode  Media code
     * @param   string  $expected   What it should return
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('allVideosContentProvider')]
    public function testExtractAllVideosContent(string $mediacode, string $expected): void
    {
        $result = CwmserverMigrationHelper::extractAllVideosContent($mediacode);
        self::assertSame($expected, $result);
    }

    /**
     * Media codes, each with the content extractAllVideosContent() should return.
     *
     * @return  array<string, array{string, string}>  Each case
     *
     * @since   10.1.0
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

    /**
     * A YouTube shortcode's bare id becomes an embed URL with player 1 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Vimeo shortcode's bare id becomes a player embed URL with player 1 and no media
     * code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Dailymotion shortcode's bare id becomes an embed URL with player 1 and no media
     * code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A YouTube shortcode holding only a dash takes the video from the filename.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A YouTube iframe's autoplay, start, loop and rel become form fields, and its embed
     * URL keeps enablejsapi=1, with player 1 and no media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A YouTube watch URL becomes an embed URL with enablejsapi=1 and without its v param.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Vimeo iframe's color, title, byline and portrait become form fields.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Wistia URL's autoPlay and controlsVisibleOnLoad become form fields.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Dailymotion iframe's autoplay, mute and startTime become form fields.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Rumble URL's pub and autoplay become form fields.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A SoundCloud player URL from an iframe is kept whole as the filename, with player 1.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * A Vimeo URL with no params becomes an embed URL with no trailing question mark.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * extractSourceUrlParams() reads v, start and autoplay from a YouTube watch URL.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * extractSourceUrlParams() reads autoplay, loop and rel from a YouTube iframe.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * extractSourceUrlParams() reads color and title from a Vimeo iframe.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testExtractSourceUrlParamsVimeo(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            '<iframe src="https://player.vimeo.com/video/123?color=FF0000&title=0"></iframe>',
            'vimeo'
        );

        self::assertSame('FF0000', $params['color']);
        self::assertSame('0', $params['title']);
    }

    /**
     * extractSourceUrlParams() returns an empty array for a URL with no query.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testExtractSourceUrlParamsNoParams(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            'https://vimeo.com/123456789',
            'vimeo'
        );

        self::assertSame([], $params);
    }

    /**
     * extractSourceUrlParams() returns an empty array for an unknown platform.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testExtractSourceUrlParamsUnknownPlatform(): void
    {
        $params = CwmserverMigrationHelper::extractSourceUrlParams(
            'https://example.com/video?id=123',
            'unknown'
        );

        self::assertSame([], $params);
    }

    /**
     * An unknown AllVideos shortcode becomes an embed with player 8, keeping its filename
     * and media code.
     *
     * @return  void
     *
     * @since   10.1.0
     */
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

    /**
     * createServerForType() read Factory::getApplication()->getIdentity()->id
     * unconditionally. getIdentity() can legitimately return null outside a
     * real request context (a CLI script, a scheduled task, PHPUnit's own CLI
     * process) -- discovered when a #1538 regression test that calls
     * CwmmigrationHelper::migrateLegacyServers() (which calls this method)
     * triggered exactly that in CI, where no identity is ever set up.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \Exception
     */
    public function testCreateServerForTypeDoesNotDereferenceANullIdentity(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Belt-and-suspenders, not redundant: this bare insertObject() does
        // not touch an asset-tracked table, but a savepoint-protected
        // transaction here still is not sufficient on its own to guarantee
        // cleanup in a long-running shared PHPUnit process -- #2218 found
        // real "ZZTEST" rows left behind in the dev database from exactly
        // this test despite the transactionStart()/rollback() pair below.
        // Deleting the row explicitly is what actually guarantees cleanup.
        $db->transactionStart(true);
        $id = 0;

        try {
            $id = CwmserverMigrationHelper::createServerForType('local', 'ZZTEST Direct Call ' . uniqid());

            $this->assertGreaterThan(0, $id, 'must return a valid new server id -- see #1538');
        } finally {
            $db->transactionRollback(true);

            if ($id > 0) {
                $db->setQuery(
                    'DELETE FROM ' . $db->quoteName('#__bsms_servers')
                    . ' WHERE ' . $db->quoteName('id') . ' = ' . $id
                )->execute();
            }
        }
    }

    // -------------------------------------------------------------------------
    // Files a legacy server served from another host
    // -------------------------------------------------------------------------

    /**
     * isRemoteLegacyHost() is true for a legacy server on another host.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testRemoteLegacyHostIsDetected(): void
    {
        $legacy = ['path' => '//www.calvarychapelnewberg.net/', 'protocol' => 'http://'];

        $this->assertTrue(CwmserverMigrationHelper::isRemoteLegacyHost($legacy, 'example.org'));
    }

    /**
     * isRemoteLegacyHost() is false for the site's own host, even with www in front.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testTheSitesOwnHostIsNotRemoteEvenWithWww(): void
    {
        $legacy = ['path' => '//www.example.org/', 'protocol' => 'https://'];

        $this->assertFalse(CwmserverMigrationHelper::isRemoteLegacyHost($legacy, 'example.org'));
    }

    /**
     * isRemoteLegacyHost() is false for a legacy server with no host.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testALegacyServerWithNoHostIsNotRemote(): void
    {
        $this->assertFalse(CwmserverMigrationHelper::isRemoteLegacyHost(['path' => '', 'protocol' => 'http://'], 'example.org'));
        $this->assertFalse(CwmserverMigrationHelper::isRemoteLegacyHost([], 'example.org'));
    }

    /**
     * resolveTargetType() sends a local file on another host to a direct server.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testALocalFileOnARemoteHostGoesToADirectServer(): void
    {
        $legacy = ['path' => '//cdn.example.net/', 'protocol' => 'http://'];

        $this->assertSame('direct', CwmserverMigrationHelper::resolveTargetType('local', $legacy, 'example.org'));
    }

    /**
     * resolveTargetType() leaves other types alone, and keeps a local file local when the
     * legacy host is the site's own or not set.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testOnlyLocalFilesAreRerouted(): void
    {
        $legacy = ['path' => '//cdn.example.net/', 'protocol' => 'http://'];

        $this->assertSame('youtube', CwmserverMigrationHelper::resolveTargetType('youtube', $legacy, 'example.org'));
        $this->assertSame('local', CwmserverMigrationHelper::resolveTargetType('local', [], 'example.org'));
        $this->assertSame(
            'local',
            CwmserverMigrationHelper::resolveTargetType('local', ['path' => '//example.org/'], 'example.org')
        );
    }

    /**
     * A relative filename migrated to a direct server gets the legacy server's protocol
     * and host in front.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testAMigratedRelativeFileKeepsItsLegacyHost(): void
    {
        $legacy = ['path' => '//www.calvarychapelnewberg.net/', 'protocol' => 'http://'];

        $result = CwmserverMigrationHelper::transformParams(
            ['filename' => '/MediaFiles/2015/2015-008.mp3', 'player' => '7'],
            'direct',
            $legacy
        );

        $this->assertSame('http://www.calvarychapelnewberg.net/MediaFiles/2015/2015-008.mp3', $result['filename']);
    }

    /**
     * absoluteLegacyUrl() doesn't add a second protocol to a legacy path that already
     * has one.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testALegacyPathWithItsOwnProtocolIsNotDoubled(): void
    {
        $legacy = ['path' => 'http://calvarynewberg.org/', 'protocol' => 'http://'];

        $this->assertSame(
            'http://calvarynewberg.org/images/q.pdf',
            CwmserverMigrationHelper::absoluteLegacyUrl('/images/q.pdf', $legacy)
        );
    }

    /**
     * absoluteLegacyUrl() leaves a filename that is already absolute alone.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    public function testAnAbsoluteFilenameIsLeftAlone(): void
    {
        $legacy = ['path' => '//cdn.example.net/', 'protocol' => 'http://'];

        $this->assertSame(
            'https://other.example.com/a.mp3',
            CwmserverMigrationHelper::absoluteLegacyUrl('https://other.example.com/a.mp3', $legacy)
        );
    }

    /**
     * createServerForType()'s source doesn't read created_by straight from $user->id,
     * since the identity can be null.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testCreateServerForTypeGuardsAgainstANullIdentity(): void
    {
        $reflection = new \ReflectionMethod(CwmserverMigrationHelper::class, 'createServerForType');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\'created_by\'\s*=>\s*\(int\)\s*\$user->id/',
            $body,
            'must not dereference $user->id unconditionally -- getIdentity() can return null -- see #1538'
        );
    }
}
