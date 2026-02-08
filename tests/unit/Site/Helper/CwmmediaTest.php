<?php

/**
 * Unit tests for Cwmmedia Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmmedia helper
 *
 * #[CoversClass(Cwmmedia::class)]
 * @since  10.0.0
 */
class CwmmediaTest extends ProclaimTestCase
{
    /**
     * Test isExternal method
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::isExternal
     */
    public function testIsExternal(): void
    {
        // Mock Uri::root() if needed, but for now we rely on default behavior or mock it if possible
        // Since we can't easily mock static methods of Joomla classes without runkit/uopz,
        // we'll test logic that doesn't depend heavily on Uri::root() or assume a default.
        
        // Test absolute URLs
        $this->assertTrue(Cwmmedia::isExternal('http://google.com/image.jpg'));
        $this->assertTrue(Cwmmedia::isExternal('https://example.com/file.mp3'));
        
        // Test relative URLs (should be false)
        $this->assertFalse(Cwmmedia::isExternal('images/local.jpg'));
        $this->assertFalse(Cwmmedia::isExternal('/images/local.jpg'));
    }

    /**
     * Test getFluidMedia method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getFluidMedia
     */
    public function testGetFluidMediaMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getFluidMedia');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
        $this->assertTrue($reflection->getReturnType()->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
        
        $this->assertEquals('template', $params[2]->getName());
        // No type hint in method signature for template
    }

    /**
     * Test mediaButton method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::mediaButton
     */
    public function testMediaButtonMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'mediaButton');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
        $this->assertTrue($reflection->getReturnType()->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('imageparams', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
        
        $this->assertEquals('media', $params[2]->getName());
        $this->assertEquals('object', $params[2]->getType()->getName());
    }

    /**
     * Test useJImage method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::useJImage
     */
    public function testUseJImageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'useJImage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('path', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('alt', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
    }

    /**
     * Test getPlayerAttributes method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getPlayerAttributes
     */
    public function testGetPlayerAttributesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getPlayerAttributes');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('object', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
        
        $this->assertEquals('media', $params[1]->getName());
        $this->assertEquals('object', $params[1]->getType()->getName());
    }

    /**
     * Test getPlayerCode method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getPlayerCode
     */
    public function testGetPlayerCodeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getPlayerCode');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
        
        $this->assertEquals('player', $params[1]->getName());
        $this->assertEquals('object', $params[1]->getType()->getName());
        
        $this->assertEquals('image', $params[2]->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        
        $this->assertEquals('media', $params[3]->getName());
        $this->assertEquals('object', $params[3]->getType()->getName());
    }

    /**
     * Test getFluidFilesize method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getFluidFilesize
     */
    public function testGetFluidFilesizeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getFluidFilesize');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
    }

    /**
     * Test convertFileSize method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::convertFileSize
     */
    public function testConvertFileSizeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'convertFileSize');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('file_size', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
        
        $this->assertEquals('media', $params[2]->getName());
        $this->assertEquals('object', $params[2]->getType()->getName());
    }

    /**
     * Test renderSB method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::renderSB
     */
    public function testRenderSBMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'renderSB');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(6, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
        
        $this->assertEquals('player', $params[2]->getName());
        $this->assertEquals('object', $params[2]->getType()->getName());
        
        $this->assertEquals('image', $params[3]->getName());
        $this->assertEquals('string', $params[3]->getType()->getName());
        
        $this->assertEquals('path', $params[4]->getName());
        $this->assertEquals('string', $params[4]->getType()->getName());
        
        $this->assertEquals('direct', $params[5]->getName());
        $this->assertEquals('bool', $params[5]->getType()->getName());
        $this->assertTrue($params[5]->isOptional());
    }

    /**
     * Test convertVimeo method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::convertVimeo
     */
    public function testConvertVimeoMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'convertVimeo');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('string', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test getAVmediacode method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getAVmediacode
     */
    public function testGetAVmediacodeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getAVmediacode');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('mediacode', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('media', $params[1]->getName());
        $this->assertEquals('object', $params[1]->getType()->getName());
    }

    /**
     * Test getDocman method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getDocman
     */
    public function testGetDocmanMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getDocman');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('image', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
    }

    /**
     * Test getArticle method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getArticle
     */
    public function testGetArticleMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getArticle');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('image', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
    }

    /**
     * Test getVirtuemart method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getVirtuemart
     */
    public function testGetVirtuemartMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getVirtuemart');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('image', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
    }

    /**
     * Test getFluidDownloadLink method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getFluidDownloadLink
     */
    public function testGetFluidDownloadLinkMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getFluidDownloadLink');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
        
        $this->assertEquals('template', $params[2]->getName());
        // No type hint in method signature for template
    }

    /**
     * Test downloadButton method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::downloadButton
     */
    public function testDownloadButtonMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'downloadButton');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
        $this->assertTrue($reflection->getReturnType()->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('download', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
    }

    /**
     * Test hitPlay method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::hitPlay
     */
    public function testHitPlayMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'hitPlay');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
    }

    /**
     * Test getMediaRows2 method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getMediaRows2
     */
    public function testGetMediaRows2MethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getMediaRows2');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is object|bool, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
    }

    /**
     * Test getMimetypes method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getMimetypes
     */
    public function testGetMimetypesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getMimetypes');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test getIcons method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getIcons
     */
    public function testGetIconsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getIcons');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test convertYoutube method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::convertYoutube
     */
    public function testConvertYoutubeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'convertYoutube');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('path', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test ensureHttpJoomla method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::ensureHttpJoomla
     */
    public function testEnsureHttpJoomlaMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'ensureHttpJoomla');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('url', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test processPopupText method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::processPopupText
     */
    public function testProcessPopupTextMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'processPopupText');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('text', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('media', $params[1]->getName());
        $this->assertEquals('object', $params[1]->getType()->getName());
        
        $this->assertEquals('params', $params[2]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[2]->getType()->getName());
    }

    /**
     * Test getPopupHeader method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getPopupHeader
     */
    public function testGetPopupHeaderMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getPopupHeader');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
    }

    /**
     * Test getPopupFooter method signature
     *
     * @return void
     * #[CoversClass(Cwmmedia::class)]::getPopupFooter
     */
    public function testGetPopupFooterMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmmedia::class, 'getPopupFooter');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('media', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[1]->getType()->getName());
    }
}
