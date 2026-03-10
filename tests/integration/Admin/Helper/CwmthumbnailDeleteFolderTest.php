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

#[CoversClass(Cwmthumbnail::class)]
class CwmthumbnailDeleteFolderTest extends IntegrationTestCase
{
    public function testRejectsPathOutsideAllowedScope(): void
    {
        $this->assertFalse(Cwmthumbnail::deleteFolder('etc/passwd'));
    }

    public function testRejectsEmptyPath(): void
    {
        $this->assertFalse(Cwmthumbnail::deleteFolder(''));
    }

    public function testRejectsArbitraryImagesPath(): void
    {
        $this->assertFalse(Cwmthumbnail::deleteFolder('images/other/evil'));
    }

    public function testAcceptsStudiesPath(): void
    {
        // Folder doesn't exist on disk, so deleteFolder returns true (idempotent)
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/studies/test-alias-123')
        );
    }

    public function testAcceptsTeachersPath(): void
    {
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/teachers/pastor-john')
        );
    }

    public function testAcceptsSeriesPath(): void
    {
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('images/biblestudy/series/romans-study')
        );
    }

    public function testNormalizesLeadingSlash(): void
    {
        // Leading slash is trimmed, then path matches allowed prefix
        $this->assertTrue(
            Cwmthumbnail::deleteFolder('/images/biblestudy/studies/test')
        );
    }
}
