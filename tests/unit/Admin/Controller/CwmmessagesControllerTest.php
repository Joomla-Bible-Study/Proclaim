<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmmessagesController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1448: checkDeleteFiles() built its own join query and
 * Registry-parsing loop directly in the controller — duplicated verbatim in
 * CwmmediafilesController, differing only in matching mf.study_id instead of
 * mf.id. Moved into CwmmediafilesHelper::findFilesForDelete(); see
 * CwmmediafilesHelperTest.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmessagesControllerTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmmessagesController method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmmessagesController::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testCheckDeleteFilesHasNoInlineQueryOrRegistryLoop(): void
    {
        $body = self::methodBody('checkDeleteFiles');

        $this->assertDoesNotMatchRegularExpression('/createQuery\(\)/', $body, 'checkDeleteFiles() must not build the query directly — see #1448');
        $this->assertDoesNotMatchRegularExpression('/new Registry\(/', $body, 'checkDeleteFiles() must not parse server/media params directly — see #1448');
        $this->assertDoesNotMatchRegularExpression('/\bforeach\b/', $body, 'checkDeleteFiles() must not contain the old row-processing loop — see #1448');
    }

    public function testCheckDeleteFilesDelegatesToSharedHelperByStudyId(): void
    {
        $body = self::methodBody('checkDeleteFiles');

        $this->assertStringContainsString(
            "CwmmediafilesHelper::findFilesForDelete(\$db, \$ids, 'study_id')",
            $body,
            'CwmmessagesController must check by study_id (cascading message delete), not media file id — see #1448'
        );
    }

    /**
     * Pre-existing bug, caught live during the #1448 extraction: BaseDatabaseModel::getDatabase()
     * is protected, so $this->getModel()->getDatabase() — present in this method since before
     * this refactor — always 500'd. Fixed alongside the dedup by fetching DatabaseInterface from
     * the container instead, matching the convention used everywhere else in this codebase.
     */
    public function testCheckDeleteFilesUsesContainerDatabaseNotProtectedModelMethod(): void
    {
        $body = self::methodBody('checkDeleteFiles');

        $this->assertStringNotContainsString(
            'getModel()->getDatabase()',
            $body,
            'getModel()->getDatabase() calls a protected method and always 500s — see #1448'
        );
        $this->assertStringContainsString('Factory::getContainer()->get(DatabaseInterface::class)', $body);
    }
}
