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

/**
 * Test class for CwmsetupwizardHelper
 *
 * @since  10.3.0
 */
class CwmsetupwizardHelperTest extends ProclaimTestCase
{
    /**
     * Test that exactly 3 presets are defined.
     */
    public function testThreePresetsExist(): void
    {
        $this->assertCount(3, CwmsetupwizardHelper::PRESETS);
    }

    /**
     * Test that all expected preset keys exist.
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
     * @dataProvider presetKeyProvider
     */
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
     */
    public function testMultiCampusPresetEnablesLocations(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['multi_campus'];

        $this->assertEquals(0, $preset['simple_mode']);
        $this->assertTrue($preset['use_locations']);
        $this->assertTrue($preset['use_topics']);
    }

    /**
     * Test that simple preset only includes backup task.
     */
    public function testSimplePresetTasksMinimal(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['simple'];

        $this->assertEquals(['backup'], $preset['tasks']);
    }

    /**
     * Test that full_media preset includes podcast and analytics tasks.
     */
    public function testFullMediaPresetTasksComplete(): void
    {
        $preset = CwmsetupwizardHelper::PRESETS['full_media'];

        $this->assertContains('backup', $preset['tasks']);
        $this->assertContains('podcast', $preset['tasks']);
        $this->assertContains('analytics', $preset['tasks']);
    }

    /**
     * Test that all preset labels are language string keys.
     *
     * @dataProvider presetKeyProvider
     */
    public function testPresetLabelsAreLangKeys(string $key): void
    {
        $preset = CwmsetupwizardHelper::PRESETS[$key];

        $this->assertStringStartsWith('JBS_WIZARD_STYLE_', $preset['label']);
        $this->assertStringStartsWith('JBS_WIZARD_STYLE_', $preset['description']);
    }

    /**
     * Test that all preset icons are FontAwesome classes.
     *
     * @dataProvider presetKeyProvider
     */
    public function testPresetIconsAreFontAwesome(string $key): void
    {
        $preset = CwmsetupwizardHelper::PRESETS[$key];

        $this->assertStringStartsWith('fa-', $preset['icon']);
    }

    /**
     * Test getPreset returns correct preset.
     */
    public function testGetPresetReturnsCorrectData(): void
    {
        $preset = CwmsetupwizardHelper::getPreset('simple');

        $this->assertIsArray($preset);
        $this->assertEquals('JBS_WIZARD_STYLE_SIMPLE', $preset['label']);
    }

    /**
     * Test getPreset returns null for unknown key.
     */
    public function testGetPresetReturnsNullForUnknown(): void
    {
        $this->assertNull(CwmsetupwizardHelper::getPreset('nonexistent'));
    }

    /**
     * Test shouldShowWizard method exists and is static.
     */
    public function testShouldShowWizardMethodExists(): void
    {
        $ref = new \ReflectionMethod(CwmsetupwizardHelper::class, 'shouldShowWizard');

        $this->assertTrue($ref->isPublic());
        $this->assertTrue($ref->isStatic());
    }

    /**
     * Data provider for preset keys.
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
