<?php

/**
 * Tests for the two JSON fallbacks whose silence had consequences.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression tests for #1558, tier B.
 *
 * Twenty-one `catch (\JsonException)` blocks across `Helper/` swallow the error
 * and substitute a fallback. Most are right to: nine are cache reads where a
 * corrupt entry is a miss, and logging every one would bury the signal. Two
 * were not.
 *
 * `CwmlocationHelper::getGroupMapping()` returns `[]`, which
 * `getUserLocations()` reads as "no locations are restricted" — so an
 * unreadable parameter opened every campus to every user without a word
 * anywhere. The fallback is still `[]`, because that is also the legitimate
 * "campuses not configured" state and failing closed would lock out every site
 * that has never used them. Only the silence was fixable.
 *
 * `CwmmigrationHelper::createGroupLocationMappings()` was worse in two ways at
 * once, and the second was found only by reading the code around the first.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmlocationHelper::class)]
#[CoversClass(CwmmigrationHelper::class)]
class SilentJsonFallbackTest extends ProclaimTestCase
{
    /**
     * Invoke the private getGroupMapping() against a params Registry.
     *
     * @param   mixed  $stored  Whatever is sitting in location_group_mapping.
     *
     * @return  array
     */
    private function mappingFor(mixed $stored): array
    {
        $method = new \ReflectionMethod(CwmlocationHelper::class, 'getGroupMapping');

        return $method->invoke(null, new Registry(['location_group_mapping' => $stored]));
    }

    /**
     * Clear the once-per-request warning latch between cases.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $flag = new \ReflectionProperty(CwmlocationHelper::class, 'mappingWarned');
        $flag->setValue(null, false);
    }

    #[TestDox('A readable mapping is returned as-is')]
    public function testReadableMappingSurvives(): void
    {
        $this->assertSame(
            ['4' => [11, 12]],
            $this->mappingFor('{"4":[11,12]}')
        );
    }

    #[TestDox('An unreadable mapping still returns [], because [] also means "not configured"')]
    public function testUnreadableMappingStillReturnsEmpty(): void
    {
        // The value, not the logging, is the contract here. Failing closed would
        // lock out every site that has never configured campuses — which cannot
        // be told apart from this case by return value alone.
        $this->assertSame([], $this->mappingFor('{"4":[11,12'));
    }

    #[TestDox('An empty mapping is indistinguishable from a corrupt one by value alone')]
    public function testEmptyAndCorruptAgreeOnValue(): void
    {
        // Pinned deliberately: it is the reason the fix had to be logging rather
        // than a different fallback. If these ever diverge, the fail-open
        // reasoning in getGroupMapping()'s docblock needs revisiting.
        $this->assertSame($this->mappingFor('{}'), $this->mappingFor('not json at all'));
    }

    #[TestDox('The warning latch fires once per request, not once per lookup')]
    public function testWarningIsLatched(): void
    {
        $flag = new \ReflectionProperty(CwmlocationHelper::class, 'mappingWarned');

        $this->assertFalse($flag->getValue(), 'setUp() should have cleared the latch');

        $this->mappingFor('{"4":[11,12');
        $this->assertTrue($flag->getValue(), 'A corrupt mapping must set the latch');

        // getUserLocations() has two call sites and runs per user per request;
        // without the latch a corrupt parameter would repeat the same line.
        $this->mappingFor('{"4":[11,12');
        $this->assertTrue($flag->getValue());
    }

    #[TestDox('A readable mapping never sets the warning latch')]
    public function testReadableMappingDoesNotWarn(): void
    {
        $this->mappingFor('{"4":[11,12]}');

        $flag = new \ReflectionProperty(CwmlocationHelper::class, 'mappingWarned');

        $this->assertFalse($flag->getValue(), 'Valid JSON must not report a failure');
    }

    #[TestDox('createGroupLocationMappings writes only its own key, never the whole params blob')]
    public function testMigrationWritesOnlyItsOwnParam(): void
    {
        // The defect this pins is not the JSON handling it was filed for.
        //
        // The method used to run its own UPDATE setting #__extensions.params to
        // json_encode($existing) — the mapping array alone, as the entire params
        // column. Every other Proclaim setting was replaced by it. Because the
        // write bypassed ComponentHelper's cache, nothing looked wrong until the
        // next request. migrateAccessToLocations() scenario 2A is the only
        // caller, so a multi-campus migration wiped the component configuration
        // as a side effect.
        //
        // Asserted on the source rather than by running it: the write path needs
        // a live #__extensions row and a cache controller, and what matters is
        // structural — that the raw UPDATE is gone and the merging writer is
        // used instead.
        $source = (string) file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Helper/CwmmigrationHelper.php'
        );

        $body = $this->methodBody($source, 'createGroupLocationMappings');

        $this->assertNotSame('', $body, 'Could not isolate createGroupLocationMappings()');

        $this->assertStringNotContainsString(
            '#__extensions',
            $body,
            'This method must not write #__extensions directly — that is what replaced the whole '
            . 'params column with the mapping array.'
        );

        $this->assertStringContainsString(
            'Cwmparams::setCompParams(',
            $body,
            'Params must be written through Cwmparams::setCompParams(), which merges into the stored '
            . 'Registry and clears the _system cache.'
        );
    }

    #[TestDox('createGroupLocationMappings refuses to merge when the stored mapping is unreadable')]
    public function testMigrationRefusesToMergeIntoNothing(): void
    {
        $source = (string) file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Helper/CwmmigrationHelper.php'
        );

        $body = $this->methodBody($source, 'createGroupLocationMappings');

        // The merge loop only adds keys it cannot already see, so starting from
        // [] writes back a mapping holding none of the administrator's manual
        // entries — precisely what the loop's own comment promises to preserve.
        $this->assertMatchesRegularExpression(
            '/catch \(\\\\JsonException [^)]*\)\s*\{[^}]*return;/s',
            $body,
            'An unreadable stored mapping must abort, not fall through to the merge with $existing = []'
        );

        $this->assertStringNotContainsString(
            '$existing = [];',
            $body,
            'Resetting $existing to [] on a decode failure is the data-loss path this test exists to stop'
        );
    }

    /**
     * Extract one method's body by brace matching, with comments removed.
     *
     * Comments have to go: both assertions below look for the absence of a
     * string, and the code they guard is explained by a comment that names the
     * very thing being asserted absent.
     *
     * @param   string  $source  File contents.
     * @param   string  $name    Method name.
     *
     * @return  string
     */
    private function methodBody(string $source, string $name): string
    {
        $source = $this->stripComments($source);

        $start = strpos($source, 'function ' . $name . '(');

        if ($start === false) {
            return '';
        }

        $open = strpos($source, '{', $start);

        if ($open === false) {
            return '';
        }

        $depth  = 0;
        $length = \strlen($source);

        for ($i = $open; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($source, $open, $i - $open + 1);
                }
            }
        }

        return '';
    }

    /**
     * Replace every comment with a space, leaving code and line structure intact.
     *
     * @param   string  $source  PHP source.
     *
     * @return  string
     */
    private function stripComments(string $source): string
    {
        $out = '';

        foreach (token_get_all($source) as $token) {
            if (\is_array($token) && \in_array($token[0], [\T_COMMENT, \T_DOC_COMMENT], true)) {
                // Keep the newlines so brace matching still lines up with the file.
                $out .= str_repeat("\n", substr_count($token[1], "\n"));

                continue;
            }

            $out .= \is_array($token) ? $token[1] : $token;
        }

        return $out;
    }
}
