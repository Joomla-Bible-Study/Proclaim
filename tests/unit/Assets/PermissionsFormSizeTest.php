<?php

/**
 * The permissions UI must stay small enough for PHP to accept the POST.
 *
 * The first design rendered one `type="rules"` grid per section on the
 * Administration screen. Each grid emits `actions x user groups` selects, so on
 * a fourteen-group site the form carried 1,134 rule selects and posted 1,217
 * fields -- against PHP's default `max_input_vars` of 1000. PHP truncates
 * `$_POST` at request startup with only a warning to the error log; Joomla's
 * `task` field was among what fell off the end, so the request dispatched to
 * `display`, no controller ran, no message was queued, and nothing saved. The
 * screen looked like it worked. See #1653.
 *
 * Field count is `actions x groups`, and a test cannot control how many user
 * groups a site has -- asserting "under 1000" against a three-group test
 * database would prove nothing. So this asserts the structural invariants that
 * keep the count bounded instead: one grid per page, and none on the
 * Administration form.
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
            "The permissions form must carry one grid per page.\n"
            . 'One grid per section on a single page posts more fields than PHP accepts by '
            . 'default, and the excess is discarded silently.'
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
            "The Administration screen must not carry permissions grids.\n"
            . 'That form already posts ~110 fields; 17 grids took it past PHP\'s max_input_vars '
            . 'and the whole save began failing silently. Section permissions belong on '
            . 'admin/forms/permissions.xml, one section at a time.'
        );
    }
}
