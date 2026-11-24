<?php

/**
 * cleanOrphanedAssets() removes #__assets rows whose content record is gone.
 *
 * Its DELETE matches the asset name with a bound LIKE pattern built inside a
 * foreach over the section prefixes -- the one genuinely new failure mode in
 * the quote()->bind() conversion, since bind() takes its value by reference and
 * the loop reassigns it. This exercises that path against a real orphan so a
 * mis-bound pattern deletes nothing and fails here.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since 10.7.1
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Cwmassets::cleanOrphanedAssets() removes the assets whose records are gone.
 *
 * @since  10.7.1
 */
class CwmassetsOrphanCleanupTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.1
     */
    private ?DatabaseInterface $db = null;

    /**
     * Skip without a test database. Otherwise open a transaction: the cleanup
     * runs a real DELETE, which also removes any other orphans the database
     * holds, and the rollback restores them.
     *
     * @return  void
     *
     * @since   10.7.1
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        // Everything below is rolled back -- cleanOrphanedAssets() runs a real
        // DELETE, and the dev DB may hold other orphans it would also remove.
        $this->db->transactionStart(true);
    }

    /**
     * Roll back the test's rows.
     *
     * @return  void
     *
     * @since   10.7.1
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * cleanOrphanedAssets() removes an asset whose location record no longer exists.
     *
     * @return  void
     *
     * @since   10.7.1
     */
    #[TestDox('cleanOrphanedAssets() removes an asset whose location record no longer exists')]
    public function testCleanOrphanedAssetsRemovesAnOrphanByBoundLikePattern(): void
    {
        Cwmassets::seedSections();
        $parentId = Cwmassets::parentId();
        $this->assertGreaterThan(0, $parentId, 'Expected a com_proclaim parent asset');

        // A location id that cannot exist in #__bsms_locations.
        $orphanId   = 990041;
        $orphanName = 'com_proclaim.location.' . $orphanId;

        $row            = new \stdClass();
        $row->parent_id = $parentId;
        $row->lft       = 0;
        $row->rgt       = 0;
        $row->level     = 2;
        $row->name      = $orphanName;
        $row->title     = $orphanName;
        $row->rules     = '{}';
        $this->db->insertObject('#__assets', $row, 'id');

        $this->assertSame(1, $this->countAssetsNamed($orphanName), 'Fixture orphan should exist before cleanup');

        $removed = Cwmassets::cleanOrphanedAssets($this->db);

        $this->assertGreaterThanOrEqual(1, $removed, 'cleanOrphanedAssets() should report removing at least the seeded orphan');
        $this->assertSame(0, $this->countAssetsNamed($orphanName), 'The seeded orphan must be gone after cleanup');
    }

    /**
     * Count the assets with a name.
     *
     * @param   string  $name  Exact asset name.
     *
     * @return  int  Number of #__assets rows with that name.
     *
     * @since   10.7.1
     */
    private function countAssetsNamed(string $name): int
    {
        $query = $this->db->createQuery()
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__assets'))
            ->where($this->db->quoteName('name') . ' = :name')
            ->bind(':name', $name, \Joomla\Database\ParameterType::STRING);

        return (int) $this->db->setQuery($query)->loadResult();
    }
}
