<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression tests for #1537: CwmImageMigration::deleteLegacyFiles() validated
 * the requested path against its allowed-base allow-list with a plain
 * str_starts_with() on the raw, un-cleaned path. Path::clean() only normalizes
 * separators -- it never collapses '../' -- so a path like
 * 'images/biblestudy/../../<anything>' passed the check while resolving
 * completely outside the allowed scope, letting any core.admin-level user
 * delete arbitrary files. Fixed by resolving the real path with realpath()
 * and checking it against the allowed bases' real paths before any deletion.
 *
 * Also covers the recoverBareIdFolders() N+1 fix: one SELECT per bare-ID
 * folder replaced with a single batched WHERE id IN (...) query.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmImageMigrationTest extends ProclaimTestCase
{
    private string $biblestudyDir;

    private bool $createdBiblestudyDir = false;

    private string $traversalTargetDir;

    private string $legitTargetDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->biblestudyDir = JPATH_ROOT . '/images/biblestudy';

        if (!is_dir($this->biblestudyDir)) {
            mkdir($this->biblestudyDir, 0777, true);
            $this->createdBiblestudyDir = true;
        }

        $this->traversalTargetDir = JPATH_ROOT . '/cwm_test_traversal_target_' . uniqid();
        mkdir($this->traversalTargetDir, 0777, true);
        file_put_contents($this->traversalTargetDir . '/secret.jpg', 'not really an image');

        $this->legitTargetDir = $this->biblestudyDir . '/cwm_test_legit_' . uniqid();
        mkdir($this->legitTargetDir, 0777, true);
        file_put_contents($this->legitTargetDir . '/photo.jpg', 'not really an image');
    }

    protected function tearDown(): void
    {
        if (is_dir($this->traversalTargetDir)) {
            @unlink($this->traversalTargetDir . '/secret.jpg');
            @rmdir($this->traversalTargetDir);
        }

        if (is_dir($this->legitTargetDir)) {
            @unlink($this->legitTargetDir . '/photo.jpg');
            @rmdir($this->legitTargetDir);
        }

        if ($this->createdBiblestudyDir && is_dir($this->biblestudyDir)) {
            @rmdir($this->biblestudyDir);

            $imagesDir = \dirname($this->biblestudyDir);

            if (is_dir($imagesDir) && \count(scandir($imagesDir)) === 2) {
                @rmdir($imagesDir);
            }
        }

        parent::tearDown();
    }

    public function testDeleteLegacyFilesRejectsPathTraversalEscapingAllowedBase(): void
    {
        $payload = 'images/biblestudy/../../' . basename($this->traversalTargetDir);

        $result = CwmImageMigration::deleteLegacyFiles([$payload]);

        $this->assertSame(0, $result['deleted'], 'must not delete anything reached via ../ traversal -- see #1537');
        $this->assertFileExists(
            $this->traversalTargetDir . '/secret.jpg',
            'the file outside the allowed base must survive the traversal attempt -- see #1537'
        );
        $this->assertNotEmpty($result['errors']);
    }

    public function testDeleteLegacyFilesStillDeletesWithinAllowedBase(): void
    {
        $payload = 'images/biblestudy/' . basename($this->legitTargetDir);

        $result = CwmImageMigration::deleteLegacyFiles([$payload]);

        $this->assertSame(1, $result['deleted'], 'a genuinely in-scope folder must still be cleaned up');
        $this->assertFileDoesNotExist($this->legitTargetDir . '/photo.jpg');
    }

    public function testRecoverBareIdFoldersQueriesTheDatabaseOnceNotOncePerFolder(): void
    {
        $reflection = new \ReflectionMethod(CwmImageMigration::class, 'recoverBareIdFolders');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertStringContainsString(
            'whereIn(',
            $body,
            'expected the per-folder DB lookup to be replaced with a single batched WHERE id IN (...) query -- see #1537'
        );

        $this->assertSame(
            1,
            preg_match_all('/\$db->setQuery\(\$query\)/', $body),
            'expected exactly one lookup query (batched), not one per eligible folder -- see #1537'
        );
    }
}
