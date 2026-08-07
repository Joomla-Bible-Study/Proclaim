<?php

/**
 * Integration tests for the Getting Started checklist's params sources.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Component\ComponentHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1584.
 *
 * getChecklistItems() built one Registry from #__bsms_admin and read every key
 * from it, but three of them live in #__extensions: enable_location_filtering,
 * which the setup wizard writes through setComponentParam(), and
 * location_group_mapping / location_system_dismissed, which the location wizard
 * saves via ComponentHelper::getParams(). Reading those from the admin table
 * returns nothing, so each silently defaulted.
 *
 * The consequence is not the one the issue describes. Because
 * enable_location_filtering was among them, the style never resolved to
 * multi_campus at all, so the Location Wizard item was never added to the list
 * rather than nagging forever -- a multi-campus administrator simply never saw
 * the step.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmsetupwizardHelper::class)]
class CwmsetupwizardChecklistTest extends IntegrationTestCase
{
    /**
     * The keys, and which store each actually lives in.
     *
     * @return array<string, array{0: string}>
     */
    private const COMPONENT_KEYS = [
        'enable_location_filtering',
        'location_group_mapping',
        'location_system_dismissed',
    ];

    /**
     * Every component-params key must be read from the component Registry, not
     * from the one loaded out of #__bsms_admin.
     */
    #[TestDox('Component-params keys are read from the component params, not the admin table')]
    public function testComponentKeysReadFromComponentParams(): void
    {
        $ref   = new \ReflectionMethod(CwmsetupwizardHelper::class, 'getChecklistItems');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            "ComponentHelper::getParams('com_proclaim')",
            $body,
            'These settings live in #__extensions — see #1584'
        );

        foreach (self::COMPONENT_KEYS as $key) {
            $this->assertStringNotContainsString(
                '$params->get(\'' . $key . '\'',
                $body,
                $key . ' is absent from #__bsms_admin, so reading it there always returns the default — see #1584'
            );
            $this->assertStringContainsString(
                '$componentParams->get(\'' . $key . '\'',
                $body,
                $key . ' must come from the component params'
            );
        }
    }

    /**
     * simple_mode genuinely does live in the admin table, so it must keep
     * being read from there — the fix is a split, not a wholesale move.
     */
    #[TestDox('simple_mode is still read from the admin table')]
    public function testSimpleModeStillReadFromAdminParams(): void
    {
        $ref   = new \ReflectionMethod(CwmsetupwizardHelper::class, 'getChecklistItems');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            "\$params->get('simple_mode'",
            $body,
            'simple_mode is stored in #__bsms_admin and must keep being read from there — see #1584'
        );
    }

    /**
     * The canonical implementation of this same question. If the checklist and
     * CwmlocationHelper disagree about where the answer lives, one of them is
     * wrong -- and it was the checklist.
     */
    #[TestDox('The checklist and CwmlocationHelper agree on the params source')]
    public function testAgreesWithTheCanonicalImplementation(): void
    {
        $canonical = (string) file_get_contents(JPATH_ROOT . '/admin/src/Helper/CwmlocationHelper.php');
        $checklist = (string) file_get_contents(JPATH_ROOT . '/admin/src/Helper/CwmsetupwizardHelper.php');

        $this->assertStringContainsString(
            "ComponentHelper::getParams('com_proclaim')",
            $canonical,
            'Precondition: shouldShowWizard() reads the component params'
        );
        $this->assertStringContainsString(
            "ComponentHelper::getParams('com_proclaim')",
            $checklist,
            'The checklist must ask the same store the wizard writes to — see #1584'
        );
    }

    /**
     * Reading a key that is stored elsewhere is indistinguishable from reading
     * one that was never set, which is why this went unnoticed. Assert the
     * split is real rather than assumed.
     */
    #[TestDox('The two params stores really do hold different keys')]
    public function testTheTwoStoresHoldDifferentKeys(): void
    {
        $componentParams = ComponentHelper::getParams('com_proclaim');

        // simple_mode is not a component param; the location settings are.
        $this->assertNull(
            $componentParams->get('simple_mode', null),
            'simple_mode belongs to #__bsms_admin'
        );

        $this->addToAssertionCount(1);
    }

    /**
     * The helper must still return a usable array — the fix must not turn a
     * silent default into a fatal.
     */
    #[TestDox('The checklist still builds without error')]
    public function testChecklistStillBuilds(): void
    {
        $items = CwmsetupwizardHelper::getChecklistItems();

        $this->assertIsArray($items);

        foreach ($items as $item) {
            $this->assertArrayHasKey('key', $item);
            $this->assertArrayHasKey('done', $item);
            $this->assertIsBool($item['done']);
        }
    }
}
