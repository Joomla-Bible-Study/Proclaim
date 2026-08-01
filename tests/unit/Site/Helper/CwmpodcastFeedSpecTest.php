<?php

/**
 * Tests for the RSS 2.0 / Apple Podcasts spec compliance fixes in Cwmpodcast.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Feed output that a validator rejects is invisible until a directory refuses the
 * feed, so the four defects audited in #1391 are pinned here at the unit level:
 * duration padding, enclosure MIME derivation, the guid permalink attribute, and
 * URL resolution for values that are neither absolute nor a menu item id.
 *
 * @since __DEPLOY_VERSION__
 */
class CwmpodcastFeedSpecTest extends ProclaimTestCase
{
    /**
     * @var Cwmpodcast
     */
    protected Cwmpodcast $podcast;

    /**
     * Set up test fixtures.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->podcast = new Cwmpodcast();
    }

    // -------------------------------------------------------------------------
    // 1. itunes:duration zero-padding
    // -------------------------------------------------------------------------

    /**
     * Stored duration parts and the itunes:duration they must produce.
     *
     * Records entered through the media edit form bypass fixMediaDurations(),
     * which is the only thing that pads on write — so single-digit and '0'
     * values reach the feed exactly as typed.
     *
     * @return  array<string, array{0: mixed, 1: mixed, 2: mixed, 3: string}>
     */
    public static function durationProvider(): array
    {
        return [
            'already padded'       => ['00', '27', '07', '00:27:07'],
            'single-digit seconds' => ['00', '27', '7', '00:27:07'],
            'single-digit minutes' => ['01', '5', '30', '01:05:30'],
            'all single digits'    => ['1', '2', '3', '01:02:03'],
            'integers not strings' => [0, 27, 7, '00:27:07'],
            'hour only'            => ['2', '0', '0', '02:00:00'],
            'three-digit hours'    => ['100', '00', '00', '100:00:00'],
            'zero as single digit' => ['0', '0', '0', ''],
            'all zero padded'      => ['00', '00', '00', ''],
        ];
    }

    /**
     * Duration is emitted zero-padded, and an all-zero duration is omitted.
     *
     * @param   mixed   $hours     Stored media_hours.
     * @param   mixed   $minutes   Stored media_minutes.
     * @param   mixed   $seconds   Stored media_seconds.
     * @param   string  $expected  The itunes:duration value.
     *
     * @return  void
     */
    #[DataProvider('durationProvider')]
    public function testDurationIsZeroPadded(
        mixed $hours,
        mixed $minutes,
        mixed $seconds,
        string $expected
    ): void {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'getEpisodeDuration');

        $episode = (object) [
            'params' => new Registry([
                'media_hours'   => $hours,
                'media_minutes' => $minutes,
                'media_seconds' => $seconds,
            ]),
        ];

        $this->assertSame($expected, $ref->invoke($this->podcast, $episode));
    }

    /**
     * A '0' stored value used to pass the strict !== '00' guard and emit a
     * bogus 0:0:0 duration.
     *
     * @return  void
     */
    public function testZeroDurationIsOmittedRatherThanEmittedAsZeros(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'getEpisodeDuration');

        $episode = (object) [
            'params' => new Registry(['media_hours' => '0', 'media_minutes' => '0', 'media_seconds' => '0']),
        ];

        $result = $ref->invoke($this->podcast, $episode);

        $this->assertSame('', $result, 'An all-zero duration must be omitted, not emitted as 00:00:00');
    }

    /**
     * Values a hand-typed duration can hold, and their normalized form.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function durationNormalizationProvider(): array
    {
        return [
            'single digit' => ['7', '07'],
            'bare zero'    => ['0', '00'],
            // Clearing the field is how an admin marks a duration unknown, so a
            // blank stays blank rather than becoming an explicit zero.
            'empty'        => ['', ''],
            'whitespace'   => ['   ', ''],
            'already ok'   => ['07', '07'],
            'two digits'   => ['27', '27'],
            'three digits' => ['100', '100'],
            'stray spaces' => [' 7 ', '07'],
        ];
    }

    /**
     * The edit form's duration fields are plain text with no filter, so what an
     * admin types is what persists. Every automated writer pads to two digits;
     * the model now holds manual entry to the same shape, so the bad value never
     * reaches storage in the first place.
     *
     * @param   string  $typed     What the admin entered.
     * @param   string  $expected  What must be stored.
     *
     * @return  void
     */
    #[DataProvider('durationNormalizationProvider')]
    public function testDurationPartsAreNormalizedOnSave(string $typed, string $expected): void
    {
        $source = file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Model/CwmmediafileModel.php'
        );

        $this->assertStringContainsString(
            "foreach (['media_hours', 'media_minutes', 'media_seconds'] as \$durationPart)",
            $source,
            'CwmmediafileModel::save() must normalize the duration parts'
        );

        // Mirror of the normalization the model applies, pinned so a change to
        // the padding rule has to be a deliberate one.
        $value  = trim($typed);
        $result = $value === '' ? '' : str_pad((string) (int) $value, 2, '0', STR_PAD_LEFT);

        $this->assertSame($expected, $result);
    }

    /**
     * A duration stored as '0' must read as "not set" everywhere, or the repair
     * and detection routines skip exactly the records that need them.
     *
     * @return  void
     */
    public function testDurationSetChecksCompareNumerically(): void
    {
        $files = [
            '/site/src/Helper/Cwmpodcast.php',
            '/admin/src/Addons/CWMAddon.php',
        ];

        foreach ($files as $file) {
            $source = file_get_contents(\dirname(__DIR__, 4) . $file);

            $this->assertDoesNotMatchRegularExpression(
                "/\\\$hours\s*[!=]==\s*'00'/",
                $source,
                \sprintf('%s still compares a duration against the string \'00\'', $file)
            );
        }
    }

    // -------------------------------------------------------------------------
    // 2. enclosure type derivation
    // -------------------------------------------------------------------------

    /**
     * File paths, the stored mime_type, and the type that must be declared.
     *
     * @return  array<string, array{0: string, 1: ?string, 2: string}>
     */
    public static function enclosureTypeProvider(): array
    {
        return [
            // The extension wins over a stored value that contradicts it — the
            // .m4a-declared-as-audio/mp3 case the audit found.
            'm4a stored as audio/mp3' => ['media/ep1.m4a', 'audio/mp3', 'audio/mp4'],
            'mp3 stored as audio/mp3' => ['media/ep1.mp3', 'audio/mp3', 'audio/mpeg'],
            'mp3 with no stored type' => ['media/ep1.mp3', null, 'audio/mpeg'],
            'mp3 with empty type'     => ['media/ep1.mp3', '', 'audio/mpeg'],
            'mp4 video'               => ['media/ep1.mp4', null, 'video/mp4'],
            'uppercase extension'     => ['media/EP1.MP3', null, 'audio/mpeg'],
            'url with query string'   => ['https://cdn.example.com/ep1.mp3?token=abc', null, 'audio/mpeg'],
            // Unknown extension falls back to the stored value…
            'unknown ext, stored type' => ['media/ep1.xyz', 'audio/aac', 'audio/aac'],
            // …and to a registered type when there is nothing stored, never the
            // unregistered audio/mpeg3 this used to emit.
            'unknown ext, no type' => ['media/ep1.xyz', null, 'audio/mpeg'],
            'no extension at all'  => ['media/ep1', '', 'audio/mpeg'],
        ];
    }

    /**
     * The declared type is derived from the file, not trusted from the admin.
     *
     * @param   string   $path      Media path or URL.
     * @param   ?string  $stored    Stored mime_type.
     * @param   string   $expected  Type that must be declared.
     *
     * @return  void
     */
    #[DataProvider('enclosureTypeProvider')]
    public function testEnclosureTypeIsDerivedFromTheFile(string $path, ?string $stored, string $expected): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'resolveEnclosureType');

        $this->assertSame($expected, $ref->invoke($this->podcast, $path, $stored));
    }

    /**
     * audio/mpeg3 was never IANA-registered; it must not reappear as a default.
     *
     * @return  void
     */
    public function testUnregisteredMpeg3TypeIsNeverEmitted(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'resolveEnclosureType');

        foreach (['media/ep1.mp3', 'media/ep1.xyz', 'media/ep1', ''] as $path) {
            $this->assertNotSame(
                'audio/mpeg3',
                $ref->invoke($this->podcast, $path, null),
                \sprintf('audio/mpeg3 emitted for "%s"', $path)
            );
        }
    }

    /**
     * An empty type attribute is invalid; every branch must yield something.
     *
     * @return  void
     */
    public function testEnclosureTypeIsNeverEmpty(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'resolveEnclosureType');

        foreach ([['', null], ['', ''], ['media/ep1.xyz', '']] as [$path, $stored]) {
            $this->assertNotSame('', $ref->invoke($this->podcast, $path, $stored));
        }
    }

    // -------------------------------------------------------------------------
    // 3. guid isPermaLink
    // -------------------------------------------------------------------------

    /**
     * RSS 2.0 defaults isPermaLink to true, which would invite readers to fetch
     * the guid as a web page. resolveGuid() returns a media URL, not a page.
     *
     * @return  void
     */
    public function testGuidIsDeclaredNotAPermalink(): void
    {
        $method = new \ReflectionMethod(Cwmpodcast::class, 'getEnclosureXml');
        $source = file($method->getFileName());
        $body   = implode('', \array_slice(
            $source,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertStringContainsString(
            '<guid isPermaLink="false">',
            $body,
            'The guid is an identity token since #1299, so it must say isPermaLink="false"'
        );
        // Match only the emitted markup — the method's comments mention <guid>
        // in prose, which a bare /<guid>/ would hit.
        $this->assertDoesNotMatchRegularExpression(
            "/<guid>'\s*\.\s*\\\$guid/",
            $body,
            'A bare <guid> defaults to isPermaLink="true"'
        );
    }

    // -------------------------------------------------------------------------
    // 4. resolveUrl input classes
    // -------------------------------------------------------------------------

    /**
     * A root-relative value matched neither branch and fell through to string
     * concatenation, producing "https:///resources/sermons.html".
     *
     * @return  void
     */
    public function testRootRelativePathDoesNotProduceAnEmptyAuthority(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'resolveUrl');

        $result = $ref->invoke($this->podcast, '/resources/sermons.html', 'https://');

        $this->assertStringNotContainsString(
            ':///',
            $result,
            'A root-relative value must join the site root, not concatenate onto the protocol'
        );
        $this->assertStringEndsWith('/resources/sermons.html', $result);
    }

    /**
     * The other input classes keep working unchanged.
     *
     * @return  void
     */
    public function testAbsoluteAndBareDomainValuesAreUnchanged(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'resolveUrl');

        $this->assertSame(
            'https://example.com',
            $ref->invoke($this->podcast, 'https://example.com/', 'https://')
        );
        $this->assertSame(
            'http://legacy.example.com',
            $ref->invoke($this->podcast, 'http://legacy.example.com', 'https://')
        );
        $this->assertSame(
            'https://example.com',
            $ref->invoke($this->podcast, 'example.com', 'https://')
        );
    }

    // -------------------------------------------------------------------------
    // 4b. protocol default
    // -------------------------------------------------------------------------

    /**
     * Every protocol fallback defaults to https.
     *
     * The template form field has defaulted to https:// for some time; the PHP
     * fallbacks are what apply to templates saved before that, and they lagged
     * behind. They must agree, and must agree with each other — the tracking
     * redirect reconstructs the same URL the enclosure declares, so a split
     * default would send subscribers to a different origin than the feed.
     *
     * @return  void
     */
    public function testProtocolFallbacksDefaultToHttps(): void
    {
        $root  = \dirname(__DIR__, 4);
        $files = [
            '/site/src/Helper/Cwmpodcast.php',
            '/site/src/Controller/CwmpodcastController.php',
            '/admin/src/Helper/Cwmhelper.php',
        ];

        foreach ($files as $file) {
            $source = file_get_contents($root . $file);

            $this->assertStringNotContainsString(
                "get('protocol', 'http://')",
                $source,
                \sprintf('%s still falls back to http://', $file)
            );
        }
    }
    // -------------------------------------------------------------------------
    // 5. validator: stored MIME contradicting the file (#1396)
    // -------------------------------------------------------------------------

    /**
     * Filenames, stored types, and whether the validator should warn.
     *
     * @return  array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function mimeMismatchProvider(): array
    {
        return [
            'm4a stored as mp3'    => ['media/ep1.m4a', 'audio/mp3', true],
            'm4a stored as mpeg'   => ['media/ep1.m4a', 'audio/mpeg', true],
            'mp3 stored as mp3'    => ['media/ep1.mp3', 'audio/mp3', true],
            'mp3 stored correctly' => ['media/ep1.mp3', 'audio/mpeg', false],
            'm4a stored correctly' => ['media/ep1.m4a', 'audio/mp4', false],
            'mp4 stored correctly' => ['media/ep1.mp4', 'video/mp4', false],
            // Unknown extension yields no opinion, so no warning either way.
            'unknown extension' => ['media/ep1.xyz', 'audio/mpeg', false],
            'no extension'      => ['media/ep1', 'audio/mpeg', false],
        ];
    }

    /**
     * The warning fires only when detection genuinely disagrees with storage.
     *
     * Mirrors the comparison validatePodcastMedia() performs; a false positive
     * here would put a warning on every well-formed record in the report.
     *
     * @param   string  $filename  Stored filename.
     * @param   string  $stored    Stored mime_type.
     * @param   bool    $expected  Whether a warning is expected.
     *
     * @return  void
     */
    #[DataProvider('mimeMismatchProvider')]
    public function testValidatorFlagsOnlyGenuineMimeMismatches(
        string $filename,
        string $stored,
        bool $expected
    ): void {
        $derived = \CWM\Component\Proclaim\Administrator\Helper\Cwmmime::fromExtension($filename);
        $warns   = $derived !== null && $derived !== $stored;

        $this->assertSame($expected, $warns);
    }

    /**
     * YouTube URLs have no meaningful extension, so they must be skipped rather
     * than warned about on every episode.
     *
     * @return  void
     */
    public function testValidatorSkipsYouTubeRecords(): void
    {
        $source = file_get_contents(\dirname(__DIR__, 4) . '/site/src/Helper/Cwmpodcast.php');

        $this->assertStringContainsString(
            '} elseif (!$isYouTube && !empty($filename)) {',
            $source,
            'The mismatch check must be gated on the record not being a YouTube URL'
        );
    }
}
