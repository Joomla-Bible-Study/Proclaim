<?php

/**
 * Contract test for the live GetBible v2 API.
 *
 * @package    Proclaim.ContractTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Contract\Scripture;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Does the response GetBible returns still look like the one we record?
 *
 * Every other scripture test runs against build/fixtures/getbible-kjv-colossians.json,
 * a capture of the real API, so the release gate never leaves the machine. That
 * trade buys speed and hermetic runs at the cost of one risk: the API changes
 * shape, the fixture goes on describing the old one, and the suite stays green
 * while the live path is broken for everyone.
 *
 * This is the one test that pays that cost back, with a single request. It is
 * deliberately in its own suite — "Scripture Contract Tests" is absent from the
 * composer test scripts — so it runs nightly rather than per commit.
 *
 * ⚠️ A network failure skips rather than fails. From here an unreachable host
 * and a decommissioned API look identical, and a runner with no egress must not
 * report the API as broken. What this asserts is the shape of a response that
 * did arrive.
 *
 * When it fails, re-capture and commit the fixture:
 *   php build/seed-scripture-fixture.php --refresh
 *
 * @since __DEPLOY_VERSION__
 */
class GetBibleContractTest extends TestCase
{
    /**
     * Query endpoint, kept in step with GetBibleProvider::API_BASE.
     */
    private const API_BASE = 'https://query.getbible.net/v2/';

    /**
     * Reference to probe. One request, and one the fixture already carries so
     * the live response and the recorded one can be compared directly.
     */
    private const PROBE_REFERENCE = 'Colossians 3:1-25';

    /**
     * Fetch the probe reference from the live API.
     *
     * @return  array|null  Decoded response, or null when the host is unreachable
     *
     * @since __DEPLOY_VERSION__
     */
    private function fetchLive(): ?array
    {
        $url = self::API_BASE . 'kjv/' . str_replace('%3A', ':', rawurlencode(self::PROBE_REFERENCE));
        $ch  = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_USERAGENT      => 'Proclaim scripture contract test',
        ]);

        $body   = curl_exec($ch);
        $errno  = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($errno !== 0) {
            $this->markTestSkipped('GetBible unreachable (curl errno ' . $errno . ') — no egress, or the host is down.');
        }

        $this->assertSame(200, $status, 'GetBible answered HTTP ' . $status . ' for ' . self::PROBE_REFERENCE);
        $this->assertIsString($body, 'GetBible returned no body.');

        $decoded = json_decode((string) $body, true);

        $this->assertIsArray($decoded, 'GetBible response is not JSON.');

        return $decoded;
    }

    /**
     * Read the committed capture the rest of the suite runs against.
     *
     * @return  array
     *
     * @since __DEPLOY_VERSION__
     */
    private function recorded(): array
    {
        $path = \dirname(__DIR__, 3) . '/build/fixtures/getbible-kjv-colossians.json';

        $this->assertFileExists($path, 'The scripture fixture is missing.');

        $fixture = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($fixture);
        $this->assertArrayHasKey(self::PROBE_REFERENCE, $fixture['responses'] ?? []);

        return $fixture['responses'][self::PROBE_REFERENCE];
    }

    /**
     * The keys GetBibleProvider reads must still be the keys GetBible sends.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('the live response still carries the fields the provider reads')]
    public function testLiveResponseKeepsTheProviderContract(): void
    {
        $live = $this->fetchLive();

        $this->assertNotEmpty($live, 'GetBible returned an empty response.');

        $passage = reset($live);

        $this->assertIsArray($passage, 'The response is no longer keyed objects.');

        // fetchByApiRef() iterates the response and reads exactly these.
        $this->assertArrayHasKey('verses', $passage, 'A passage no longer carries "verses".');
        $this->assertIsArray($passage['verses']);
        $this->assertNotEmpty($passage['verses'], 'The passage carries no verses.');

        $verse = $passage['verses'][0];

        $this->assertArrayHasKey('verse', $verse, 'A verse no longer carries "verse".');
        $this->assertArrayHasKey('text', $verse, 'A verse no longer carries "text".');
        $this->assertNotSame('', trim((string) $verse['text']), 'The first verse has no text.');
    }

    /**
     * The recorded fixture must still describe the same response.
     *
     * Compares structure and verse count rather than text: a translation's
     * wording does not move, but asserting it byte for byte would turn a
     * punctuation fix upstream into a failed release gate.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('the committed fixture still matches the live response shape')]
    public function testFixtureStillMatchesLive(): void
    {
        $live     = $this->fetchLive();
        $recorded = $this->recorded();

        $this->assertSame(
            array_keys($recorded),
            array_keys($live),
            'GetBible now keys the response differently — refresh the fixture.'
        );

        $livePassage     = reset($live);
        $recordedPassage = reset($recorded);

        $this->assertSame(
            array_keys($recordedPassage),
            array_keys($livePassage),
            'A passage carries different fields than the capture — refresh the fixture.'
        );

        $this->assertCount(
            \count($recordedPassage['verses']),
            $livePassage['verses'],
            'The verse count for ' . self::PROBE_REFERENCE . ' has changed — refresh the fixture.'
        );
    }
}
