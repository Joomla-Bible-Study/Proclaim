<?php

/**
 * Integration tests for Cwmthumbnail::deleteFolder()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * deleteFolder() refuses an empty path and paths outside the studies, teachers and series
 * image folders. It accepts a path in each of them, and a studies path that starts with a
 * slash.
 *
 * @since  10.1.0
 */
#[CoversClass(Cwmthumbnail::class)]
class CwmthumbnailDeleteFolderTest extends IntegrationTestCase
{
    /**
     * deleteFolder() refuses etc/passwd, a path outside the image folders.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRejectsPathOutsideAllowedScope(): void
    {
        $this->assertFalse(Cwmthumbnail::deleteFolder('etc/passwd'));
    }

    /**
     * deleteFolder() refuses an empty path.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRejectsEmptyPath(): void
    {
        $this->assertFalse(Cwmthumbnail::deleteFolder(''));
    }

    /**
     * deleteFolder() refuses a path under images that is outside the three thumbnail folders.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRejectsArbitraryImagesPath(): void
    {
        $this->assertFalse(Cwmthumbnail::deleteFolder('images/other/evil'));
    }

    /**
     * deleteFolder() accepts a path in the studies image folder, returning true though the
     * folder doesn't exist.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testAcceptsStudiesPath(): void
    {
        // Folder doesn't exist on disk, so deleteFolder returns true (idempotent)
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/studies/test-alias-123')
        );
    }

    /**
     * deleteFolder() accepts a path in the teachers image folder.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testAcceptsTeachersPath(): void
    {
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/teachers/pastor-john')
        );
    }

    /**
     * deleteFolder() accepts a path in the series image folder.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testAcceptsSeriesPath(): void
    {
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/series/romans-study')
        );
    }

    /**
     * deleteFolder() accepts a studies path that starts with a slash.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testNormalizesLeadingSlash(): void
    {
        // Leading slash is trimmed, then path matches allowed prefix
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('/images/biblestudy/studies/test')
        );
    }
}
