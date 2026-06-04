<?php

/**
 * Integration tests for CwmsermonsModel::setModuleState() -> query building.
 *
 * Exercises the real model the way mod_proclaim does (build it through the
 * component MVCFactory, call setModuleState, then inspect the list query and
 * results) to guard the empty-string module-param bug end-to-end: unset module
 * dropdowns arrive as [""] and must NOT add filter clauses that hide every
 * sermon.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmsermonsModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Integration test for the module-state path of CwmsermonsModel.
 *
 * @since  10.3.3
 */
class CwmsermonsModuleStateTest extends IntegrationTestCase
{
    /**
     * Build a real CwmsermonsModel through a component MVCFactory.
     *
     * bootComponent() falls back to a LegacyComponent/LegacyFactory in the test
     * harness (the namespaced provider is not registered), so we wire an
     * MVCFactory for the component namespace directly — the same class the
     * provider registers — and inject the live container services.
     *
     * Skips when no live database/container is available, since the model needs
     * a real driver to build and run its list query.
     *
     * @return  CwmsermonsModel
     */
    private function createModel(): CwmsermonsModel
    {
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Live database/container not available for model integration test.');
        }

        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwmsermonsModel $model */
        $model = $factory->createModel('Cwmsermons', 'Site', ['ignore_request' => true]);

        $this->assertInstanceOf(CwmsermonsModel::class, $model, 'MVCFactory should build the site sermons model');

        return $model;
    }

    /**
     * Render the model's list query as SQL via the protected getListQuery().
     *
     * @param   CwmsermonsModel  $model  Configured model
     *
     * @return  string  The query cast to string
     */
    private function listQuerySql(CwmsermonsModel $model): string
    {
        $ref   = new \ReflectionMethod($model, 'getListQuery');
        $query = $this->silenced(static fn () => $ref->invoke($model));

        $this->assertInstanceOf(QueryInterface::class, $query, 'getListQuery should return a query object');

        return (string) $query;
    }

    /**
     * Run getItems() while buffering stray core output.
     *
     * @param   CwmsermonsModel  $model  Configured model
     *
     * @return  array
     */
    private function items(CwmsermonsModel $model): array
    {
        return $this->silenced(static fn () => $model->getItems());
    }

    /**
     * Execute a callback, suppressing only the known Joomla core Language
     * warning.
     *
     * Joomla core emits "Trying to access array offset on null" from
     * Language.php under the CLI/test harness (language metadata is not fully
     * loaded). It is unrelated to the code under test; this handler swallows
     * that one warning and lets every other error propagate to PHPUnit.
     *
     * @param   callable  $fn  Callback to run
     *
     * @return  mixed  The callback's return value
     */
    private function silenced(callable $fn): mixed
    {
        set_error_handler(
            static fn (int $errno, string $errstr, string $errfile): bool =>
                str_contains($errfile, 'joomla/language/src/Language.php')
        );

        try {
            return $fn();
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Build a module-param Registry: a default moduleitems limit plus overrides.
     *
     * @param   array  $overrides  Module param key => value pairs
     *
     * @return  Registry
     */
    private function moduleParams(array $overrides = []): Registry
    {
        $params = new Registry();
        $params->set('moduleitems', 5);

        foreach ($overrides as $key => $value) {
            $params->set($key, $value);
        }

        return $params;
    }

    /**
     * Every filter dropdown left unset — the real-world [""] shape that
     * triggered the bug.
     *
     * @return  Registry
     */
    private function unsetFilterParams(): Registry
    {
        return $this->moduleParams([
            'teacher_id'  => [''],
            'series_id'   => [''],
            'booknumber'  => [''],
            'topic_id'    => [''],
            'locations'   => [''],
            'messagetype' => [''],
        ]);
    }

    #[TestDox('setModuleState maps module params to m-prefixed state keys')]
    public function testSetModuleStateMapsParams(): void
    {
        $model = $this->createModel();

        $model->setModuleState($this->moduleParams([
            'teacher_id' => ['3'],
            'series_id'  => ['7'],
        ]));

        /** @var Registry $state */
        $state = $model->getState('params');

        $this->assertSame(['3'], $state->get('mteacher_id'), 'teacher_id should map to mteacher_id');
        $this->assertSame(['7'], $state->get('mseries_id'), 'series_id should map to mseries_id');
        $this->assertSame(5, $model->getState('list.limit'), 'moduleitems should set list.limit');
    }

    #[TestDox('Empty-string teacher param adds no EXISTS filter subquery (regression)')]
    public function testEmptyTeacherParamAddsNoExists(): void
    {
        $model = $this->createModel();
        $model->setModuleState($this->unsetFilterParams());

        $sql = $this->listQuerySql($model);

        // Base query intact, but no teacher EXISTS filter. The base query LEFT
        // JOINs #__bsms_study_teachers (alias stj) for the primary teacher, so
        // the discriminator is the filter's EXISTS subquery (alias stf), not the
        // table name itself.
        $this->assertStringContainsString('#__bsms_studies', $sql, 'Base FROM clause should be present');
        $this->assertStringNotContainsString('EXISTS', $sql, 'Empty teacher param must not add an EXISTS subquery');
    }

    #[TestDox('A real teacher param does add an EXISTS filter subquery')]
    public function testValidTeacherParamAddsExists(): void
    {
        $model = $this->createModel();
        $model->setModuleState($this->moduleParams(['teacher_id' => ['5']]));

        $sql = $this->listQuerySql($model);

        $this->assertStringContainsString('EXISTS', $sql, 'Valid teacher param should add an EXISTS subquery');
    }

    #[TestDox('Empty-string filter params return the same items as no filters (regression)')]
    public function testEmptyParamsDoNotFilterEverythingOut(): void
    {
        // Baseline: no filter params at all.
        $baseline = $this->createModel();
        $baseline->setModuleState($this->moduleParams());
        $baselineCount = \count($this->items($baseline));

        // Same request, but every dropdown unset as [""] — the bug condition.
        $empty = $this->createModel();
        $empty->setModuleState($this->unsetFilterParams());
        $emptyCount = \count($this->items($empty));

        $this->assertSame(
            $baselineCount,
            $emptyCount,
            'Empty-string params must return the same items as an unfiltered query; '
            . 'a smaller count means [""] was treated as a real filter (the bug).'
        );
    }

    #[TestDox('getItems returns an array for default module params without throwing')]
    public function testGetItemsReturnsArray(): void
    {
        $model = $this->createModel();
        $model->setModuleState($this->unsetFilterParams());

        $this->assertIsArray($this->items($model), 'getItems should return an array for default module params');
    }
}
