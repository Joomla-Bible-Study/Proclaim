<?php

/**
 * Toolbar contract for the admin edit views.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\View;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Joomla renders an edit form's save variants as one split button: Save, with
 * Save & Close / Save & New / Save as Copy behind its dropdown. Calling
 * `ToolbarHelper::save2new()` and friends instead lays every variant out flat,
 * which is what Proclaim did until the toolbars were regrouped — six green
 * buttons in a row, and a toolbar that overflows on a narrow window.
 *
 * These are source assertions rather than render assertions on purpose: the
 * toolbar needs a booted administrator application to render, and the thing
 * worth pinning is the API each view calls.
 *
 * @since  __DEPLOY_VERSION__
 */
class ToolbarSaveGroupTest extends ProclaimTestCase
{
    /**
     * Views with no save variants to group — a single Save is already correct.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const NO_VARIANTS = [
        'Cwmadmin',
        'Cwmmessagetype',
        'Cwmpermissions',
        'Cwmserie',
        'Cwmteacher',
        'Cwmtopic',
    ];

    /**
     * @return  string
     * @since   __DEPLOY_VERSION__
     */
    private static function repoRoot(): string
    {
        return \dirname(__DIR__, 4);
    }

    /**
     * Every admin view that builds an edit toolbar.
     *
     * @return  array<string, array{0: string, 1: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function editViewProvider(): array
    {
        $cases = [];

        foreach (glob(self::repoRoot() . '/admin/src/View/*/HtmlView.php') as $file) {
            $source = file_get_contents($file);

            // An edit view is one that offers Apply; list views never do.
            if (!str_contains($source, '->apply(') && !str_contains($source, 'ToolbarHelper::apply(')) {
                continue;
            }

            $cases[basename(\dirname($file))] = [basename(\dirname($file)), $source];
        }

        return $cases;
    }

    #[DataProvider('editViewProvider')]
    #[TestDox('$view groups its save variants into a dropdown')]
    public function testSaveVariantsAreGrouped(string $view, string $source): void
    {
        $flat = [];

        foreach (['save2new', 'save2copy'] as $variant) {
            if (str_contains($source, 'ToolbarHelper::' . $variant . '(')) {
                $flat[] = 'ToolbarHelper::' . $variant . '()';
            }
        }

        self::assertSame(
            [],
            $flat,
            $view . ' calls ' . implode(' and ', $flat) . ' directly, which renders the variants as separate '
            . 'buttons. Add them to a $toolbar->dropdownButton(\'save-group\')->configure(…) child bar instead.'
        );

        $offersVariants = str_contains($source, 'save2new(') || str_contains($source, 'save2copy(');

        if (!$offersVariants) {
            self::assertContains(
                $view,
                self::NO_VARIANTS,
                $view . ' offers no Save & New or Save as Copy. If that is deliberate, list it in NO_VARIANTS; '
                . 'otherwise it is missing variants every other edit view has.'
            );

            return;
        }

        self::assertStringContainsString(
            "dropdownButton('save-group')",
            $source,
            $view . ' has save variants but no save-group dropdown to hold them.'
        );
    }

    #[DataProvider('editViewProvider')]
    #[TestDox('$view imports Toolbar when it type-hints the child bar')]
    public function testToolbarImported(string $view, string $source): void
    {
        if (!str_contains($source, 'Toolbar $childBar')) {
            $this->assertTrue(true, $view . ' does not type-hint a child bar.');

            return;
        }

        self::assertStringContainsString(
            'use Joomla\CMS\Toolbar\Toolbar;',
            $source,
            $view . ' type-hints Toolbar in a configure() closure but never imports it, which is a fatal '
            . 'the moment that branch runs.'
        );
    }
}
