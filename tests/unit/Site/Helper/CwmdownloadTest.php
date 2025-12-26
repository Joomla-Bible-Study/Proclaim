<?php

/**
 * Unit tests for Cwmdownload
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmdownload;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmdownload
 *
 * @since  10.0.0
 */
class CwmdownloadTest extends ProclaimTestCase
{
    /**
     * Test class file exists
     *
     * @return void
     */
    public function testClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmdownload.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     */
    public function testClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmdownload.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Site\Helper;',
            $content
        );
    }

    /**
     * Test download method exists
     *
     * @return void
     */
    public function testDownloadMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmdownload::class, 'download'));
    }

    /**
     * Test download method signature
     *
     * @return void
     */
    public function testDownloadMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmdownload::class, 'download');

        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertGreaterThanOrEqual(1, \count($params));
        $this->assertEquals('mid', $params[0]->getName());
    }

    /**
     * Test hitDownloads method exists
     *
     * @return void
     */
    public function testHitDownloadsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmdownload::class, 'hitDownloads'));
    }

    /**
     * Test hitDownloads method signature
     *
     * @return void
     */
    public function testHitDownloadsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmdownload::class, 'hitDownloads');

        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    /**
     * Test class uses Joomla Factory
     *
     * @return void
     */
    public function testClassUsesJoomlaFactory(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmdownload.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('use Joomla\CMS\Factory;', $content);
    }
}
