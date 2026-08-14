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
use Joomla\Registry\Registry;
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

    /**
     * Section descriptors in the shape getSectionOrder() returns.
     *
     * @param  list<array{0: string, 1?: bool}>  $spec
     * @return list<object>
     * @since __DEPLOY_VERSION__
     */
    private static function sections(array $spec): array
    {
        return array_map(
            static fn (array $s) => (object) ['id' => $s[0], 'enabled' => $s[1] ?? true],
            $spec
        );
    }

    #[TestDox('each section\'s CSS is wrapped in its own selector')]
    public function testSectionCssIsScoped(): void
    {
        $params = new Registry([
            'teachers' . CwmcustomcssHelper::SECTION_PARAM_SUFFIX => 'a { color: red }',
            'series' . CwmcustomcssHelper::SECTION_PARAM_SUFFIX   => 'a { color: blue }',
        ]);

        $css = CwmcustomcssHelper::buildSectionCss($params, self::sections([['teachers'], ['series']]));

        // The point of the feature: a bare `a` rule cannot reach the other
        // section, because it only exists inside that section's block.
        $this->assertStringContainsString('[data-section="teachers"] {' . "\n" . 'a { color: red }', $css);
        $this->assertStringContainsString('[data-section="series"] {' . "\n" . 'a { color: blue }', $css);
    }

    #[TestDox('a section with no CSS contributes no selector')]
    public function testSectionWithoutCssIsSkipped(): void
    {
        $params = new Registry(['teachers' . CwmcustomcssHelper::SECTION_PARAM_SUFFIX => 'a { color: red }']);

        $css = CwmcustomcssHelper::buildSectionCss($params, self::sections([['teachers'], ['series']]));

        // An empty wrapper would be harmless but is still noise in the head.
        $this->assertStringNotContainsString('series', $css);
    }

    #[TestDox('a disabled section is not styled')]
    public function testDisabledSectionIsSkipped(): void
    {
        $params = new Registry(['teachers' . CwmcustomcssHelper::SECTION_PARAM_SUFFIX => 'a { color: red }']);

        $css = CwmcustomcssHelper::buildSectionCss($params, self::sections([['teachers', false]]));

        $this->assertSame('', $css);
    }

    /**
     * @return array<string, array{string}>
     * @since __DEPLOY_VERSION__
     */
    public static function unsafeSectionIdProvider(): array
    {
        return [
            'quote closes the attribute'  => ['te"] , * {'],
            'bracket closes the selector' => ['a] {'],
            'whitespace'                  => ['two words'],
            'uppercase'                   => ['Teachers'],
            'empty'                       => [''],
        ];
    }

    /**
     * landing_layout is JSON an administrator can hand-edit, and the id is
     * interpolated into an attribute selector — so anything that could end the
     * selector has to be refused rather than escaped.
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('unsafeSectionIdProvider')]
    #[TestDox('a section id that is not a plain slug is refused')]
    public function testUnsafeSectionIdIsRefused(string $id): void
    {
        $params = new Registry([$id . CwmcustomcssHelper::SECTION_PARAM_SUFFIX => 'a { color: red }']);

        $this->assertSame('', CwmcustomcssHelper::buildSectionCss($params, self::sections([[$id]])));
    }

    #[TestDox('a section sheet cannot break out of the style element either')]
    public function testSectionCssIsSanitised(): void
    {
        $params = new Registry([
            'teachers' . CwmcustomcssHelper::SECTION_PARAM_SUFFIX => 'a{}</style><script>alert(1)</script>',
        ]);

        $css = CwmcustomcssHelper::buildSectionCss($params, self::sections([['teachers']]));

        $this->assertStringNotContainsString('</style>', $css);
    }
}
