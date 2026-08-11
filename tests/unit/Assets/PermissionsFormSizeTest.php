<?php

/**
 * The permissions UI must stay small enough for PHP to accept the POST.
 *
 * 17 grids on one form posted 1,217 fields past PHP's default max_input_vars of
 * 1000; $_POST was truncated at request startup, Joomla's `task` went with it,
 * and the save silently did nothing (#1653).
 *
 * Field count is actions x a site's user groups, which a test cannot control --
 * a bound asserted against a three-group database passes vacuously. These
 * assert the structural invariants that keep the count small instead.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Assets;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @since __DEPLOY_VERSION__
 */
class PermissionsFormSizeTest extends TestCase
{
    /**
     * Repository root.
     *
     * @return  string
     * @since __DEPLOY_VERSION__
     */
    private static function root(): string
    {
        return \dirname(__DIR__, 3);
    }

    #[TestDox('the permissions form declares exactly one rules field')]
    public function testPermissionsFormHasASingleRulesField(): void
    {
        $file = self::root() . '/admin/forms/permissions.xml';

        $this->assertFileExists($file, 'The section permissions form is missing.');

        $xml = simplexml_load_file($file);

        $this->assertNotFalse($xml, 'admin/forms/permissions.xml is not parseable.');

        $rules = $xml->xpath('//field[@type="rules"]');

        $this->assertCount(
            1,
            $rules,
            'The permissions form must carry one grid per page: all of them at once posts '
            . 'more fields than PHP accepts by default, and the excess is discarded silently.'
        );
    }

    #[TestDox('the rules field names the asset_id field that carries the section asset')]
    public function testRulesFieldIsBoundToAnAssetField(): void
    {
        $xml   = simplexml_load_file(self::root() . '/admin/forms/permissions.xml');
        $rules = $xml->xpath('//field[@type="rules"]')[0];

        $assetField = (string) $rules['asset_field'];

        $this->assertNotSame(
            '',
            $assetField,
            'RulesField falls back to the component asset when asset_field is empty, so the grid '
            . "would show -- and save -- com_proclaim's own rules under a section label."
        );

        $this->assertNotEmpty(
            $xml->xpath('//field[@name="' . $assetField . '"]'),
            "The rules field names '{$assetField}', but the form declares no such field."
        );
    }

    #[TestDox('the Administration form carries no permissions grid')]
    public function testAdministrationFormCarriesNoRulesGrid(): void
    {
        $offences = [];

        foreach (['admin/forms/admin.xml', 'admin/tmpl/cwmadmin/edit.php', 'admin/src/Model/CwmadminModel.php'] as $path) {
            $file = self::root() . '/' . $path;

            if (!is_file($file)) {
                continue;
            }

            $code = (string) file_get_contents($file);

            if (preg_match('/rules_[a-z]+|type="rules"|\'rules\'\s*=>/i', $code)) {
                $offences[] = $path;
            }
        }

        $this->assertSame(
            [],
            $offences,
            'The Administration screen must not carry permissions grids -- they took the whole '
            . 'form past max_input_vars and its save began failing silently. They belong on '
            . 'admin/forms/permissions.xml, one section at a time.'
        );
    }
}
