<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\UpdateFiltersTrait;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

/**
 * Tests for UpdateFiltersTrait.
 *
 * The searchtools layout decides whether the filter bar renders open purely from
 * `empty($view->activeFilters)`, so the shape and contents of that array are the
 * contract under test here.
 *
 * @since 10.5.0
 */
class UpdateFiltersTraitTest extends ProclaimTestCase
{
    /**
     * Filter form XML mirroring the groups site/forms/filter_cwmsermons.xml declares.
     *
     * @var    string
     * @since  10.5.0
     */
    private const FORM_XML = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<form>
    <fields name="filter">
        <field name="search" type="text" />
        <field name="book" type="text" />
        <field name="teacher" type="text" />
        <field name="series" type="text" />
        <field name="messagetype" type="text" />
        <field name="year" type="text" />
        <field name="topic" type="text" />
        <field name="location" type="text" />
        <field name="language" type="text" />
    </fields>
    <fields name="list">
        <field name="fullordering" type="text" />
        <field name="limit" type="text" />
    </fields>
</form>
XML;

    /**
     * Request keys the trait reads, cleared between tests.
     *
     * @var    string[]
     * @since  10.5.0
     */
    private const REQUEST_KEYS = [
        'filter_search',
        'filter_book',
        'filter_teacher',
        'filter_series',
        'filter_messagetype',
        'filter_year',
        'filter_topic',
        'filter_location',
        'filter_language',
    ];

    /**
     * Reset the shared CLI input between tests.
     *
     * @return  void
     *
     * @since   10.5.0
     * @throws  \Exception
     */
    protected function tearDown(): void
    {
        $input = Factory::getApplication()->getInput();

        foreach (self::REQUEST_KEYS as $key) {
            $input->set($key, null);
        }

        parent::tearDown();
    }

    /**
     * Filters left at their user-state default of '' must not count as active,
     * otherwise the searchtools panel renders open on every request.
     *
     * @return  void
     *
     * @since   10.5.0
     */
    public function testEmptyFilterValuesAreNotActive(): void
    {
        $host = $this->makeHost(array_fill_keys(
            ['search', 'book', 'teacher', 'series', 'messagetype', 'year', 'topic', 'location', 'language'],
            ''
        ));

        $host->run();

        $this->assertSame([], $host->activeFilters);
    }

    /**
     * A filter carrying a real value is reported as active, keyed by field name
     * with its value — the shape ListModel::getActiveFilters() produces.
     *
     * @return  void
     *
     * @since   10.5.0
     */
    public function testSelectedFilterIsActiveAndKeyedByName(): void
    {
        $host = $this->makeHost(['search' => 'grace', 'teacher' => '5', 'book' => '', 'series' => '0']);

        $host->run();

        $this->assertSame(['search' => 'grace', 'teacher' => '5'], $host->activeFilters);
    }

    /**
     * A landing page call (?filter_teacher=3) sets the form value and marks the
     * filter active so the panel opens showing what was pre-selected.
     *
     * @return  void
     *
     * @since   10.5.0
     * @throws  \Exception
     */
    public function testLandingPageValueIsAppliedAndActive(): void
    {
        Factory::getApplication()->getInput()->set('filter_teacher', 3);

        $host = $this->makeHost(['teacher' => '', 'book' => '']);
        $host->run();

        $this->assertSame(3, $host->filterForm->getValue('teacher', 'filter'));
        $this->assertSame(['teacher' => 3], $host->activeFilters);
    }

    /**
     * Filters absent from the request keep whatever the user state loaded — an
     * unset request key must not blank the form value.
     *
     * @return  void
     *
     * @since   10.5.0
     */
    public function testAbsentRequestKeyDoesNotClearExistingValue(): void
    {
        $host = $this->makeHost(['teacher' => '5']);

        $host->run();

        $this->assertSame('5', $host->filterForm->getValue('teacher', 'filter'));
    }

    /**
     * A filter hidden by template params is dropped from both the form and the
     * active list, so a hidden filter cannot force the panel open.
     *
     * @return  void
     *
     * @since   10.5.0
     */
    public function testHiddenFilterIsRemovedFromFormAndActiveFilters(): void
    {
        $host = $this->makeHost(['teacher' => '5'], ['show_teacher_search' => 0]);

        $host->run();

        $this->assertFalse($host->filterForm->getField('teacher', 'filter'));
        $this->assertSame([], $host->activeFilters);
    }

    /**
     * Build an object using the trait, standing in for the site list views.
     *
     * @param   array  $filterValues  Values bound into the form's filter group.
     * @param   array  $params        Template params driving the show_*_search toggles.
     *
     * @return  object
     *
     * @since   10.5.0
     */
    private function makeHost(array $filterValues, array $params = []): object
    {
        $form = new Form('filter_cwmsermons', ['control' => '']);
        $form->load(self::FORM_XML);
        $form->bind(['filter' => $filterValues]);

        return new class ($form, new Registry($params)) {
            use UpdateFiltersTrait;

            /**
             * The filter form the trait updates.
             *
             * @var    Form|null
             * @since  10.5.0
             */
            public ?Form $filterForm = null;

            /**
             * The filters the trait reports as active.
             *
             * @var    array|null
             * @since  10.5.0
             */
            public ?array $activeFilters = null;

            /**
             * The params that decide which filters show.
             *
             * @var    Registry|null
             * @since  10.5.0
             */
            protected ?Registry $params = null;

            /**
             * Hold the form and params the trait works on.
             *
             * @param   Form      $filterForm  The filter form
             * @param   Registry  $params      The params
             *
             * @since   10.5.0
             */
            public function __construct(Form $filterForm, Registry $params)
            {
                $this->filterForm = $filterForm;
                $this->params     = $params;
            }

            /**
             * Run the trait's updateFilters().
             *
             * @return  void
             *
             * @since   10.5.0
             */
            public function run(): void
            {
                $this->updateFilters();
            }
        };
    }
}
