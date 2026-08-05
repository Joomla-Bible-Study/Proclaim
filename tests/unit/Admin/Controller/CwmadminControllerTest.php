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
 * changePlayerByMediaTypeXHR() is deliberately left in the controller
 * (not extracted to the Model): its query was independently broken —
 * queried 'media_image' as a table column when that key only exists in
 * the JSON params blob, and read the MIME-type 'mediatype' param via
 * getInt(), which mangled it into a meaningless digit extraction. Fixed
 * in place — see #1492 and CwmadminControllerTest's dedicated tests below.
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
     * Regression test for #1492: changePlayerByMediaTypeXHR() read the
     * 'mediatype' request param (a MIME type string like "video/mp4", the
     * value of the mtFrom MimeType field) via getInt(), which strips
     * non-digit characters and silently turns it into a meaningless number
     * ("video/mp4" -> 4, "video/x-ms-wmv" -> 0) instead of rejecting or
     * preserving it. It then compared that against 'media_image', a key
     * that only exists inside the JSON params blob, never a real column on
     * #__bsms_mediafiles — so any request whose mangled value got past the
     * "=== 0" guard still threw a DB error ("Unknown column 'media_image'").
     */
    public function testChangePlayerByMediaTypeXHRReadsMediatypeAsAString(): void
    {
        $body = self::methodBody('changePlayerByMediaTypeXHR');

        $this->assertDoesNotMatchRegularExpression(
            '/getInt\(\s*[\'"]mediatype[\'"]/',
            $body,
            "getInt('mediatype', ...) mangles a MIME type string like \"video/mp4\" into a meaningless digit " .
                'extraction (4) — see #1492'
        );
        $this->assertMatchesRegularExpression(
            '/getString\(\s*[\'"]mediatype[\'"]/',
            $body,
            "changePlayerByMediaTypeXHR() must read 'mediatype' via getString() to preserve the MIME type " .
                'string intact (getCmd() would strip the "/") — see #1492'
        );
    }

    public function testChangePlayerByMediaTypeXHRDoesNotQueryMediaImageAsAColumn(): void
    {
        $body = self::methodBody('changePlayerByMediaTypeXHR');

        $this->assertDoesNotMatchRegularExpression(
            '/quoteName\(\s*[\'"]media_image[\'"]\s*\)/',
            $body,
            "'media_image' is a key inside the JSON params blob, not a column on #__bsms_mediafiles — every " .
                'query using it as a column throws "Unknown column" — see #1492'
        );
    }

    /**
     * Deliberately NOT validated against Cwmmedia::getMimetypes()'s value
     * list -- live-testing on j5-dev caught that the list dropped the legacy
     * 'audio/mp3' string in favor of 'audio/mpeg' (#1397), but existing rows
     * can still have 'audio/mp3' stored, and MimeTypeField keeps a
     * stored-but-no-longer-offered value selectable. A whitelist check would
     * have silently broken matching for exactly those records.
     */
    public function testChangePlayerByMediaTypeXHRDoesNotRejectUnrecognizedMimetypes(): void
    {
        $body = self::methodBody('changePlayerByMediaTypeXHR');

        $this->assertDoesNotMatchRegularExpression(
            '/!\s*\\\\?in_array\(\s*\$mediaType\s*,\s*\$mimetypes/',
            $body,
            'changePlayerByMediaTypeXHR() must not reject a mediatype value just because it is missing from ' .
                "Cwmmedia::getMimetypes()'s current list — legacy stored values (e.g. 'audio/mp3', see #1397) " .
                'must still be matchable — see #1492'
        );
        // Match the actual call expression, not the bare method name -- the
        // method body also mentions getMimetypes() in a comment, which kept
        // the previous substring check green even with the real call removed.
        $this->assertMatchesRegularExpression(
            '/\(new Cwmmedia\(\)\)->getMimetypes\(\)/',
            $body,
            'changePlayerByMediaTypeXHR() must still call Cwmmedia::getMimetypes() to resolve the ' .
                'extension-based fallback match — see #1492'
        );
    }
}
