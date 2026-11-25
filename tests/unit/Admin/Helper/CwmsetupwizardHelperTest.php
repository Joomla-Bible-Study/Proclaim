<?php

/**
 * Unit tests for CwmsetupwizardHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for CwmsetupwizardHelper
 *
 * @since  10.3.0
 */
class CwmsetupwizardHelperTest extends ProclaimTestCase
{
    /**
     * Test that exactly 3 presets are defined.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testThreePresetsExist(): void
    {
        $this->assertCount(3, CwmsetupwizardHelper::PRESETS);
    }

    /**
     * Test that all expected preset keys exist.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testPresetKeysExist(): void
    {
        $this->assertArrayHasKey('simple', CwmsetupwizardHelper::PRESETS);
        $this->assertArrayHasKey('full_media', CwmsetupwizardHelper::PRESETS);
        $this->assertArrayHasKey('multi_campus', CwmsetupwizardHelper::PRESETS);
    }

    /**
     * Test that each preset has all required fields.
     *
     * @param   string  $key  The preset's key
     *
     * @return  void
     *
     * @since   10.3.0
     */
    #[DataProvider('presetKeyProvider')]
    public function testPresetHasRequiredFields(string $key): void
    {
        $preset = CwmsetupwizardHelper::PRESETS[$key];

        $this->assertArrayHasKey('label', $preset);
        $this->assertArrayHasKey('description', $preset);
        $this->assertArrayHasKey('icon', $preset);
        $this->assertArrayHasKey('simple_mode', $preset);
        $this->assertArrayHasKey('use_series', $preset);
        $this->assertArrayHasKey('use_topics', $preset);
        $this->assertArrayHasKey('use_locations', $preset);
        $this->assertArrayHasKey('servers', $preset);
        $this->assertArrayHasKey('tasks', $preset);
    }

    /**
     * Test simple preset has simple_mode enabled.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testSimplePresetEnablesSimpleMode(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['simple'];

        $this->assertEquals(1, $preset['simple_mode']);
        $this->assertFalse($preset['use_topics']);
        $this->assertFalse($preset['use_locations']);
    }

    /**
     * Test full_media preset has simple_mode disabled.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testFullMediaPresetDisablesSimpleMode(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['full_media'];

        $this->assertEquals(0, $preset['simple_mode']);
        $this->assertTrue($preset['use_topics']);
        $this->assertFalse($preset['use_locations']);
    }

    /**
     * Test multi_campus preset enables locations.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testMultiCampusPresetEnablesLocations(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['multi_campus'];

        $this->assertEquals(0, $preset['simple_mode']);
        $this->assertTrue($preset['use_locations']);
        $this->assertTrue($preset['use_topics']);
    }

    /**
     * Test that simple preset auto-creates analytics only (backup/podcast optional).
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testSimplePresetTasksAnalyticsOnly(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['simple'];

        $this->assertEquals(['analytics'], $preset['tasks']);
    }

    /**
     * Test that full_media preset includes analytics (podcast/backup are optional toggles).
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testFullMediaPresetTasksIncludeAnalytics(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['full_media'];

        $this->assertContains('analytics', $preset['tasks']);
        $this->assertNotContains('backup', $preset['tasks']);
        $this->assertNotContains('podcast', $preset['tasks']);
    }

    /**
     * Test that all preset labels are language string keys.
     *
     * @param   string  $key  The preset's key
     *
     * @return  void
     *
     * @since   10.3.0
     */
    #[DataProvider('presetKeyProvider')]
    public function testPresetLabelsAreLangKeys(string $key): void
    {
        $preset = CwmsetupwizardHelper::PRESETS[$key];

        $this->assertStringStartsWith('JBS_WIZARD_STYLE_', $preset['label']);
        $this->assertStringStartsWith('JBS_WIZARD_STYLE_', $preset['description']);
    }

    /**
     * Test that all preset icons are FontAwesome classes.
     *
     * @param   string  $key  The preset's key
     *
     * @return  void
     *
     * @since   10.3.0
     */
    #[DataProvider('presetKeyProvider')]
    public function testPresetIconsAreFontAwesome(string $key): void
    {
        $preset = CwmsetupwizardHelper::PRESETS[$key];

        $this->assertStringStartsWith('fa-', $preset['icon']);
    }

    /**
     * Test getPreset returns correct preset.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testGetPresetReturnsCorrectData(): void
    {
        $preset = CwmsetupwizardHelper::getPreset('simple');

        $this->assertIsArray($preset);
        $this->assertEquals('JBS_WIZARD_STYLE_SIMPLE', $preset['label']);
    }

    /**
     * Test getPreset returns null for unknown key.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testGetPresetReturnsNullForUnknown(): void
    {
        $this->assertNull(CwmsetupwizardHelper::getPreset('nonexistent'));
    }

    /**
     * Test shouldShowWizard method exists and is static.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testShouldShowWizardMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardHelper::class, 'shouldShowWizard');

        $this->assertTrue($ref->isPublic());
        $this->assertTrue($ref->isStatic());
    }

    /**
     * Data provider for preset keys.
     *
     * @return  array<string, array{0: string}>  Each case
     *
     * @since   10.3.0
     */
    public static function presetKeyProvider(): array
    {
        return [
            'simple'       => ['simple'],
            'full_media'   => ['full_media'],
            'multi_campus' => ['multi_campus'],
        ];
    }
}
