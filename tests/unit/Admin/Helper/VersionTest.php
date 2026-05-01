<?php

/**
 * Unit tests for Version Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Version;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Version helper
 *
 * #[CoversClass(Version::class)]
 * @since  10.0.0
 */
class VersionTest extends ProclaimTestCase
{
    /**
     * Test product name constant
     *
     * @return void
     * #[CoversClass(Version::class)]::PRODUCT
     */
    public function testProductNameIsCorrect(): void
    {
        $this->assertEquals('Proclaim', Version::PRODUCT);
    }

    /**
     * Test major version constant
     *
     * @return void
     * #[CoversClass(Version::class)]::MAJOR_VERSION
     */
    public function testMajorVersionIsInteger(): void
    {
        $this->assertIsInt(Version::MAJOR_VERSION);
    }

    /**
     * Test minor version constant
     *
     * @return void
     * #[CoversClass(Version::class)]::MINOR_VERSION
     */
    public function testMinorVersionIsInteger(): void
    {
        $this->assertIsInt(Version::MINOR_VERSION);
    }

    /**
     * Test patch version constant
     *
     * @return void
     * #[CoversClass(Version::class)]::PATCH_VERSION
     */
    public function testPatchVersionIsInteger(): void
    {
        $this->assertIsInt(Version::PATCH_VERSION);
    }

    /**
     * Test isInDevelopmentState method
     *
     * @return void
     * #[CoversClass(Version::class)]::isInDevelopmentState
     */
    public function testIsInDevelopmentState(): void
    {
        $version = new Version();
        $this->assertIsBool($version->isInDevelopmentState());
    }

    /**
     * Test isCompatible method
     *
     * @return void
     * #[CoversClass(Version::class)]::isCompatible
     */
    public function testIsCompatible(): void
    {
        $version = new Version();

        // Should always be compatible with a lower version
        $this->assertTrue($version->isCompatible('3.0.0'));

        // Should not be compatible with a version higher than current
        $futureVersion = ((int) JVERSION + 1) . '.0.0';
        $this->assertFalse($version->isCompatible($futureVersion));
    }

    /**
     * Test getHelpVersion method
     *
     * @return void
     * #[CoversClass(Version::class)]::getHelpVersion
     */
    public function testGetHelpVersion(): void
    {
        $version     = new Version();
        $helpVersion = $version->getHelpVersion();

        $this->assertIsString($helpVersion);
        $this->assertStringStartsWith('.', $helpVersion);
        $this->assertStringContainsString((string)Version::MAJOR_VERSION, $helpVersion);
    }

    /**
     * Test getShortVersion method
     *
     * @return void
     * #[CoversClass(Version::class)]::getShortVersion
     */
    public function testGetShortVersion(): void
    {
        $version      = new Version();
        $shortVersion = $version->getShortVersion();

        $this->assertIsString($shortVersion);
        $this->assertStringContainsString('.', $shortVersion);

        $expectedStart = Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION . '.' . Version::PATCH_VERSION;
        $this->assertStringStartsWith($expectedStart, $shortVersion);
    }

    /**
     * Test getLongVersion method
     *
     * @return void
     * #[CoversClass(Version::class)]::getLongVersion
     */
    public function testGetLongVersion(): void
    {
        $version     = new Version();
        $longVersion = $version->getLongVersion();

        $this->assertIsString($longVersion);
        $this->assertStringContainsString(Version::PRODUCT, $longVersion);
        $this->assertStringContainsString(Version::CODENAME, $longVersion);
        $this->assertStringContainsString(Version::RELDATE, $longVersion);
    }

    /**
     * Test getUserAgent method
     *
     * @return void
     * #[CoversClass(Version::class)]::getUserAgent
     */
    public function testGetUserAgent(): void
    {
        $version = new Version();

        // Test default
        $ua = $version->getUserAgent();
        $this->assertStringContainsString(Version::PRODUCT, $ua);
        $this->assertStringContainsString('Framework', $ua);

        // Test with suffix
        $ua = $version->getUserAgent('TestSuffix');
        $this->assertStringContainsString('TestSuffix', $ua);

        // Test with mask
        $ua = $version->getUserAgent('', true);
        $this->assertStringStartsWith('Mozilla/5.0', $ua);
    }
}
