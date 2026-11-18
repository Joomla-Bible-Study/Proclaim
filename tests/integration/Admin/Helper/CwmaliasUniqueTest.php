<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Cwmalias::ensureUniqueAliases() against a copy of the messages table.
 *
 * The copy is a TEMPORARY table built with `AS SELECT ... LIMIT 0`, which
 * takes the columns but none of the indexes, so duplicates can be seeded on a
 * database whose real table already has the unique index. The helper's ALTER
 * TABLE commits implicitly, so nothing here relies on a rollback: these tests
 * write only to the temporary table, and drop it afterwards.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmaliasUniqueTest extends IntegrationTestCase
{
    private const string TABLE = '#__zz_alias_unique_test';

    private ?DatabaseInterface $db = null;

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->setQuery('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->quoteName(self::TABLE))->execute();
        $this->db->setQuery(
            'CREATE TEMPORARY TABLE ' . $this->db->quoteName(self::TABLE)
            . ' AS SELECT * FROM ' . $this->db->quoteName('#__bsms_studies') . ' LIMIT 0'
        )->execute();
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->setQuery('DROP TEMPORARY TABLE IF EXISTS ' . $this->db->quoteName(self::TABLE))->execute();
            } catch (\Throwable) {
                // Connection may have gone away; the table dies with the session anyway.
            }
        }

        parent::tearDown();
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The visible row the router serves keeps a shared alias; the others get the next free suffix')]
    public function testVisibleRowKeepsTheSharedAlias(): void
    {
        $this->seed(10, 'shared', -2);
        $this->seed(11, 'shared', 1);
        $this->seed(12, 'shared', 1);

        $changed = $this->ensure();

        $this->assertSame('shared', $this->aliasOf(11), 'The lowest visible id keeps the alias');
        $this->assertSame('shared-2', $this->aliasOf(12));
        $this->assertSame('shared-3', $this->aliasOf(10), 'The trashed row goes last');
        $this->assertSame(
            [
                ['id' => 12, 'from' => 'shared', 'to' => 'shared-2'],
                ['id' => 10, 'from' => 'shared', 'to' => 'shared-3'],
            ],
            $changed
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An archived row is visible, so it keeps an alias it shares with an older trashed one')]
    public function testArchivedRowKeepsTheAlias(): void
    {
        $this->seed(20, 'kept', -2);
        $this->seed(21, 'kept', 2);

        $this->ensure();

        $this->assertSame('kept', $this->aliasOf(21));
        $this->assertSame('kept-2', $this->aliasOf(20));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A suffix another row already holds is skipped')]
    public function testTakenSuffixIsSkipped(): void
    {
        $this->seed(30, 'part', 1);
        $this->seed(31, 'part', 1);
        $this->seed(32, 'part-2', 1);

        $this->ensure();

        $this->assertSame('part', $this->aliasOf(30));
        $this->assertSame('part-3', $this->aliasOf(31));
        $this->assertSame('part-2', $this->aliasOf(32), 'A row that was not a duplicate is left alone');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Rows with no alias get one from their title, even a lone one')]
    public function testEmptyAliasesAreBuiltFromTheTitle(): void
    {
        $this->seed(40, '', 1, 'Zz Unique Alias Title');
        $this->seed(41, '', 1, 'Zz Unique Alias Title');
        $this->seed(42, '', 0, '');

        $this->ensure();

        $this->assertSame('zz-unique-alias-title', $this->aliasOf(40));
        $this->assertSame('zz-unique-alias-title-2', $this->aliasOf(41));
        $this->assertSame('item-42', $this->aliasOf(42), 'A title with nothing to slug still gets an alias');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The unique index is added, a second run changes nothing, and the index refuses a duplicate')]
    public function testIndexIsAddedAndHolds(): void
    {
        $this->seed(50, 'twice', 1);
        $this->seed(51, 'twice', 1);

        $this->assertNotSame([], $this->ensure());
        $this->assertSame('unique', $this->indexState());

        $this->assertSame([], $this->ensure(), 'Once the index exists there is nothing left to do');
        $this->assertSame('twice-2', $this->aliasOf(51));

        $this->expectException(\RuntimeException::class);
        $this->seed(52, 'twice', 1);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An index of the same name that is not unique is replaced by a unique one')]
    public function testNonUniqueIndexOfTheSameNameIsReplaced(): void
    {
        $this->db->setQuery(
            'ALTER TABLE ' . $this->db->quoteName(self::TABLE)
            . ' ADD KEY ' . $this->db->quoteName(Cwmalias::UNIQUE_ALIAS_INDEX) . ' (' . $this->db->quoteName('alias') . ')'
        )->execute();
        $this->assertSame('other', $this->indexState());

        $this->seed(60, 'drift', 1);
        $this->seed(61, 'drift', 1);

        $this->ensure();

        $this->assertSame('unique', $this->indexState());
        $this->assertSame('drift-2', $this->aliasOf(61));
    }

    /**
     * @return  list<array{id: int, from: string, to: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function ensure(): array
    {
        return Cwmalias::ensureUniqueAliases($this->db, self::TABLE, 'studytitle');
    }

    /**
     * @param   int     $id         Row id
     * @param   string  $alias      Alias
     * @param   int     $published  State
     * @param   string  $title      Title
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seed(int $id, string $alias, int $published, string $title = 'zz alias unique'): void
    {
        $row = (object) [
            'id'         => $id,
            'studytitle' => $title,
            'alias'      => $alias,
            'studydate'  => '2026-01-15 10:00:00',
            'published'  => $published,
            'access'     => 1,
            'language'   => '*',
            'ordering'   => 0,
            'params'     => '{}',
        ];

        $this->db->insertObject(self::TABLE, $row);
    }

    /**
     * @param   int  $id  Row id
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function aliasOf(int $id): string
    {
        return (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName(self::TABLE))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadResult();
    }

    /**
     * @return  string  'unique', 'other' or 'none'
     *
     * @since   __DEPLOY_VERSION__
     */
    private function indexState(): string
    {
        foreach ($this->db->getTableKeys(self::TABLE) as $key) {
            if ($key->Key_name === Cwmalias::UNIQUE_ALIAS_INDEX) {
                return (int) $key->Non_unique === 0 ? 'unique' : 'other';
            }
        }

        return 'none';
    }
}
