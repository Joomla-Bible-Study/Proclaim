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

use CWM\Component\Proclaim\Administrator\Helper\CwmbibleVersionHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1484: BibleVersionField::getOptions() had ~120 lines
 * of DB query, provider-filtering, language grouping/sorting, and label
 * building inline in the field class. Extracted to CwmbibleVersionHelper,
 * with the pure grouping/sorting/labelling step (buildOptions()) further
 * isolated from the DB fetch so it can be tested with synthetic rows
 * rather than live DB content.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmbibleVersionHelperTest extends ProclaimTestCase
{
    /**
     * Invoke the private buildOptions() static method via reflection.
     *
     * @param   array<int, \stdClass>  $rows
     * @param   bool                   $servableOnly
     * @param   string[]               $enabledSources
     * @param   string                 $currentLang
     *
     * @return  array<int, array{value: string, text: string}>
     */
    private static function buildOptions(array $rows, bool $servableOnly, array $enabledSources, string $currentLang): array
    {
        $method = new \ReflectionMethod(CwmbibleVersionHelper::class, 'buildOptions');

        return $method->invoke(null, $rows, $servableOnly, $enabledSources, $currentLang);
    }

    /**
     * @param   string  $abbreviation
     * @param   string  $name
     * @param   string  $language
     * @param   bool    $installed
     * @param   string  $source
     *
     * @return  \stdClass
     */
    private static function row(string $abbreviation, string $name, string $language, bool $installed = true, string $source = 'getbible'): \stdClass
    {
        return (object) [
            'abbreviation' => $abbreviation,
            'name'         => $name,
            'language'     => $language,
            'installed'    => $installed ? 1 : 0,
            'source'       => $source,
        ];
    }

    public function testNativeLanguageVersionsSortBeforeOthersAndByName(): void
    {
        $rows = [
            self::row('zzz', 'Zulu Bible', 'en'),
            self::row('esv', 'English Standard Version', 'en'),
            self::row('lsg', 'Louis Segond', 'fr'),
        ];

        $options = self::buildOptions($rows, false, [], 'en');

        $this->assertSame(
            ['esv', 'zzz', 'lsg'],
            array_column($options, 'value'),
            'Native-language (en) versions must come first, sorted by name; other-language versions after'
        );
    }

    public function testNativeOptionLabelHasNoLanguageSuffix(): void
    {
        $rows = [self::row('esv', 'English Standard Version', 'en')];

        $options = self::buildOptions($rows, false, [], 'en');

        $this->assertSame('English Standard Version (ESV)', $options[0]['text']);
    }

    public function testOtherLanguageOptionLabelIncludesLanguageName(): void
    {
        $rows = [self::row('lsg', 'Louis Segond', 'fr')];

        $options = self::buildOptions($rows, false, [], 'en');

        $this->assertSame('Louis Segond (LSG) — French', $options[0]['text']);
    }

    public function testFirstOccurrenceWinsOnDuplicateAbbreviation(): void
    {
        $rows = [
            self::row('kjv', 'King James Version (first)', 'en'),
            self::row('kjv', 'King James Version (second)', 'en'),
        ];

        $options = self::buildOptions($rows, false, [], 'en');

        $this->assertCount(1, $options);
        $this->assertSame('King James Version (first) (KJV)', $options[0]['text']);
    }

    public function testServableOnlyExcludesUninstalledVersionsFromDisabledSources(): void
    {
        $rows = [
            self::row('kjv', 'King James Version', 'en', installed: true, source: 'getbible'),
            self::row('niv', 'New International Version', 'en', installed: false, source: 'api_bible'),
        ];

        $options = self::buildOptions($rows, true, ['getbible'], 'en');

        $this->assertSame(['kjv'], array_column($options, 'value'), 'niv must be excluded — not installed and its source is not enabled');
    }

    public function testServableOnlyIncludesInstalledVersionsRegardlessOfSource(): void
    {
        $rows = [
            self::row('niv', 'New International Version', 'en', installed: true, source: 'api_bible'),
        ];

        $options = self::buildOptions($rows, true, ['getbible'], 'en');

        $this->assertSame(['niv'], array_column($options, 'value'), 'Installed translations must be included even if their source is not in the enabled list');
    }

    public function testEmptyRowsFallBackToCommonDefaults(): void
    {
        $options = self::buildOptions([], false, [], 'en');

        // All fallback versions are English, so with $currentLang = 'en' they're
        // all "native" and sorted alphabetically by name (not declaration order).
        $this->assertSame(
            ['asv', 'kjv', 'web', 'ylt'],
            array_column($options, 'value'),
            'With no rows at all, the common fallback versions must be used, sorted by name — see the original getOptions() empty($versions) branch'
        );
    }

    public function testGetDefaultVersionFallsBackToKjv(): void
    {
        // The contract is: plugin default_version when configured, 'kjv'
        // otherwise. The plugin param genuinely varies by environment (may
        // be set on a local dev DB), so derive which branch applies and
        // assert THAT branch's exact outcome -- on CI's empty DB this always
        // exercises and exact-asserts the 'kjv' fallback, which the prior
        // assertNotEmpty() never actually pinned.
        try {
            $pluginDefault = \CWM\Library\Scripture\Helper\ScriptureParamsHelper::getParams()->get('default_version', '');
        } catch (\Exception) {
            $pluginDefault = '';
        }

        $this->assertSame(
            $pluginDefault !== '' ? $pluginDefault : 'kjv',
            CwmbibleVersionHelper::getDefaultVersion(),
            'Must return the configured plugin default, or exactly kjv when none is set'
        );
    }
}
