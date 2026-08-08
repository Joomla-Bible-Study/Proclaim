<?php

/**
 * Integration tests for the analytics opt-out signals and the consent gate.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1613.
 *
 * 1. isOptedOut() honoured only DNT, which is defunct -- the W3C discontinued
 *    the specification, Safari and Firefox removed the setting, and Chrome
 *    never sent it by default. Sec-GPC (Global Privacy Control) is its working
 *    successor, sent by Firefox, Brave and DuckDuckGo and recognised under
 *    CCPA/CPRA. It was not honoured at all.
 *
 * 2. The outbound_click branch assigned referrer_url *outside* the $consentOn
 *    gate, so a visitor who had signalled opt-out still had a destination URL
 *    and timestamp recorded. On a default install (referrer mode 'type') this
 *    was the only path that wrote to that column at all.
 *
 * 3. The opt-out cookie is Proclaim's published integration contract for
 *    whatever consent manager the site already runs -- Proclaim ships no
 *    banner of its own. It must therefore have a stable, discoverable name
 *    rather than a string literal buried in a conditional.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmanalyticsHelper::class)]
class CwmanalyticsConsentTest extends IntegrationTestCase
{
    /** @var array<string, string|null> */
    private array $savedServer = [];

    private mixed $savedCookie = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['HTTP_SEC_GPC', 'HTTP_DNT'] as $key) {
            $this->savedServer[$key] = $_SERVER[$key] ?? null;
            unset($_SERVER[$key]);
        }

        $this->savedCookie = $_COOKIE[CwmanalyticsHelper::OPTOUT_COOKIE] ?? null;
        unset($_COOKIE[CwmanalyticsHelper::OPTOUT_COOKIE]);
    }

    protected function tearDown(): void
    {
        foreach ($this->savedServer as $key => $value) {
            if ($value === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $value;
            }
        }

        if ($this->savedCookie === null) {
            unset($_COOKIE[CwmanalyticsHelper::OPTOUT_COOKIE]);
        } else {
            $_COOKIE[CwmanalyticsHelper::OPTOUT_COOKIE] = $this->savedCookie;
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- Sec-GPC is honoured
    // -------------------------------------------------------------------------

    #[TestDox('A Sec-GPC: 1 header opts the visitor out')]
    public function testGlobalPrivacyControlIsHonoured(): void
    {
        $_SERVER['HTTP_SEC_GPC'] = '1';

        $this->assertTrue(
            CwmanalyticsHelper::isOptedOut(),
            'GPC is the live successor to DNT and is recognised under CCPA — see #1613'
        );
    }

    #[TestDox('DNT: 1 still opts the visitor out')]
    public function testDoNotTrackStillHonoured(): void
    {
        $_SERVER['HTTP_DNT'] = '1';

        $this->assertTrue(CwmanalyticsHelper::isOptedOut(), 'DNT support must not regress');
    }

    #[TestDox('The published opt-out cookie opts the visitor out')]
    public function testOptOutCookieIsHonoured(): void
    {
        $_COOKIE[CwmanalyticsHelper::OPTOUT_COOKIE] = '1';

        $this->assertTrue(CwmanalyticsHelper::isOptedOut());
    }

    /**
     * Only the exact opt-out value counts. A header that is merely present, or
     * carries GPC's "tracking permitted" value, must not be read as an
     * objection — that would silently disable the personal-data tier for
     * visitors who never asked for it.
     *
     * @param   string  $header  Header name
     * @param   string  $value   Header value
     */
    #[DataProvider('nonOptOutSignalProvider')]
    #[TestDox('A non-opt-out signal value does not opt the visitor out')]
    public function testNonOptOutSignalsAreIgnored(string $header, string $value): void
    {
        $_SERVER[$header] = $value;

        $this->assertFalse(
            CwmanalyticsHelper::isOptedOut(),
            'Only the literal opt-out value may count as an objection'
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function nonOptOutSignalProvider(): array
    {
        return [
            'GPC explicitly zero' => ['HTTP_SEC_GPC', '0'],
            'GPC empty'           => ['HTTP_SEC_GPC', ''],
            'DNT explicitly zero' => ['HTTP_DNT', '0'],
            'DNT unspecified'     => ['HTTP_DNT', 'unspecified'],
        ];
    }

    #[TestDox('With no signal at all the visitor is not opted out')]
    public function testNoSignalMeansNotOptedOut(): void
    {
        $this->assertFalse(CwmanalyticsHelper::isOptedOut());
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- the outbound_click bypass
    // -------------------------------------------------------------------------

    /**
     * logEvent() needs a full application/session stack to run end to end, so
     * the gate is pinned structurally: every assignment to $referrerUrl must
     * sit behind $consentOn.
     */
    #[TestDox('Every referrer_url assignment is behind the consent gate')]
    public function testOutboundClickIsGatedOnConsent(): void
    {
        $ref   = new \ReflectionMethod(CwmanalyticsHelper::class, 'logEvent');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        // Find the outbound_click branch and confirm it tests $consentOn.
        $this->assertMatchesRegularExpression(
            '/if \(\s*\$consentOn\s*&&\s*\$type === \'outbound_click\'/',
            $body,
            'An opted-out visitor still had a destination URL recorded — see #1613'
        );

        // Nothing may assign $referrerUrl from an ungated branch. Every
        // occurrence should be inside a $consentOn-guarded block.
        preg_match_all('/^\s*if \((.+)\)\s*\{/m', $body, $conditions);
        $ungated = array_filter(
            $conditions[1],
            static fn (string $c): bool => str_contains($c, 'outbound_click') && !str_contains($c, '$consentOn')
        );

        $this->assertSame([], array_values($ungated), 'No outbound_click branch may bypass the consent gate');
    }

    /**
     * Locate the component language file.
     *
     * JPATH_ROOT is '.' under phpunit.xml, so this resolves against the repo
     * layout; the installed layout is checked too. Probed with is_file()
     * rather than a file_get_contents() fallback chain, because the failed
     * first read emits a warning and phpunit.xml sets failOnWarning.
     *
     * @return  string  File contents
     */
    private function languageIni(): string
    {
        $candidates = [
            JPATH_ROOT . '/admin/language/en-GB/en-GB.com_proclaim.ini',
            JPATH_ROOT . '/administrator/components/com_proclaim/language/en-GB/en-GB.com_proclaim.ini',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return (string) file_get_contents($path);
            }
        }

        $this->fail('Could not locate en-GB.com_proclaim.ini in: ' . implode(', ', $candidates));
    }

    // -------------------------------------------------------------------------
    // Finding 3 -- the integration contract
    // -------------------------------------------------------------------------

    #[TestDox('The opt-out cookie name is published as a stable constant')]
    public function testOptOutCookieNameIsPublished(): void
    {
        $this->assertSame(
            'proclaim_analytics_optout',
            CwmanalyticsHelper::OPTOUT_COOKIE,
            'Consent managers integrate against this name — changing it breaks every site that wired it up'
        );
    }

    /**
     * The admin-facing help text previously told administrators that visitors
     * could opt out via this cookie, while nothing in the product set it and
     * the contract was undocumented. It must now tell admins to wire up their
     * own consent extension instead.
     */
    #[TestDox('Admin help text describes the cookie as a consent-manager integration')]
    public function testAdminHelpTextDocumentsTheIntegration(): void
    {
        $ini = $this->languageIni();

        preg_match('/^JBS_ANA_GDPR_OPTOUT_DESC="(.*)"$/m', $ini, $m);
        $this->assertNotEmpty($m, 'Expected JBS_ANA_GDPR_OPTOUT_DESC to be present');

        $this->assertStringContainsString(
            'Sec-GPC',
            $m[1],
            'The help text must mention the signal that actually works'
        );
        $this->assertStringContainsString(
            'cookie-consent extension',
            $m[1],
            'Admins must be told to wire their own consent banner to the cookie — Proclaim ships none'
        );
    }

    #[TestDox('GDPR Compliance Mode discloses that it also suppresses analytics')]
    public function testGdprModeDisclosesItsAnalyticsEffect(): void
    {
        $ini = $this->languageIni();

        preg_match('/^JBS_ADM_GDPR_MODE_DESC\s*=\s*"(.*)"$/m', $ini, $m);
        $this->assertNotEmpty($m, 'Expected JBS_ADM_GDPR_MODE_DESC to be present');

        $this->assertStringContainsString(
            'analytics',
            strtolower($m[1]),
            'gdpr_mode silently gated the analytics personal-data tier while documenting only outbound API calls — see #1613'
        );
    }
}
