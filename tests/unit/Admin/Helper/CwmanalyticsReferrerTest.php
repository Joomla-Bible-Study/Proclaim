<?php

/**
 * Unit tests for referrer capture and the retired retention setting.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1612 and #1609.
 *
 * #1612: the referrer was stored verbatim, query string included. That string
 * is written by the referring site and routinely carries search terms, session
 * tokens, addresses from newsletter links and reset tokens -- none of it
 * analytics data, and none of it ours. Expiring the column later does not help;
 * what is never stored cannot leak.
 *
 * #1609: `analytics_retention_days` was rendered in Proclaim's settings and read
 * by nothing. Retention lives on the purge task, which is opt-in; a component
 * field defaulting to 90 days implied a limit that was never applied.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmanalyticsHelper::class)]
class CwmanalyticsReferrerTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function urlProvider(): array
    {
        return [
            'query string dropped' => [
                'https://example.org/page?token=secret&email=a@b.c',
                'https://example.org/page',
            ],
            'fragment dropped' => [
                'https://example.org/page#section',
                'https://example.org/page',
            ],
            'query and fragment dropped' => [
                'https://example.org/page?q=how+do+i+leave+my+church#results',
                'https://example.org/page',
            ],
            'clean url unchanged' => [
                'https://example.org/sermons/grace',
                'https://example.org/sermons/grace',
            ],
            'root path preserved' => [
                'https://example.org/',
                'https://example.org/',
            ],
            'bare host keeps no path' => [
                'https://example.org',
                'https://example.org',
            ],
            'scheme preserved' => [
                'http://example.org/page?a=1',
                'http://example.org/page',
            ],
            'non-standard port preserved' => [
                'https://example.org:8443/page?a=1',
                'https://example.org:8443/page',
            ],
            'search engine query dropped' => [
                'https://www.google.com/search?q=baptism+sermon&client=firefox',
                'https://www.google.com/search',
            ],
            'no host is not a source' => ['/relative/path?a=1', null],
            'mailto is not a source'  => ['mailto:someone@example.org', null],
            'empty string'            => ['', null],
            'garbage'                 => ['http://', null],
        ];
    }

    /**
     * @param   string       $input     URL as received.
     * @param   string|null  $expected  What may be stored.
     */
    #[DataProvider('urlProvider')]
    #[TestDox('A URL is reduced to scheme, host and path')]
    public function testStripUrlParameters(string $input, ?string $expected): void
    {
        $this->assertSame($expected, CwmanalyticsHelper::stripUrlParameters($input));
    }

    #[TestDox('Stripping is idempotent, so the migration can run more than once')]
    public function testStrippingIsIdempotent(): void
    {
        $once  = CwmanalyticsHelper::stripUrlParameters('https://example.org/page?a=1#b');
        $twice = CwmanalyticsHelper::stripUrlParameters((string) $once);

        $this->assertSame($once, $twice);
    }

    #[TestDox('A stripped URL never exceeds the column width')]
    public function testResultFitsTheColumn(): void
    {
        $long = 'https://example.org/' . str_repeat('a', 4000);

        $this->assertLessThanOrEqual(2048, \strlen((string) CwmanalyticsHelper::stripUrlParameters($long)));
    }

    /**
     * logEvent() needs a full application and session stack to run end to end,
     * so this is pinned structurally the way the consent gate is in
     * CwmanalyticsConsentTest: no assignment to $referrerUrl may store a raw URL.
     */
    #[TestDox('Every referrer_url assignment strips parameters first')]
    public function testNoAssignmentStoresARawUrl(): void
    {
        $ref   = new \ReflectionMethod(CwmanalyticsHelper::class, 'logEvent');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        preg_match_all('/\$referrerUrl\s*=\s*(.+);/', $body, $matches);

        $assignments = array_filter(
            $matches[1],
            static fn (string $rhs): bool => trim($rhs) !== 'null'
        );

        $this->assertNotEmpty($assignments, 'The referrer capture should still exist');

        foreach ($assignments as $rhs) {
            $this->assertStringContainsString(
                'stripUrlParameters',
                $rhs,
                'A referrer or outbound URL was stored without dropping its query string — see #1612: ' . trim($rhs)
            );
        }
    }

    #[TestDox("Proclaim's settings no longer offer a retention period that nothing applies")]
    public function testRetentionSettingIsGone(): void
    {
        $form = file_get_contents(\dirname(__DIR__, 4) . '/admin/forms/admin.xml');

        $this->assertStringNotContainsString(
            'analytics_retention_days',
            (string) $form,
            'Retention lives on the purge task; a second field here applied to nothing — see #1609'
        );
    }

    #[TestDox('The purge task still owns a retention period')]
    public function testTaskRetainsItsOwnSetting(): void
    {
        $form = file_get_contents(
            \dirname(__DIR__, 4) . '/plugins/task/proclaim/forms/analytics.xml'
        );

        $this->assertStringContainsString(
            'name="retention_days"',
            (string) $form,
            'Removing the component field must not leave retention unconfigurable'
        );
    }
}
