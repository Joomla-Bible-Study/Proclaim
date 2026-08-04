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
}
