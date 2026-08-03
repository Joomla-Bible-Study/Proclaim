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

use CWM\Component\Proclaim\Administrator\Helper\CwmaiHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression tests for the AI Assist "Invalid JSON" bug: the AI Assist
 * feature surfaced a raw "AI generation failed: Invalid JSON — ..." error
 * to the user instead of a friendly message. Root-caused via live testing
 * against the real Gemini API (j5-dev, gemini-pro-latest):
 *
 * 1. callClaude()/callGemini()/callOpenAI() only special-cased the single
 *    truncation-equivalent literal ('max_tokens'/'MAX_TOKENS'/'length').
 *    Any other abnormal completion reason (e.g. Gemini's RECITATION,
 *    reproduced live: empty content, 0 parts) fell through to
 *    parseJsonResponse(), which can only report a raw, unhelpful error.
 *    Fixed by extracting assertNormalFinish(), which allowlists each
 *    provider's known-normal reasons instead of denylisting one truncation
 *    literal.
 *
 * 2. Gemini's loose JSON mode (responseMimeType alone, no responseSchema)
 *    intermittently emitted syntactically invalid JSON — a stray trailing
 *    bracket/brace after a structurally complete object — even on a clean
 *    finishReason=STOP completion (reproduced live, ~25% of calls across
 *    two independent test batches). Adding responseSchema (constrained
 *    decoding) eliminated the malformation across 4/4 follow-up live calls.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmaiHelperTest extends ProclaimTestCase
{
    /**
     * Get the source body of a CwmaiHelper method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmaiHelper::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testCallGeminiSendsResponseSchema(): void
    {
        $body = self::methodBody('callGemini');

        $this->assertStringContainsString(
            "'responseSchema'",
            $body,
            'callGemini() must request constrained decoding to prevent malformed trailing-bracket JSON — see AI Assist investigation'
        );
    }

    public function testGeminiResponseSchemaShape(): void
    {
        $method = new \ReflectionMethod(CwmaiHelper::class, 'geminiResponseSchema');
        $schema = $method->invoke(null);

        $this->assertSame('object', $schema['type']);
        $this->assertSame(['topics', 'studyintro', 'studytext', 'chapters'], $schema['required']);
        $this->assertArrayHasKey('topics', $schema['properties']);
        $this->assertArrayHasKey('studyintro', $schema['properties']);
        $this->assertArrayHasKey('studytext', $schema['properties']);
        $this->assertArrayHasKey('chapters', $schema['properties']);
    }

    public function testProviderCallsDelegateFinishReasonCheckToSharedHelper(): void
    {
        foreach (['callClaude', 'callGemini', 'callOpenAI'] as $method) {
            $body = self::methodBody($method);

            $this->assertStringContainsString(
                'self::assertNormalFinish(',
                $body,
                $method . '() must delegate finish-reason validation to assertNormalFinish() — see AI Assist investigation'
            );

            $this->assertDoesNotMatchRegularExpression(
                "/===\s*'(max_tokens|MAX_TOKENS|length)'/",
                $body,
                $method . '() must not re-implement a single-literal truncation check inline'
            );
        }
    }

    /**
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function normalReasonProvider(): array
    {
        return [
            'claude end_turn'      => ['claude', 'end_turn'],
            'claude stop_sequence' => ['claude', 'stop_sequence'],
            'claude tool_use'      => ['claude', 'tool_use'],
            'gemini STOP'          => ['gemini', 'STOP'],
            'openai stop'          => ['openai', 'stop'],
            'claude empty/absent'  => ['claude', ''],
            'gemini empty/absent'  => ['gemini', ''],
            'openai empty/absent'  => ['openai', ''],
        ];
    }

    #[DataProvider('normalReasonProvider')]
    public function testAssertNormalFinishAllowsNormalReasons(string $provider, string $reason): void
    {
        $method = new \ReflectionMethod(CwmaiHelper::class, 'assertNormalFinish');
        $method->invoke(null, $provider, $reason);
        $this->addToAssertionCount(1);
    }

    /**
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function truncatedReasonProvider(): array
    {
        return [
            'claude max_tokens' => ['claude', 'max_tokens'],
            'gemini MAX_TOKENS' => ['gemini', 'MAX_TOKENS'],
            'openai length'     => ['openai', 'length'],
        ];
    }

    #[DataProvider('truncatedReasonProvider')]
    public function testAssertNormalFinishThrowsTruncatedMessageForTruncation(string $provider, string $reason): void
    {
        $method = new \ReflectionMethod(CwmaiHelper::class, 'assertNormalFinish');

        try {
            $method->invoke(null, $provider, $reason);
            $this->fail('Expected a RuntimeException for a truncated finish reason');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString(
                'Invalid JSON',
                $e->getMessage(),
                'Truncation must be reported with a friendly message, not surfaced as a raw parse failure'
            );
        }
    }

    /**
     * Regression case: Gemini's RECITATION reason (reproduced live against
     * the real API — empty content, 0 candidate parts) must not fall through
     * to parseJsonResponse(), which would otherwise throw a raw, unhelpful
     * "Invalid JSON — " error with an empty snippet.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function abnormalReasonProvider(): array
    {
        return [
            'gemini RECITATION'                    => ['gemini', 'RECITATION'],
            'gemini SAFETY'                        => ['gemini', 'SAFETY'],
            'gemini OTHER'                         => ['gemini', 'OTHER'],
            'claude refusal'                       => ['claude', 'refusal'],
            'claude pause_turn'                    => ['claude', 'pause_turn'],
            'claude model_context_window_exceeded' => ['claude', 'model_context_window_exceeded'],
        ];
    }

    #[DataProvider('abnormalReasonProvider')]
    public function testAssertNormalFinishThrowsForOtherAbnormalReasons(string $provider, string $reason): void
    {
        $method = new \ReflectionMethod(CwmaiHelper::class, 'assertNormalFinish');

        try {
            $method->invoke(null, $provider, $reason);
            $this->fail('Expected a RuntimeException for an abnormal finish reason');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString('Invalid JSON', $e->getMessage());
        }
    }

    /**
     * The unit test environment does not load component language files, so
     * Text::sprintf() falls back to returning the raw key unsubstituted —
     * this asserts the reason is wired into the translated string at the
     * source level instead, which is what actually matters in production.
     */
    public function testAssertNormalFinishInterpolatesReasonIntoAbnormalMessage(): void
    {
        $body = self::methodBody('assertNormalFinish');

        $this->assertStringContainsString(
            "Text::sprintf('JBS_CMN_AI_RESPONSE_ABNORMAL', \$reason)",
            $body,
            'The raw finish/stop reason must be interpolated into the abnormal-completion message for diagnosability'
        );
    }
}
