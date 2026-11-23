<?php

/**
 * An item asset should exist only where its rules differ from the component's,
 * and removing one should not damage the tree.
 *
 * Two defects met here:
 *
 * - `Table::store()` runs its asset block even when the INSERT threw, so a
 *   failed save created `com_proclaim.<section>.0` for a record that never
 *   existed. Proclaim only stripped the asset when the save succeeded (#1723).
 * - the strip used a raw `DELETE` on a nested set, leaving `lft`/`rgt` numbers
 *   that only a full `rebuild()` reclaims (#1724).
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since 10.5.8
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Table\CwmteacherTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Asset;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The empty asset a failed save leaves is removed, and removing an asset leaves
 * no gap in the nested set.
 *
 * @since  10.5.8
 */
class CwmassetsStripTest extends IntegrationTestCase
{
    /**
     * The database. A test deletes the rows it adds instead of rolling them back,
     * because Table\Asset::store() takes a table lock, an implicit COMMIT in MySQL.
     *
     * @var    DatabaseInterface|null
     * @since  10.5.8
     */
    private ?DatabaseInterface $db = null;

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

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Delete any asset a test left behind.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    protected function tearDown(): void
    {
        // Belt and braces: nothing here should leave one behind.
        foreach (['com_proclaim.teacher.0', 'com_proclaim.stripdemo.1'] as $name) {
            $asset = new Asset($this->db);

            if ($asset->loadByName($name)) {
                $asset->delete((int) $asset->id);
            }
        }

        parent::tearDown();
    }

    /**
     * The highest right-hand bound in the tree.
     *
     * @return  int
     *
     * @since   10.5.8
     */
    private function maxRgt(): int
    {
        return (int) $this->db->setQuery(
            'SELECT MAX(' . $this->db->quoteName('rgt') . ') FROM ' . $this->db->quoteName('#__assets')
        )->loadResult();
    }

    /**
     * The asset a failed save leaves behind is removed, and the record detached from it.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[TestDox('the asset a failed save leaves behind is removed, and the record detached from it')]
    public function testOrphanAssetFromAFailedSaveIsRemoved(): void
    {
        // Reproduces the state a failed save leaves: Table::store() runs its
        // asset block even after the insert throws, so the asset exists while
        // the record does not and the key is still null.
        //
        // Built directly rather than by forcing a real insert failure. Whether
        // an insert fails at all depends on the server's sql_mode and on
        // whether the column has a default in that schema — both differ between
        // a developer machine and CI, and either turns a forced failure into a
        // test that silently measures nothing.
        $asset = new Asset($this->db);
        $asset->setLocation(Cwmassets::sectionId('teacher') ?: Cwmassets::parentId(), 'last-child');
        $asset->name  = 'com_proclaim.teacher.0';
        $asset->title = 'com_proclaim.teacher.0';
        $asset->rules = '{}';

        $this->assertTrue($asset->check() && $asset->store(), 'Could not create the orphan fixture.');

        $table           = new CwmteacherTable($this->db);
        $table->id       = null;
        $table->asset_id = (int) $asset->id;

        Cwmassets::stripEmptyAssetRow($table);

        $survivor = new Asset($this->db);

        $this->assertFalse(
            $survivor->loadByName('com_proclaim.teacher.0'),
            'The orphan survived. It belongs to no record, and now that item assets are parented to '
            . 'their section it would read as a real per-record permission.'
        );
        $this->assertSame(
            0,
            (int) $table->asset_id,
            'The table still points at the asset that was removed. There is no record row to update '
            . 'after a failed insert, so the in-memory value is all there is to correct.'
        );
    }

    /**
     * Removing an empty asset closes its gap in the nested set.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[TestDox('removing an empty asset closes its gap in the nested set')]
    public function testStrippingAnAssetLeavesNoGap(): void
    {
        $before = $this->maxRgt();

        $asset = new Asset($this->db);
        $asset->setLocation(Cwmassets::parentId(), 'last-child');
        $asset->name  = 'com_proclaim.stripdemo.1';
        $asset->title = 'com_proclaim.stripdemo.1';
        $asset->rules = '{}';

        $this->assertTrue($asset->check() && $asset->store(), 'Could not create the fixture asset.');
        $this->assertSame($before + 2, $this->maxRgt(), 'Creating a leaf should widen the tree by exactly 2.');

        $table           = new CwmteacherTable($this->db);
        $table->asset_id = (int) $asset->id;

        Cwmassets::stripEmptyAssetRow($table);

        $this->assertSame(
            $before,
            $this->maxRgt(),
            'The tree did not shrink back. A raw DELETE removes the row without closing its lft/rgt gap, '
            . 'and only a full rebuild() reclaims the numbers.'
        );
    }
}
