<?php

/**
 * Unit tests for Cwmlisting Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmlisting helper
 *
 * #[CoversClass(Cwmlisting::class)]
 * @since  10.0.0
 */
class CwmlistingTest extends ProclaimTestCase
{
    /**
     * Test getFluidListing method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getFluidListing
     */
    public function testGetFluidListingMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getFluidListing');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('items', $params[0]->getName());
        // No type hint in method signature for items

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);

        $this->assertEquals('template', $params[2]->getName());
        $this->assertParamTypeName('stdClass', $params[2]);

        $this->assertEquals('type', $params[3]->getName());
        $this->assertParamTypeName('string', $params[3]);
    }

    /**
     * Test getFluidMediaids method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getFluidMediaids
     */
    public function testGetFluidMediaidsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getFluidMediaids');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('item', $params[0]->getName());
        // No type hint in method signature for item
    }

    /**
     * Test getMediaFiles method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getMediaFiles
     */
    public function testGetMediaFilesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getMediaFiles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('medias', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);
    }

    /**
     * Test getListParamsArray method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getListParamsArray
     */
    public function testGetListParamsArrayMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getListParamsArray');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('stdClass', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('paramtext', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test sortArrayofObjectByProperty method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::sortArrayofObjectByProperty
     */
    public function testSortArrayofObjectByPropertyMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'sortArrayofObjectByProperty');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('array', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);

        $this->assertEquals('property', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);

        $this->assertEquals('order', $params[2]->getName());
        $this->assertParamTypeName('string', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getFluidRow method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getFluidRow
     */
    public function testGetFluidRowMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getFluidRow');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(7, $params);
        $this->assertEquals('listrows', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);

        $this->assertEquals('listsorts', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);

        $this->assertEquals('item', $params[2]->getName());
        $this->assertParamTypeName('object', $params[2]);

        $this->assertEquals('params', $params[3]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[3]);

        $this->assertEquals('template', $params[4]->getName());
        // No type hint in method signature for template

        $this->assertEquals('header', $params[5]->getName());
        $this->assertParamTypeName('int', $params[5]);

        $this->assertEquals('type', $params[6]->getName());
        $this->assertParamTypeName('string', $params[6]);
    }

    /**
     * Test useJImage method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::useJImage
     */
    public function testUseJImageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'useJImage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|string, which reflection might show differently

        $params = $reflection->getParameters();
        $this->assertCount(6, $params);
        $this->assertEquals('path', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('alt', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());

        $this->assertEquals('id', $params[2]->getName());
        $this->assertParamTypeName('string', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());

        $this->assertEquals('width', $params[3]->getName());
        $this->assertParamTypeName('string', $params[3]);
        $this->assertTrue($params[3]->allowsNull());
        $this->assertTrue($params[3]->isOptional());

        $this->assertEquals('height', $params[4]->getName());
        $this->assertParamTypeName('string', $params[4]);
        $this->assertTrue($params[4]->allowsNull());
        $this->assertTrue($params[4]->isOptional());

        $this->assertEquals('class', $params[5]->getName());
        $this->assertParamTypeName('string', $params[5]);
        $this->assertTrue($params[5]->allowsNull());
        $this->assertTrue($params[5]->isOptional());
    }

    /**
     * Test getFluidData method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getFluidData
     */
    public function testGetFluidDataMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getFluidData');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(6, $params);
        $this->assertEquals('item', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);

        $this->assertEquals('row', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);

        $this->assertEquals('params', $params[2]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[2]);

        $this->assertEquals('template', $params[3]->getName());
        $this->assertParamTypeName('stdClass', $params[3]);

        $this->assertEquals('header', $params[4]->getName());
        $this->assertParamTypeName('int', $params[4]);

        $this->assertEquals('type', $params[5]->getName());
        $this->assertParamTypeName('string', $params[5]);
    }

    /**
     * Test getFluidCustom method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getFluidCustom
     */
    public function testGetFluidCustomMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getFluidCustom');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        $this->assertEquals('custom', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('item', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);

        $this->assertEquals('params', $params[2]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[2]);

        $this->assertEquals('template', $params[3]->getName());
        // No type hint in method signature for template

        $this->assertEquals('type', $params[4]->getName());
        $this->assertParamTypeName('string', $params[4]);
    }

    /**
     * Test getElement method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getElement
     */
    public function testGetElementMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getElement');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is mixed, which reflection might show differently

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        $this->assertEquals('custom', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('row', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);

        $this->assertEquals('params', $params[2]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[2]);

        $this->assertEquals('template', $params[3]->getName());
        $this->assertParamTypeName('stdClass', $params[3]);

        $this->assertEquals('type', $params[4]->getName());
        $this->assertParamTypeName('string', $params[4]);
    }

    /**
     * Test getScripture method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getScripture
     */
    public function testGetScriptureMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getScripture');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);

        $this->assertEquals('row', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);

        $this->assertEquals('esv', $params[2]->getName());
        $this->assertParamTypeName('int', $params[2]);

        $this->assertEquals('scripturerow', $params[3]->getName());
        $this->assertParamTypeName('int', $params[3]);

        $this->assertEquals('elementConfig', $params[4]->getName());
        $this->assertParamTypeName('object', $params[4]);
        $this->assertTrue($params[4]->allowsNull());
        $this->assertTrue($params[4]->isOptional());
    }

    /**
     * Test getFluidMediaFiles method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getFluidMediaFiles
     */
    public function testGetFluidMediaFilesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getFluidMediaFiles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('item', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[1]);

        $this->assertEquals('template', $params[2]->getName());
        $this->assertParamTypeName('stdClass', $params[2]);
    }

    /**
     * Test getStudyDate method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getStudyDate
     */
    public function testGetStudyDateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getStudyDate');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);

        $this->assertEquals('studydate', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);

        $this->assertEquals('row', $params[2]->getName());
        $this->assertParamTypeName('object', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test createelement method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::createelement
     */
    public function testCreateelementMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'createelement');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('element', $params[0]->getName());
        // No type hint in method signature for element
    }

    /**
     * Test getOtherlinks method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getOtherlinks
     */
    public function testGetOtherlinksMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getOtherlinks');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('id3', $params[0]->getName());
        // No type hint in method signature for id3

        $this->assertEquals('islink', $params[1]->getName());
        // No type hint in method signature for islink

        $this->assertEquals('params', $params[2]->getName());
        // No type hint in method signature for params
    }

    /**
     * Test getListingExp method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getListingExp
     */
    public function testGetListingExpMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getListingExp');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('row', $params[0]->getName());
        // No type hint in method signature for row

        $this->assertEquals('params', $params[1]->getName());
        // No type hint in method signature for params

        $this->assertEquals('template', $params[2]->getName());
        // No type hint in method signature for template
    }

    /**
     * Test getPassage method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getPassage
     */
    public function testGetPassageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getPassage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('params', $params[0]->getName());
        // No type hint in method signature for params

        $this->assertEquals('row', $params[1]->getName());
        // No type hint in method signature for row
    }

    /**
     * Test getShare method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::getShare
     */
    public function testGetShareMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'getShare');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
        $this->assertTrue($reflection->getReturnType()->allowsNull());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('link', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);

        $this->assertEquals('row', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);

        $this->assertEquals('params', $params[2]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[2]);
    }

    /**
     * Test runContentPlugins method signature
     *
     * @return void
     * #[CoversClass(Cwmlisting::class)]::runContentPlugins
     */
    public function testRunContentPluginsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmlisting::class, 'runContentPlugins');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('object', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('item', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);

        $this->assertEquals('params', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);
    }
}
