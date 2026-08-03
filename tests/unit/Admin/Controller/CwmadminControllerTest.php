<?php

/**
 * Unit tests for CwmadminController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmadminController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1443: changePlayers(), changePopup(), and
 * mediaimages() built and executed raw SQL directly in the controller
 * (each with its own per-row N+1 loop), while changePlayersXHR() and
 * changePopupXHR() duplicated the same job with a more efficient batch
 * query. All five now delegate to CwmadminModel — see CwmadminModelTest.
 *
 * changePlayerByMediaTypeXHR() is deliberately left untouched: its query
 * is independently broken (queries a table column that doesn't exist —
 * see #1492) and moving broken SQL into the Model would not fix it.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmadminControllerTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmadminController method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testChangePlayersDelegatesToModelAndHasNoRawSql(): void
    {
        $body = self::methodBody('changePlayers');

        $this->assertMatchesRegularExpression('/getModel\(\)->changePlayer\(/', $body, 'changePlayers() must delegate to CwmadminModel::changePlayer() — see #1443');
        $this->assertDoesNotMatchRegularExpression('/createQuery\(\)/', $body, 'changePlayers() must not build SQL directly — see #1443');
        $this->assertDoesNotMatchRegularExpression('/\bforeach\b/', $body, 'changePlayers() must not contain the old per-row loop — see #1443');
    }

    public function testChangePopupDelegatesToModelAndHasNoRawSql(): void
    {
        $body = self::methodBody('changePopup');

        $this->assertMatchesRegularExpression('/getModel\(\)->changePopup\(/', $body, 'changePopup() must delegate to CwmadminModel::changePopup() — see #1443');
        $this->assertDoesNotMatchRegularExpression('/createQuery\(\)/', $body, 'changePopup() must not build SQL directly — see #1443');
        $this->assertDoesNotMatchRegularExpression('/\bforeach\b/', $body, 'changePopup() must not contain the old per-row loop — see #1443');
    }

    public function testMediaimagesDelegatesToModelAndHasNoSwitchStatement(): void
    {
        $body = self::methodBody('mediaimages');

        $this->assertMatchesRegularExpression('/getModel\(\)->changeMediaImages\(/', $body, 'mediaimages() must delegate to CwmadminModel::changeMediaImages() — see #1443');
        $this->assertDoesNotMatchRegularExpression('/\bswitch\b/', $body, 'mediaimages() must not contain the old four-case switch — see #1443');
    }

    /**
     * Regression test for the decode-mode bug bundled into #1443: the
     * payload was decoded with json_decode(..., true, ...) (associative
     * array) while every switch branch read it via `->` property access.
     * See CwmadminModelTest::testResolveMatcherWorksAgainstRealJsonDecodedObject().
     */
    public function testMediaimagesDecodesPayloadAsObjectNotArray(): void
    {
        $body = self::methodBody('mediaimages');

        $this->assertMatchesRegularExpression(
            '/json_decode\(\s*\$post\[.mediaimage.\]\s*,\s*false\s*,/',
            $body,
            'mediaimages() must decode the payload as an object (associative=false) to match the `->` access used throughout — see #1443'
        );
    }

    public function testChangePlayersXHRDelegatesToModelAndHasNoDuplicateSql(): void
    {
        $body = self::methodBody('changePlayersXHR');

        $this->assertMatchesRegularExpression('/getModel\(\)->changePlayer\(/', $body, 'changePlayersXHR() must delegate to CwmadminModel::changePlayer() — see #1443');
        $this->assertDoesNotMatchRegularExpression('/REPLACE\(/', $body, 'changePlayersXHR() must not duplicate the batch SQL now owned by the Model — see #1443');
    }

    public function testChangePopupXHRDelegatesToModelAndHasNoDuplicateSql(): void
    {
        $body = self::methodBody('changePopupXHR');

        $this->assertMatchesRegularExpression('/getModel\(\)->changePopup\(/', $body, 'changePopupXHR() must delegate to CwmadminModel::changePopup() — see #1443');
        $this->assertDoesNotMatchRegularExpression('/REPLACE\(/', $body, 'changePopupXHR() must not duplicate the batch SQL now owned by the Model — see #1443');
    }

    /**
     * changePlayerByMediaTypeXHR() is intentionally NOT refactored here —
     * its query is independently broken (see #1492) and this asserts the
     * scoping decision is visible in the method body via the doc comment,
     * not silently dropped.
     */
    public function testChangePlayerByMediaTypeXHRDocumentsWhyItWasSkipped(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'changePlayerByMediaTypeXHR');
        $lines      = file($reflection->getFileName());
        $docStart   = $reflection->getStartLine() - 10;
        $docBlock   = implode('', \array_slice($lines, max(0, $docStart - 1), 10));

        $this->assertStringContainsString('#1492', $docBlock, 'changePlayerByMediaTypeXHR() must document why it was left out of the #1443 refactor');
    }
}
