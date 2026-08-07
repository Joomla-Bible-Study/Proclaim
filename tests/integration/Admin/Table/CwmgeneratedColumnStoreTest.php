<?php

/**
 * Integration tests for storing rows on tables that carry a generated column.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use CWM\Component\Proclaim\Administrator\Table\CwmplaylistTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Two tables carry a STORED generated column that exists only to back a unique
 * index: #__bsms_studies.studynumber_uk (uq_series_studynumber) and
 * #__bsms_playlists.remote_playlist_uk (uq_server_remote_playlist).
 *
 * MySQL rejects any statement that writes a generated column. Joomla's Table
 * seeds a property for every schema column and load() fills them all in, so
 * re-storing a loaded row carries the generated value into the SET clause and
 * the save fails -- but only for rows where the column is non-null, which is why
 * it survives a casual smoke test. These tests pin the exclusion.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmmessageTable::class)]
#[CoversClass(CwmplaylistTable::class)]
class CwmgeneratedColumnStoreTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * Rows this test created, as table => ids, for the explicit cleanup below.
     *
     * @var  array<string, int[]>
     */
    private array $created = [];

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }

            try {
                $this->purgeCreatedRows();
            } catch (\Throwable) {
                // Best effort; the assertions have already run.
            }
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    #[TestDox('A message that has both a series and an episode number can still be re-stored')]
    public function testMessageWithGeneratedUniquenessValueStores(): void
    {
        $this->requireColumn('#__bsms_studies', 'studynumber_uk');

        $id = $this->seedNumberedStudy();

        $table = new CwmmessageTable($this->db);
        $this->assertTrue($table->load($id), 'Failed to load the seeded study');

        // The bug only bites when the generated column is non-null, which needs
        // both a real series and a non-empty episode number.
        $this->assertNotNull($table->studynumber_uk ?? null, 'Fixture must produce a non-null generated value');

        $this->assertTrue(
            $table->store(),
            'Storing a loaded message must not attempt to write studynumber_uk: ' . $table->getError()
        );
    }

    #[TestDox('A playlist that has a remote ID can still be re-stored')]
    public function testPlaylistWithGeneratedUniquenessValueStores(): void
    {
        $this->requireColumn('#__bsms_playlists', 'remote_playlist_uk');

        $row = (object) [
            'title'              => 'Generated column fixture',
            'alias'              => 'genfix-' . uniqid('', true),
            'remote_playlist_id' => 'PLgenerated' . uniqid('', true),
            'server_id'          => 0,
            'published'          => 1,
            'access'             => 1,
            'language'           => '*',
            'params'             => '{}',
        ];
        $this->db->insertObject('#__bsms_playlists', $row);
        $id                                   = (int) $this->db->insertid();
        $this->created['#__bsms_playlists'][] = $id;

        $table = new CwmplaylistTable($this->db);
        $this->assertTrue($table->load($id), 'Failed to load the seeded playlist');
        $this->assertNotNull($table->remote_playlist_uk ?? null, 'Fixture must produce a non-null generated value');

        $this->assertTrue(
            $table->store(),
            'Storing a loaded playlist must not attempt to write remote_playlist_uk: ' . $table->getError()
        );
    }

    /**
     * Delete everything this test seeded.
     *
     * The surrounding transaction does not cover it: Table::store() creates the
     * row's #__assets entry, and the nested-set bookkeeping there runs LOCK
     * TABLES, which MySQL treats as an implicit COMMIT. Whatever was written
     * before that point is already durable when the rollback runs.
     *
     * @return  void
     */
    private function purgeCreatedRows(): void
    {
        foreach ($this->created['#__bsms_playlists'] ?? [] as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.playlist.' . $id)
            )->execute();
        }

        foreach ($this->created['#__bsms_studies'] ?? [] as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.message.' . $id)
            )->execute();
        }

        foreach ($this->created as $table => $ids) {
            if ($ids === []) {
                continue;
            }

            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName($table)
                . ' WHERE ' . $this->db->quoteName('id')
                . ' IN (' . implode(',', array_map('intval', $ids)) . ')'
            )->execute();
        }

        $this->created = [];
    }

    /**
     * Skip when the database predates the migration that adds the column, so the
     * suite still runs against an un-upgraded developer database.
     *
     * @param   string  $table   Table name.
     * @param   string  $column  Column name.
     *
     * @return  void
     */
    private function requireColumn(string $table, string $column): void
    {
        $found = $this->db->setQuery(
            'SHOW COLUMNS FROM ' . $this->db->quoteName($table) . ' LIKE ' . $this->db->quote($column)
        )->loadResult();

        if (!$found) {
            $this->markTestSkipped($table . '.' . $column . ' not present; run the 10.5.6 migration first');
        }
    }

    /**
     * Seed a published study inside a real series, carrying an episode number.
     *
     * @return  int  Study ID.
     */
    private function seedNumberedStudy(): int
    {
        $series = (object) [
            'series_text' => 'Generated column fixture series',
            'alias'       => 'genfix-series-' . uniqid('', true),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
        ];
        $this->db->insertObject('#__bsms_series', $series);
        $seriesId                          = (int) $this->db->insertid();
        $this->created['#__bsms_series'][] = $seriesId;

        $study = (object) [
            'studytitle'  => 'Generated column fixture study',
            'alias'       => 'genfix-study-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'studynumber' => '77',
            'series_id'   => $seriesId,
            'messagetype' => '1',
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => '{}',
        ];
        $this->db->insertObject('#__bsms_studies', $study);

        $id                                 = (int) $this->db->insertid();
        $this->created['#__bsms_studies'][] = $id;

        return $id;
    }
}
