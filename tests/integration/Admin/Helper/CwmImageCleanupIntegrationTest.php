<?php

/**
 * Integration test for CwmImageCleanup's DB-touching TOCTOU guard.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmImageCleanup;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1563 finding 2: findOrphanedFolders() (scan) and
 * deleteOrphans() (delete) are two independent round trips with no lock or
 * version token between them. If a record reappears in the DB in that window
 * (e.g. a restore re-inserts it with a reused ID), deleteOrphans() previously
 * deleted the folder anyway -- it only checked path scope and is_dir(), never
 * re-querying current DB state. Fixed by re-deriving the folder's ID and
 * re-checking it against a fresh getValidIds() call immediately before delete.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmImageCleanup::class)]
class CwmImageCleanupIntegrationTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private string $folderPath;

    private string $absoluteFolderPath;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    protected function tearDown(): void
    {
        if (isset($this->absoluteFolderPath) && is_dir($this->absoluteFolderPath)) {
            @rmdir($this->absoluteFolderPath);

            // @rmdir() is a silent no-op unless the directory is actually
            // empty, so this is safe to attempt regardless of whether this
            // test or another one created these parent dirs -- avoids
            // leaving an empty images/biblestudy/studies behind that would
            // make a later test's mkdir(..., recursive: true) warn "File
            // exists".
            @rmdir(\dirname($this->absoluteFolderPath));
            @rmdir(\dirname($this->absoluteFolderPath, 2));
        }

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    #[TestDox('deleteOrphans() re-validates against current DB state and skips a folder whose record has reappeared')]
    public function testDeleteOrphansSkipsFolderWhoseRecordReappearedSinceScan(): void
    {
        $studyId = $this->insertStudy();

        $this->folderPath         = 'images/biblestudy/studies/cwm-test-reappeared-' . $studyId;
        $this->absoluteFolderPath = JPATH_ROOT . '/' . $this->folderPath;
        mkdir($this->absoluteFolderPath, 0777, true);

        $result = CwmImageCleanup::deleteOrphans([$this->folderPath]);

        $this->assertSame(0, $result['deleted'], 'must not delete a folder whose record exists at delete time');
        $this->assertDirectoryExists($this->absoluteFolderPath);
        $this->assertNotEmpty($result['errors']);
    }

    private function insertStudy(): int
    {
        $row = (object) [
            'studytitle'  => 'Test Study ' . uniqid('', true),
            'alias'       => 'cwm-test-reappeared',
            'studydate'   => '2026-01-15 10:00:00',
            'messagetype' => '1',
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }
}
