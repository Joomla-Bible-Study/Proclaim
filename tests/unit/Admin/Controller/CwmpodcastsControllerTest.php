<?php

/**
 * Unit tests for CwmpodcastsController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmpodcastsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmpodcastsController
 *
 * #[CoversClass(CwmpodcastsController::class)]
 * @since  10.0.0
 */
class CwmpodcastsControllerTest extends ProclaimTestCase
{
    /**
     * Test writeXMLFile method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::writeXMLFile
     */
    public function testWriteXMLFileMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'writeXMLFile');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test validate method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::validate
     */
    public function testValidateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'validate');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test fixDurations method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::fixDurations
     */
    public function testFixDurationsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'fixDurations');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getMediaFilesForDuration method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::getMediaFilesForDuration
     */
    public function testGetMediaFilesForDurationMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'getMediaFilesForDuration');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test fixSingleDuration method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::fixSingleDuration
     */
    public function testFixSingleDurationMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'fixSingleDuration');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getMediaFilesForMetadata method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::getMediaFilesForMetadata
     */
    public function testGetMediaFilesForMetadataMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'getMediaFilesForMetadata');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test fixSingleMetadata method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::fixSingleMetadata
     */
    public function testFixSingleMetadataMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'fixSingleMetadata');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test storeFixResults method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::storeFixResults
     */
    public function testStoreFixResultsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'storeFixResults');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getModel method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::getModel
     */
    public function testGetModelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'getModel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('Joomla\CMS\MVC\Model\BaseDatabaseModel', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());

        $this->assertEquals('config', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getQuickIconPodcasts method signature
     *
     * @return void
     * #[CoversClass(CwmpodcastsController::class)]::getQuickIconPodcasts
     */
    public function testGetQuickIconPodcastsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmpodcastsController::class, 'getQuickIconPodcasts');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }
}
