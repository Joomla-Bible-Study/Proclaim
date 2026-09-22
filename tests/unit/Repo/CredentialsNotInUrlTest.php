<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * No credential may be built into a request URL.
 *
 * A secret in a query string is written verbatim into the web server's access
 * log on every call. That log is read by more people than the record the secret
 * is stored in, retained far longer, copied into backups, and often shipped to
 * aggregation off the host — so it is nowhere near where anyone auditing "who
 * can see this key" would look. The URL also reaches browser history and any
 * intermediate proxy.
 *
 * ⚠️ Three separate places did this, and the third is why this test exists as a
 * sweep rather than three fixes. The first was found on a live site, with a
 * Google API key sitting in a 124 MB unrotated access log. Looking for siblings
 * by grepping `build/media_source/js/` found none — and concluded wrongly, twice
 * over, because the other two handlers are inline scripts **emitted from PHP**
 * and never appear in the JavaScript sources at all.
 *
 * A grep that looks in one place proves something about that place. This one
 * walks every PHP and JavaScript source the package ships.
 *
 * @since __DEPLOY_VERSION__
 */
class CredentialsNotInUrlTest extends ProclaimTestCase
{
    /**
     * Parameter names that carry a secret.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array CREDENTIAL_PARAMS = [
        'api_key',
        'apikey',
        'api-key',
        'client_secret',
        'client_id',
        'access_token',
        'refresh_token',
        'password',
        'secret',
        'private_key',
    ];

    /**
     * Source trees the package ships.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array ROOTS = ['admin/src', 'site/src', 'api/src', 'build/media_source'];

    /**
     * Every shipped source file, excluding vendored third-party code.
     *
     * @return  array<int, string>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function sourceFiles(): array
    {
        $base  = \dirname(__DIR__, 3);
        $files = [];

        foreach (self::ROOTS as $root) {
            if (!is_dir($base . '/' . $root)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . '/' . $root));

            foreach ($it as $file) {
                $path = str_replace('\\', '/', $file->getPathname());

                if (!preg_match('#\.(php|js)$#', $path)) {
                    continue;
                }

                // Vendored libraries are not ours to hold to this.
                if (str_contains($path, '/vendor/') || str_contains($path, '/node_modules/')) {
                    continue;
                }

                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * ⚠️ Matches the *construction* of a query parameter, not a mention of the
     * name. `$post->getString('api_key')` reads one and is exactly what the fix
     * looks like, so a test keyed on the bare name would fail on the fixed code.
     * What is banned is `'&api_key=' .` / `+ '&api_key=' +` / `` `&api_key=${…}` ``
     * — a value being appended to a URL.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('No credential is appended to a request URL anywhere in the shipped sources')]
    public function testNoCredentialIsAppendedToAUrl(): void
    {
        $base     = \dirname(__DIR__, 3);
        $names    = implode('|', array_map('preg_quote', self::CREDENTIAL_PARAMS));
        $pattern  = '#[?&](' . $names . ')=(?:\'\s*[.+]|"\s*[.+]|\$\{|\'\s*\+|\s*\'\s*\.)#i';
        $offences = [];
        $checked  = 0;

        foreach (self::sourceFiles() as $path) {
            $checked++;
            $source = (string) file_get_contents($path);

            foreach (explode("\n", $source) as $number => $line) {
                if (preg_match($pattern, $line)) {
                    $offences[] = \sprintf(
                        '%s:%d  %s',
                        substr($path, \strlen($base) + 1),
                        $number + 1,
                        trim($line)
                    );
                }
            }
        }

        // ⚠️ Not a silent pass over an empty scan. A broken file walk would
        // otherwise report success having examined nothing.
        $this->assertGreaterThan(200, $checked, 'Too few source files scanned; the scan is broken.');

        $this->assertSame(
            [],
            $offences,
            "These build a secret into a request URL, which writes it to the web server's access log.\n"
            . "Send it in a POST body instead, and read it from the POST body server-side:\n"
            . implode("\n", $offences)
        );
    }

    /**
     * The handlers that receive those credentials must read them from the POST
     * body, so a cached copy of an older script — or a hand-built URL — cannot
     * put the value back in the log after the browser side was fixed.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Credential handlers read from the POST body, not the merged request')]
    public function testCredentialsAreReadFromPostOnly(): void
    {
        $base     = \dirname(__DIR__, 3);
        $names    = implode('|', array_map('preg_quote', self::CREDENTIAL_PARAMS));
        $offences = [];

        foreach (self::sourceFiles() as $path) {
            if (!str_ends_with($path, '.php')) {
                continue;
            }

            foreach (explode("\n", (string) file_get_contents($path)) as $number => $line) {
                // ⚠️ Only reads on an Input object. `$app->get('secret')` is
                // Joomla's *configuration* secret and has nothing to do with
                // the request — matching a bare ->get() flagged two of those.
                $input = '(?:\$input|\$this->input|getInput\(\))';

                if (!preg_match('#' . $input . '->get(?:String|Int|Var)?\(\s*[\'"](' . $names . ')[\'"]#i', $line)) {
                    continue;
                }

                // Reading through ->post is the fix, not the defect.
                if (preg_match('#->post->#', $line)) {
                    continue;
                }

                $offences[] = \sprintf(
                    '%s:%d  %s',
                    substr($path, \strlen($base) + 1),
                    $number + 1,
                    trim($line)
                );
            }
        }

        $this->assertSame(
            [],
            $offences,
            "These read a credential from the merged request, so a GET still supplies it:\n"
            . implode("\n", $offences)
        );
    }
}
