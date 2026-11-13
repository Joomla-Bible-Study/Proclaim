<?php

/**
 * Part of Proclaim Package
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Every service the code asks Joomla's container for is one the container
 * holds.
 *
 * A lookup of an unregistered key throws, and a caller that catches the throw
 * fails silently. Comment emails were never sent on Joomla 5 or 6 for that
 * reason: both senders asked for MailerInterface, which neither registers, and
 * both caught the error. Nothing else noticed for ten months.
 *
 * The container is the harness's real one, built by Factory::createContainer()
 * as cli/joomla.php builds it, so has() answers as a site's would.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContainerLookupsTest extends ProclaimTestCase
{
    /**
     * Where the shipped code lives. Vendored code is skipped.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private const array ROOTS = ['admin/src', 'site/src', 'api/src', 'plugins', 'modules', 'proclaim.script.php'];

    /**
     * Keys known to be missing from the container, each with why the lookup
     * is tolerated for now. Drop an entry once its lookup is fixed.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array KNOWN_UNREGISTERED = [
        'cache.output' => 'mod_proclaim_youtube asks for it inside a try/catch around a secondary cache; '
            . 'its file cache does the work, so the miss costs only that second layer',
    ];

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Every service the code asks the container for is registered')]
    public function testEveryLookupIsRegistered(): void
    {
        $lookups = $this->lookups();

        // A scan that finds nothing passes vacuously; the database lookup
        // alone appears hundreds of times.
        $this->assertGreaterThan(100, \count($lookups), 'The scan found too few lookups to trust');

        $missing = [];

        foreach ($lookups as [$key, $where]) {
            if (!Factory::getContainer()->has($key) && !isset(self::KNOWN_UNREGISTERED[$key])) {
                $missing[] = $key . ' at ' . $where;
            }
        }

        $this->assertSame([], $missing, 'These keys are not registered in Joomla\'s container, so the lookup throws');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Each key listed as known to be missing is still looked up and still missing')]
    public function testKnownGapsAreStillGaps(): void
    {
        $keys = array_column($this->lookups(), 0);

        foreach (array_keys(self::KNOWN_UNREGISTERED) as $key) {
            $this->assertContains($key, $keys, $key . ' is no longer looked up; drop it from KNOWN_UNREGISTERED');
            $this->assertFalse(Factory::getContainer()->has($key), $key . ' is registered now; drop it from KNOWN_UNREGISTERED');
        }
    }

    /**
     * Every Factory::getContainer()->get() in the shipped code, with the key
     * resolved: a string as written, a ::class name through the file's
     * imports and namespace. Comments are stripped first, so a docblock that
     * quotes an old lookup does not count.
     *
     * @return  list<array{0: string, 1: string}>  Each key and where it is looked up
     *
     * @since   __DEPLOY_VERSION__
     */
    private function lookups(): array
    {
        $root    = \dirname(__DIR__, 3);
        $lookups = [];

        foreach ($this->sourceFiles($root) as $file) {
            $source = $this->withoutComments((string) file_get_contents($file));

            if (!str_contains($source, 'getContainer')) {
                continue;
            }

            $namespace = preg_match('/^namespace\s+([^;\s]+)\s*;/m', $source, $m) ? $m[1] : '';
            $imports   = [];

            foreach (preg_match_all('/^use\s+([A-Za-z0-9_\\\\]+)(?:\s+as\s+(\w+))?\s*;/m', $source, $uses, PREG_SET_ORDER) ? $uses : [] as $use) {
                $imports[$use[2] ?? substr(strrchr('\\' . $use[1], '\\'), 1)] = $use[1];
            }

            preg_match_all(
                '/getContainer\(\)\s*->\s*get\(\s*(?:\'([^\']+)\'|"([^"]+)"|(\\\\?[A-Za-z_][A-Za-z0-9_\\\\]*)::class)\s*\)/',
                $source,
                $calls,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE
            );

            foreach ($calls as $call) {
                $where = substr($file, \strlen($root) + 1) . ':' . (substr_count(substr($source, 0, $call[0][1]), "\n") + 1);

                if (isset($call[3]) && $call[3][1] !== -1) {
                    $lookups[] = [$this->resolve($call[3][0], $namespace, $imports), $where];
                } else {
                    $lookups[] = [$call[1][0] !== '' ? $call[1][0] : $call[2][0], $where];
                }
            }
        }

        return $lookups;
    }

    /**
     * A class name as PHP would resolve it in the file.
     *
     * @param   string                 $name       The name as written
     * @param   string                 $namespace  The file's namespace
     * @param   array<string, string>  $imports    Imported names by their alias
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resolve(string $name, string $namespace, array $imports): string
    {
        if ($name[0] === '\\') {
            return substr($name, 1);
        }

        [$first, $rest] = array_pad(explode('\\', $name, 2), 2, '');

        if (isset($imports[$first])) {
            return $imports[$first] . ($rest !== '' ? '\\' . $rest : '');
        }

        return ltrim($namespace . '\\' . $name, '\\');
    }

    /**
     * PHP source with its comments removed, line breaks kept so line numbers
     * still match.
     *
     * @param   string  $source  The source
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function withoutComments(string $source): string
    {
        $out = '';

        foreach (\PhpToken::tokenize($source) as $token) {
            $out .= $token->is([T_COMMENT, T_DOC_COMMENT])
                ? str_repeat("\n", substr_count($token->text, "\n"))
                : $token->text;
        }

        return $out;
    }

    /**
     * @param   string  $root  The repository root
     *
     * @return  list<string>  Every PHP file under ROOTS, without vendored code
     *
     * @since   __DEPLOY_VERSION__
     */
    private function sourceFiles(string $root): array
    {
        $files = [];

        foreach (self::ROOTS as $path) {
            $path = $root . '/' . $path;

            if (is_file($path)) {
                $files[] = $path;

                continue;
            }

            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $name = $file->getPathname();

                if (str_ends_with($name, '.php') && !str_contains($name, '/vendor/') && !str_contains($name, '/node_modules/')) {
                    $files[] = $name;
                }
            }
        }

        return $files;
    }
}
