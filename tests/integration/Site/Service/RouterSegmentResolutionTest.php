<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Service;

use CWM\Component\Proclaim\Site\Service\Router;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * How the site router turns a URL segment into a record id when "Remove IDs
 * from URLs" is on.
 *
 * Which record keeps an alias several rows once shared is decided by
 * Cwmalias::ensureUniqueAliases() and asserted in CwmaliasUniqueTest: the
 * unique index on alias means no test can seed a shared one here.
 *
 * @since  10.7.3
 */
class RouterSegmentResolutionTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.3
     */
    private ?DatabaseInterface $db = null;

    /**
     * The router under test, which counts its redirect flags.
     *
     * @var    TaintRecordingRouter
     * @since  10.7.3
     */
    private TaintRecordingRouter $router;

    /**
     * Skip without a test database. Otherwise open a transaction, and build the
     * router without its constructor, with the database and "Remove IDs" set.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);

        // The constructor wants a site application and menu; resolving a
        // segment needs only the database and the "Remove IDs" flag.
        $this->router = (new \ReflectionClass(TaintRecordingRouter::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Router::class, 'db'))->setValue($this->router, $this->db);
        (new \ReflectionProperty(Router::class, 'noIDs'))->setValue($this->router, true);
    }

    /**
     * Roll back the seeded rows.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away.
            }
        }

        parent::tearDown();
    }

    /**
     * An alias that starts with another record's id resolves to its own record.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('An alias that starts with another record\'s id resolves to its own record')]
    public function testAliasStartingWithAnotherRecordsIdResolvesByAlias(): void
    {
        $other = $this->seedStudy($this->uniqueAlias('router-other'), 1);
        $alias = $other . '-' . $this->uniqueAlias('router-rescue');
        $own   = $this->seedStudy($alias, 1);

        $this->assertSame($own, $this->router->getCWMSermonId($alias, []));
        $this->assertSame(0, $this->router->taints, 'An exact alias match is the canonical URL');
    }

    /**
     * An id-alias segment resolves to that record and is flagged for a redirect.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('An <id>-<alias> segment resolves to that record and is flagged for a redirect')]
    public function testIdAliasSegmentResolvesAndIsFlagged(): void
    {
        $alias = $this->uniqueAlias('router-idalias');
        $id    = $this->seedStudy($alias, 1);

        $this->assertSame($id, $this->router->getCWMSermonId($id . '-' . $alias, []));
        $this->assertSame(1, $this->router->taints);
    }

    /**
     * A bare id segment resolves to that record and is flagged for a redirect.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('A bare id segment resolves to that record and is flagged for a redirect')]
    public function testBareIdSegmentResolvesAndIsFlagged(): void
    {
        $id = $this->seedStudy($this->uniqueAlias('router-bare'), 1);

        $this->assertSame($id, $this->router->getCWMSermonId((string) $id, []));
        $this->assertSame(1, $this->router->taints);
    }

    /**
     * An id-alias segment for a trashed record is not found.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('An <id>-<alias> segment for a trashed record is not found')]
    public function testIdAliasSegmentForAHiddenRecordIsNotFound(): void
    {
        $alias = $this->uniqueAlias('router-gone');
        $id    = $this->seedStudy($alias, -2);

        $this->assertSame(-1, $this->router->getCWMSermonId($id . '-' . $alias, []));
        $this->assertSame(0, $this->router->taints);
    }

    /**
     * A segment that starts with an id but carries a different alias is not found.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('A segment that starts with an id but carries a different alias is not found')]
    public function testIdWithAnotherAliasIsNotFound(): void
    {
        $id = $this->seedStudy($this->uniqueAlias('router-real'), 1);

        $this->assertSame(-1, $this->router->getCWMSermonId($id . '-' . $this->uniqueAlias('router-wrong'), []));
        $this->assertSame(0, $this->router->taints);
    }

    /**
     * An unknown alias is not found.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('An unknown alias is not found')]
    public function testUnknownAliasIsNotFound(): void
    {
        $this->assertSame(-1, $this->router->getCWMSermonId($this->uniqueAlias('router-missing'), []));
    }

    /**
     * A series resolves by alias, and by id-alias with a redirect flag.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('A series resolves by alias, and by <id>-<alias> with a redirect flag')]
    public function testSeriesResolvesByAliasAndIdAlias(): void
    {
        $alias = $this->uniqueAlias('router-series');
        $id    = $this->seedRow('#__bsms_series', ['series_text' => 'zz router series', 'alias' => $alias, 'published' => 1]);

        $this->assertSame($id, $this->router->getCWMSeriesDisplayId($alias, []));
        $this->assertSame(0, $this->router->taints);

        $this->assertSame($id, $this->router->getCWMSeriesDisplayId($id . '-' . $alias, []));
        $this->assertSame(1, $this->router->taints);
    }

    /**
     * A teacher resolves by alias, and by id-alias with a redirect flag.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    #[TestDox('A teacher resolves by alias, and by <id>-<alias> with a redirect flag')]
    public function testTeacherResolvesByAliasAndIdAlias(): void
    {
        $alias = $this->uniqueAlias('router-teacher');
        $id    = $this->seedRow('#__bsms_teachers', [
            'teachername' => 'zz router teacher',
            'alias'       => $alias,
            'address'     => '',
            'published'   => 1,
        ]);

        $this->assertSame($id, $this->router->getCWMTeacherId($alias, []));
        $this->assertSame(0, $this->router->taints);

        $this->assertSame($id, $this->router->getCWMTeacherId($id . '-' . $alias, []));
        $this->assertSame(1, $this->router->taints);
    }

    /**
     * Insert a message.
     *
     * @param   string  $alias      The message alias
     * @param   int     $published  The message state
     *
     * @return  int  The new message id
     *
     * @since   10.7.3
     */
    private function seedStudy(string $alias, int $published): int
    {
        return $this->seedRow('#__bsms_studies', [
            'studytitle' => 'zz router study',
            'alias'      => $alias,
            'studydate'  => '2026-01-15 10:00:00',
            'published'  => $published,
            'ordering'   => 0,
            'params'     => '{}',
        ]);
    }

    /**
     * Insert a row, public and for all languages unless the values say otherwise.
     *
     * @param   string                $table   The table
     * @param   array<string, mixed>  $values  Column values; access and language default to public and all
     *
     * @return  int  The new row id
     *
     * @since   10.7.3
     */
    private function seedRow(string $table, array $values): int
    {
        $row = (object) ($values + ['access' => 1, 'language' => '*']);

        $this->db->insertObject($table, $row);

        return (int) $this->db->insertid();
    }
}

/**
 * Counts redirect flags instead of setting them on the site router, which a
 * test run does not have.
 *
 * @since  10.7.3
 */
final class TaintRecordingRouter extends Router
{
    /**
     * How many times the router flagged a URL for a redirect.
     *
     * @var    int
     * @since  10.7.3
     */
    public int $taints = 0;

    /**
     * Count a redirect flag instead of setting it on the site router.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    protected function markTainted(): void
    {
        $this->taints++;
    }
}
