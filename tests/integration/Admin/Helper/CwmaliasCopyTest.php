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
 * @since  __DEPLOY_VERSION__
 */
class CwmaliasCopyTest extends IntegrationTestCase
{
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
        $this->db->transactionStart(true);
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
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away.
            }
        }

        parent::tearDown();
    }

    /**
     * @return  array<string, array{string, string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function tables(): array
    {
        return [
            'message' => ['#__bsms_studies', 'studytitle'],
            'series'  => ['#__bsms_series', 'series_text'],
        ];
    }

    /**
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function uniqueTitle(): string
    {
        return 'Zz Copy ' . str_replace('.', ' ', uniqid('', true));
    }

    /**
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     * @param   string  $title       The row's title
     * @param   string  $alias       The row's alias; built from the title when empty
     *
     * @return  string  The row's alias
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seed(string $table, string $titleField, string $title, string $alias = ''): string
    {
        $alias = $alias !== '' ? $alias : ApplicationHelper::stringURLSafe($title);
        $row   = (object) [$titleField => $title, 'alias' => $alias, 'published' => 1, 'access' => 1, 'language' => '*'];

        $this->db->insertObject($table, $row);

        return $alias;
    }
}
