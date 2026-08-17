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

use CWM\Component\Proclaim\Administrator\Lib\Cwmrestore;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression tests for #1522.
 *
 * restoreDB()/installdb() executed every statement in an uploaded/selected backup
 * file with only two substr_count() checks for literal marker strings -- trivially
 * satisfied while the rest of the file contained arbitrary SQL (DROP DATABASE,
 * GRANT, writes to #__users/#__extensions, etc.). isSafeRestoreStatement() now
 * restricts execution to the statement shapes and tables Cwmbackup itself is known
 * to generate.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmrestoreTest extends ProclaimTestCase
{
    private static function isSafeRestoreStatement(string $statement): bool
    {
        $reflection = new \ReflectionMethod(Cwmrestore::class, 'isSafeRestoreStatement');

        return $reflection->invoke(null, $statement);
    }

    public static function legitimateStatementProvider(): array
    {
        return [
            'CREATE TABLE on a bsms table' => [
                'CREATE TABLE `#__bsms_studies` (`id` int NOT NULL) ENGINE=InnoDB;',
            ],
            'DROP TABLE IF EXISTS on a bsms table' => [
                'DROP TABLE IF EXISTS `#__bsms_studies`;',
            ],
            'INSERT INTO a bsms table' => [
                "INSERT INTO `#__bsms_studies` SET `id`='1',`studytitle`='Test';",
            ],
            'UPDATE #__extensions (component config restore)' => [
                "UPDATE `#__extensions` SET `params` = '{}' WHERE `element` = 'com_proclaim';",
            ],
            'UPDATE #__assets (ACL restore)' => [
                "UPDATE `#__assets` SET `rules` = '{}' WHERE `name` = 'com_proclaim';",
            ],
            'DELETE FROM #__scheduler_tasks (scheduled task restore)' => [
                "DELETE FROM `#__scheduler_tasks` WHERE `type` LIKE 'proclaim.%';",
            ],
            'INSERT INTO ... SELECT (per-record ACL relink)' => [
                "INSERT INTO `#__assets` (`parent_id`, `lft`) SELECT p.`id`, 0 FROM `#__assets` p WHERE p.`name` = 'com_proclaim';",
            ],
        ];
    }

    #[DataProvider('legitimateStatementProvider')]
    public function testAcceptsStatementShapesCwmbackupActuallyGenerates(string $statement): void
    {
        $this->assertTrue(self::isSafeRestoreStatement($statement), 'rejected a statement shape Cwmbackup legitimately produces: ' . $statement);
    }

    public static function maliciousStatementProvider(): array
    {
        return [
            'DROP DATABASE'                                                       => ['DROP DATABASE j5_dev;'],
            'GRANT ALL'                                                           => ["GRANT ALL PRIVILEGES ON *.* TO 'attacker'@'%';"],
            'UPDATE #__users password'                                            => ["UPDATE `#__users` SET `password` = 'hacked' WHERE `id` = 1;"],
            'INSERT INTO #__users'                                                => ["INSERT INTO `#__users` SET `username`='backdoor',`password`='x';"],
            'DROP TABLE on core users table'                                      => ['DROP TABLE `#__users`;'],
            'marker strings hidden in a comment, real payload is a DROP DATABASE' => [
                "-- #__bsms_admin_genesis #__bsms_studies\nDROP DATABASE j5_dev;",
            ],
            'SELECT INTO OUTFILE'                   => ["SELECT * FROM `#__bsms_studies` INTO OUTFILE '/tmp/leak.csv';"],
            'unrecognized statement shape entirely' => ['SET GLOBAL general_log = 1;'],
        ];
    }

    #[DataProvider('maliciousStatementProvider')]
    public function testRejectsStatementsOutsideCwmbackupsKnownShapes(string $statement): void
    {
        $this->assertFalse(self::isSafeRestoreStatement($statement), 'accepted a statement that should have been rejected: ' . $statement);
    }

    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(Cwmrestore::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testRestoreDbCallsTheSafetyCheckBeforeExecuting(): void
    {
        $body = self::methodBody('restoreDB');

        $this->assertMatchesRegularExpression('/isSafeRestoreStatement\(/', $body);
    }

    public function testInstalldbCallsTheSafetyCheckBeforeExecuting(): void
    {
        $body = self::methodBody('installdb');

        $this->assertMatchesRegularExpression('/isSafeRestoreStatement\(/', $body);
    }

    /**
     * Regression tests for #1523.
     *
     * importdb() read the selected server-side backup filename via
     * $input->getWord('backuprestore', ''), whose 'word' filter strips
     * everything except letters and underscores. Real backup filenames
     * (proclaim-backup_SiteName_YYYY-MM-DD_vX.X.X.sql) always contain
     * digits and a dot, so the value could never contain '.sql' after
     * filtering -- the substr_count($backupRestore, '.sql') check at the
     * top of importdb() was permanently false, making "restore from
     * server backup folder" dead on arrival. getCmd()'s 'cmd' filter
     * allows [A-Za-z0-9_.-], which matches the actual filename format.
     */
    public function testImportdbReadsBackupRestoreViaGetCmdNotGetWord(): void
    {
        $body = self::methodBody('importdb');

        $this->assertMatchesRegularExpression(
            '/->getCmd\(\s*.backuprestore./',
            $body,
            'importdb() must read backuprestore via getCmd(), not getWord() -- see #1523'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/->getWord\(\s*.backuprestore./',
            $body,
            'importdb() must not read backuprestore via getWord() -- its filter strips digits and dots, ' .
                'so a real "proclaim-backup_Site_2026-08-04_v10.5.6.sql" filename could never survive -- see #1523'
        );
    }

    /**
     * Proves the actual bug, not just the code shape: Joomla's 'word' filter
     * mangles a realistic backup filename into something that can never
     * contain '.sql', while 'cmd' passes it through unchanged.
     */
    public function testWordFilterCannotSurviveARealBackupFilenameButCmdFilterCan(): void
    {
        $filter   = new \Joomla\Filter\InputFilter();
        $filename = 'proclaim-backup_SiteName_2026-08-04_v10.5.6.sql';

        $this->assertStringNotContainsString(
            '.sql',
            $filter->clean($filename, 'word'),
            "word filter unexpectedly preserved '.sql' -- if this ever changes, #1523's root cause is gone"
        );
        $this->assertSame($filename, $filter->clean($filename, 'cmd'));
    }

    public function testImportdbAndRestoreDbHaveTypedParameters(): void
    {
        $importdb = new \ReflectionMethod(Cwmrestore::class, 'importdb');
        $this->assertSame('bool', (string) $importdb->getParameters()[0]->getType());

        $restoreDb = new \ReflectionMethod(Cwmrestore::class, 'restoreDB');
        $this->assertSame('string', (string) $restoreDb->getParameters()[0]->getType());
    }

    /**
     * tablesToBlob()/tablesToText() were grep-confirmed unused anywhere in
     * the repo -- dead code found during the same review as #1523.
     */
    public function testDeadTableConversionMethodsAreRemoved(): void
    {
        $this->assertFalse(method_exists(Cwmrestore::class, 'tablesToBlob'));
        $this->assertFalse(method_exists(Cwmrestore::class, 'tablesToText'));
    }

    /**
     * importdb() and restoreDB() built the com_installer DatabaseModel via
     * `new DatabaseModel()`, inconsistent with the MVCFactory-based
     * UpdateModel creation three lines below it in the same method.
     */
    public function testDatabaseModelIsCreatedViaMvcFactory(): void
    {
        $body = self::methodBody('importdb') . self::methodBody('restoreDB');

        $this->assertDoesNotMatchRegularExpression(
            '/new DatabaseModel\(\)/',
            $body,
            'DatabaseModel must not be directly instantiated -- see #1523'
        );
        $this->assertSame(
            2,
            preg_match_all('/bootComponent\(.com_installer.\)\s*\n?\s*->getMVCFactory\(\)->createModel\(.Database./', $body),
            'expected both DatabaseModel creation sites to go through MVCFactory -- see #1523'
        );
    }

    /**
     * The shared scripture tables belong to lib_cwmscripture, not to this
     * component. They are swept into the prefix-driven table list only because
     * the library inherited Proclaim's `bsms_` naming.
     */
    public static function preservedTableProvider(): array
    {
        return [
            'downloaded verses'  => ['#__bsms_bible_verses'],
            'download catalogue' => ['#__bsms_bible_translations'],
            'provider cache'     => ['#__bsms_scripture_cache'],
            'consumer registry'  => ['#__bsms_scripture_consumers'],
        ];
    }

    #[DataProvider('preservedTableProvider')]
    public function testSharedScriptureTablesAreExcludedFromTheDropPhase(string $table): void
    {
        $preserved = new \ReflectionProperty(Cwmrestore::class, 'preserveTables');

        $this->assertContains(
            $table,
            $preserved->getValue(),
            "{$table} belongs to lib_cwmscripture and must survive a restore"
        );
    }

    /**
     * The catalogue travels with the verses.
     *
     * Up to 10.5.9 only #__bsms_bible_verses was preserved, so the table
     * recording which of those verses are downloaded (`installed`) was dropped
     * and replaced from the backup. That made the Local Translations panel wrong
     * in both directions -- orphaned verses reported as not installed, or
     * translations reported installed with no verses behind them.
     */
    public function testTheVerseTableAndItsCatalogueArePreservedTogether(): void
    {
        $preserved = (new \ReflectionProperty(Cwmrestore::class, 'preserveTables'))->getValue();

        $this->assertSame(
            \in_array('#__bsms_bible_verses', $preserved, true),
            \in_array('#__bsms_bible_translations', $preserved, true),
            'preserving one of the verse/catalogue pair without the other is incoherent'
        );
    }

    /**
     * ⚠️ Excluding a table from the DROP phase does not stop the backup's own
     * INSERT statements for it. isSafeRestoreStatement() is a security
     * allow-list -- it asks whether the target is a Proclaim table, not whether
     * it is preserved -- so those rows landed in the "preserved" table anyway.
     */
    public function testStatementsTargetingAPreservedTableAreSkipped(): void
    {
        $targets = new \ReflectionMethod(Cwmrestore::class, 'targetsPreservedTable');

        $this->assertTrue($targets->invoke(null, "INSERT INTO `#__bsms_bible_verses` SET `id`='1';"));
        $this->assertTrue($targets->invoke(null, "INSERT INTO `#__bsms_scripture_consumers` SET `element`='com_other';"));
        $this->assertTrue($targets->invoke(null, 'DROP TABLE IF EXISTS `#__bsms_bible_translations`;'));

        $this->assertFalse(
            $targets->invoke(null, "INSERT INTO `#__bsms_studies` SET `id`='1';"),
            "the component's own content tables must still be restored"
        );
    }

    /**
     * Both execution loops must skip preserved tables. Guarding only one leaves
     * the other restore path overwriting the rows.
     */
    public function testBothRestorePathsSkipPreservedTables(): void
    {
        // The two statement-execution loops live in restoreDB() and installdb() —
        // the same pair the isSafeRestoreStatement() guards above are asserted on.
        foreach (['restoreDB', 'installdb'] as $method) {
            $this->assertMatchesRegularExpression(
                '/targetsPreservedTable\(/',
                self::methodBody($method),
                "{$method}() must skip statements targeting a preserved table"
            );
        }
    }

    /**
     * CwmdbHelper::getObjects() was corrected to an anchored prefix match after
     * an unanchored substr_count() matched a sibling install's table on shared
     * hosting and yielded a mangled `#__s_bsms_studies`. This copy kept the bug.
     */
    public function testTableDiscoveryIsAnchoredToThePrefix(): void
    {
        $body = self::methodBody('getObjects');

        $this->assertMatchesRegularExpression(
            '/str_starts_with\(\$table,\s*\$prefix\s*\.\s*\$bsms\)/',
            $body,
            'table discovery must anchor to the prefix, not substring-match it'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/substr_count\(\$table,\s*\$bsms\)/',
            $body,
            'the unanchored match matches sibling installs on shared hosting'
        );
    }
}
