<?php

/**
 * Tests for Cwmpodcast duration detection methods
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
 * Test class for Cwmpodcast duration detection
 *
 * @since 10.1.0
 */
class CwmpodcastDurationTest extends ProclaimTestCase
{
    /**
     * @var Cwmpodcast
     */
    protected Cwmpodcast $podcast;

    /**
     * @var string  Path to test fixtures
     */
    protected string $fixturesPath;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->podcast      = new Cwmpodcast();
        $this->fixturesPath = \dirname(__DIR__, 3) . '/fixtures/media/';

        // Create fixtures directory if it doesn't exist
        if (!is_dir($this->fixturesPath)) {
            mkdir($this->fixturesPath, 0755, true);
        }
    }

    /**
     * Test that getAvailableDurationMethods returns expected structure
     */
    public function testGetAvailableDurationMethodsReturnsArray(): void
    {
        $methods = $this->podcast->getAvailableDurationMethods();

        $this->assertIsArray($methods);
        $this->assertArrayHasKey('ffprobe', $methods);
        $this->assertArrayHasKey('native_m4a', $methods);
        $this->assertArrayHasKey('native_wav', $methods);
        $this->assertArrayHasKey('native_ogg', $methods);
        $this->assertArrayHasKey('getid3', $methods);
        $this->assertArrayHasKey('mp3_parser', $methods);
        $this->assertArrayHasKey('youtube_api', $methods);

        // Native parsers should always be available
        $this->assertTrue($methods['native_m4a']);
        $this->assertTrue($methods['native_wav']);
        $this->assertTrue($methods['native_ogg']);
        $this->assertTrue($methods['mp3_parser']);

        // YouTube API availability depends on component params
        $this->assertIsBool($methods['youtube_api']);
    }

    /**
     * Test that getSupportedDurationFormats returns expected formats
     */
    public function testGetSupportedDurationFormatsReturnsArray(): void
    {
        $formats = $this->podcast->getSupportedDurationFormats();

        $this->assertIsArray($formats);
        $this->assertContains('mp3', $formats);
        $this->assertContains('m4a', $formats);
        $this->assertContains('wav', $formats);
        $this->assertContains('ogg', $formats);
    }

    /**
     * Test formatTime with various durations
     */
    #[DataProvider('formatTimeProvider')]
    public function testFormatTime(int $seconds, int $expectedHours, int $expectedMinutes, int $expectedSeconds): void
    {
        $result = $this->podcast->formatTime($seconds);

        $this->assertIsObject($result);
        $this->assertEquals($expectedHours, $result->hours);
        $this->assertEquals($expectedMinutes, $result->minutes);
        $this->assertEquals($expectedSeconds, $result->seconds);
    }

    /**
     * Data provider for formatTime tests
     */
    public static function formatTimeProvider(): array
    {
        return [
            'zero seconds'    => [0, 0, 0, 0],
            'one second'      => [1, 0, 0, 1],
            'one minute'      => [60, 0, 1, 0],
            'one hour'        => [3600, 1, 0, 0],
            'mixed time'      => [3661, 1, 1, 1],
            'typical podcast' => [1845, 0, 30, 45],
            'long podcast'    => [7384, 2, 3, 4],
        ];
    }

    /**
     * Test getDuration returns 0 for non-existent file
     */
    public function testGetDurationReturnsZeroForNonExistentFile(): void
    {
        $result = $this->podcast->getMediaDuration('/non/existent/file.mp3');
        $this->assertEquals(0, $result);
    }

    /**
     * Test getDuration returns 0 for empty file
     */
    public function testGetDurationReturnsZeroForEmptyFile(): void
    {
        $tempFile = $this->fixturesPath . 'empty.mp3';
        file_put_contents($tempFile, '');

        $result = $this->podcast->getMediaDuration($tempFile);
        $this->assertEquals(0, $result);

        @unlink($tempFile);
    }

    /**
     * Test WAV duration parser with a valid WAV header
     */
    public function testWavDurationParser(): void
    {
        // Create a minimal valid WAV file (1 second of silence at 44100Hz, 16-bit stereo)
        $wavFile = $this->createTestWavFile(44100, 16, 2, 1);

        $result = $this->podcast->getMediaDuration($wavFile);

        // Should be approximately 1 second
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(2, $result);

        @unlink($wavFile);
    }

    /**
     * Test WAV parser returns 0 for invalid WAV file
     */
    public function testWavDurationParserReturnsZeroForInvalidFile(): void
    {
        $tempFile = $this->fixturesPath . 'invalid.wav';
        file_put_contents($tempFile, 'This is not a WAV file');

        // Use reflection to test protected method directly
        $reflection = new \ReflectionClass($this->podcast);
        $method     = $reflection->getMethod('getWavDuration');
        $method->setAccessible(true);

        $result = $method->invoke($this->podcast, $tempFile);
        $this->assertEquals(0, $result);

        @unlink($tempFile);
    }

    /**
     * Test M4A duration parser with minimal M4A structure
     */
    public function testM4aDurationParser(): void
    {
        // Create a minimal M4A file structure with moov/mvhd atom
        $m4aFile = $this->createTestM4aFile(30); // 30 seconds

        // Use reflection to test protected method directly
        $reflection = new \ReflectionClass($this->podcast);
        $method     = $reflection->getMethod('getM4aDuration');
        $method->setAccessible(true);

        $result = $method->invoke($this->podcast, $m4aFile);

        // Should detect approximately 30 seconds
        $this->assertGreaterThanOrEqual(29, $result);
        $this->assertLessThanOrEqual(31, $result);

        @unlink($m4aFile);
    }

    /**
     * Test OGG duration parser returns 0 for invalid file
     */
    public function testOggDurationParserReturnsZeroForInvalidFile(): void
    {
        $tempFile = $this->fixturesPath . 'invalid.ogg';
        file_put_contents($tempFile, 'This is not an OGG file');

        // Use reflection to test protected method directly
        $reflection = new \ReflectionClass($this->podcast);
        $method     = $reflection->getMethod('getOggDuration');
        $method->setAccessible(true);

        $result = $method->invoke($this->podcast, $tempFile);
        $this->assertEquals(0, $result);

        @unlink($tempFile);
    }

    /**
     * Test MP3 duration parser returns 0 for invalid file
     */
    public function testMp3DurationParserReturnsZeroForInvalidFile(): void
    {
        $tempFile = $this->fixturesPath . 'invalid.mp3';
        file_put_contents($tempFile, 'This is not an MP3 file');

        $result = $this->podcast->getMp3Duration($tempFile);
        $this->assertEquals(0, $result);

        @unlink($tempFile);
    }

    /**
     * Test findFFprobe method
     */
    public function testFindFFprobe(): void
    {
        $reflection = new \ReflectionClass($this->podcast);
        $method     = $reflection->getMethod('findFFprobe');
        $method->setAccessible(true);

        $result = $method->invoke($this->podcast);

        // Result should be either null or a valid path
        if ($result !== null) {
            $this->assertIsString($result);
            $this->assertTrue(is_executable($result) || str_contains($result, 'ffprobe'));
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * Test getDurationWithFFprobe returns 0 for non-existent file
     */
    public function testGetDurationWithFFprobeReturnsZeroForNonExistentFile(): void
    {
        $reflection = new \ReflectionClass($this->podcast);
        $method     = $reflection->getMethod('getDurationWithFFprobe');
        $method->setAccessible(true);

        $result = $method->invoke($this->podcast, '/non/existent/file.mp3');
        $this->assertEquals(0, $result);
    }

    /**
     * Test getDurationWithGetID3 returns 0 when library not available
     */
    public function testGetDurationWithGetID3ReturnsZeroWhenNotAvailable(): void
    {
        // Only test if getID3 is NOT available
        if (class_exists('getID3')) {
            $this->markTestSkipped('getID3 is available, skipping unavailable test');
        }

        $reflection = new \ReflectionClass($this->podcast);
        $method     = $reflection->getMethod('getDurationWithGetID3');
        $method->setAccessible(true);

        $tempFile = $this->fixturesPath . 'test.mp3';
        file_put_contents($tempFile, 'test data');

        $result = $method->invoke($this->podcast, $tempFile);
        $this->assertEquals(0, $result);

        @unlink($tempFile);
    }

    /**
     * Test skipID3v2Tag with ID3v2 header
     */
    public function testSkipID3v2TagWithValidHeader(): void
    {
        // Create a valid ID3v2 header
        // ID3v2 header: "ID3" + version (2 bytes) + flags (1 byte) + size (4 bytes syncsafe)
        $header = "ID3";
        $header .= \chr(4) . \chr(0); // Version 2.4.0
        $header .= \chr(0); // Flags
        // Size: 1000 bytes (syncsafe encoding)
        $header .= \chr(0) . \chr(0) . \chr(7) . \chr(104); // 1000 in syncsafe

        // Pad to 100 bytes
        $header .= str_repeat("\0", 100 - \strlen($header));

        $result = $this->podcast->skipID3v2Tag($header);

        // Should return 10 (header) + 1000 (tag) = 1010
        $this->assertEquals(1010, $result);
    }

    /**
     * Test skipID3v2Tag with non-ID3 data
     */
    public function testSkipID3v2TagWithNonID3Data(): void
    {
        $data = "This is not an ID3 header at all";

        $result = $this->podcast->skipID3v2Tag($data);

        $this->assertEquals(0, $result);
    }

    /**
     * Test YouTube URL detection
     */
    #[DataProvider('youtubeUrlProvider')]
    public function testIsYouTubeUrl(string $url, bool $expected): void
    {
        $result = $this->podcast->isYouTubeUrl($url);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for YouTube URL tests
     */
    public static function youtubeUrlProvider(): array
    {
        return [
            'youtu.be short url'   => ['youtu.be/JVkaaZxzVXg', true],
            'youtube.com watch'    => ['https://www.youtube.com/watch?v=JVkaaZxzVXg', true],
            'youtube.com embed'    => ['https://youtube.com/embed/JVkaaZxzVXg', true],
            'youtube.com live'     => ['https://www.youtube.com/live/JVkaaZxzVXg', true],
            'm.youtube.com'        => ['m.youtube.com/watch?v=JVkaaZxzVXg', true],
            'not youtube'          => ['https://vimeo.com/12345', false],
            'local file'           => ['images/media/file.mp3', false],
            'external non-youtube' => ['www.example.com/video.mp4', false],
        ];
    }

    /**
     * Test YouTube video ID extraction
     */
    #[DataProvider('youtubeVideoIdProvider')]
    public function testExtractYouTubeVideoId(string $url, ?string $expectedId): void
    {
        $result = $this->podcast->extractYouTubeVideoId($url);
        $this->assertEquals($expectedId, $result);
    }

    /**
     * Data provider for YouTube video ID extraction tests
     */
    public static function youtubeVideoIdProvider(): array
    {
        return [
            'youtu.be short url' => ['youtu.be/JVkaaZxzVXg', 'JVkaaZxzVXg'],
            'youtube.com watch'  => ['https://www.youtube.com/watch?v=abc123_XYZ', 'abc123_XYZ'],
            'youtube.com embed'  => ['https://youtube.com/embed/test-ID_12', 'test-ID_12'],
            'youtube.com live'   => ['https://www.youtube.com/live/LiveVideo1', 'LiveVideo1'],
            'with extra params'  => ['https://www.youtube.com/watch?v=xyz789&t=120', 'xyz789'],
            'invalid url'        => ['https://example.com/video', null],
        ];
    }

    /**
     * Test ISO 8601 duration parsing
     */
    #[DataProvider('iso8601DurationProvider')]
    public function testParseIso8601Duration(string $duration, int $expectedSeconds): void
    {
        $result = $this->podcast->parseIso8601Duration($duration);
        $this->assertEquals($expectedSeconds, $result);
    }

    /**
     * Data provider for ISO 8601 duration parsing tests
     */
    public static function iso8601DurationProvider(): array
    {
        return [
            '1 hour 30 min 45 sec' => ['PT1H30M45S', 5445],
            '45 minutes'           => ['PT45M', 2700],
            '30 seconds'           => ['PT30S', 30],
            '2 hours'              => ['PT2H', 7200],
            '1 hour 5 seconds'     => ['PT1H5S', 3605],
            '10 min 30 sec'        => ['PT10M30S', 630],
        ];
    }

    /**
     * Create a test WAV file
     *
     * @param int $sampleRate  Sample rate in Hz
     * @param int $bitsPerSample  Bits per sample (8, 16, 24)
     * @param int $channels  Number of channels (1=mono, 2=stereo)
     * @param int $durationSeconds  Duration in seconds
     *
     * @return string  Path to created file
     */
    protected function createTestWavFile(int $sampleRate, int $bitsPerSample, int $channels, int $durationSeconds): string
    {
        $filePath = $this->fixturesPath . 'test_' . uniqid() . '.wav';

        $bytesPerSample = $bitsPerSample / 8;
        $byteRate       = $sampleRate * $channels * $bytesPerSample;
        $blockAlign     = $channels * $bytesPerSample;
        $dataSize       = $byteRate * $durationSeconds;

        $header = '';

        // RIFF header
        $header .= 'RIFF';
        $header .= pack('V', 36 + $dataSize); // File size - 8
        $header .= 'WAVE';

        // fmt chunk
        $header .= 'fmt ';
        $header .= pack('V', 16); // Chunk size
        $header .= pack('v', 1); // Audio format (1 = PCM)
        $header .= pack('v', $channels);
        $header .= pack('V', $sampleRate);
        $header .= pack('V', $byteRate);
        $header .= pack('v', $blockAlign);
        $header .= pack('v', $bitsPerSample);

        // data chunk
        $header .= 'data';
        $header .= pack('V', $dataSize);

        // Write header + some silence (zeros)
        file_put_contents($filePath, $header . str_repeat("\0", min($dataSize, 1000)));

        return $filePath;
    }

    /**
     * Create a test M4A file with moov/mvhd atom
     *
     * @param int $durationSeconds  Duration in seconds
     *
     * @return string  Path to created file
     */
    protected function createTestM4aFile(int $durationSeconds): string
    {
        $filePath = $this->fixturesPath . 'test_' . uniqid() . '.m4a';

        $timescale = 1000; // milliseconds
        $duration  = $durationSeconds * $timescale;

        // Create ftyp atom
        $ftyp = 'M4A ';  // Brand
        $ftyp .= pack('N', 0); // Minor version
        $ftyp .= 'M4A '; // Compatible brand
        $ftyp .= 'mp42'; // Compatible brand
        $ftyp = pack('N', \strlen($ftyp) + 8) . 'ftyp' . $ftyp;

        // Create mvhd atom (movie header)
        $mvhd = '';
        $mvhd .= \chr(0); // Version
        $mvhd .= str_repeat(\chr(0), 3); // Flags
        $mvhd .= pack('N', 0); // Creation time
        $mvhd .= pack('N', 0); // Modification time
        $mvhd .= pack('N', $timescale); // Timescale
        $mvhd .= pack('N', $duration); // Duration
        $mvhd .= pack('N', 0x00010000); // Preferred rate (1.0)
        $mvhd .= pack('n', 0x0100); // Preferred volume (1.0)
        $mvhd .= str_repeat(\chr(0), 10); // Reserved
        $mvhd .= str_repeat(\chr(0), 36); // Matrix
        $mvhd .= str_repeat(\chr(0), 24); // Pre-defined
        $mvhd .= pack('N', 2); // Next track ID

        $mvhd = pack('N', \strlen($mvhd) + 8) . 'mvhd' . $mvhd;

        // Create moov atom containing mvhd
        $moov = pack('N', \strlen($mvhd) + 8) . 'moov' . $mvhd;

        // Write file
        file_put_contents($filePath, $ftyp . $moov);

        return $filePath;
    }

    /**
     * Clean up test fixtures after all tests
     */
    public static function tearDownAfterClass(): void
    {
        $fixturesPath = \dirname(__DIR__, 3) . '/fixtures/media/';

        // Remove any leftover test files
        if (is_dir($fixturesPath)) {
            $files = glob($fixturesPath . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($fixturesPath);
        }

        parent::tearDownAfterClass();
    }
}
