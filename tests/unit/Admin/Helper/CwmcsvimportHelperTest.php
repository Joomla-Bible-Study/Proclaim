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

use CWM\Component\Proclaim\Administrator\Helper\CwmcsvimportHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression tests for #1539, found during a Helper-folder-wide bug-hunting
 * review (sequel to #1521-#1528).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmcsvimportHelperTest extends ProclaimTestCase
{
    /**
     * parseDate() tried 'm/d/Y' before 'd/m/Y' using createFromFormat()
     * non-strictly and without checking getLastErrors(). PHP's lenient
     * component overflow means a clearly non-US date like "25/03/2024"
     * (day > 12) was silently accepted by the 'm/d/Y' branch -- month 25
     * rolls over into a different year/month entirely -- instead of falling
     * through to 'd/m/Y', silently corrupting the imported studydate.
     */
    public function testParseDateFallsThroughToDayMonthYearWhenMonthOverflows(): void
    {
        $this->assertSame(
            '2024-03-25',
            CwmcsvimportHelper::parseDate('25/03/2024'),
            'day > 12 must fall through to d/m/Y instead of overflowing m/d/Y into a nonsense date -- see #1539'
        );
    }

    public function testParseDateStillPrefersMonthDayYearForGenuinelyAmbiguousDates(): void
    {
        // Both m/d/Y and d/m/Y could parse this; the fix must not change the
        // existing (documented) US-first preference for the truly ambiguous case.
        $this->assertSame('2024-01-15', CwmcsvimportHelper::parseDate('01/15/2024'));
    }

    public function testParseDateAcceptsValidDayMonthYear(): void
    {
        $this->assertSame('2024-01-15', CwmcsvimportHelper::parseDate('15/01/2024'));
    }

    public function testParseDateRejectsAnOutOfRangeDateInEveryFormat(): void
    {
        // Neither m/d/Y nor d/m/Y can make sense of month=13; must not silently
        // roll over into a different date via strtotime() either.
        $this->assertNull(CwmcsvimportHelper::parseDate('13/13/2024'));
    }

    public function testParseDateAcceptsIsoFormat(): void
    {
        $this->assertSame('2024-01-15', CwmcsvimportHelper::parseDate('2024-01-15'));
    }

    public function testParseDateAcceptsNamedMonthFormat(): void
    {
        $this->assertSame('2024-01-15', CwmcsvimportHelper::parseDate('Jan 15, 2024'));
    }

    public function testParseDateReturnsNullForEmptyInput(): void
    {
        $this->assertNull(CwmcsvimportHelper::parseDate(null));
        $this->assertNull(CwmcsvimportHelper::parseDate(''));
        $this->assertNull(CwmcsvimportHelper::parseDate('   '));
    }

    /**
     * updateExistingRow() resolved the location via resolveOrCreateLocation()
     * directly, bypassing the access-control check that processLocation()
     * enforces on the insert path (importRow()) -- "must be within the
     * current user's accessible locations to avoid cross-campus leaks", per
     * processLocation()'s own docblock. A restricted (non-core.admin) user's
     * CSV update could silently move a study to a campus/location they
     * aren't authorized to write to.
     */
    public function testUpdateExistingRowUsesProcessLocationForAccessControl(): void
    {
        $reflection = new \ReflectionMethod(CwmcsvimportHelper::class, 'updateExistingRow');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\$locationId\s*=\s*self::resolveOrCreateLocation\(/',
            $body,
            'must not resolve the location via the unchecked resolveOrCreateLocation() -- see #1539'
        );
        $this->assertMatchesRegularExpression(
            '/\$locationId\s*=\s*self::processLocation\(/',
            $body,
            'must go through processLocation() so the access-control check applies -- see #1539'
        );
    }
}
