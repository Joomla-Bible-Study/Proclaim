<?php

/**
 * Integration tests: API list filters reach the SQL.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The five filters #1753 fixed, asserted on the query the model builds.
 *
 * ApiFilterContractTest is the cheap half: it proves each controller's
 * `filter.*` key is mentioned somewhere in its model. That catches "never
 * wired up" and nothing more -- a model could read the state and forget to
 * apply it, and the contract test would still pass.
 *
 * This is the other half. Each case builds the real list query twice, with the
 * filter set and without, and requires the predicate to appear only in the
 * first. ⚠️ The negative half is the point: asserting only that the column name
 * appears would pass on every one of these models, because all five columns are
 * already in the SELECT list or a JOIN condition.
 *
 * @since  10.5.8
 */
class ApiFilterQueryTest extends IntegrationTestCase
{
    /**
     * Skip without a test database.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }
    }

    /**
     * model, filter key, filter value, and a pattern the WHERE must gain.
     *
     * @return  array<string, array{0: string, 1: string, 2: mixed, 3: string}>  Each case: model, filter key, value,
     *                                                                           then a pattern the WHERE must gain
     *
     * @since   10.5.8
     */
    public static function filterProvider(): array
    {
        return [
            'comments by study'   => ['Cwmcomments', 'filter.study_id', 7, '/comment`?\.`?study_id`?\s*=\s*:/i'],
            'playlists by series' => ['Cwmplaylists', 'filter.series_id', 4, '/playlist`?\.`?series_id`?\s*=\s*:/i'],
            'servers by type'     => ['Cwmservers', 'filter.type', 'legacy', '/server`?\.`?type`?\s*=\s*:/i'],
            'servers by search'   => ['Cwmservers', 'filter.search', 'sermon', '/server`?\.`?server_name`?\s+LIKE\s+:/i'],
            'topics by language'  => ['Cwmtopics', 'filter.language', 'en-GB', '/topic`?\.`?language`?\s*=\s*:/i'],
        ];
    }

    /**
     * Build a model's list query as SQL, with the given state applied.
     *
     * @param   string  $name   Model name without the Model suffix
     * @param   array   $state  State keys to set before building
     *
     * @return  string  The list query's SQL, with bound values left as placeholders
     *
     * @since   10.5.8
     */
    private function listSql(string $name, array $state): string
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));

        if ($container->has(FormFactoryInterface::class)) {
            $factory->setFormFactory($container->get(FormFactoryInterface::class));
        }

        // ignore_request mirrors how ApiController builds these models: it sets
        // __state_set, so populateState() never runs and the state below is the
        // only state there is. Without it the admin session's filters leak in.
        $model = $factory->createModel($name, 'Administrator', ['ignore_request' => true]);

        foreach ($state as $key => $value) {
            $model->setState($key, $value);
        }

        $ref = new \ReflectionMethod($model, 'getListQuery');

        $query = $this->silenced(static fn () => $ref->invoke($model));
        $this->assertInstanceOf(QueryInterface::class, $query);

        return (string) $query;
    }

    /**
     * Each model applies its filter to the list query.
     *
     * @param   string  $model    Model name without the Model suffix
     * @param   string  $filter   The filter's state key
     * @param   mixed   $value    The value to filter by
     * @param   string  $pattern  A pattern the WHERE must gain
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[DataProvider('filterProvider')]
    #[TestDox('$model applies $filter to the query')]
    public function testTheFilterReachesTheQuery(string $model, string $filter, mixed $value, string $pattern): void
    {
        $withFilter = $this->listSql($model, [$filter => $value]);

        $this->assertMatchesRegularExpression(
            $pattern,
            $withFilter,
            \sprintf(
                '%sModel was given %s and built a query with no predicate for it. The API accepts that filter, '
                . 'so the caller would get the whole unfiltered set back with nothing to say so (#1753).',
                $model,
                $filter
            )
        );
    }

    /**
     * Each model leaves its filter's predicate out of the list query when the
     * filter is unset.
     *
     * @param   string  $model    Model name without the Model suffix
     * @param   string  $filter   The filter's state key
     * @param   mixed   $value    The value to filter by, unused here
     * @param   string  $pattern  A pattern the WHERE must gain
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[DataProvider('filterProvider')]
    #[TestDox('$model omits the $filter predicate when the filter is unset')]
    public function testThePredicateIsAbsentWithoutTheFilter(string $model, string $filter, mixed $value, string $pattern): void
    {
        $withoutFilter = $this->listSql($model, []);

        $this->assertDoesNotMatchRegularExpression(
            $pattern,
            $withoutFilter,
            \sprintf(
                '%sModel emitted the %s predicate with no filter set, so the test above proves nothing -- it '
                . 'would pass whether or not the filter is wired up.',
                $model,
                $filter
            )
        );
    }

    /**
     * Run something with Joomla's language warnings suppressed.
     *
     * ⚠️ List queries resolve language strings and the harness has no active
     * language, so Language.php emits a warning PHPUnit reports as risky
     * output. Same helper as CwmsermonsMultiTeacherTest.
     *
     * @param   callable  $fn  Work to run
     *
     * @return  mixed  Whatever $fn returns
     *
     * @since   10.5.8
     */
    private function silenced(callable $fn): mixed
    {
        set_error_handler(
            static fn (int $errno, string $errstr, string $errfile): bool
                => str_contains($errfile, 'joomla/language/src/Language.php')
        );

        try {
            return $fn();
        } finally {
            restore_error_handler();
        }
    }
}
