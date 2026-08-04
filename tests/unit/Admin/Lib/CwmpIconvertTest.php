<?php

/**
 * Unit tests for CwmpIconvert
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\CwmpIconvert;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Regression tests for #1513.
 *
 * convertPI() is not exercised end-to-end here: it truncates
 * #__bsms_studies/#__bsms_teachers/#__bsms_mediafiles/#__bsms_podcast/
 * #__bsms_locations/#__bsms_series unconditionally and reads from PreachIT's
 * own tables (#__pistudies, #__piteachers, ...), none of which exist outside
 * a real PreachIT install. Running it against a real DB would destroy
 * whatever Proclaim data is already there. These are structural/source
 * assertions instead, matching the pattern used for other DB-touching
 * converters in this test suite.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmpIconvertTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmpIconvert method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmpIconvert::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * convertPI() returned void, so CwmadminController::convertPreachIt() passed null to
     * setRedirect() as the flash message on every conversion — success or failure looked
     * identical to the user. The sibling Cwmssconvert::convertSS() already returns a built
     * results-summary string; convertPI() must do the same.
     */
    public function testConvertPIReturnsString(): void
    {
        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'convertPI');

        $this->assertSame('string', (string) $reflection->getReturnType(), 'convertPI() must return string — see #1513');
    }

    public function testConvertPIReturnsTheBuiltResultsTable(): void
    {
        $body = self::methodBody('convertPI');

        $this->assertMatchesRegularExpression(
            '/return\s+\$piconvertresults;/',
            $body,
            'convertPI() must return the results-summary table it builds, not a dangling local — see #1513'
        );
        // The pre-#1513 dead code referenced counters that were never tracked
        // ($this->svadd/$this->svnoadd) and a `$$this->sradd` typo; neither must resurface.
        $this->assertDoesNotMatchRegularExpression('/\$this->svadd|\$this->svnoadd/', $body);
        $this->assertDoesNotMatchRegularExpression('/\$\$this->sradd/', $body);
    }

    public function testConvertPreachItControllerPassesResultToRedirect(): void
    {
        $reflection = new \ReflectionMethod(
            \CWM\Component\Proclaim\Administrator\Controller\CwmadminController::class,
            'convertPreachIt'
        );
        $lines = file($reflection->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/\$piconversion\s*=\s*\$convert->convertPI\(\)/',
            $body
        );
        $this->assertMatchesRegularExpression(
            '/setRedirect\([^;]*\$piconversion/',
            $body,
            'convertPreachIt() must pass convertPI()\'s return value to setRedirect() — see #1513'
        );
    }

    /**
     * insertMedia() hand-built its `params` JSON blob via string concatenation, mixing
     * $db->escape() (SQL escaping) into JSON content — wrong escaping semantics, and one
     * branch (JWPlayer) was outright malformed JSON (missing colon after "size", a stray
     * '":""' token). All branches must build an array and encode it instead.
     */
    public function testInsertMediaBuildsParamsViaJsonEncode(): void
    {
        $body = self::methodBody('insertMedia');

        $this->assertDoesNotMatchRegularExpression(
            '/\$media(->params)?\s*=\s*.\{"/',
            $body,
            'insertMedia() must not hand-build the params JSON string via concatenation — see #1513'
        );
        $this->assertSame(
            6,
            preg_match_all('/json_encode\(\s*\[/', $body),
            'insertMedia() must build params via json_encode() in all 6 branches ' .
                '(JWPlayer/Vimeo/YouTube video, audio, notes, slides) — see #1513'
        );
    }

    public function testInsertMediaDoesNotSqlEscapeValuesBeforeJsonEncoding(): void
    {
        $body = self::methodBody('insertMedia');

        $this->assertDoesNotMatchRegularExpression(
            '/\$db->escape\(\s*\$(filename|mediacode)\s*\)/',
            $body,
            'filename/mediacode must not be SQL-escaped before being embedded in JSON — json_encode() ' .
                'handles its own escaping, and SQL-escaping first corrupts values containing backslashes or quotes — see #1513'
        );
    }

    public function testInsertMediaHasTypedParameters(): void
    {
        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'insertMedia');
        $params     = $reflection->getParameters();

        $this->assertSame('object', (string) $params[0]->getType());
        $this->assertSame('string', (string) $params[1]->getType());
        $this->assertSame('int', (string) $params[2]->getType());
        $this->assertCount(3, $params, 'the unused $oldid parameter must stay removed — see #1513');
    }

    public function testInsertPodcastReturnsNullableIntWithExplicitFallthrough(): void
    {
        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'insertPodcast');

        $this->assertSame('?int', (string) $reflection->getReturnType());

        $body = self::methodBody('insertPodcast');
        $this->assertMatchesRegularExpression(
            '/return\s+null;\s*\}\s*$/',
            rtrim($body),
            'insertPodcast() must explicitly return null on fall-through, not rely on implicit null — see #1513'
        );
    }

    public function testCheckMediaReturnsNullableIntWithExplicitFallthrough(): void
    {
        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'checkMedia');

        $this->assertSame('?int', (string) $reflection->getReturnType());

        $body = self::methodBody('checkMedia');
        $this->assertMatchesRegularExpression(
            '/return\s+null;\s*\}\s*$/',
            rtrim($body),
            'checkMedia() must explicitly return null on fall-through, not rely on implicit null — see #1513'
        );
    }

    /**
     * Regression tests for the access-level validation gap found while investigating #1515:
     * $pi->saccess / $pi->access / $pi->accesscode (PreachIT's own access-code values) were
     * written straight into Proclaim's `access` column with no check against this site's
     * `#__viewlevels` table. That table's ids aren't even guaranteed contiguous, so an
     * unmapped PreachIT code silently assigns an imported record to a view level no user
     * holds -- the record exists but is invisible to everyone, with no error at import time.
     *
     * resolveAccessLevel() is exercised directly against the real `#__viewlevels` table
     * (read-only) rather than a fabricated schema -- unlike PreachIT's own tables, this one
     * genuinely exists on every Joomla install, so this is real behavior, not a guess.
     */
    public function testResolveAccessLevelAcceptsAnExistingViewLevel(): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()->select($db->quoteName('id'))->from($db->quoteName('#__viewlevels'));
        $db->setQuery($query, 0, 1);
        $validId = (int) $db->loadResult();

        $this->assertGreaterThan(0, $validId, 'test precondition: #__viewlevels must have at least one row');

        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'resolveAccessLevel');
        $result     = $reflection->invoke(new CwmpIconvert(), $validId);

        $this->assertSame($validId, $result);
    }

    public function testResolveAccessLevelFallsBackToPublicForAnUnmappedId(): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()->select('MAX(' . $db->quoteName('id') . ')')->from($db->quoteName('#__viewlevels'));
        $db->setQuery($query);
        $unmappedId = (int) $db->loadResult() + 1000;

        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'resolveAccessLevel');
        $result     = $reflection->invoke(new CwmpIconvert(), $unmappedId);

        $this->assertSame(1, $result, 'an unmapped access value must fall back to Public (id 1) — see #1515');
    }

    public function testAllAccessSitesGoThroughResolveAccessLevel(): void
    {
        $bodies = self::methodBody('convertPI') . self::methodBody('insertMedia');

        $this->assertDoesNotMatchRegularExpression(
            '/->access\s*=\s*\$pi->(saccess|access|accesscode)\s*;/',
            $bodies,
            'every ->access assignment sourced from PreachIT data must go through ' .
                'resolveAccessLevel() rather than being written unvalidated — see #1515'
        );
        $this->assertSame(
            5,
            preg_match_all('/resolveAccessLevel\(/', $bodies),
            'expected 5 call sites (studies, locations, and 3x media video branches) — see #1515'
        );
    }

    public function testClassDocblockIsAboveTheClassDeclaration(): void
    {
        $reflection = new \ReflectionClass(CwmpIconvert::class);
        $lines      = file($reflection->getFileName());
        $classLine  = $reflection->getStartLine();

        // The line immediately above the class declaration must close a docblock (`*/`),
        // not a `use` import statement — the class docblock was previously stranded above
        // the use-import block instead of directly above the class.
        $this->assertMatchesRegularExpression('/\*\/\s*$/', $lines[$classLine - 2]);
    }
}
