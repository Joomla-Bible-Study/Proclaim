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
}
