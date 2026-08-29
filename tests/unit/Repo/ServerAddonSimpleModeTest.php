<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Simple Mode hides a server's advanced settings by reading `simplemode="hide"`
 * off the addon's own XML — on a fieldset, or on a single field where the
 * fieldset mixes advanced settings with ones a simple site still needs.
 *
 * The server form knows nothing about which addons exist, which is the point:
 * a third-party addon gets the same treatment. The cost is that a *new* addon
 * inherits nothing, and its button-styling settings would quietly reappear in
 * Simple Mode with nobody noticing.
 *
 * So this pins the convention rather than any one addon: every `media_type`
 * fieldset carries the same seven styling fields, and all seven have to be
 * hidden one way or the other.
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerAddonSimpleModeTest extends ProclaimTestCase
{
    /**
     * The button-appearance settings, hidden in Simple Mode for the same reason
     * their component-level counterparts are: choosing whether to offer media
     * is not the same decision as styling the button.
     *
     * @var  string[]
     * @since  __DEPLOY_VERSION__
     */
    private const STYLING_FIELDS = [
        'media_image',
        'media_use_button_icon',
        'media_button_text',
        'media_button_type',
        'media_button_color',
        'media_icon_type',
        'media_custom_icon',
    ];

    /**
     * @return  array<string, array{0: string}>
     * @since   __DEPLOY_VERSION__
     */
    public static function addonXmlProvider(): array
    {
        $root  = \dirname(__DIR__, 3) . '/admin/src/Addons/Servers';
        $cases = [];

        foreach (glob($root . '/*/*.xml') ?: [] as $file) {
            $xml = file_get_contents($file);

            // Only the ones that actually declare the styling fieldset.
            if ($xml === false || !str_contains($xml, '<fieldset name="media_type"')) {
                continue;
            }

            $cases[basename(\dirname($file)) . '/' . basename($file)] = [$file];
        }

        return $cases;
    }

    #[TestDox('every server addon hides its media button styling in Simple Mode')]
    #[\PHPUnit\Framework\Attributes\DataProvider('addonXmlProvider')]
    public function testStylingFieldsAreHiddenInSimpleMode(string $file): void
    {
        $xml = simplexml_load_file($file);

        $this->assertNotFalse($xml, 'Addon XML should parse: ' . $file);

        $hiddenSets = [];

        foreach ($xml->xpath('//fieldset[@simplemode="hide"]') ?: [] as $set) {
            $hiddenSets[] = (string) $set['name'];
        }

        foreach (self::STYLING_FIELDS as $name) {
            $nodes = $xml->xpath(\sprintf('//field[@name="%s"]', $name)) ?: [];

            if ($nodes === []) {
                // Not every addon offers every styling field.
                continue;
            }

            foreach ($nodes as $node) {
                $ownSet = self::owningFieldset($node);
                $hidden = (string) $node['simplemode'] === 'hide' || \in_array($ownSet, $hiddenSets, true);

                $this->assertTrue(
                    $hidden,
                    \sprintf(
                        '%s: field "%s" is media button styling and must be hidden in Simple Mode — mark the field '
                        . 'simplemode="hide", or its "%s" fieldset if every field in it is advanced.',
                        basename(\dirname($file)) . '/' . basename($file),
                        $name,
                        $ownSet ?: '(none)'
                    )
                );
            }
        }
    }

    /**
     * Name of the fieldset a field sits in, walking up rather than assuming.
     *
     * @param   \SimpleXMLElement  $node  The field element
     *
     * @return  string  Fieldset name, or '' when the field is not inside one
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function owningFieldset(\SimpleXMLElement $node): string
    {
        $parents = $node->xpath('ancestor::fieldset[@name][1]') ?: [];

        return $parents === [] ? '' : (string) $parents[0]['name'];
    }
}
