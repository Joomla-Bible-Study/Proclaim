<?php

/**
 * Administrator-authored CSS must reach the page without being able to leave the
 * `<style>` element it is placed in.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\CwmcustomcssHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmcustomcssHelperTest extends ProclaimTestCase
{
    /**
     * Ways a browser's tokeniser will accept the end of a style element.
     *
     * @return  array<string, array{string}>
     * @since __DEPLOY_VERSION__
     */
    public static function breakoutProvider(): array
    {
        return [
            'plain'          => ['a{}</style><script>alert(1)</script>'],
            'uppercase'      => ['a{}</STYLE><script>alert(1)</script>'],
            'mixed case'     => ['a{}</StYlE><script>alert(1)</script>'],
            'space before'   => ['a{}< /style><script>alert(1)</script>'],
            'space after'    => ['a{}</ style><script>alert(1)</script>'],
            'tab inside'     => ["a{}<\t/style><script>alert(1)</script>"],
            'newline inside' => ["a{}</\nstyle><script>alert(1)</script>"],
        ];
    }

    #[DataProvider('breakoutProvider')]
    #[TestDox('a closing style tag cannot survive sanitising')]
    public function testClosingStyleTagIsNeutralised(string $css): void
    {
        $out = CwmcustomcssHelper::sanitise($css);

        $this->assertDoesNotMatchRegularExpression(
            '#<\s*/\s*style#i',
            $out,
            'The stored CSS could end the <style> element, turning styling into markup injection.'
        );
    }

    #[TestDox('ordinary CSS is left alone')]
    public function testOrdinaryCssSurvives(): void
    {
        $css = ".proclaim-list { color: #333; }\n.proclaim-list a:hover { text-decoration: underline; }";

        $this->assertSame($css, CwmcustomcssHelper::sanitise($css));
    }

    #[TestDox('url() is preserved: fetching a remote resource is inherent to CSS')]
    public function testUrlIsNotStripped(): void
    {
        // Deliberate. Anyone able to save this can already put arbitrary markup
        // in a template override; stripping url() would break legitimate use
        // (web fonts, background images) without removing any capability.
        $css = ".proclaim-hero { background-image: url('https://example.org/hero.png'); }";

        $this->assertSame($css, CwmcustomcssHelper::sanitise($css));
    }

    #[TestDox('an empty or whitespace-only sheet yields nothing')]
    public function testEmptySheetYieldsNothing(): void
    {
        $this->assertSame('', CwmcustomcssHelper::sanitise(''));
        $this->assertSame('', CwmcustomcssHelper::sanitise("  \n\t "));
    }

    #[TestDox('both levels store under the same param name')]
    public function testParamNameIsShared(): void
    {
        // The component sheet lives in #__bsms_admin.params and the template
        // sheet in #__bsms_templates.params; sharing the key is what lets the
        // helper read them the same way.
        $this->assertSame('custom_css', CwmcustomcssHelper::PARAM);
    }
}
