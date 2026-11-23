<?php

/**
 * Unit tests for CwmscriptureLookupHelper
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\CwmscriptureLookupHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;

/**
 * Tests for the CwmscriptureLookupHelper class, extracted from
 * CwmscriptureController::getPassageXHR() as part of #1445.
 *
 * lookupPassage() drives real Bible provider I/O (remote APIs, DB-backed
 * local lookups whose seed data varies by environment), so full behavioral
 * coverage lives in the live dispatch test performed against j5-dev during
 * the #1445 investigation rather than here. These tests cover structure.
 *
 * @since  10.5.6
 */
class CwmscriptureLookupHelperTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmscriptureLookupHelper method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmscriptureLookupHelper::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * lookupPassage() is static, takes two strings and a Registry, and returns an
     * array.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testLookupPassageSignature(): void
    {
        $method = new \ReflectionMethod(CwmscriptureLookupHelper::class, 'lookupPassage');
        $this->assertTrue($method->isStatic());
        $this->assertReturnTypeName('array', $method);

        $params = $method->getParameters();
        $this->assertParamTypeName('string', $params[0]);
        $this->assertParamTypeName('string', $params[1]);
        $this->assertParamTypeName(Registry::class, $params[2]);
    }

    /**
     * lookupPassage() keeps its three-tier fallback chain.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testLookupPassagePreservesThreeTierFallbackChain(): void
    {
        $body = self::methodBody('lookupPassage');

        $this->assertStringContainsString('Fallback 1', $body, 'see #1445 — same version via local provider');
        $this->assertStringContainsString('Fallback 2', $body, 'see #1445 — admin default version via local provider');
        $this->assertStringContainsString('Fallback 3', $body, 'see #1445 — hard fallback to KJV');
    }

    /**
     * lookupPassage() catches a provider's exceptions and reports them.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testLookupPassageCatchesProviderExceptions(): void
    {
        $body = self::methodBody('lookupPassage');

        $this->assertStringContainsString('catch (\Throwable $e)', $body);
        $this->assertStringContainsString('COM_PROCLAIM_SCRIPTURE_AJAX_PROVIDER_ERROR', $body);
    }

    /**
     * lookupPassage() reports the translation it used on success, and on both
     * failure paths whether the failure is retryable.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testLookupPassageReturnsArrayContractForBothOutcomes(): void
    {
        $body = self::methodBody('lookupPassage');

        // Success branch
        $this->assertStringContainsString("'success'     => true,", $body);
        $this->assertStringContainsString("'translation' => \$usedVersion,", $body);

        // Failure branches (no-text and provider-exception) both report retryable
        $this->assertSame(2, substr_count($body, "'retryable' =>"), 'Both failure branches must report whether the failure is retryable — see #1445');
    }
}
