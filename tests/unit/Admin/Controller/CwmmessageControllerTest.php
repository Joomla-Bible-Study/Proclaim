<?php

/**
 * Unit tests for CwmmessageController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmmessageController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1444: save() built and executed raw SQL directly in
 * the controller (delete + per-tag lookup/insert loop) to sync topic tags.
 * That work moved into CwmmessageModel::saveTopics() — see CwmmessageModelTest.
 * save() now only checks the CSRF token and delegates to parent::save().
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmessageControllerTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmmessageController method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmmessageController::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testSaveHasNoRawSqlOrTagSyncLoop(): void
    {
        $body = self::methodBody('save');

        $this->assertDoesNotMatchRegularExpression('/createQuery\(\)/', $body, 'save() must not build SQL directly — see #1444');
        $this->assertDoesNotMatchRegularExpression('/\bforeach\b/', $body, 'save() must not contain the old tag-sync loop — see #1444');
        $this->assertDoesNotMatchRegularExpression('/createTable\(.Cwmstudytopics./', $body, 'save() must not construct Cwmstudytopics rows directly — see #1444');
    }

    public function testSaveStillChecksTokenAndDelegatesToParent(): void
    {
        $body = self::methodBody('save');

        $this->assertStringContainsString('Session::checkToken()', $body, 'save() must still guard against CSRF — see #1444');
        $this->assertStringContainsString('parent::save($key, $urlVar)', $body, 'save() must delegate to FormController::save() — see #1444');
    }
}
