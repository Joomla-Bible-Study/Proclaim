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
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The title and alias a Save as Copy gives a message or a series: an
 * unchanged title takes the next (2), (3), ... whose alias is free, and an
 * alias the user typed is kept.
 *
 * @since  10.7.4
 */
class CwmaliasCopyTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.4
     */
    private ?DatabaseInterface $db = null;

    /**
     * Skip without a test database. Otherwise open a transaction.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
    }

    /**
     * Roll back the transaction setUp() opened.
     *
     * @return  void
     *
     * @since   10.7.4
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
     * The messages and series tables, each with its title column.
     *
     * @return  array<string, array{string, string}>  Each case
     *
     * @since   10.7.4
     */
    public static function tables(): array
    {
        return [
            'message' => ['#__bsms_studies', 'studytitle'],
            'series'  => ['#__bsms_series', 'series_text'],
        ];
    }

    /**
     * A copy keeping the original's title and alias gets the title with (2), and an empty
     * alias for the save to build.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('A copy with the title unchanged gets (2), and its alias is cleared to be built from that')]
    public function testFirstCopyIsNumbered(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle();
        $alias = $this->seed($table, $titleField, $title);

        $this->assertSame([$title . ' (2)', ''], Cwmalias::forCopy($this->db, $table, $title, $alias, $title, $alias));
    }

    /**
     * When a (2) copy of the original exists, the next copy gets (3) and an empty alias.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('Copying the same original again gives (3), not a second (2)')]
    public function testNextCopyStepsPastTheEarlierCopy(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle();
        $alias = $this->seed($table, $titleField, $title);
        $this->seed($table, $titleField, $title . ' (2)');

        $this->assertSame([$title . ' (3)', ''], Cwmalias::forCopy($this->db, $table, $title, $alias, $title, $alias));
    }

    /**
     * When a differently titled row holds the alias (2) would build, the copy gets (3)
     * instead.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('A number whose alias another row holds is skipped, whatever that row is titled')]
    public function testNumberWhoseAliasIsHeldIsSkipped(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle();
        $alias = $this->seed($table, $titleField, $title);
        $this->seed($table, $titleField, 'Zz Someone Else', ApplicationHelper::stringURLSafe($title . ' (2)'));

        $this->assertSame([$title . ' (3)', ''], Cwmalias::forCopy($this->db, $table, $title, $alias, $title, $alias));
    }

    /**
     * A title ending in Psalm 23 keeps its number: the copy's title gains (2), and the alias
     * built from it is the original's with -2 added.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('A title ending in a number keeps it; the copy is numbered after it')]
    public function testTrailingNumberInTheTitleIsKept(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle() . ' Psalm 23';
        $alias = $this->seed($table, $titleField, $title);

        [$copyTitle] = Cwmalias::forCopy($this->db, $table, $title, $alias, $title, $alias);

        $this->assertSame($title . ' (2)', $copyTitle);
        $this->assertSame($alias . '-2', ApplicationHelper::stringURLSafe($copyTitle));
    }

    /**
     * A copy whose alias was cleared is numbered like an untouched one: its title gets (2),
     * and its alias stays empty.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('An alias the user cleared is treated as one they left alone')]
    public function testClearedAliasIsNumberedLikeAnUntouchedOne(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle();
        $alias = $this->seed($table, $titleField, $title);

        $this->assertSame([$title . ' (2)', ''], Cwmalias::forCopy($this->db, $table, $title, '', $title, $alias));
    }

    /**
     * A copy with a typed alias keeps it, though another row already holds it, and its
     * unchanged title isn't numbered.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('An alias the user typed is kept as typed, even when taken, and the title is left alone')]
    public function testTypedAliasIsKept(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle();
        $alias = $this->seed($table, $titleField, $title);
        $typed = $this->seed($table, $titleField, 'Zz Typed Alias Holder');

        $this->assertSame([$title, $typed], Cwmalias::forCopy($this->db, $table, $title, $typed, $title, $alias));
    }

    /**
     * A copy given a new title keeps it, and the alias copied from the original is cleared
     * for the save to build from that title.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[DataProvider('tables')]
    #[TestDox('A new title is kept, and the copied alias is cleared to be built from it')]
    public function testNewTitleClearsTheCopiedAlias(string $table, string $titleField): void
    {
        $title = $this->uniqueTitle();
        $alias = $this->seed($table, $titleField, $title);

        $this->assertSame(
            ['Zz Copy, Part Two', ''],
            Cwmalias::forCopy($this->db, $table, 'Zz Copy, Part Two', $alias, $title, $alias)
        );
    }

    /**
     * A title no other test run shares, so the aliases built from it are free.
     *
     * @return  string  A Zz Copy title ending in a unique id
     *
     * @since   10.7.4
     */
    private function uniqueTitle(): string
    {
        return 'Zz Copy ' . str_replace('.', ' ', uniqid('', true));
    }

    /**
     * Insert a published row with the given title and alias.
     *
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     * @param   string  $title       The row's title
     * @param   string  $alias       The row's alias; built from the title when empty
     *
     * @return  string  The row's alias
     *
     * @since   10.7.4
     */
    private function seed(string $table, string $titleField, string $title, string $alias = ''): string
    {
        $alias = $alias !== '' ? $alias : ApplicationHelper::stringURLSafe($title);
        $row   = (object) [$titleField => $title, 'alias' => $alias, 'published' => 1, 'access' => 1, 'language' => '*'];

        $this->db->insertObject($table, $row);

        return $alias;
    }
}
