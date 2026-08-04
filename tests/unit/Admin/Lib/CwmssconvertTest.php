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

use CWM\Component\Proclaim\Administrator\Lib\Cwmssconvert;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression tests for #1525.
 *
 * convertSS() is not exercised end-to-end here: it reads from SermonSpeaker's
 * own tables (#__sermon_sermons, #__sermon_speakers, #__sermon_series), none
 * of which exist outside a real SermonSpeaker install, and unconditionally
 * writes into #__bsms_servers/teachers/series. These are structural/source
 * assertions instead, matching the pattern used for other DB-touching
 * converters in this test suite (e.g. CwmpIconvertTest).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmssconvertTest extends ProclaimTestCase
{
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(Cwmssconvert::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * $db->escape($teacher->bio) pre-escaped the string before insertObject()
     * escaped it again internally -- apostrophes/backslashes in imported bios
     * got double-escaped (e.g. "O'Brien" stored as literal "O''Brien").
     */
    public function testTeacherBioIsNotPreEscapedBeforeInsertObject(): void
    {
        $body = self::methodBody('convertSS');

        $this->assertDoesNotMatchRegularExpression(
            '/\$db->escape\(\s*\$teacher->bio\s*\)/',
            $body,
            'teacher bio must not be pre-escaped -- insertObject() already escapes it, ' .
                'and doing both corrupts apostrophes/backslashes -- see #1525'
        );
        $this->assertSame(
            2,
            preg_match_all('/\$data->(information|short)\s*=\s*\$teacher->bio;/', $body),
            'expected both information and short to be assigned $teacher->bio directly -- see #1525'
        );
    }

    /**
     * The series/teacher crosswalk loop's else branch ran on every
     * non-matching iteration and unconditionally overwrote $data->teacher,
     * so a correct match found early could be clobbered by a later
     * non-matching iteration -- the final value depended on iteration order
     * rather than the actual match.
     */
    public function testSeriesCrosswalkStopsAtFirstMatchInsteadOfOverwriting(): void
    {
        $body = self::methodBody('convertSS');

        $this->assertMatchesRegularExpression(
            '/\$data->teacher = \$id;.*?foreach \(\$seriesspeakers as \$speakers\) \{\s*if \(\$speakers->series_id == \$single->id\) \{\s*\$data->teacher = \$speakers->newid;\s*break;/s',
            $body,
            'the series crosswalk must default to $id, then break on the first real match -- see #1525'
        );
    }

    /**
     * "Last inserted id" was fetched via a fresh SELECT ... ORDER BY id DESC
     * LIMIT 1 query instead of using insertObject()'s own $key parameter --
     * a race hazard under concurrent writes, an avoidable query inside a
     * loop, and (for series) the query targeted the wrong table entirely
     * (#__bsms_studies instead of #__bsms_series), corrupting every
     * series-to-teacher crosswalk id downstream.
     */
    public function testInsertsUseTheInsertObjectKeyParameterNotAFollowUpQuery(): void
    {
        $convertSsBody  = self::methodBody('convertSS');
        $newStudiesBody = self::methodBody('newStudies');

        $this->assertSame(
            3,
            preg_match_all(
                "/insertObject\('#__bsms_(teachers|series|studies)',\s*\\\$data,\s*'id'\)/",
                $convertSsBody . $newStudiesBody
            ),
            'teachers/series/studies inserts must pass \'id\' as the third insertObject() argument -- see #1525'
        );
        // The one remaining ORDER BY id DESC in convertSS() is the
        // unrelated server-record lookup (out of scope for #1525); none
        // should remain for teachers/series/studies follow-up lookups, and
        // none at all in newStudies().
        $this->assertSame(
            0,
            preg_match_all('/from\(\$db->quoteName\(.#__bsms_(teachers|series|studies).\)\)\s*->order\(/', $convertSsBody),
            'must not fetch the last-inserted teacher/series/study id via a follow-up ORDER BY id query -- see #1525'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/->order\(/',
            $newStudiesBody,
            'newStudies() must not fetch the last-inserted study id via a follow-up ORDER BY id query -- see #1525'
        );
    }

    public function testNewStudiesAndGetVersesHaveTypedParameters(): void
    {
        $newStudies = new \ReflectionMethod(Cwmssconvert::class, 'newStudies');
        $this->assertSame('object', (string) $newStudies->getParameters()[0]->getType());
        $this->assertSame('array', (string) $newStudies->getParameters()[1]->getType());

        $getVerses = new \ReflectionMethod(Cwmssconvert::class, 'getVerses');
        $this->assertSame('?string', (string) $getVerses->getParameters()[0]->getType());
    }
}
