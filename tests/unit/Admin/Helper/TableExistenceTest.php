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

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Existence checks must ask an equality question, not a LIKE one.
 *
 * `SHOW TABLES LIKE 'jos_bsms_admin'` reads as an equality test and is not one:
 * `_` matches any single character, so it also matches a table literally named
 * `josXbsmsYadmin`. Every Joomla prefix ends in an underscore, so every such
 * check carried at least one wildcard.
 *
 * Measured across a real 104-table schema, zero pairs collide -- so this was
 * never firing in the field, and the fix is about asking the right question
 * rather than repairing damage.
 *
 * ⚠️ Source-inspection, like CwmrestoreTest: the helpers read information_schema
 * and the unit suite has no database. What is worth pinning without one is that
 * the pattern is gone and does not come back by copy-paste -- there were seven
 * instances, and each was a plausible model for the next.
 *
 * @since  __DEPLOY_VERSION__
 */
class TableExistenceTest extends ProclaimTestCase
{
    /**
     * Directories holding code that ships to a site.
     *
     * build/ and tests/ are excluded deliberately: the harness has its own
     * database access and is not what a user runs.
     */
    private const SHIPPED_PATHS = ['admin/src', 'site/src', 'api/src', 'plugins'];

    /**
     * @return list<string>  Absolute paths of shipped PHP files.
     */
    private static function shippedFiles(): array
    {
        $root  = \dirname(__DIR__, 4);
        $files = [];

        foreach (self::SHIPPED_PATHS as $relative) {
            $dir = $root . '/' . $relative;

            if (!is_dir($dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

            foreach ($iterator as $file) {
                $path = $file->getPathname();

                // Bundled third-party code is not ours to hold to this.
                if (str_contains($path, '/vendor/') || !str_ends_with($path, '.php')) {
                    continue;
                }

                $files[] = $path;
            }
        }

        // The installer scriptfile lives at the root and is very much shipped.
        $files[] = $root . '/proclaim.script.php';

        return $files;
    }

    /**
     * Strip block and line comments, so prose explaining the old pattern is not
     * mistaken for the pattern. The explanation of why `SHOW TABLES LIKE` is
     * wrong necessarily contains the words.
     */
    private static function code(string $path): string
    {
        $source   = (string) file_get_contents($path);
        $stripped = '';

        foreach (token_get_all($source) as $token) {
            if (\is_array($token) && \in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            $stripped .= \is_array($token) ? $token[1] : $token;
        }

        return $stripped;
    }

    public function testNoShippedCodeAsksForExistenceWithALikePattern(): void
    {
        $offenders = [];

        foreach (self::shippedFiles() as $path) {
            if (!is_file($path)) {
                continue;
            }

            // ⚠️ Only the LIKE form. `SHOW COLUMNS FROM x WHERE Field IN (...)`
            // is an exact match and perfectly fine -- an earlier version of this
            // test flagged one of those and was wrong. Plain `SHOW TABLES` is
            // included because its only uses are the wildcard form or a full
            // enumeration, and neither is what these checks want.
            $code = self::code($path);

            if (
                preg_match('/SHOW\s+TABLES\b/i', $code) === 1
                || preg_match('/SHOW\s+COLUMNS\b[^;]*\bLIKE\b/is', $code) === 1
            ) {
                $offenders[] = str_replace(\dirname(__DIR__, 4) . '/', '', $path);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Use CwmdbHelper::tableExists()/columnExists(). A LIKE pattern treats the\n"
            . "prefix underscore as a wildcard, so the check can answer yes for a\n"
            . 'different table. Offending files: ' . implode(', ', $offenders)
        );
    }

    public function testTableExistsAsksInformationSchemaWithABoundEqualityMatch(): void
    {
        $body = self::methodBody('tableExists');

        $this->assertMatchesRegularExpression('/information_schema\.TABLES/', $body);
        $this->assertMatchesRegularExpression('/TABLE_NAME.{0,12}=\s*:name/s', $body, 'equality, not LIKE');
        $this->assertMatchesRegularExpression('/->bind\(/', $body, 'the name is bound, not interpolated');
        $this->assertDoesNotMatchRegularExpression('/LIKE/i', $body);
    }

    public function testColumnExistsBindsBothTheTableAndTheColumn(): void
    {
        $body = self::methodBody('columnExists');

        $this->assertMatchesRegularExpression('/information_schema\.COLUMNS/', $body);
        $this->assertMatchesRegularExpression('/->bind\(\':table\'/', $body);
        $this->assertMatchesRegularExpression('/->bind\(\':column\'/', $body);
        $this->assertDoesNotMatchRegularExpression('/LIKE/i', $body);
    }

    /**
     * Both helpers accept either `#__x` or an already-prefixed name, because
     * their callers hold one or the other.
     */
    public function testBothHelpersResolveThePlaceholderPrefix(): void
    {
        foreach (['tableExists', 'columnExists'] as $method) {
            $this->assertMatchesRegularExpression(
                "/str_replace\('#__'/",
                self::methodBody($method),
                $method . '() must accept a #__ token as well as a real name.'
            );
        }
    }

    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice(
                $lines,
                $reflection->getStartLine() - 1,
                $reflection->getEndLine() - $reflection->getStartLine() + 1
            )
        );
    }
}
