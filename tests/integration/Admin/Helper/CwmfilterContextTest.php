<?php

/**
 * Integration tests for filter-context isolation between list pages.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmfilterHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1582.
 *
 * getFilterValue() hardcoded the sermons list context, but every *ListField
 * calls applyCrossFilters() on whatever page is rendering it. A Book filter set
 * on the Sermons list therefore constrained the Teacher dropdown on Series
 * Displays -- a page whose form has no Book filter and no way to show one is
 * active -- and submitting a filter there wrote back into the sermons list's
 * session key.
 *
 * ListModel::getFilterForm() names the filter form `{context}.filter`, so a
 * field can recover the context of the page it is on.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmfilterHelper::class)]
class CwmfilterContextTest extends IntegrationTestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function formNameProvider(): array
    {
        return [
            'sermons list'    => ['com_proclaim.sermons.list.filter', 'com_proclaim.sermons.list'],
            'series displays' => ['com_proclaim.seriesdisplays.filter', 'com_proclaim.seriesdisplays'],
            'admin messages'  => ['com_proclaim.messages.filter', 'com_proclaim.messages'],
            'nested context'  => ['com_proclaim.a.b.c.filter', 'com_proclaim.a.b.c'],
        ];
    }

    /**
     * @param   string  $formName  The filter form's name
     * @param   string  $expected  The context it should yield
     */
    #[DataProvider('formNameProvider')]
    #[TestDox('The list context is recovered from the filter form name')]
    public function testContextIsDerivedFromTheForm(string $formName, string $expected): void
    {
        $form = new Form($formName);

        $this->assertSame(
            $expected,
            CwmfilterHelper::contextFromForm($form),
            'Each page must read its own filters, not the sermons list — see #1582'
        );
    }

    /**
     * Two different pages must never resolve to the same context, which is
     * precisely what the hardcoded value did.
     */
    #[TestDox('Different pages resolve to different contexts')]
    public function testDifferentPagesDoNotShareAContext(): void
    {
        $sermons = CwmfilterHelper::contextFromForm(new Form('com_proclaim.sermons.list.filter'));
        $series  = CwmfilterHelper::contextFromForm(new Form('com_proclaim.seriesdisplays.filter'));

        $this->assertNotSame(
            $sermons,
            $series,
            'A Book filter on one page silently constrained dropdowns on the other — see #1582'
        );
    }

    /**
     * @return array<string, array{0: ?Form}>
     */
    public static function unresolvableProvider(): array
    {
        return [
            'no form at all'       => [null],
            'form without a name'  => [new Form('')],
            'name is not a filter' => [new Form('com_proclaim.something')],
        ];
    }

    /**
     * Where the context cannot be determined the historic value is kept, so a
     * caller without a form is no worse off than before rather than losing its
     * filters entirely.
     *
     * @param   ?Form  $form  Form to resolve from
     */
    #[DataProvider('unresolvableProvider')]
    #[TestDox('An unresolvable form falls back to the previous behaviour')]
    public function testFallbackIsThePreviousBehaviour(?Form $form): void
    {
        $this->assertSame('com_proclaim.sermons.list', CwmfilterHelper::contextFromForm($form));
    }

    /**
     * The helper is only useful if the dropdowns actually pass their context.
     * A field that keeps calling the two-argument form silently keeps the bug.
     */
    #[TestDox('Every list field passes its own context')]
    public function testEveryListFieldPassesItsContext(): void
    {
        $fields = [
            'MessageTypeListField',
            'TopicsListField',
            'TeacherListField',
            'BookListField',
            'SeriesField',
            'LocationListField',
            'YearListField',
        ];

        foreach ($fields as $field) {
            $source = (string) file_get_contents(JPATH_ROOT . '/admin/src/Field/' . $field . '.php');

            $this->assertStringContainsString(
                'CwmfilterHelper::contextFromForm($this->form)',
                $source,
                $field . ' must read the filters of the page it is on — see #1582'
            );
        }
    }

    /**
     * applyCrossFilters() must accept a context; without the parameter the
     * fields above would have nowhere to pass it.
     */
    #[TestDox('applyCrossFilters accepts a context argument')]
    public function testApplyCrossFiltersAcceptsAContext(): void
    {
        $params = (new \ReflectionMethod(CwmfilterHelper::class, 'applyCrossFilters'))->getParameters();
        $names  = array_map(static fn (\ReflectionParameter $p): string => $p->getName(), $params);

        $this->assertContains('context', $names, 'The caller must be able to say which page it is — see #1582');
    }
}
