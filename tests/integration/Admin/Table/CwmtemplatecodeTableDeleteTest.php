<?php

/**
 * Deleting a template code record must not remove a layout the package ships.
 *
 * A record named after a shipped layout overwrote that file when it was saved.
 * Deleting the record then deleted the file — and the shipped `default.php` for
 * each view calls its sublayouts unconditionally (`cwmsermon/default.php:70` is
 * `loadTemplate('footer')`), with Joomla throwing a 500 when one is missing. So
 * removing the record took the message page down for everyone.
 *
 * ⚠️ **What this test can and cannot see.** `JPATH_ROOT` in this harness is the
 * repository root, which has no `components/` tree, so the composed layout path
 * never exists and a "did the file survive" assertion would pass whatever the
 * code did. Building that tree to make it real would leave an untracked
 * `components/` directory at the repo root on any crash — the stray-directory
 * failure that has broken every dev site before. So the branch is asserted by
 * its observable effect instead: the shipped path logs and returns before
 * reaching the delete, the ordinary path does not.
 *
 * ⚠️ Observed through `Log`, not through the message queue. The console
 * application's queue lives in a **session that is persisted to disk**, so a
 * message enqueued by one test leaked into the next *process* and the suite
 * alternated pass/fail on every other run. `Log::setInstance()` is
 * process-local and cannot leak.
 *
 * The filenames themselves are covered exhaustively by ShippedLayoutsTest,
 * including both directions of drift against the real shipped tree.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(CwmtemplatecodeTable::class)]
class CwmtemplatecodeTableDeleteTest extends IntegrationTestCase
{
    /**
     * Entries the subject logged during the call.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private static array $logged = [];

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::$logged = [];

        Log::setInstance(new class () extends Log {
            /**
             * Log's own constructor is protected, and it wires up the
             * configured loggers — neither of which is wanted here. Widened and
             * deliberately empty; there is no parent state to initialise.
             *
             * @since __DEPLOY_VERSION__
             */
            public function __construct()
            {
            }

            /**
             * @param   LogEntry  $entry  The entry to record
             *
             * @return  bool
             *
             * @since __DEPLOY_VERSION__
             */
            #[\Override]
            protected function addLogEntry(LogEntry $entry): bool
            {
                CwmtemplatecodeTableDeleteTest::record($entry->message);

                return true;
            }
        });
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        // Joomla's own reset: setInstance() is untyped and branches on null.
        Log::setInstance(null);

        parent::tearDown();
    }

    /**
     * Called by the capturing logger installed in setUp().
     *
     * @param   string  $message  The logged message
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    public static function record(string $message): void
    {
        self::$logged[] = $message;
    }

    /**
     * A table wired to a database that cannot touch a real row.
     *
     * ⚠️ Stubbed on purpose. The harness has a live j5_dev connection and
     * parent::delete() issues a real DELETE — against whatever id is set here.
     *
     * @param   int     $type      Template type
     * @param   string  $filename  Record filename
     *
     * @return  CwmtemplatecodeTable
     *
     * @since __DEPLOY_VERSION__
     */
    private function tableFor(int $type, string $filename): CwmtemplatecodeTable
    {
        $query = $this->createStub(DatabaseQuery::class);
        $query->method('delete')->willReturnSelf();
        $query->method('from')->willReturnSelf();
        $query->method('where')->willReturnSelf();
        $query->method('bind')->willReturnSelf();

        $db = $this->createStub(DatabaseDriver::class);
        $db->method('createQuery')->willReturn($query);
        $db->method('getQuery')->willReturn($query);
        $db->method('quoteName')->willReturnCallback(
            static fn ($name) => \is_array($name)
                ? array_map(static fn ($n) => '`' . $n . '`', $name)
                : '`' . $name . '`'
        );
        $db->method('setQuery')->willReturnSelf();
        $db->method('execute')->willReturn(true);

        /** @var CwmtemplatecodeTable $table */
        $table = $this->createTableInstance(CwmtemplatecodeTable::class);

        $ref  = new \ReflectionClass(CwmtemplatecodeTable::class);
        $ref->getProperty('_tbl')->setValue($table, '#__bsms_templatecode');

        $table->setDatabase($db);
        $table->id       = 4242;
        $table->type     = (string) $type;
        $table->filename = $filename;

        return $table;
    }

    /**
     * How many entries the subject logged.
     *
     * ⚠️ Counted, not matched on text. `LogEntry` rewrites occurrences of
     * JPATH_ROOT in a message to `[ROOT]`, and JPATH_ROOT is `'.'` in this
     * harness — so every full stop in the message is substituted and the text
     * cannot be asserted on. The count is what distinguishes the branches.
     *
     * @return  int
     *
     * @since __DEPLOY_VERSION__
     */
    private function loggedCount(): int
    {
        return \count(self::$logged);
    }

    /**
     * The regression: this record's removal used to take the file with it.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Deleting a record named after a shipped layout keeps the file and says so')]
    public function testDeleteOfAShippedNameKeepsTheFile(): void
    {
        // Not vacuous: the guard only means anything if this name is shipped.
        $this->assertTrue(CwmtemplatecodeTable::isShippedLayout(2, 'footer'));

        $this->assertTrue($this->tableFor(2, 'footer')->delete(), 'The row should still be removed.');

        // ⚠️ Not an exact count. The branch logs once, but other machinery on
        // the delete path logs too; what distinguishes the branches is that the
        // ordinary path below logs nothing at all.
        $this->assertGreaterThan(
            0,
            $this->loggedCount(),
            'delete() did not report keeping the shipped layout, so the guard was not taken.'
        );
    }

    /**
     * ⚠️ The other half. A guard that matched everything would pass the test
     * above while silently leaving a file behind for every deleted record.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Deleting an ordinary record does not take the shipped-layout branch')]
    public function testDeleteOfAnOrdinaryNameIsUnaffected(): void
    {
        $this->assertFalse(CwmtemplatecodeTable::isShippedLayout(2, 'zzordinary'));

        $this->assertTrue($this->tableFor(2, 'zzordinary')->delete());

        $this->assertSame(
            0,
            $this->loggedCount(),
            'An ordinary record took the shipped-layout branch; the guard is matching too much.'
        );
    }

    /**
     * The refusal is per type, not global — `header` ships for type 2 only.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A shipped name belonging to another type is not protected here')]
    public function testShippedNameOfAnotherTypeIsNotProtected(): void
    {
        $this->assertTrue(CwmtemplatecodeTable::isShippedLayout(2, 'header'));
        $this->assertFalse(CwmtemplatecodeTable::isShippedLayout(3, 'header'));

        $this->assertTrue($this->tableFor(3, 'header')->delete());

        $this->assertSame(
            0,
            $this->loggedCount(),
            'Type 3 ships no default_header.php, so its record must delete normally.'
        );
    }
}
