<?php

/**
 * Unit tests for Cwmthumbnail Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmthumbnail helper
 *
 *#[CoversClass(Cwmthumbnail::class)]
 * @since  10.1.0
 */
class CwmthumbnailTest extends ProclaimTestCase
{
    /**
     * Test that the SCALE_INSIDE constant is defined correctly
     *
     * @return  void
     *#[CoversClass(Cwmthumbnail::class)]::SCALE_INSIDE
     *
     * @since   10.1.0
     */
    public function testScaleInsideConstant(): void
    {
        $this->assertEquals(2, Cwmthumbnail::SCALE_INSIDE);
    }

    /**
     * Test check method returns false for non-existent directory
     *
     * @return  void
     *#[CoversClass(Cwmthumbnail::class)]::check
     *
     * @since   10.1.0
     */
    public function testCheckReturnsFalseForNonExistentDirectory(): void
    {
        $result = Cwmthumbnail::check('/non/existent/path');

        $this->assertFalse($result);
    }

    /**
     * Test check method returns true for existing directory
     *
     * @return  void
     *#[CoversClass(Cwmthumbnail::class)]::check
     *
     * @since   10.1.0
     */
    public function testCheckReturnsTrueForExistingDirectory(): void
    {
        $result = Cwmthumbnail::check(sys_get_temp_dir());

        $this->assertTrue($result);
    }

    /**
     * Test check method with file parameter returns false for non-existent file
     *
     * @return  void
     *#[CoversClass(Cwmthumbnail::class)]::check
     *
     * @since   10.1.0
     */
    public function testCheckReturnsFalseForNonExistentFile(): void
    {
        $result = Cwmthumbnail::check(sys_get_temp_dir(), '/non_existent_file.jpg');

        $this->assertFalse($result);
    }

    /**
     * extensionForType() names JPEG output jpg, not jpeg.
     *
     * @return  void
     *
     * @since   10.7.2
     */
    #[\PHPUnit\Framework\Attributes\TestDox('JPEG output is named .jpg, not .jpeg')]
    public function testJpegOutputIsNamedJpg(): void
    {
        $this->assertSame('jpg', Cwmthumbnail::extensionForType(IMAGETYPE_JPEG), 'Every other path writes .jpg; JPEG must match.');
    }

    /**
     * extensionForType() gives PNG, GIF and WebP output their own extensions.
     *
     * @return  void
     *
     * @since   10.7.2
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Other image types keep their own extension')]
    public function testOtherTypesKeepTheirExtension(): void
    {
        $this->assertSame('png', Cwmthumbnail::extensionForType(IMAGETYPE_PNG));
        $this->assertSame('gif', Cwmthumbnail::extensionForType(IMAGETYPE_GIF));
        $this->assertSame('webp', Cwmthumbnail::extensionForType(IMAGETYPE_WEBP));
    }

    /**
     * Stored media-field values, each with the relative path mediaFieldPath() should
     * return.
     *
     * @return  array<string, array{0: ?string, 1: string}>  Each case
     *
     * @since   10.7.3
     */
    public static function mediaFieldPathProvider(): array
    {
        return [
            'null'                 => [null, ''],
            'empty'                => ['', ''],
            'plain path unchanged' => ['images/teacher.jpg', 'images/teacher.jpg'],
            'joomlaImage fragment' => [
                'images/teacher.jpg#joomlaImage://local-images/teacher.jpg?width=300&height=200',
                'images/teacher.jpg',
            ],
            'encoded space with fragment' => [
                'images/Pastor%20John%202024.jpg#joomlaImage://local-images/Pastor John 2024.jpg?width=10&height=10',
                'images/Pastor John 2024.jpg',
            ],
            'encoded space, no fragment'  => ['images/my%20photo.jpg', 'images/my photo.jpg'],
            'already-decoded space'       => ['images/my photo.jpg', 'images/my photo.jpg'],
            'plus is kept'                => ['images/a+b.jpg', 'images/a+b.jpg'],
            'dot-dot segment'             => ['images/../configuration.php', ''],
            'encoded dot-dot segment'     => ['images/%2e%2e/configuration.php', ''],
            'backslash dot-dot segment'   => ['images\\..\\configuration.php', ''],
            'encoded NUL byte'            => ['images/a.jpg%00.php', ''],
            'dots inside a name are fine' => ['images/sermon..final.jpg', 'images/sermon..final.jpg'],
        ];
    }

    /**
     * mediaFieldPath() drops the joomlaImage fragment and decodes the path, and returns an
     * empty string for null, empty, traversal and NUL-byte values.
     *
     * @param   ?string  $value     Stored media-field value
     * @param   string   $expected  Expected relative path
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mediaFieldPathProvider')]
    #[\PHPUnit\Framework\Attributes\TestDox('mediaFieldPath() turns a media-field value into a file path: $_dataName')]
    public function testMediaFieldPath(?string $value, string $expected): void
    {
        $this->assertSame($expected, Cwmthumbnail::mediaFieldPath($value));
    }
}
