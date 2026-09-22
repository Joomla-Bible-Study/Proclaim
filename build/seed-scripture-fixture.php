<?php

/**
 * Put real scripture data on the test site so the front end has verses to render.
 *
 * #__bsms_bible_verses is empty on a fresh install: the catalogue lists what is
 * on offer, but nothing carries data until the Download Core Translations task
 * has run. A seeded study therefore cites a book that resolves to nothing, and
 * the sermon page the front-end check requests exercises only the unresolvable
 * path — never the one a site with a translation actually takes.
 *
 * Seeds two things from one committed capture:
 *
 *   - #__bsms_bible_verses, so the Local provider can serve any sub-range;
 *   - #__bsms_scripture_cache, so the GetBible provider returns on its cache
 *     read and never reaches the network. getPassageFor() consults the cache
 *     before it builds a URL, so the real provider code runs against real
 *     recorded data.
 *
 * ⚠️ The capture is committed rather than downloaded. CI has no network, and a
 * fixture that reaches query.getbible.net would fail the release gate for
 * reasons that have nothing to do with the build. It also keeps the API hit
 * count at zero per run: drift is caught by the nightly contract test
 * (tests/integration/Scripture/GetBibleContractTest.php), not by every install.
 *
 *   php build/seed-scripture-fixture.php             # seed the role=test installs
 *   php build/seed-scripture-fixture.php --refresh   # re-capture from GetBible
 *
 * Refresh is the only mode that talks to the API, and it writes the fixture
 * rather than any database. Run it when the contract test reports the response
 * shape has moved.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

/**
 * Where the committed capture lives, and where --refresh writes back to.
 */
const FIXTURE_FILE = '/build/fixtures/getbible-kjv-colossians.json';

/**
 * GetBible v2 query endpoint. Only --refresh uses it.
 */
const GETBIBLE_BASE = 'https://query.getbible.net/v2/';

/**
 * How long a seeded cache row stays live. readCache() discards anything whose
 * expires_at has passed, so a short window would make the fixture stop working
 * partway through a long test run.
 */
const CACHE_TTL = '+10 years';

$refresh = \in_array('--refresh', \array_slice($argv, 1), true);

/**
 * Render verses the way GetBibleProvider renders them, so a seeded cache row is
 * byte-identical to one the provider would have written itself.
 *
 * @param   array  $verses  Verse rows as the API returns them
 *
 * @return  string
 *
 * @since __DEPLOY_VERSION__
 */
function renderPassage(array $verses): string
{
    $text = '';

    foreach ($verses as $verse) {
        $text .= '<sup>' . htmlspecialchars((string) ($verse['verse'] ?? '')) . '</sup>'
            . htmlspecialchars(trim((string) ($verse['text'] ?? ''))) . ' ';
    }

    return trim($text);
}

/**
 * Pull one reference from GetBible.
 *
 * @param   string  $reference  Reference in the form the API expects
 *
 * @return  array|null  Decoded response, or null when the call did not succeed
 *
 * @since __DEPLOY_VERSION__
 */
function captureReference(string $reference): ?array
{
    $url = GETBIBLE_BASE . 'kjv/' . str_replace('%3A', ':', rawurlencode($reference));
    $ch  = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_USERAGENT      => 'Proclaim scripture fixture capture',
    ]);

    $body   = curl_exec($ch);
    $errno  = curl_errno($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($errno !== 0 || $status !== 200 || !\is_string($body)) {
        fwrite(STDERR, "  FAIL {$reference}: curl errno {$errno}, HTTP {$status}\n");

        return null;
    }

    $decoded = json_decode($body, true);

    return \is_array($decoded) ? $decoded : null;
}

$fixturePath = $root . FIXTURE_FILE;

if ($refresh) {
    if (!is_file($fixturePath)) {
        fwrite(STDERR, "No fixture at {$fixturePath} to refresh.\n");

        exit(1);
    }

    $fixture = json_decode((string) file_get_contents($fixturePath), true);

    if (!\is_array($fixture) || empty($fixture['responses'])) {
        fwrite(STDERR, "Fixture is unreadable — refusing to overwrite it.\n");

        exit(1);
    }

    echo "=== refreshing " . \count($fixture['responses']) . " reference(s) from GetBible ===\n";

    $captured = [];

    foreach (array_keys($fixture['responses']) as $reference) {
        $response = captureReference((string) $reference);

        if ($response === null) {
            fwrite(STDERR, "Refresh aborted — the fixture on disk is unchanged.\n");

            exit(1);
        }

        $captured[$reference] = $response;
        echo "  OK   {$reference}\n";
    }

    $fixture['responses'] = $captured;
    $fixture['_captured'] = date('Y-m-d');

    file_put_contents(
        $fixturePath,
        json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );

    echo "Fixture rewritten: " . FIXTURE_FILE . "\n";

    exit(0);
}

if (!is_file($fixturePath)) {
    fwrite(STDERR, "No scripture fixture at {$fixturePath}.\n");
    fwrite(STDERR, "Capture one: php build/seed-scripture-fixture.php --refresh\n");

    exit(1);
}

$fixture = json_decode((string) file_get_contents($fixturePath), true);

if (!\is_array($fixture) || empty($fixture['responses'])) {
    fwrite(STDERR, "Scripture fixture is empty or unreadable: {$fixturePath}\n");

    exit(1);
}

$abbr = (string) ($fixture['translation'] ?? 'kjv');
$book = (int) ($fixture['book'] ?? 0);

// Flatten the captured chapters into verse rows, and keep each reference's
// rendered text for the cache.
$verseRows  = [];
$cacheEntry = [];

foreach ($fixture['responses'] as $reference => $response) {
    foreach ((array) $response as $passage) {
        if (!\is_array($passage) || empty($passage['verses'])) {
            continue;
        }

        $cacheEntry[(string) $reference] = renderPassage($passage['verses']);

        foreach ($passage['verses'] as $verse) {
            $verseRows[] = [
                (int) ($passage['chapter'] ?? $verse['chapter'] ?? 0),
                (int) ($verse['verse'] ?? 0),
                (string) ($verse['text'] ?? ''),
            ];
        }
    }
}

if ($verseRows === []) {
    fwrite(STDERR, "Scripture fixture carries no verses — nothing to seed.\n");

    exit(1);
}

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to seed.\n");

    // Not exit(0): a seed with no target seeded nothing, and the front-end
    // check that depends on it would then fail for an unrelated-looking reason.
    exit(1);
}

$failures = 0;

foreach ($installs as $install) {
    echo "=== seed scripture fixture on {$install->id} ({$install->path}) ===\n";

    try {
        $site = TestSite::fromPath($install->path);
        $db   = $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  FAIL ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    $verses       = $site->table('#__bsms_bible_verses');
    $translations = $site->table('#__bsms_bible_translations');
    $cache        = $site->table('#__bsms_scripture_cache');

    if (!$site->hasTable($verses) || !$site->hasTable($translations)) {
        fwrite(STDERR, "  FAIL the bible tables are missing — is lib_cwmscripture installed?\n");
        $failures++;

        continue;
    }

    // Add-only. A site that has downloaded real translations keeps exactly what
    // it has; this exists to guarantee a floor, not to impose a fixture.
    $existing = (int) $db->query("SELECT COUNT(*) FROM `{$verses}`")->fetchColumn();

    if ($existing > 0) {
        echo "  verse data already present ({$existing} verse(s)) — left alone\n";

        continue;
    }

    $insert = $db->prepare(
        "INSERT INTO `{$verses}` (`translation`, `book`, `chapter`, `verse`, `text`) VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($verseRows as [$chapter, $verse, $text]) {
        $insert->execute([$abbr, $book, $chapter, $verse, $text]);
    }

    $db->prepare(
        "UPDATE `{$translations}` SET `installed` = 1, `verse_count` = ? WHERE `abbreviation` = ?"
    )->execute([\count($verseRows), $abbr]);

    echo '  seeded ' . \count($verseRows) . " {$abbr} verses (book {$book})\n";

    if (!$site->hasTable($cache)) {
        echo "  provider cache table absent — skipped\n";

        continue;
    }

    $expires = (new DateTimeImmutable())->modify(CACHE_TTL)->format('Y-m-d H:i:s');
    $now     = (new DateTimeImmutable())->format('Y-m-d H:i:s');

    $cacheInsert = $db->prepare(
        "INSERT INTO `{$cache}` (`provider`, `translation`, `reference`, `text`, `copyright`, `created_at`, `expires_at`)
         VALUES ('getbible', ?, ?, ?, '', ?, ?)"
    );

    foreach ($cacheEntry as $reference => $text) {
        $cacheInsert->execute([$abbr, $reference, $text, $now, $expires]);
    }

    echo '  seeded ' . \count($cacheEntry) . " getbible cache row(s)\n";
}

exit($failures > 0 ? 1 : 0);
