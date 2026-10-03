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
 * Depends on the `menus` seed layer (build/seed/menus.php) having run: it reads the menu items
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
use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__);

require $root . '/libraries/vendor/autoload.php';
require $root . '/build/seed/lib.php';

/**
 * Every menu item a seed layer wrote carries a `note` starting with the project's seed marker,
 * declared once in cwm-build.config.json and shared with build/seed/menus.php.
 */
$projectConfig = json_decode((string) file_get_contents($root . '/cwm-build.config.json'), true);
$seedMarker    = (string) ($projectConfig['seed']['marker'] ?? 'cwmseed-');

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
    '<b>Warning</b>',
    '<b>Deprecated</b>',
    '<b>Notice</b>',
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

    try {
        $site = TestSite::fromPath($install->path);
        $db   = $site->db();
    } catch (\RuntimeException $e) {
        fwrite(STDERR, '  cannot connect: ' . $e->getMessage() . "\n");
        $failures++;

        continue;
    }

    $statement = $db->prepare(
        'SELECT id, alias, link FROM ' . $site->table('#__menu')
        . ' WHERE client_id = 0 AND LEFT(note, ?) = ? ORDER BY id'
    );
    $statement->execute([\strlen($seedMarker), $seedMarker]);
    $items = $statement->fetchAll(PDO::FETCH_ASSOC);

    // The book the seeded study cites, read from the stored reference rather
    // than resolved through the library: this script asserts what the page
    // says, so it must not get its expectation from the same code that
    // produces the page.
    $expectedBook = null;
    $reference    = $db->query(
        'SELECT s.reference_text FROM ' . $site->table('#__bsms_study_scriptures') . ' AS s '
        . 'INNER JOIN ' . $site->table('#__bsms_studies') . ' AS st ON st.id = s.study_id '
        . "WHERE st.published = 1 AND s.reference_text <> '' ORDER BY s.id LIMIT 1"
    )->fetchColumn();

    if ($reference !== false) {
        $expectedBook = trim(preg_replace('/\s+\d.*$/', '', (string) $reference));
    }

    if ($items === []) {
        fwrite(STDERR, "  no seeded menu items — run `php build/seed/menus.php apply` first.\n");
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

    $failures += checkSeededStudies($site, $install->url, $seedMarker, $root);
    $failures += checkSeededModules($site, $install->url, $items, $root);
    $failures += checkSeededPlugins($site, $install->url, $seedMarker, $root);
    $failures += checkSeededAccounts($site, $install, $seedMarker, $root);
    $failures += checkSeededSearch($install, $seedMarker, $root);
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
 * @param   string    $url      Absolute URL to fetch
 * @param   int|null  $status   Set to the HTTP status, or 0 when there was none
 * @param   string    $accept   The Accept header; Joomla's API answers 406 to a request that names no JSON type
 *
 * @return  string|null  Body, or null when the request produced nothing
 *
 * @since __DEPLOY_VERSION__
 */
function fetch(string $url, ?int &$status, string $accept = '*/*'): ?string
{
    $status  = 0;
    $context = stream_context_create([
        'http' => [
            'timeout'       => 30,
            'ignore_errors' => true,
            'user_agent'    => 'proclaim-release-gate',
            'header'        => 'Accept: ' . $accept,
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

/**
 * Fetch the sermon page of every study the `content` layer wrote and compare what a guest gets
 * with what `content.json` declares.
 *
 * The declared status is the point: an unpublished or access-restricted study must not be
 * served, and every other study, however awkward (no teacher, no scripture, a title full of
 * markup), must render without an error marker. A study the layer should have written but did
 * not is a failure, not a skip: a skipped check here would pass a gate that tested nothing.
 *
 * @param   TestSite  $site    The site under test
 * @param   string    $url     The site's base URL
 * @param   string    $marker  The seed marker (alias prefix)
 * @param   string    $root    The project root
 *
 * @return  int  Problems found
 *
 * @since __DEPLOY_VERSION__
 */
function checkSeededStudies(TestSite $site, string $url, string $marker, string $root): int
{
    $file = $root . '/build/seed/content.json';

    if (!is_file($file)) {
        return 0;
    }

    $declared = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $db       = $site->db();
    $template = $db->query('SELECT id FROM ' . $site->table('#__bsms_templates') . ' WHERE published = 1 ORDER BY id LIMIT 1')->fetchColumn();
    $find     = $db->prepare('SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');
    $problems = 0;

    echo "\n  -- seeded studies, as a guest\n";

    foreach ($declared['studies'] as $study) {
        $label = $study['key'];
        $find->execute([$marker . $study['key']]);
        $id = $find->fetchColumn();

        if ($id === false) {
            report(false, $label, 'not seeded — run the content layer');
            $problems++;

            continue;
        }

        $want = (int) ($study['guest'] ?? 200);
        $body = fetch(rtrim($url, '/') . '/index.php?option=com_proclaim&view=cwmsermon&id=' . $id . '&t=' . $template, $status);

        if ($body === null || $status !== $want) {
            report(false, $label, "HTTP {$status}, expected {$want}");
            $problems++;

            continue;
        }

        $found = array_filter(ERROR_MARKERS, static fn (string $m): bool => str_contains($body, $m));

        if ($want === 200 && $found !== []) {
            report(false, $label, 'error marker in body: ' . implode(', ', $found));
            $problems++;

            continue;
        }

        report(true, $label, "HTTP {$status}" . ($want === 200 ? ', ' . \strlen($body) . ' bytes' : ' (not served, as declared)'));
    }

    return $problems;
}

/**
 * Fetch the seeded landing page as a guest and check which seeded module instances it carries.
 *
 * Instances are assigned to every page, so one page shows them all. A module with `guest`
 * "hidden" (unpublished, or a level a guest lacks) must not appear; every other one must, with
 * the text `modules.json` says its output contains. The admin module is not checked here: it
 * only renders in the administrator, behind a login.
 *
 * @param   TestSite                           $site   The site under test
 * @param   string                             $url    The site's base URL
 * @param   list<array<string, string|int>>    $items  The seeded menu items (id, alias, link)
 * @param   string                             $root   The project root
 *
 * @return  int  Problems found
 *
 * @since __DEPLOY_VERSION__
 */
function checkSeededModules(TestSite $site, string $url, array $items, string $root): int
{
    $file = $root . '/build/seed/modules.json';

    if (!is_file($file)) {
        return 0;
    }

    $landing = null;

    foreach ($items as $item) {
        if (str_contains((string) $item['link'], 'cwmlandingpage')) {
            $landing = $item;

            break;
        }
    }

    echo "\n  -- seeded modules, as a guest\n";

    if ($landing === null) {
        report(false, 'modules', 'no seeded landing page to carry them');

        return 1;
    }

    $declared = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $body     = fetch(rtrim($url, '/') . '/index.php?Itemid=' . $landing['id'], $status);
    $problems = 0;

    if ($body === null || $status !== 200) {
        report(false, 'modules', "landing page HTTP {$status}");

        return 1;
    }

    $found = array_filter(ERROR_MARKERS, static fn (string $m): bool => str_contains($body, $m));

    if ($found !== []) {
        report(false, 'modules', 'error marker in the page: ' . implode(', ', $found));
        $problems++;
    }

    foreach ($declared['modules'] as $module) {
        if (($module['client'] ?? 'site') !== 'site') {
            continue;
        }

        $shown = str_contains($body, $module['title']);
        $want  = ($module['guest'] ?? 'visible') === 'visible';

        if ($shown !== $want) {
            report(false, $module['key'], $want ? 'not on the page' : 'on the page, but a guest should not see it');
            $problems++;

            continue;
        }

        if ($want && isset($module['contains']) && !str_contains($body, $module['contains'])) {
            report(false, $module['key'], 'rendered without "' . $module['contains'] . '"');
            $problems++;

            continue;
        }

        report(true, $module['key'], $want ? 'shown' : 'hidden, as declared');
    }

    return $problems;
}

/**
 * Check what the Proclaim plugins do for a guest.
 *
 * The webservices plugin registers its routes in Joomla's API application. Without a token a
 * registered route answers 401 and an unknown one 404, so route registration is checked without
 * needing credentials. The schema.org plugin family means a sermon page carries structured data:
 * the page of the study named in plugins.json must have a JSON-LD CreativeWork node with its title.
 *
 * @param   TestSite  $site    The site under test
 * @param   string    $url     The site's base URL
 * @param   string    $marker  The seed marker (alias prefix)
 * @param   string    $root    The project root
 *
 * @return  int  Problems found
 *
 * @since __DEPLOY_VERSION__
 */
function checkSeededPlugins(TestSite $site, string $url, string $marker, string $root): int
{
    $file = $root . '/build/seed/plugins.json';

    if (!is_file($file)) {
        return 0;
    }

    $declared = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $problems = 0;

    echo "\n  -- plugins, as a guest\n";

    $api = $declared['api'];

    foreach ([...array_map(static fn (string $r): array => [$r, 401], $api['resources']), [$api['absent'], 404]] as [$resource, $want]) {
        fetch(rtrim($url, '/') . $api['path'] . '/' . $resource, $status, 'application/vnd.api+json');

        if ($status !== $want) {
            report(false, 'api ' . $resource, "HTTP {$status}, expected {$want}" . ($want === 401 ? ' (route not registered?)' : ''));
            $problems++;
        } else {
            report(true, 'api ' . $resource, "HTTP {$status}");
        }
    }

    $key   = $declared['schemaorg']['study'];
    $db    = $site->db();
    $find  = $db->prepare('SELECT id, studytitle FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');
    $find->execute([$marker . $key]);
    $study = $find->fetch(PDO::FETCH_ASSOC);

    if ($study === false) {
        report(false, 'schema.org', "study \"{$key}\" is not seeded — run the content layer");

        return $problems + 1;
    }

    $template = $db->query('SELECT id FROM ' . $site->table('#__bsms_templates') . ' WHERE published = 1 ORDER BY id LIMIT 1')->fetchColumn();
    $body     = fetch(rtrim($url, '/') . '/index.php?option=com_proclaim&view=cwmsermon&id=' . $study['id'] . '&t=' . $template, $status);
    $node     = false;

    if ($body !== null && preg_match_all('#<script[^>]*ld\+json[^>]*>(.*?)</script>#s', $body, $blocks) > 0) {
        foreach ($blocks[1] as $json) {
            $data = json_decode($json, true);

            foreach (\is_array($data) ? ($data['@graph'] ?? [$data]) : [] as $nodeData) {
                if (($nodeData['@type'] ?? null) === 'CreativeWork' && ($nodeData['name'] ?? $nodeData['headline'] ?? '') === $study['studytitle']) {
                    $node = true;
                }
            }
        }
    }

    if (!$node) {
        report(false, 'schema.org', 'no CreativeWork node named "' . $study['studytitle'] . '" on the sermon page');
        $problems++;
    } else {
        report(true, 'schema.org', 'CreativeWork node present');
    }

    return $problems;
}

/**
 * Check the access rules and the API from the inside, as each seeded account.
 *
 * Logs in as the registered member, the editor and the manager over the real login form and
 * compares the status each gets for every study that declares one in content.json: the access
 * levels and the unpublished state are the rules most worth proving, and a guest check alone only
 * proves the closed door. Then calls the API with the API user's token: reads answer 200, a write
 * to the read-only servers resource has no route (404), and a write to sermons reaches validation
 * (400) without creating anything.
 *
 * @param   TestSite      $site     The site under test
 * @param   InstallConfig $install  The install (url, path)
 * @param   string        $marker   The seed marker (username prefix)
 * @param   string        $root     The project root
 *
 * @return  int  Problems found
 *
 * @since __DEPLOY_VERSION__
 */
function checkSeededAccounts(TestSite $site, $install, string $marker, string $root): int
{
    $accountsFile = $root . '/build/seed/accounts.json';
    $contentFile  = $root . '/build/seed/content.json';

    if (!is_file($accountsFile) || !is_file($contentFile)) {
        return 0;
    }

    echo "\n  -- accounts, logged in\n";

    if (!\function_exists('curl_init')) {
        report(false, 'accounts', 'the curl extension is needed to log in');

        return 1;
    }

    $accounts = json_decode((string) file_get_contents($accountsFile), true, 512, JSON_THROW_ON_ERROR);
    $content  = json_decode((string) file_get_contents($contentFile), true, 512, JSON_THROW_ON_ERROR);
    $url      = rtrim((string) $install->url, '/');
    $db       = $site->db();
    $problems = 0;

    $template = $db->query('SELECT id FROM ' . $site->table('#__bsms_templates') . ' WHERE published = 1 ORDER BY id LIMIT 1')->fetchColumn();
    $find     = $db->prepare('SELECT id FROM ' . $site->table('#__bsms_studies') . ' WHERE alias = ?');

    foreach (['registered', 'editor', 'manager'] as $role) {
        $jar = loginAs($url, $marker . $role, (string) $accounts['password']);

        if ($jar === null) {
            report(false, $role, 'could not log in as ' . $marker . $role . ' — run the accounts layer');
            $problems++;

            continue;
        }

        $wrong = [];
        $count = 0;

        foreach ($content['studies'] as $study) {
            if (!isset($study[$role])) {
                continue;
            }

            $find->execute([$marker . $study['key']]);
            $id = $find->fetchColumn();

            if ($id === false) {
                $wrong[] = $study['key'] . ' not seeded';

                continue;
            }

            $count++;
            $status = 0;
            fetchAs($url . '/index.php?option=com_proclaim&view=cwmsermon&id=' . $id . '&t=' . $template, $jar, $status);

            if ($status !== (int) $study[$role]) {
                $wrong[] = $study['key'] . " HTTP {$status} (expected {$study[$role]})";
            }
        }

        @unlink($jar);

        if ($wrong !== []) {
            report(false, $role, implode('; ', $wrong));
            $problems++;
        } else {
            report(true, $role, "logged in; {$count} studies behave as declared");
        }
    }

    $apiUser = null;

    foreach ($accounts['accounts'] as $account) {
        if ($account['token'] ?? false) {
            $apiUser = $marker . $account['key'];
        }
    }

    $token = $apiUser === null ? null : apiToken($site, $apiUser, (string) $install->path);

    if ($token === null) {
        report(false, 'api token', 'no API account with a token — run the accounts layer');

        return $problems + 1;
    }

    $declared = json_decode((string) file_get_contents($root . '/build/seed/plugins.json'), true, 512, JSON_THROW_ON_ERROR);
    $base     = $url . $declared['api']['path'];

    foreach ([['GET', 'sermons', 200], ['GET', 'servers', 200], ['POST', 'servers', 404], ['POST', 'sermons', 400]] as [$method, $resource, $want]) {
        $status = apiCall($method, $base . '/' . $resource, $token);

        if ($status !== $want) {
            report(false, "api {$method} {$resource}", "HTTP {$status}, expected {$want}");
            $problems++;
        } else {
            report(true, "api {$method} {$resource}", "HTTP {$status} with the token");
        }
    }

    return $problems;
}

/**
 * Log in through the site's own login form, returning the cookie jar file, or null on failure.
 *
 * @param   string  $url       The site's base URL
 * @param   string  $username  The username
 * @param   string  $password  The password
 *
 * @return  string|null
 *
 * @since __DEPLOY_VERSION__
 */
function loginAs(string $url, string $username, string $password): ?string
{
    $jar = (string) tempnam(sys_get_temp_dir(), 'cwmseed');

    $form = fetchAs($url . '/index.php?option=com_users&view=login', $jar, $status);

    if ($form === null || $status !== 200 || preg_match_all('/<input[^>]+type="hidden"[^>]*>/i', $form, $inputs) < 1) {
        @unlink($jar);

        return null;
    }

    $post = ['username' => $username, 'password' => $password, 'task' => 'user.login'];

    foreach ($inputs[0] as $input) {
        if (preg_match('/name="([^"]+)"/', $input, $name) === 1 && preg_match('/value="([^"]*)"/', $input, $value) === 1) {
            $post[$name[1]] = html_entity_decode($value[1]);
        }
    }

    $page = fetchAs($url . '/index.php?option=com_users&task=user.login', $jar, $status, $post);

    if ($page === null || !str_contains($page, 'user.logout')) {
        @unlink($jar);

        return null;
    }

    return $jar;
}

/**
 * Fetch a URL with a cookie jar, following redirects, optionally as a POST.
 *
 * @param   string                    $url     The URL
 * @param   string                    $jar     The cookie jar file
 * @param   int|null                  $status  Set to the final HTTP status
 * @param   array<string, string>|null $post   Form fields, or null for a GET
 *
 * @return  string|null
 *
 * @since __DEPLOY_VERSION__
 */
function fetchAs(string $url, string $jar, ?int &$status, ?array $post = null): ?string
{
    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_USERAGENT      => 'proclaim-release-gate',
    ]);

    if ($post !== null) {
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($post)]);
    }

    $body   = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

    return $body === false ? null : (string) $body;
}

/**
 * Call the API with a token and return the HTTP status.
 *
 * @param   string  $method  GET or POST
 * @param   string  $url     The URL
 * @param   string  $token   The bearer token
 *
 * @return  int
 *
 * @since __DEPLOY_VERSION__
 */
function apiCall(string $method, string $url, string $token): int
{
    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_POSTFIELDS     => $method === 'POST' ? '{}' : null,
        CURLOPT_HTTPHEADER     => ['Accept: application/vnd.api+json', 'Content-Type: application/json', 'X-Joomla-Token: ' . $token],
    ]);
    curl_exec($curl);

    return (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
}

/**
 * Search the Smart Search index as a guest and as each seeded account.
 *
 * The studies in the results must be exactly the published seeded ones that role can open, which is
 * the access matrix the pages follow: a guest never finds an unpublished or restricted study, a
 * Registered member finds the Registered-level one, editors and managers find the Special-level one
 * as well. An unpublished study is in the index but must be in no one's results.
 *
 * @param   InstallConfig  $install  The install (url)
 * @param   string         $marker   The seed marker (username prefix)
 * @param   string         $root     The project root
 *
 * @return  int  Problems found
 *
 * @since __DEPLOY_VERSION__
 */
function checkSeededSearch($install, string $marker, string $root): int
{
    $files = [$root . '/build/seed/finder.json', $root . '/build/seed/content.json', $root . '/build/seed/accounts.json'];

    foreach ($files as $file) {
        if (!is_file($file)) {
            return 0;
        }
    }

    echo "\n  -- search, as each role\n";

    if (!\function_exists('curl_init')) {
        report(false, 'search', 'the curl extension is needed to log in');

        return 1;
    }

    [$finder, $content, $accounts] = array_map(
        static fn (string $file): array => json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR),
        $files
    );

    $url      = rtrim((string) $install->url, '/');
    $problems = 0;

    foreach (['guest', 'registered', 'editor', 'manager'] as $role) {
        $jar = tempnam(sys_get_temp_dir(), 'cwmseed');

        if ($role !== 'guest') {
            $jar = loginAs($url, $marker . $role, (string) $accounts['password']);

            if ($jar === null) {
                report(false, 'search ' . $role, 'could not log in — run the accounts layer');
                $problems++;

                continue;
            }
        }

        $body = fetchAs($url . '/index.php?option=com_finder&view=search&q=' . rawurlencode((string) $finder['query']), $jar, $status);
        @unlink($jar);

        if ($body === null || $status !== 200) {
            report(false, 'search ' . $role, "HTTP {$status}");
            $problems++;

            continue;
        }

        $wrong = [];
        $found = 0;

        foreach ($content['studies'] as $study) {
            // Search lists published content only, however much the role may open by direct link:
            // an editor can open an unpublished study, and still must not find it here.
            $expected = (int) ($study[$role] ?? 200) === 200 && (int) ($study['published'] ?? 1) !== 0;
            $shown    = str_contains($body, htmlspecialchars(substr((string) $study['title'], 0, 30), ENT_QUOTES | ENT_SUBSTITUTE));
            $found += $shown ? 1 : 0;

            if ($shown !== $expected) {
                $wrong[] = $study['key'] . ($expected ? ' missing' : ' should not appear');
            }
        }

        if ($wrong !== []) {
            report(false, 'search ' . $role, implode('; ', $wrong));
            $problems++;
        } else {
            report(true, 'search ' . $role, "{$found} seeded studies found, as declared");
        }
    }

    return $problems;
}
