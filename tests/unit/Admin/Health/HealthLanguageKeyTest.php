<?php

/**
 * The health report builds language keys from enum cases, so nothing scanning
 * for literal Text::_() arguments can see them.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Health;

use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * ⚠️ StringKeyExistsTest deliberately skips a key followed by a concatenation,
 * because several call sites build one at runtime. `HealthStatus::labelKey()`
 * and `HealthGroup::labelKey()` are exactly that shape, so their keys are
 * invisible to it — a missing one would print `JBS_HEALTH_STATUS_WARNING` in
 * the report with nothing failing.
 *
 * @since 10.6.0
 */
class HealthLanguageKeyTest extends TestCase
{
    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Every status has a label defined in en-GB')]
    public function testStatusLabelsExist(): void
    {
        $strings = self::englishStrings();

        foreach (HealthStatus::cases() as $status) {
            $this->assertArrayHasKey(
                $status->labelKey(),
                $strings,
                $status->labelKey() . ' is built from HealthStatus::' . $status->name . ' but never defined.'
            );
        }
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('Every report section has a heading defined in en-GB')]
    public function testGroupLabelsExist(): void
    {
        $strings = self::englishStrings();

        foreach (HealthGroup::cases() as $group) {
            $this->assertArrayHasKey(
                $group->labelKey(),
                $strings,
                $group->labelKey() . ' is built from HealthGroup::' . $group->name . ' but never defined.'
            );
        }
    }

    /**
     * The component's own en-GB strings.
     *
     * @return  array<string, string>
     *
     * @since   10.6.0
     */
    private static function englishStrings(): array
    {
        $file   = \dirname(__DIR__, 4) . '/admin/language/en-GB/en-GB.com_proclaim.ini';
        $parsed = parse_ini_file($file, false, \INI_SCANNER_RAW);

        self::assertIsArray($parsed, 'en-GB.com_proclaim.ini could not be parsed — this test would pass on nothing.');

        return $parsed;
    }
}
