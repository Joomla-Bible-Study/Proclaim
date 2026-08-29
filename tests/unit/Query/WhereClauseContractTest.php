<?php

/**
 * Two ways `QueryInterface::where()` does not behave the way its call site
 * reads. Both have bitten this codebase; neither is visible in review.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Unit\Query;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @since __DEPLOY_VERSION__
 */
class WhereClauseContractTest extends TestCase
{
    /**
     * Source trees whose queries reach a database.
     *
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const ROOTS = ['admin', 'site', 'api', 'components', 'modules', 'plugins', 'libraries/lib_cwmscripture'];

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    private const SKIP = ['vendor', 'node_modules', 'tests', 'scripturelinks/libraries'];

    /**
     * @return  string[]  Absolute paths of every PHP file to inspect
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
                $path = $file->getPathname();

                if (!str_ends_with($path, '.php')) {
                    continue;
                }

                foreach (self::SKIP as $skip) {
                    if (str_contains($path, $skip)) {
                        continue 2;
                    }
                }

                $files[] = $path;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * The string literals a where() argument concatenates, with PHP
     * expressions replaced by a placeholder.
     *
     * @param   string  $argument  Raw PHP source of the argument
     *
     * @return  string
     *
     * @since __DEPLOY_VERSION__
     */
    private static function reconstructSql(string $argument): string
    {
        preg_match_all("/'([^']*)'/", $argument, $matches);

        return trim((string) preg_replace('/\s+/', ' ', implode(' X ', $matches[1])));
    }

    /**
     * Whether an OR appears outside every bracket in a reconstructed fragment.
     *
     * @param   string  $sql  Reconstructed SQL fragment
     *
     * @return  bool
     *
     * @since __DEPLOY_VERSION__
     */
    private static function hasTopLevelOr(string $sql): bool
    {
        $depth = 0;

        foreach (preg_split('/(\(|\)|\bOR\b)/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE) as $token) {
            $token = trim($token);

            if ($token === '(') {
                $depth++;
            } elseif ($token === ')') {
                $depth--;
            } elseif ($token === 'OR' && $depth <= 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every bare where()/having() argument in the codebase, as
     * [file, line, reconstructed SQL].
     *
     * @return  array<int, array{0: string, 1: int, 2: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    private static function whereArguments(): array
    {
        $found = [];

        foreach (self::sourceFiles() as $path) {
            $lines = explode("\n", (string) file_get_contents($path));
            $i     = 0;

            while ($i < \count($lines)) {
                $isBare = preg_match('/->(where|having)\s*\(/', $lines[$i])
                    && !preg_match('/->(extendWhere|whereIn|whereNotIn|orWhere|andWhere)\s*\(/', $lines[$i]);

                if (!$isBare) {
                    $i++;
                    continue;
                }

                $depth = 0;
                $chunk = [];
                $j     = $i;

                while ($j < \count($lines) && $j < $i + 25) {
                    $chunk[] = $lines[$j];
                    $depth += substr_count($lines[$j], '(') - substr_count($lines[$j], ')');

                    if ($depth <= 0) {
                        break;
                    }

                    $j++;
                }

                $blob = implode("\n", $chunk);

                if (preg_match('/->(?:where|having)\s*\((.*)\)\s*[;,)]?\s*$/s', $blob, $m)) {
                    $found[] = [$path, $i + 1, self::reconstructSql($m[1])];
                }

                $i = $j + 1;
            }
        }

        return $found;
    }

    #[TestDox('no where() condition contains an OR outside its own brackets')]
    public function testNoUnbracketedOrInAWhereCondition(): void
    {
        $offenders = [];

        foreach (self::whereArguments() as [$path, $line, $sql]) {
            if ($sql !== '' && self::hasTopLevelOr($sql)) {
                $offenders[] = \sprintf('%s:%d  %s', basename($path), $line, $sql);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "where() appends its argument and glues it with AND, adding no brackets of its own, so an OR at\n"
            . "the top level of the string binds looser than every condition before it and overrides them --\n"
            . "including published, access and language. Wrap the whole disjunction in its own brackets.\n\n"
            . implode("\n", $offenders)
        );
    }

    #[TestDox('the detector still recognises the shape it was written for')]
    public function testTheDetectorWorks(): void
    {
        // Guards the test above: a scan that finds nothing proves nothing unless
        // it is known to find something. This is the clause from #1735.
        $this->assertTrue(
            self::hasTopLevelOr("( X = X AND X >= X ) OR X ="),
            'The detector no longer recognises the unbracketed clause it was written for.'
        );

        $this->assertFalse(
            self::hasTopLevelOr("(( X = X AND X >= X ) OR X = X )"),
            'The detector reports a correctly bracketed clause as an offender.'
        );
    }

    #[TestDox('no call relies on where()\'s glue argument')]
    public function testNoCallReliesOnTheGlueArgument(): void
    {
        $offenders = [];

        foreach (self::sourceFiles() as $path) {
            foreach (explode("\n", (string) file_get_contents($path)) as $n => $line) {
                if (preg_match('/->where\s*\(.*,\s*(\$\w+\s*=\s*)?[\'"](OR|XOR)[\'"]\s*\)/', $line)) {
                    $offenders[] = \sprintf('%s:%d  %s', basename($path), $n + 1, trim($line));
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "where(\$conditions, 'OR') honours the glue ONLY when it is the first where() on the query --\n"
            . "every later call goes through append(), which keeps the existing AND and discards the glue\n"
            . "silently. Adding an earlier where() then changes this call's meaning with no edit to it.\n"
            . "Join and bracket the conditions instead: where('(' . implode(' OR ', \$conditions) . ')').\n\n"
            . implode("\n", $offenders)
        );
    }

    /**
     * Conditions handed to where() as a variable, with the reason each is safe.
     *
     * ⚠️ Keep this list short. An entry means the OR check above cannot see
     * that call at all, so its correctness rests on this note rather than on a
     * test.
     *
     * @var array<string, string>
     * @since __DEPLOY_VERSION__
     */
    private const UNVERIFIABLE_WHERE = [
        'CwmarchiveModel.php:105' => 'One-element array holding a single date comparison; no OR to bracket.',
        'CwmarchiveModel.php:120' => 'One-element array holding a single date comparison; no OR to bracket.',
    ];

    /**
     * ⚠️ The OR check reconstructs SQL from the literal argument at the call
     * site, so `->where($conditions)` reconstructs to nothing and is skipped —
     * silently, and reported as clean.
     *
     * Found in #1985: with the predicate assembled into a variable first,
     * removing its brackets still passed. Moved inline, the identical edit
     * failed at that file and line.
     *
     * Flagging the shape rather than teaching the detector to follow variables:
     * there are two of these, both wanting no OR anyway, so the cost of
     * refusing the shape is nearly nothing and the rule is easy to state.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('no where() is handed conditions the OR check cannot read')]
    public function testNoWhereIsGivenAnUnreadableArgument(): void
    {
        $offenders = [];
        $seen      = 0;

        foreach (self::sourceFiles() as $path) {
            foreach (explode("\n", (string) file_get_contents($path)) as $n => $line) {
                if (!preg_match('/->(where|having)\s*\(\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*\)/', $line)) {
                    continue;
                }

                $seen++;
                $key = basename($path) . ':' . ($n + 1);

                if (isset(self::UNVERIFIABLE_WHERE[$key])) {
                    continue;
                }

                $offenders[] = \sprintf('%s  %s', $key, trim($line));
            }
        }

        $this->assertGreaterThan(
            0,
            $seen,
            'No where($variable) calls were found at all — the scan is not reading the source.'
        );

        $this->assertSame(
            [],
            $offenders,
            "where(\$variable) hides the condition from the unbracketed-OR check above, which reads the\n"
            . "literal argument at the call site. Write the condition inline, or use andWhere()/orWhere()\n"
            . "so the builder does the bracketing, or add the line to self::UNVERIFIABLE_WHERE with the\n"
            . 'reason it holds no OR.' . "\n\n" . implode("\n", $offenders)
        );
    }

    #[TestDox('the unreadable-argument list only names lines that still look that way')]
    public function testUnverifiableEntriesAreStillAccurate(): void
    {
        foreach (self::UNVERIFIABLE_WHERE as $key => $why) {
            [$file, $line] = explode(':', $key);

            $match = null;

            foreach (self::sourceFiles() as $path) {
                if (basename($path) === $file) {
                    $match = explode("\n", (string) file_get_contents($path))[(int) $line - 1] ?? null;

                    break;
                }
            }

            $this->assertNotNull($match, $key . ' is exempted but the file or line is gone.');

            $this->assertMatchesRegularExpression(
                '/->(where|having)\s*\(\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*\)/',
                (string) $match,
                $key . ' no longer passes a variable to where(), so the exemption is stale. Remove it: ' . $why
            );
        }
    }
}
