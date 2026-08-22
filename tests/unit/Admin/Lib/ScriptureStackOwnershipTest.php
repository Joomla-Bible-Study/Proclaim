<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Backup and restore must leave the shared scripture stack alone (#1867).
 *
 * The four scripture tables carry the `bsms_` prefix but belong to
 * lib_cwmscripture. `CwmdbHelper::getObjects()` is prefix-driven and cannot
 * tell the difference, so the backup swept them in: the export wrote them, and
 * the restore dropped and replaced them from the snapshot.
 *
 * `#__bsms_scripture_consumers` is the one that bites. It is derived state
 * describing what is installed on this site *now*, and every uninstall guard
 * consults it before dropping anything. Restoring an older copy makes the
 * guards under-report, and the next uninstall takes the downloaded bibles with
 * it.
 *
 * ⚠️ These are source-inspection tests, following the pattern already used in
 * CwmrestoreTest: the real paths need a live database, and the property worth
 * pinning is *which list each site consults*, which is checkable without one.
 * The rule is one line and easy to undo by reflex -- someone "tidying" a call
 * back to getObjects() reintroduces the whole defect silently.
 *
 * @since  __DEPLOY_VERSION__
 */
class ScriptureStackOwnershipTest extends ProclaimTestCase
{
    /**
     * Every table the library owns, and nothing else.
     */
    public function testTheScriptureStackIsTheFourSharedTables(): void
    {
        $this->assertSame(
            [
                '#__bsms_bible_translations',
                '#__bsms_bible_verses',
                '#__bsms_scripture_cache',
                '#__bsms_scripture_consumers',
            ],
            CwmdbHelper::getScriptureTables(),
            'The stack is what lib_cwmscripture creates. Adding a table there without '
            . 'adding it here puts it back in the backup.'
        );
    }

    /**
     * The registry specifically, because it is the dangerous one.
     */
    public function testTheConsumerRegistryIsTreatedAsShared(): void
    {
        $this->assertContains(
            '#__bsms_scripture_consumers',
            CwmdbHelper::getScriptureTables(),
            'Restoring a stale registry makes every uninstall guard under-report, '
            . 'which is how downloaded translations get dropped.'
        );
    }

    /**
     * getOwnObjects() must actually subtract, not just forward.
     */
    public function testOwnObjectsIsDefinedAsGetObjectsMinusTheStack(): void
    {
        $body = self::methodBody(CwmdbHelper::class, 'getOwnObjects');

        $this->assertMatchesRegularExpression('/getScriptureTables\(\)/', $body);
        $this->assertMatchesRegularExpression('/array_filter/', $body);
        $this->assertMatchesRegularExpression('/self::getObjects\(\)/', $body);
    }

    /**
     * The export gate. Rejecting a table here keeps it out of the dump
     * entirely -- no DROP, no CREATE, no rows.
     */
    public function testTheExportAllowListExcludesTheStack(): void
    {
        $body = self::methodBody(
            \CWM\Component\Proclaim\Administrator\Lib\Cwmbackup::class,
            'isKnownProclaimTable'
        );

        $this->assertMatchesRegularExpression('/getOwnObjects\(\)/', $body);
        $this->assertDoesNotMatchRegularExpression(
            '/CwmdbHelper::getObjects\(\)/',
            $body,
            'getObjects() here puts the shared tables back into every backup.'
        );
    }

    /**
     * The restore gate. This is what protects against a backup taken *before*
     * this change, which still carries those tables.
     */
    public function testTheRestoreAllowListExcludesTheStack(): void
    {
        $body = self::methodBody(
            \CWM\Component\Proclaim\Administrator\Lib\Cwmrestore::class,
            'isSafeRestoreStatement'
        );

        $this->assertMatchesRegularExpression('/getOwnObjects\(\)/', $body);
        $this->assertDoesNotMatchRegularExpression(
            '/CwmdbHelper::getObjects\(\)/',
            $body,
            'An older backup still contains the shared tables. Without this, restoring '
            . 'one reaches them even though the export no longer writes them.'
        );
    }

    /**
     * The drop loop, and the dead guard it used to carry.
     */
    public function testTheRestoreDropLoopNeitherTouchesTheStackNorKeepsTheDeadPreserveList(): void
    {
        $source = (string) file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Controller/CwmbackupController.php'
        );

        $this->assertMatchesRegularExpression(
            '/\$objects\s*=\s*CwmdbHelper::getOwnObjects\(\)/',
            $source,
            'The batch-0 drop loop must not enumerate the shared tables.'
        );

        // ⚠️ The old `$preserve = ['#__bsms_bible_verses']` never worked: the
        // export wrote a DROP for that table too, so the backup's own SQL
        // dropped it moments later. Keeping it would suggest a protection that
        // is not there.
        // Anchored to the start of a line so the comment above the loop -- which
        // quotes the old `$preserve = [...]` to explain why it went -- is not
        // read as the code itself. An unanchored pattern matched that prose and
        // failed, which is the same trap cwm-lint-comments exists for.
        $this->assertDoesNotMatchRegularExpression(
            '/^\s*\$preserve\s*=\s*\[/m',
            $source,
            'The preserve list was ineffective and is replaced by excluding the stack.'
        );
    }

    private static function methodBody(string $class, string $method): string
    {
        $reflection = new \ReflectionMethod($class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice(
                $lines,
                $reflection->getStartLine() - 1,
                $reflection->getEndLine() - $reflection->getStartLine() + 1
            )
        );
    }
}
