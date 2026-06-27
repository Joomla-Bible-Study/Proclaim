<?php

/**
 * Integration tests for Cwmthumbnail::buildSafeBaseFilename()
 *
 * Covers issue #1272: message image filenames built from long sermon titles or
 * raw upload names (with spaces, smart quotes and commas) must be slugified and
 * length-capped so they are safe on the filesystem and in URLs.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cwmthumbnail::class)]
class CwmthumbnailBuildSafeBaseFilenameTest extends IntegrationTestCase
{
    public function testSlugifiesTitle(): void
    {
        $result = Cwmthumbnail::buildSafeBaseFilename('Romans Study Part 1', '/tmp/original.jpg');

        $this->assertSame('romans-study-part-1', $result);
    }

    public function testEmptyTitleFallsBackToBasename(): void
    {
        $result = Cwmthumbnail::buildSafeBaseFilename('   ', '/tmp/photo.jpg');

        $this->assertSame('photo', $result);
    }

    /**
     * The fallback basename branch must be sanitised too — previously it passed
     * the raw filename through unchanged, which is the core of issue #1272.
     */
    public function testSanitisesFallbackBasename(): void
    {
        $result = Cwmthumbnail::buildSafeBaseFilename(null, '/tmp/My Photo, Final.jpg');

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9-]+$/', $result);
        $this->assertNotSame('', $result);
    }

    /**
     * The real-world #1272 filename: long, smart quotes, commas — must become a
     * safe, length-capped slug with no trailing dash.
     */
    public function testCapsLongMessyTitle(): void
    {
        $title = '“Anticipating the Return of Christ A Future Hope” by Elder Uche Sampson '
            . 'Communion Sabbath Sabbath, June 27th at 1105 am by';

        $result = Cwmthumbnail::buildSafeBaseFilename($title, '/tmp/original.jpg');

        $this->assertLessThanOrEqual(80, mb_strlen($result));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9-]+$/', $result);
        $this->assertStringEndsNotWith('-', $result);
    }

    public function testFallsBackToImageWhenSlugIsEmpty(): void
    {
        // A filename of only dashes slugifies to an empty string.
        $result = Cwmthumbnail::buildSafeBaseFilename('', '/tmp/---.jpg');

        $this->assertSame('image', $result);
    }
}
