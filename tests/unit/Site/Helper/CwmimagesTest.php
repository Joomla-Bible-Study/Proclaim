<?php

/**
 * Unit tests for Cwmimages Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmimages helper
 *
 *#[CoversClass(Cwmimages::class)]
 * @since  10.0.0
 */
class CwmimagesTest extends ProclaimTestCase
{
    /**
     * @var string Path to the Cwmimages class file
     */
    private string $classFile;

    /**
     * @var string Content of the class file
     */
    private string $classContent;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->classFile = JPATH_ROOT . '/site/src/Helper/Cwmimages.php';
        $this->classContent = file_get_contents($this->classFile);
    }

    /**
     * Test class file exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]
     */
    public function testClassFileExists(): void
    {
        $this->assertFileExists($this->classFile);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]
     */
    public function testClassHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Site\Helper;',
            $this->classContent
        );
    }

    /**
     * Test extracted method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::extracted
     */
    public function testExtractedMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function extracted(?string $image1, ?string $image2, string $folder)',
            $this->classContent
        );
    }

    /**
     * Test extracted handles null images
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::extracted
     */
    public function testExtractedHandlesNullImages(): void
    {
        // Verify the null check exists in the extracted method
        $this->assertStringContainsString(
            'if ($image1 === null && $image2 === null)',
            $this->classContent
        );
    }

    /**
     * Test extracted handles dash prefix
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::extracted
     */
    public function testExtractedHandlesDashPrefix(): void
    {
        // Verify the dash prefix check exists
        $this->assertStringContainsString(
            "str_starts_with(\$image1, '- ')",
            $this->classContent
        );
    }

    /**
     * Test getImagePath method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getImagePath
     */
    public function testGetImagePathMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function getImagePath(string $path): object',
            $this->classContent
        );
    }

    /**
     * Test getTeacherThumbnail method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getTeacherThumbnail
     */
    public function testGetTeacherThumbnailMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function getTeacherThumbnail(?string $image1 = \'\', ?string $image2 = \'\')',
            $this->classContent
        );
    }

    /**
     * Test getTeacherImage method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getTeacherImage
     */
    public function testGetTeacherImageMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function getTeacherImage(?string $image1 = null, ?string $image2 = null)',
            $this->classContent
        );
    }

    /**
     * Test getMediaImage method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getMediaImage
     */
    public function testGetMediaImageMethodExists(): void
    {
        $this->assertStringContainsString(
            "public static function getMediaImage(string \$media1 = '', string \$media2 = '')",
            $this->classContent
        );
    }

    /**
     * Test getSeriesThumbnail method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getSeriesThumbnail
     */
    public function testGetSeriesThumbnailMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function getSeriesThumbnail(?string $image)',
            $this->classContent
        );
    }

    /**
     * Test getStudyThumbnail method exists with default
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getStudyThumbnail
     */
    public function testGetStudyThumbnailMethodExistsWithDefault(): void
    {
        $this->assertStringContainsString(
            "public static function getStudyThumbnail(string \$image = 'openbible.png')",
            $this->classContent
        );
    }

    /**
     * Test mainStudyImage method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::mainStudyImage
     */
    public function testMainStudyImageMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function mainStudyImage(?Registry $params = null)',
            $this->classContent
        );
    }

    /**
     * Test getShowHide method exists
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]::getShowHide
     */
    public function testGetShowHideMethodExists(): void
    {
        $this->assertStringContainsString(
            'public static function getShowHide(): object',
            $this->classContent
        );
    }

    /**
     * Test class uses required Joomla classes
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]
     */
    public function testClassUsesRequiredJoomlaClasses(): void
    {
        $this->assertStringContainsString('use Joomla\CMS\Factory;', $this->classContent);
        $this->assertStringContainsString('use Joomla\CMS\HTML\HTMLHelper;', $this->classContent);
        $this->assertStringContainsString('use Joomla\CMS\Image\Image;', $this->classContent);
        $this->assertStringContainsString('use Joomla\Registry\Registry;', $this->classContent);
    }

    /**
     * Test default image paths are defined
     *
     * @return void
     *#[CoversClass(Cwmimages::class)]
     */
    public function testDefaultImagePathsAreDefined(): void
    {
        $this->assertStringContainsString('media/com_proclaim/images/openbible.png', $this->classContent);
        $this->assertStringContainsString('media/com_proclaim/images/showhide.gif', $this->classContent);
    }
}
