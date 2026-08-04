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

    // =========================================================================
    // Regression tests for #1550, found during a Helper-folder-wide
    // bug-hunting review, tier 2 (external I/O + security surface, sequel
    // to #1537-#1542).
    // =========================================================================

    private static function invokeExtractFirstJsonObject(string $content): ?string
    {
        $method = new \ReflectionMethod(CwmaiHelper::class, 'extractFirstJsonObject');

        return $method->invoke(null, $content);
    }

    private static function invokeParseJsonResponse(string $content): array
    {
        $method = new \ReflectionMethod(CwmaiHelper::class, 'parseJsonResponse');

        return $method->invoke(null, $content);
    }

    /**
     * The old greedy regex (first '{' to the LAST '}' in the whole
     * response) swallowed trailing prose that itself contained a '}' --
     * a real risk here specifically because the system prompt repeatedly
     * discusses the literal '{scripture}...{/scripture}' tag syntax, so a
     * model (Claude has no JSON-mode constraint) adding a follow-up
     * remark referencing that syntax reintroduces a '}' after the actual
     * JSON object ends.
     */
    public function testExtractFirstJsonObjectStopsAtTheMatchingCloseBraceNotTheLastOne(): void
    {
        $content = '{"topics":["Faith"],"studyintro":"","studytext":"","chapters":[]}'
            . ' Note: use the {scripture} tag format described above.';

        $this->assertSame(
            '{"topics":["Faith"],"studyintro":"","studytext":"","chapters":[]}',
            self::invokeExtractFirstJsonObject($content)
        );
    }

    /**
     * A literal '}' inside a JSON string value is completely ordinary --
     * only '"' and '\' need escaping in JSON strings -- so the scan must
     * not treat it as closing the object early.
     */
    public function testExtractFirstJsonObjectIgnoresBracesInsideStringValues(): void
    {
        $content = '{"topics":[],"studyintro":"","studytext":"the set {1,2,3} was mentioned","chapters":[]}';

        $this->assertSame($content, self::invokeExtractFirstJsonObject($content));
    }

    /**
     * An escaped quote inside a string value must not be mistaken for the
     * end of the string (which would then miscount subsequent braces as
     * outside a string).
     */
    public function testExtractFirstJsonObjectHandlesEscapedQuotesInsideStrings(): void
    {
        $content = '{"topics":[],"studyintro":"He said \"hello\" to the {crowd}","studytext":"","chapters":[]}';

        $this->assertSame($content, self::invokeExtractFirstJsonObject($content));
    }

    public function testExtractFirstJsonObjectReturnsNullWhenNoBraceIsPresent(): void
    {
        $this->assertNull(self::invokeExtractFirstJsonObject('not json at all'));
    }

    /**
     * End-to-end: parseJsonResponse() must still successfully decode a
     * response with trailing prose containing a closing brace, instead of
     * corrupting the JSON and throwing "Invalid JSON".
     */
    public function testParseJsonResponseSurvivesTrailingProseContainingAClosingBrace(): void
    {
        $content = '{"topics":["Faith"],"studyintro":"Intro","studytext":"Text","chapters":[]}'
            . ' Remember to use the {scripture} tag format.';

        $result = self::invokeParseJsonResponse($content);

        $this->assertSame(['Faith'], $result['topics']);
        $this->assertSame('Intro', $result['studyintro']);
    }

    /**
     * Gemini's API key must travel via the x-goog-api-key header, not the
     * URL query string -- a URL-embedded key leaks into transport-failure
     * exception messages, gets echoed back to the browser by both AJAX
     * callers on error, and lands in the debug log whenever JBSMDEBUG is
     * on. Checked structurally since exercising the real HTTP call
     * requires live network access this suite doesn't perform.
     */
    public function testGeminiCallsSendTheApiKeyViaHeaderNotUrl(): void
    {
        foreach (['callGemini', 'fetchGeminiModels'] as $method) {
            $body = self::methodBody($method);

            $this->assertStringNotContainsString(
                '?key=',
                $body,
                $method . '() must not embed the API key in the URL query string -- see #1550'
            );
            $this->assertStringContainsString(
                "'x-goog-api-key'",
                $body,
                $method . '() must send the API key via the x-goog-api-key header -- see #1550'
            );
        }
    }

    /**
     * A prompt-level safety block returns HTTP 200 with a
     * promptFeedback.blockReason field and no 'candidates' key at all --
     * an architecturally different rejection path from a per-candidate
     * abnormal finishReason. Without a dedicated check, $content and
     * $finishReason both silently default to '' and parseJsonResponse('')
     * throws the raw "Invalid JSON —" error this class was already fixed
     * once to eliminate (#1495/#1443/#1444), for a different trigger.
     * Checked structurally: reproducing this requires a live Gemini
     * response this suite doesn't fetch.
     */
    public function testCallGeminiChecksForAMissingCandidatesKeyBeforeParsing(): void
    {
        $body = self::methodBody('callGemini');

        $this->assertStringContainsString(
            "isset(\$data['candidates'])",
            $body,
            'callGemini() must check for a missing candidates key (prompt-level block) -- see #1550'
        );
        $this->assertStringContainsString(
            "\$data['promptFeedback']['blockReason']",
            $body,
            'callGemini() must surface promptFeedback.blockReason for a prompt-level block -- see #1550'
        );
    }

    /**
     * getVideoContext() previously swallowed every exception from the
     * YouTube/Vimeo metadata fetchers with no logging at all, making a
     * transient network/API failure indistinguishable from "this video
     * genuinely has no metadata" -- syncFromYouTube() reports the same
     * generic message either way, with no diagnostic trail to tell them
     * apart. Checked structurally: reproducing a live fetch failure
     * requires network access this suite doesn't perform.
     */
    public function testGetVideoContextLogsTheExceptionInsteadOfSilentlySwallowingIt(): void
    {
        $body = self::methodBody('getVideoContext');

        $this->assertStringContainsString(
            'CwmDebug::error(',
            $body,
            'getVideoContext() must log the caught exception, not silently swallow it -- see #1550'
        );
    }

    /**
     * Every outbound HTTP call in this class previously had no timeout, so
     * a slow/hung provider could tie up the PHP worker handling the
     * synchronous AI-Assist AJAX request indefinitely. Checked
     * structurally across every method that calls the HTTP client.
     */
    public function testEveryHttpCallPassesTheSharedTimeoutConstant(): void
    {
        foreach (['fetchClaudeModels', 'fetchOpenAIModels', 'fetchGeminiModels', 'postJson', 'fetchYouTubeMetadata', 'fetchVimeoMetadata'] as $method) {
            $body = self::methodBody($method);

            $this->assertStringContainsString(
                'self::HTTP_TIMEOUT',
                $body,
                $method . '() must pass a timeout to its HTTP call -- see #1550'
            );
        }
    }
}
