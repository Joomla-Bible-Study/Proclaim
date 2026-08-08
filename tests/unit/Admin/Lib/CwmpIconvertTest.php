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

    /**
     * Regression tests for #1528.
     *
     * The book-number loop's `else` branch ran on every non-matching iteration
     * of the 66-entry book array and unconditionally overwrote $booknumber back
     * to '101' (Genesis) -- only study_book==66 (the last array entry) ever
     * survived. Every other imported study landed on the wrong book regardless
     * of its actual scripture reference. $booknumber must default once before
     * the loop and only be assigned on an actual match.
     */
    public function testBookNumberDefaultsBeforeLoopInsteadOfOnEveryNonMatch(): void
    {
        $body = self::methodBody('convertPI');

        $this->assertMatchesRegularExpression(
            "/\\\$booknumber\s*=\s*'101';.*?foreach\s*\(\\\$books as \\\$book\)/s",
            $body,
            'booknumber must default to \'101\' before the book-matching loop -- see #1528'
        );
        $this->assertDoesNotMatchRegularExpression(
            "/if \(\\\$book\['id'\] == \\\$pi->study_book\) \{\s*\\\$booknumber = \\\$book\['jbs'\];\s*\} else \{/",
            $body,
            'the book-matching loop must not reset booknumber to \'101\' on every non-matching iteration -- see #1528'
        );
    }

    /**
     * getBooks() gave Hosea (id 28) the same jbs code as Joel (id 29,
     * '129'), instead of continuing the 100+index pattern with '128'.
     */
    public function testGetBooksHoseaAndJoelHaveDistinctJbsCodes(): void
    {
        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'getBooks');
        $books      = $reflection->invoke(new CwmpIconvert());
        $byId       = array_column($books, 'jbs', 'id');

        $this->assertSame('128', $byId['28'], 'Hosea must map to jbs 128, not duplicate Joel\'s 129 -- see #1528');
        $this->assertSame('129', $byId['29']);
    }

    /**
     * insertPodcast() is typed ?int but had 3 exclusion paths returning
     * `false`, which PHP's coercive typing silently turns into `0` rather
     * than `null` -- writing podcast_id=0 (an invalid FK) instead of leaving
     * the field unset for excluded records.
     */
    public function testInsertPodcastNeverReturnsFalse(): void
    {
        $body = self::methodBody('insertPodcast');

        $this->assertDoesNotMatchRegularExpression(
            '/return\s+false;/',
            $body,
            'insertPodcast() is typed ?int -- every exclusion path must return null, not false -- see #1528'
        );
    }

    /**
     * The teacher/ministry exclusion checks passed the still-JSON-encoded
     * $pi->teacher/$pi->ministry strings straight to in_array(), which
     * requires an array haystack -- a TypeError that would abort the whole
     * conversion whenever any podcast had teacher/ministry exclusion
     * configured. Both must be Registry-decoded first, matching the
     * pattern already used by the inclusion checks in the same method.
     */
    public function testInsertPodcastDecodesTeacherAndMinistryBeforeExclusionCheck(): void
    {
        $body = self::methodBody('insertPodcast');

        $this->assertDoesNotMatchRegularExpression(
            '/in_array\(\$t,\s*\$pi->teacher\)/',
            $body,
            'the teacher exclusion check must not pass the raw JSON-encoded $pi->teacher to in_array() -- see #1528'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/in_array\(\$m,\s*\$pi->ministry\)/',
            $body,
            'the ministry exclusion check must not pass the raw JSON-encoded $pi->ministry to in_array() -- see #1528'
        );
    }

    /**
     * checkMedia()'s "all media types" sentinel compared an array
     * ($includemedia) to the string 'all' with ==, which is always false --
     * podcasts configured for "all media types" (media==0) never matched
     * anything. Exercised live: checkMedia() has no DB dependency once
     * $podcastids is set directly on the instance.
     */
    public function testCheckMediaAllSentinelMatchesAgainstTheArray(): void
    {
        $instance             = new CwmpIconvert();
        $instance->podcastids = [['newid' => 42, 'oldid' => 7]];

        $reflection = new \ReflectionMethod(CwmpIconvert::class, 'checkMedia');
        $pi         = (object) ['audio_link' => null, 'video_link' => null, 'slides_link' => 0];
        $podcast    = (object) ['id' => 7];

        $result = $reflection->invoke($instance, ['all'], $pi, $podcast);

        $this->assertSame(42, $result, 'checkMedia() must match the \'all\' sentinel inside the $includemedia array -- see #1528');
    }

    /**
     * The single-item teacher/ministry inclusion fallback compared the
     * podcast's configured value against the still-encoded $pi->teacher/
     * $pi->ministry string instead of the decoded array built two lines
     * earlier -- essentially never true.
     */
    public function testInsertPodcastSingleItemInclusionComparesAgainstDecodedArray(): void
    {
        $body = self::methodBody('insertPodcast');

        $this->assertDoesNotMatchRegularExpression(
            '/if \(\$value == \$pi->teacher\)/',
            $body,
            'the single-teacher inclusion check must compare against the decoded $teacher array, not raw $pi->teacher -- see #1528'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/if \(\$value == \$pi->ministry\)/',
            $body,
            'the single-ministry inclusion check must compare against the decoded $ministry array, not raw $pi->ministry -- see #1528'
        );
    }

    /**
     * The teacher-inclusion branch guard tested \count($teacher) -- the
     * study's own decoded teacher array -- while the loop it guards
     * iterates $teacher_list, the podcast's configured list. The parallel
     * ministry block correctly guards on \count($ministry_list). With the
     * bug, a podcast configured with multiple teacher_list entries but a
     * study with only one teacher fell into the elseif branch and checked
     * only $teacher_list[0], silently ignoring the rest of the configured
     * list.
     */
    public function testInsertPodcastTeacherInclusionGuardsOnTheConfiguredListNotTheStudy(): void
    {
        $body = self::methodBody('insertPodcast');

        $this->assertDoesNotMatchRegularExpression(
            '/if \(\\\\count\(\$teacher\) > 1\)/',
            $body,
            'the teacher-inclusion branch must guard on \\count($teacher_list), matching the ministry block -- see #1528'
        );
        $this->assertMatchesRegularExpression(
            '/if \(\\\\count\(\$teacher_list\) > 1\)/',
            $body,
            'the teacher-inclusion branch must guard on \\count($teacher_list), matching the ministry block -- see #1528'
        );
    }

    /**
     * "Last inserted id" was fetched via a fresh SELECT ... ORDER BY id DESC
     * LIMIT 1 query at 6 call sites instead of insertObject()'s own $key
     * parameter -- a race hazard under concurrent writes and an avoidable
     * query per insert. Same pattern as #1525's Cwmssconvert finding.
     */
    public function testLastInsertedIdUsesInsertObjectKeyParamNotAFollowUpQuery(): void
    {
        $body = self::methodBody('convertPI');

        $this->assertDoesNotMatchRegularExpression(
            "/order\(\\\$db->quoteName\('id'\)\s*\.\s*'\s*DESC'\)/",
            $body,
            'must not fetch the last-inserted id via a follow-up ORDER BY id DESC query -- see #1528'
        );
        $this->assertSame(
            6,
            preg_match_all("/insertObject\('#__bsms_\w+',\s*\\\$\w+,\s*'id'\)/", $body),
            'expected all 6 insertObject() calls (teacher x2, location, series, podcast, study) to pass \'id\' as the key argument -- see #1528'
        );
    }

    /**
     * insertMedia() unconditionally reloaded the entire #__pifilepath table
     * even though only the audio branch used it -- video/notes/slides
     * either don't need it or already do their own scoped query. Now scoped
     * like the notes/slides branches (WHERE id = ...).
     */
    public function testInsertMediaOnlyQueriesPifilepathWithinTheAudioBranch(): void
    {
        $body = self::methodBody('insertMedia');

        $this->assertDoesNotMatchRegularExpression(
            "/^\s*\\\$query->select\('\*'\)->from\(\\\$db->quoteName\('#__pifilepath'\)\);\s*$/m",
            $body,
            'must not unconditionally load the whole #__pifilepath table at the top of insertMedia() -- see #1528'
        );
        $this->assertSame(
            4,
            preg_match_all("/from\(\\\$db->quoteName\('#__pifilepath'\)\)->where\(/", $body),
            'expected 4 scoped #__pifilepath lookups (JWPlayer video, audio, notes, slides), each WHERE id = ... -- see #1528'
        );
    }

    /**
     * insertPodcast() reloaded the entire #__pipodcast table on every call,
     * and it's invoked up to 4x per study inside the studies loop despite
     * the source table never changing during a single conversion run.
     */
    public function testInsertPodcastCachesPipodcastAcrossCalls(): void
    {
        $reflection = new \ReflectionClass(CwmpIconvert::class);
        $this->assertTrue(
            $reflection->hasProperty('piPodcastsCache'),
            'insertPodcast() must cache #__pipodcast on the instance instead of reloading it every call -- see #1528'
        );

        $body = self::methodBody('insertPodcast');
        $this->assertMatchesRegularExpression(
            '/if \(\$this->piPodcastsCache === null\)/',
            $body
        );
    }
}
