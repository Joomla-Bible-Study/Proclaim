<?php

/**
 * Fetch the seeded site menu items and assert the front end actually rendered.
 *
 * Everything else in the release gate stops at the install boundary: the
 * package registers, the migrations land, the API answers. None of it loads a
 * page, so a build can pass the whole gate and still show a site owner an empty
 * box (#1701).
 *
 * The assertions are chosen against what 10.5.7 shipped, because those two bugs
 * are the shape this check exists to catch:
 *
 *   - The landing page's Books section died with a database error on MySQL and
 *     rendered nothing. So a 200 is not enough — the section has to be present
 *     and it has to contain the book the seeded study actually cites.
 *   - Book names resolved to raw language keys ("JBS_BBK_JOHN 3") wherever
 *     com_proclaim's language file had not been loaded. So any JBS_BBK_ in the
 *     body is a failure, not cosmetic.
 *
 * Both failures rendered a valid page with a 200 status. Checking only the
 * status code would have caught neither.
 *
 * Depends on build/seed-testsite-menus.php having run: it reads the menu items
 * back by their seed marker rather than guessing URLs, so the two scripts
 * cannot drift apart.
 *
 * SAFETY: only installs marked `role = test` in build.properties are touched.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\PropertiesReader;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';

/**
 * Must match build/seed-testsite-menus.php.
 */
const SEED_NOTE = 'proclaim-testsite-seed';

/**
 * Markers that mean the page failed while still returning 200.
 *
 * `SQL=` is Joomla's own database-error format, and is what the Books failure
 * would have printed had display_errors been on; the PHP-level markers catch a
 * fatal rendered inside the component's output buffer.
 */
const ERROR_MARKERS = [
    'Fatal error',
    'Uncaught',
    'Parse error',
    'Warning:',
    'Deprecated:',
    'SQL=',
    'JDatabaseExceptionExecuting',
    'Error displaying the error page',
];

$reader   = new PropertiesReader($root . '/build.properties');
$installs = $reader->installsFor('test');

if ($installs === []) {
    fwrite(STDERR, "No role=test install in build.properties — nothing to check.\n");
    fwrite(STDERR, "Declare one (builder.<id>.role = test) so this check has a target.\n");

    // ⚠️ Not exit(0). A verification with no target verified nothing, and a
    // green exit here would let the whole release gate pass while testing
    // an empty set -- the same silence that made #1866 expensive.
    exit(1);
}

$failures = 0;

echo "========================================================================\n";
echo " FRONT-END CHECK — seeded site menu items\n";
echo "========================================================================\n";

foreach ($installs as $install) {
    echo "\n=== {$install->id} ({$install->url}) ===\n";

    if ($install->url === null || $install->url === '') {
        fwrite(STDERR, "  no url in build.properties — skipping.\n");
        $failures++;

        continue;
    }

    $configFile = $install->path . '/configuration.php';

    if (!is_file($configFile)) {
        fwrite(STDERR, "  configuration.php not found — skipping.\n");
        $failures++;

        continue;
    }

    [$host, $user, $pass, $name, $prefix] = (static function (string $file): array {
        require $file;
        $c = new \JConfig();

        return [$c->host, $c->user, $c->password, $c->db, $c->dbprefix];
    })($configFile);

    $port = 3306;

    if (str_contains($host, ':')) {
        [$host, $portString] = explode(':', $host, 2);
        $port                = (int) $portString;
    }

    $db = @mysqli_connect($host, $user, $pass, $name, $port);

    if (!$db) {
        fwrite(STDERR, '  cannot connect: ' . mysqli_connect_error() . "\n");
        $failures++;

        continue;
    }

    $items  = [];
    $result = mysqli_query(
        $db,
        "SELECT id, alias, link FROM {$prefix}menu WHERE client_id = 0 AND note = '" . SEED_NOTE . "' ORDER BY id"
    );

    while ($result && $row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    // The book the seeded study cites, read from the stored reference rather
    // than resolved through the library: this script asserts what the page
    // says, so it must not get its expectation from the same code that
    // produces the page.
    $expectedBook = null;
    $reference    = mysqli_fetch_row(mysqli_query(
        $db,
        "SELECT s.reference_text FROM {$prefix}bsms_study_scriptures AS s "
        . "INNER JOIN {$prefix}bsms_studies AS st ON st.id = s.study_id "
        . 'WHERE st.published = 1 AND s.reference_text <> \'\' ORDER BY s.id LIMIT 1'
    ) ?: null);

    if ($reference !== null) {
        $expectedBook = trim(preg_replace('/\s+\d.*$/', '', (string) $reference[0]));
    }

    mysqli_close($db);

    if ($items === []) {
        fwrite(STDERR, "  no seeded menu items — run build/seed-testsite-menus.php first.\n");
        $failures++;

        continue;
    }

    foreach ($items as $item) {
        $url  = rtrim($install->url, '/') . '/index.php?Itemid=' . $item['id'];
        $body = fetch($url, $status);

        if ($body === null) {
            report(false, $item['alias'], 'no response from ' . $url);
            $failures++;

            continue;
        }

        if ($status !== 200) {
            report(false, $item['alias'], "HTTP {$status}");
            $failures++;

            continue;
        }

        $found = [];

        foreach (ERROR_MARKERS as $marker) {
            if (str_contains($body, $marker)) {
                $found[] = $marker;
            }
        }

        if ($found !== []) {
            report(false, $item['alias'], 'error marker in body: ' . implode(', ', $found));
            $failures++;

            continue;
        }

        if (str_contains($body, 'JBS_BBK_')) {
            report(false, $item['alias'], 'raw JBS_BBK_ language key in body — book names are not resolving');
            $failures++;

            continue;
        }

        // The landing page carries the assertion the others cannot: that the
        // Books section both ran and produced a row.
        //
        // Asserted on rendered text, not on markup. The three landing styles
        // emit different containers for the same section — hero prints a band
        // with no section identifier at all, while cards prints
        // data-section="books" — so any class or attribute check passes on one
        // style and fails on the other two for no reason a reader could guess.
        // The heading text and the book name are the only things all three
        // agree on, and they are also the two things the 10.5.7 bugs removed.
        //
        // This does assume the site renders en-GB. A localised test site would
        // need the expected heading read from the language file instead.
        //
        // Both assertions are gated on the database actually holding a cited
        // book. A section with nothing to list does not render, so on a site
        // whose install seed carried no scripture reference this would fail for
        // having no data rather than for being broken — and a check that fails
        // when nothing is wrong gets muted, which costs more than it caught.
        if (str_contains((string) $item['link'], 'cwmlandingpage')) {
            if ($expectedBook === null || $expectedBook === '') {
                report(true, $item['alias'], \strlen($body) . ' bytes (no cited book — Books section not asserted)');

                continue;
            }

            if (!str_contains($body, '>Books')) {
                report(false, $item['alias'], 'no Books heading in the landing page');
                $failures++;

                continue;
            }

            if (!str_contains($body, $expectedBook)) {
                report(false, $item['alias'], "Books section rendered without '{$expectedBook}'");
                $failures++;

                continue;
            }
        }

        report(true, $item['alias'], \strlen($body) . ' bytes');
    }
}

echo "\n";

if ($failures > 0) {
    fwrite(STDERR, "Front-end check FAILED ({$failures} problem(s)).\n");

    exit(1);
}

echo "Front-end OK.\n";

/**
 * Fetch a URL, returning the body and setting the status code.
 *
 * TLS verification is off: these are local .local hosts with self-signed
 * certificates, and the thing under test is the page, not the certificate.
 *
 * @param   string    $url     Absolute URL to fetch
 * @param   int|null  $status  Set to the HTTP status, or 0 when there was none
 *
 * @return  string|null  Body, or null when the request produced nothing
 *
 * @since __DEPLOY_VERSION__
 */
function fetch(string $url, ?int &$status): ?string
{
    $status  = 0;
    $context = stream_context_create([
        'http' => [
            'timeout'       => 30,
            'ignore_errors' => true,
            'user_agent'    => 'proclaim-release-gate',
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m) === 1) {
            $status = (int) $m[1];
        }
    }

    return $body === false ? null : $body;
}

/**
 * Print one PASS/FAIL line.
 *
 * @param   bool    $ok      Whether the check passed
 * @param   string  $label   What was checked
 * @param   string  $detail  Supporting detail
 *
 * @return  void
 *
 * @since __DEPLOY_VERSION__
 */
function report(bool $ok, string $label, string $detail): void
{
    printf(
        "%s %-24s %s\n",
        $ok ? "\033[32mPASS\033[0m" : "\033[31mFAIL\033[0m",
        $label,
        $detail
    );
}
