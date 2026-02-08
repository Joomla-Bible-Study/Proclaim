<?php

/**
 * Integration tests for Cwmthumbnail::check()
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
class CwmthumbnailCheckTest extends IntegrationTestCase
{
    public function testCheckReturnsFalseForNonexistentDirectory(): void
    {
        $this->assertFalse(Cwmthumbnail::check('/nonexistent/directory/path'));
    }

    public function testCheckReturnsTrueForExistingDirectory(): void
    {
        $this->assertTrue(Cwmthumbnail::check(sys_get_temp_dir()));
    }

    public function testCheckReturnsFalseForMissingFile(): void
    {
        // sys_get_temp_dir() exists as a directory, but the file won't exist
        // check() builds: JPATH_ROOT . $path . $file
        $this->assertFalse(
            Cwmthumbnail::check(sys_get_temp_dir(), '/nonexistent_file_' . uniqid() . '.png')
        );
    }
}
