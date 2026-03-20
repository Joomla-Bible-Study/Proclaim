<?php

/**
 * Tests for Cwmpodcast Podcasting 2.0 methods
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for Cwmpodcast Podcasting 2.0 features
 *
 * @since 10.3.0
 */
class CwmpodcastP2Test extends ProclaimTestCase
{
    /**
     * @var Cwmpodcast
     */
    protected Cwmpodcast $podcast;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->podcast = new Cwmpodcast();
    }

    // -------------------------------------------------------------------------
    // UUIDv5 generation
    // -------------------------------------------------------------------------

    /**
     * Test UUIDv5 generation produces valid format.
     *
     * @return void
     * @since  10.3.0
     */
    public function testUuidv5ProducesValidFormat(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'uuidv5');
        $ref->setAccessible(true);

        // Podcasting 2.0 namespace UUID
        $namespace = 'ead4c236-bf58-58c6-a2c6-a6b28d128cb6';
        $result    = $ref->invoke(null, $namespace, 'example.com/podcast.xml');

        // Should be a valid UUID format
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $result,
            'UUIDv5 should have version 5 and correct variant bits'
        );
    }

    /**
     * Test UUIDv5 is deterministic — same input always produces same output.
     *
     * @return void
     * @since  10.3.0
     */
    public function testUuidv5IsDeterministic(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'uuidv5');
        $ref->setAccessible(true);

        $namespace = 'ead4c236-bf58-58c6-a2c6-a6b28d128cb6';
        $result1   = $ref->invoke(null, $namespace, 'mychurch.org/podcast.xml');
        $result2   = $ref->invoke(null, $namespace, 'mychurch.org/podcast.xml');

        $this->assertSame($result1, $result2);
    }

    /**
     * Test UUIDv5 produces different results for different inputs.
     *
     * @return void
     * @since  10.3.0
     */
    public function testUuidv5DiffersForDifferentInputs(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'uuidv5');
        $ref->setAccessible(true);

        $namespace = 'ead4c236-bf58-58c6-a2c6-a6b28d128cb6';
        $result1   = $ref->invoke(null, $namespace, 'site-a.org/podcast.xml');
        $result2   = $ref->invoke(null, $namespace, 'site-b.org/podcast.xml');

        $this->assertNotSame($result1, $result2);
    }

    // -------------------------------------------------------------------------
    // normalizeTime
    // -------------------------------------------------------------------------

    /**
     * Test normalizeTime converts various formats to HH:MM:SS.
     *
     * @return void
     * @since  10.3.0
     */
    #[DataProvider('normalizeTimeProvider')]
    public function testNormalizeTime(string $input, string $expected): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'normalizeTime');
        $ref->setAccessible(true);

        $this->assertSame($expected, $ref->invoke($this->podcast, $input));
    }

    /**
     * Data provider for normalizeTime tests.
     */
    public static function normalizeTimeProvider(): array
    {
        return [
            'M:SS'           => ['5:30', '00:05:30'],
            'MM:SS'          => ['15:30', '00:15:30'],
            'H:MM:SS'        => ['1:30:00', '01:30:00'],
            'HH:MM:SS'       => ['01:05:30', '01:05:30'],
            'zero'           => ['0:00', '00:00:00'],
            'single segment' => ['invalid', '00:00:00'],
        ];
    }

    // -------------------------------------------------------------------------
    // generatePodcastGuid
    // -------------------------------------------------------------------------

    /**
     * Test generatePodcastGuid strips protocol and trailing slashes.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGeneratePodcastGuidNormalizesUrl(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'generatePodcastGuid');
        $ref->setAccessible(true);

        // Same feed with/without protocol should produce same GUID
        $guid1 = $ref->invoke($this->podcast, 'https://example.com', 'podcast.xml');
        $guid2 = $ref->invoke($this->podcast, 'http://example.com', 'podcast.xml');

        $this->assertSame($guid1, $guid2, 'GUID should be protocol-independent');
    }

    /**
     * Test generatePodcastGuid produces valid UUIDv5.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGeneratePodcastGuidIsValidUuid(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'generatePodcastGuid');
        $ref->setAccessible(true);

        $guid = $ref->invoke($this->podcast, 'https://mychurch.org', 'sermons.xml');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $guid
        );
    }

    // -------------------------------------------------------------------------
    // getChapterTimecodes
    // -------------------------------------------------------------------------

    /**
     * Test getChapterTimecodes returns empty for no chapters.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGetChapterTimecodesEmptyWhenNoChapters(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'getChapterTimecodes');
        $ref->setAccessible(true);

        $episode = (object) ['params' => new \Joomla\Registry\Registry()];
        $result  = $ref->invoke($this->podcast, $episode);

        $this->assertSame('', $result);
    }

    /**
     * Test getChapterTimecodes formats output correctly.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGetChapterTimecodesFormatsCorrectly(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'getChapterTimecodes');
        $ref->setAccessible(true);

        $params = new \Joomla\Registry\Registry([
            'chapters' => [
                ['time' => '0:00', 'label' => 'Introduction'],
                ['time' => '5:30', 'label' => 'Scripture Reading'],
                ['time' => '1:15:00', 'label' => 'Main Teaching'],
            ],
        ]);

        $episode = (object) ['params' => $params];
        $result  = $ref->invoke($this->podcast, $episode);

        $this->assertStringContainsString('00:00:00 Introduction', $result);
        $this->assertStringContainsString('00:05:30 Scripture Reading', $result);
        $this->assertStringContainsString('01:15:00 Main Teaching', $result);
    }

    // -------------------------------------------------------------------------
    // getTranscriptXml
    // -------------------------------------------------------------------------

    /**
     * Test getTranscriptXml returns empty for no tracks.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGetTranscriptXmlEmptyWhenNoTracks(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'getTranscriptXml');
        $ref->setAccessible(true);

        $episode = (object) ['params' => new \Joomla\Registry\Registry()];
        $result  = $ref->invoke($this->podcast, $episode, 'https://example.com', 'https://');

        $this->assertSame('', $result);
    }

    /**
     * Test getTranscriptXml generates correct MIME types.
     *
     * @return void
     * @since  10.3.0
     */
    public function testGetTranscriptXmlCorrectMimeTypes(): void
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'getTranscriptXml');
        $ref->setAccessible(true);

        $params = new \Joomla\Registry\Registry([
            'subtitle_tracks' => [
                ['src' => 'media/captions/ep1.vtt', 'srclang' => 'en', 'kind' => 'captions', 'label' => 'English'],
                ['src' => 'media/captions/ep1.srt', 'srclang' => 'es', 'kind' => 'subtitles', 'label' => 'Spanish'],
            ],
        ]);

        $episode = (object) ['params' => $params];
        $result  = $ref->invoke($this->podcast, $episode, 'https://example.com', 'https://');

        $this->assertStringContainsString('type="text/vtt"', $result);
        $this->assertStringContainsString('type="application/x-subrip"', $result);
        $this->assertStringContainsString('language="en"', $result);
        $this->assertStringContainsString('language="es"', $result);
    }

    // -------------------------------------------------------------------------
    // Controller: timeToSeconds
    // -------------------------------------------------------------------------

    /**
     * Test CwmpodcastController::timeToSeconds conversion.
     *
     * @return void
     * @since  10.3.0
     */
    #[DataProvider('timeToSecondsProvider')]
    public function testTimeToSeconds(string $input, float $expected): void
    {
        $ref = new \ReflectionMethod(
            \CWM\Component\Proclaim\Site\Controller\CwmpodcastController::class,
            'timeToSeconds'
        );
        $ref->setAccessible(true);

        $this->assertSame($expected, $ref->invoke(null, $input));
    }

    /**
     * Data provider for timeToSeconds tests.
     */
    public static function timeToSecondsProvider(): array
    {
        return [
            'zero'      => ['0:00', 0.0],
            'M:SS'      => ['5:30', 330.0],
            'H:MM:SS'   => ['1:30:00', 5400.0],
            'with secs' => ['1:05:30', 3930.0],
            'just secs' => ['45', 45.0],
        ];
    }

    // -------------------------------------------------------------------------
    // Method existence checks
    // -------------------------------------------------------------------------

    /**
     * Test that all Podcasting 2.0 private helper methods exist.
     *
     * @return void
     * @since  10.3.0
     */
    public function testP2MethodsExist(): void
    {
        $class    = new \ReflectionClass(Cwmpodcast::class);
        $expected = [
            'generatePodcastGuid',
            'getTranscriptXml',
            'getChapterXml',
            'getChapterTimecodes',
            'normalizeTime',
            'getPersonXml',
            'getAlternateEnclosureXml',
        ];

        foreach ($expected as $name) {
            $this->assertTrue(
                $class->hasMethod($name),
                "Expected method {$name}() to exist on Cwmpodcast"
            );
        }
    }

    /**
     * Test that CwmpodcastController exists and has chapters method.
     *
     * @return void
     * @since  10.3.0
     */
    public function testPodcastControllerExists(): void
    {
        $this->assertTrue(
            class_exists(\CWM\Component\Proclaim\Site\Controller\CwmpodcastController::class)
        );

        $class = new \ReflectionClass(
            \CWM\Component\Proclaim\Site\Controller\CwmpodcastController::class
        );
        $this->assertTrue($class->hasMethod('chapters'));
    }
}
