<?php

/**
 * Unit tests for CwmadminModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmadminModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1443: CwmadminController's legacy AJAX tools
 * (changePlayers(), changePopup(), mediaimages()) built and executed raw SQL
 * directly in the controller, each with its own per-row N+1 loop. That work
 * moved into CwmadminModel::changePlayer()/changePopup()/changeMediaImages(),
 * using the single-query batch REPLACE() pattern the newer XHR siblings
 * (changePlayersXHR(), changePopupXHR()) already proved out, and both the
 * legacy and XHR controller methods now delegate to the same model methods.
 *
 * mediaimages()'s four near-identical switch-case blocks collapsed into
 * changeMediaImages(), backed by the pure resolveMediaImageMatcher() decision
 * function tested directly here (no DB needed).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmadminModelTest extends ProclaimTestCase
{
    /**
     * Invoke the private static resolveMediaImageMatcher() via reflection.
     *
     * @param   object  $decoded
     *
     * @return  array{keys: string[], values: array<int, mixed>}|null
     */
    private static function resolveMatcher(object $decoded): ?array
    {
        $method = new \ReflectionMethod(CwmadminModel::class, 'resolveMediaImageMatcher');

        return $method->invoke(null, $decoded);
    }

    /**
     * Get the source body of a CwmadminModel method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testResolveMatcherButtonOnlyMode(): void
    {
        $decoded = (object) [
            'media_use_button_icon' => 1,
            'media_button_type'     => 'primary',
            'media_button_text'     => 'Watch',
        ];

        $this->assertSame(
            ['keys' => ['media_button_type', 'media_button_text'], 'values' => ['primary', 'Watch']],
            self::resolveMatcher($decoded)
        );
    }

    public function testResolveMatcherButtonAndIconMode(): void
    {
        $decoded = (object) [
            'media_use_button_icon' => 2,
            'media_button_type'     => 'primary',
            'media_icon_type'       => 'fa-play',
        ];

        $this->assertSame(
            ['keys' => ['media_button_type', 'media_icon_type'], 'values' => ['primary', 'fa-play']],
            self::resolveMatcher($decoded)
        );
    }

    public function testResolveMatcherIconOnlyMode(): void
    {
        $decoded = (object) [
            'media_use_button_icon' => 3,
            'media_icon_type'       => 'fa-play',
        ];

        $this->assertSame(
            ['keys' => ['media_icon_type'], 'values' => ['fa-play']],
            self::resolveMatcher($decoded)
        );
    }

    public function testResolveMatcherImageMode(): void
    {
        $decoded = (object) [
            'media_use_button_icon' => 0,
            'media_image'           => 'images/biblestudy/streamingvideo24.png',
        ];

        $this->assertSame(
            ['keys' => ['media_image'], 'values' => ['images/biblestudy/streamingvideo24.png']],
            self::resolveMatcher($decoded)
        );
    }

    public function testResolveMatcherUnknownModeReturnsNull(): void
    {
        $decoded = (object) ['media_use_button_icon' => 99];

        $this->assertNull(self::resolveMatcher($decoded));
    }

    /**
     * Regression test for the decode-mode bug bundled into #1443:
     * mediaimages() used to json_decode() the payload as an associative
     * array (2nd arg `true`) while every branch read it with `->` property
     * access. PHP's loose `==` makes `null == 0` true, so the switch always
     * silently fell into case 0 regardless of what was actually selected.
     * Passing a genuine object (as the fixed controller now decodes it)
     * through the real json_decode() path proves the matcher works against
     * real request payloads, not just hand-built stdClass fixtures.
     */
    public function testResolveMatcherWorksAgainstRealJsonDecodedObject(): void
    {
        $json    = '{"media_use_button_icon":2,"media_button_type":"primary","media_icon_type":"fa-play"}';
        $decoded = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            ['keys' => ['media_button_type', 'media_icon_type'], 'values' => ['primary', 'fa-play']],
            self::resolveMatcher($decoded)
        );
    }

    public function testChangePlayerUsesSingleBatchUpdateNotPerRowLoop(): void
    {
        $body = self::methodBody('changePlayer');

        $this->assertStringContainsString('REPLACE(', $body, 'changePlayer() must use a batch UPDATE ... REPLACE() — see #1443');
        $this->assertDoesNotMatchRegularExpression('/\bforeach\b/', $body, 'changePlayer() must not re-introduce a per-row N+1 loop — see #1443');
    }

    public function testChangePopupUsesBatchUpdatesNotPerRowLoop(): void
    {
        $body = self::methodBody('changePopup');

        $this->assertSame(2, substr_count($body, 'REPLACE('), 'changePopup() must issue at most two batch REPLACE() updates (plain value + legacy "100" alias) — see #1443');
        $this->assertDoesNotMatchRegularExpression('/\bforeach\b/', $body, 'changePopup() must not re-introduce a per-row N+1 loop — see #1443');
    }

    public function testChangeMediaImagesHasOneLoopNotFourSwitchCases(): void
    {
        $body = self::methodBody('changeMediaImages');

        $this->assertSame(1, substr_count($body, 'foreach ($images'), 'changeMediaImages() must collapse the four per-mode loops over $images into one — see #1443');
        $this->assertDoesNotMatchRegularExpression('/\bswitch\b/', $body, 'changeMediaImages() must not re-introduce the mediaimages() switch statement — see #1443');
    }

    /**
     * Invoke the private static mergeStoredParams().
     *
     * @param   array     $stored
     * @param   array     $submitted
     * @param   string[]  $formNames
     *
     * @return  array
     */
    private static function mergeStored(array $stored, array $submitted, array $formNames): array
    {
        $method = new \ReflectionMethod(CwmadminModel::class, 'mergeStoredParams');

        return $method->invoke(null, $stored, $submitted, $formNames);
    }

    /**
     * The bare param names the shipped settings form defines, read through the
     * model's own extraction against the real admin.xml.
     *
     * @return  string[]
     */
    private static function shippedFormParamNames(): array
    {
        $form = new \Joomla\CMS\Form\Form('com_proclaim.admin', ['control' => 'jform']);
        $form->loadFile(\dirname(__DIR__, 4) . '/admin/forms/admin.xml');

        $method = new \ReflectionMethod(CwmadminModel::class, 'formParamNames');

        return $method->invoke(null, $form);
    }

    public function testUnformedStoredParamSurvivesASave(): void
    {
        // #2134: health_quiet has no field in admin.xml, so it is absent from
        // every submission and a wholesale rewrite dropped it.
        $merged = self::mergeStored(
            ['simple_mode' => '1', 'health_quiet' => '{"content.legacy-servers":"4:222"}'],
            ['simple_mode' => '0'],
            ['simple_mode']
        );

        $this->assertSame(
            '{"content.legacy-servers":"4:222"}',
            $merged['health_quiet'] ?? null,
            'A stored param the form does not define must survive a save — see #2134.'
        );
        $this->assertSame('0', $merged['simple_mode'], 'The submitted value must still win.');
    }

    public function testClearingAFormFieldThatPostsNothingStillClearsIt(): void
    {
        // A `multiple` field with nothing selected posts no key at all. Carrying
        // the stored value across for a key the form DOES define would make
        // those fields impossible to clear — the bug a naive merge introduces.
        $merged = self::mergeStored(
            ['podcast' => ['3', '7'], 'simple_mode' => '1'],
            ['simple_mode' => '1'],
            ['podcast', 'simple_mode']
        );

        $this->assertArrayNotHasKey(
            'podcast',
            $merged,
            'A form-defined field that posts no key must clear, not resurrect its stored value.'
        );
    }

    public function testMergeOverwritesNumericStringKeysRatherThanRenumbering(): void
    {
        // array_merge() renumbers numeric-string keys; array_replace() does not.
        $merged = self::mergeStored(['9' => 'stored'], ['9' => 'submitted'], []);

        $this->assertSame(['9' => 'submitted'], $merged);
    }

    public function testFormParamNamesReadsBareNamesNotFieldIds(): void
    {
        // ⚠️ Form::getGroup() keys by field id (jform_params_podcast). Taking
        // array_keys() there would match no stored key, every stored key would
        // carry forward, and the clearable fields would silently stop clearing
        // — with the merge tests above still green.
        $names = self::shippedFormParamNames();

        $this->assertContains('simple_mode', $names, 'Bare field names expected, not jform_params_* ids.');
        $this->assertContains('podcast', $names);
        $this->assertNotContains('health_quiet', $names, 'health_quiet has no field — that is why #2134 happened.');

        foreach ($names as $name) {
            $this->assertStringStartsNotWith('jform', $name, "Field id leaked into the name list: {$name}");
        }
    }

    public function testShippedFormCoversEveryKeyTheInstallSqlSeeds(): void
    {
        // The seeded blob is what a fresh site stores. Any key in it that the
        // form does not define is one this carry-forward now has to preserve;
        // this records which those are rather than leaving it to chance.
        $names = self::shippedFormParamNames();

        $this->assertNotEmpty($names);
        $this->assertGreaterThan(40, \count($names), 'The settings form should define dozens of params.');
    }

    public function testSaveActuallyRoutesParamsThroughTheCarryForward(): void
    {
        // ⚠️ A source assertion on purpose, and the weakest test here. The
        // helpers are covered by executing tests, but nothing else notices if
        // save() stops calling them -- reverting that one line restores #2134
        // in full with the rest of this class still green. save() needs an
        // application, a form and the database, so this guards the call site;
        // the behaviour itself is verified against a real site.
        $body = self::methodBody('save');

        $this->assertStringContainsString(
            '$this->paramsToStore($data)',
            $body,
            'save() must build params through paramsToStore() or unformed keys are dropped again -- see #2134.'
        );
        $this->assertStringNotContainsString(
            '$params->loadArray($data[\'params\'])',
            $body,
            'save() must not load the submitted params directly -- that is the #2134 bug.'
        );
    }

    /**
     * Invoke the private static settingsRowId().
     *
     * @param   array  $data
     *
     * @return  int
     */
    private static function rowId(array $data): int
    {
        $method = new \ReflectionMethod(CwmadminModel::class, 'settingsRowId');

        return $method->invoke(null, $data);
    }

    public function testEmptyPostedIdStillResolvesTheSettingsRow(): void
    {
        // ⚠️ The edit form posts `id` as an empty string, so trusting it
        // resolved to row 0, the carry-forward was skipped, and #2134 stayed
        // reproducible while every other test here passed.
        $this->assertSame(1, self::rowId(['id' => '']), 'An empty posted id must fall back to the singleton row.');
        $this->assertSame(1, self::rowId(['id' => 0]));
        $this->assertSame(1, self::rowId([]), 'An absent id must fall back too.');
    }

    public function testARealPostedIdIsHonoured(): void
    {
        $this->assertSame(7, self::rowId(['id' => '7']));
    }
}
