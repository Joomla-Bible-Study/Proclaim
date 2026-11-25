<?php

/**
 * Docblock contract for the test suite.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test code follows the docblock layout shipped code does. Every class,
 * property, constant and method has a docblock with a description and a
 * since tag. A method also lists and describes each parameter in signature
 * order, and states its return type, describing the value unless the type is
 * void, with a blank line between the groups. A property or constant states
 * its type. An inheritDoc tag stands in for the description and the return
 * type.
 *
 * The gaps the suite already had are listed in docblock-debt/, and the list
 * may only shrink. A gap that is not listed fails, and so does an entry whose
 * declaration no longer has the gap. An entry whose file or declaration is
 * gone is ignored, because a branch can delete a test the list still names.
 * A branch with test files of its own lists their gaps in a file of its own
 * there, so the shared list is not edited on two branches.
 *
 * Run with PROCLAIM_SHRINK_DOCBLOCK_DEBT=1 to rewrite each list to the
 * entries still owed. It never adds one.
 *
 * @since  __DEPLOY_VERSION__
 */
class TestDocblocksTest extends ProclaimTestCase
{
    /**
     * Folders under tests/ that hold data rather than test code, with why.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array EXCLUDED = [
        'tests/fixtures/' => 'seed data and sample files that tests read',
    ];

    /**
     * The tokens that may sit between a docblock and its declaration.
     *
     * @var    list<int>
     * @since  __DEPLOY_VERSION__
     */
    private const array MODIFIERS = [T_WHITESPACE, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_FINAL, T_READONLY, T_ABSTRACT];

    /**
     * The tokens a property's type is written with, or a trait use.
     *
     * @var    list<int|string>
     * @since  __DEPLOY_VERSION__
     */
    private const array TYPE_TOKENS = [T_STRING, T_ARRAY, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR, '?', '|', T_USE];

    /**
     * The audit of every file in scope, keyed by path, once per run.
     *
     * @var    array<string, list<array{kind: string, name: string, line: int, gaps: list<string>}>>|null
     * @since  __DEPLOY_VERSION__
     */
    private static ?array $suite = null;

    /**
     * The audit reads the suite, this file included. An empty file list would
     * let every check below pass on nothing.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The audit reads the test suite')]
    public function testTheAuditReadsTheSuite(): void
    {
        $suite = self::suite();
        $self  = substr(__FILE__, \strlen(self::repoRoot()) + 1);

        $this->assertGreaterThan(100, \count($suite), 'git listed too few test files, so the audit read almost nothing.');
        $this->assertArrayHasKey($self, $suite, 'The audit should read this file too.');
    }

    /**
     * The audit reports each kind of gap, by declaration, on a sample that has
     * one of each. A return described after a bracketed type, a bare void one,
     * and parameters described after a bracketed type, by reference or
     * variadic, pass.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The audit finds each kind of gap')]
    public function testTheAuditFindsEachKindOfGap(): void
    {
        $sample = <<<'PHP'
            <?php
            /**
             * A sample.
             */
            class Sample
            {
                public $loose;

                /**
                 * A constant.
                 *
                 * @since  1.0.0
                 */
                private const LIMIT = 1;

                /**
                 * @return  void
                 * @since   1.0.0
                 */
                public function compact(int $count): void
                {
                }

                public function bare(): void
                {
                }

                /**
                 * Return nothing in particular.
                 *
                 * @param   int  $count  How many
                 * @return  int  The count
                 *
                 * @since   1.0.0
                 */
                public function noBlankAfterParam(int $count): int
                {
                    return $count;
                }

                /**
                 * Count something.
                 *
                 * @return  int
                 *
                 * @since   1.0.0
                 */
                public function bareReturn(): int
                {
                    return 1;
                }

                /**
                 * Map something.
                 *
                 * @return  array<string, int>
                 *
                 * @since   1.0.0
                 */
                public function bareGenericReturn(): array
                {
                    return [];
                }

                /**
                 * Shape something.
                 *
                 * @return  array{a: int, b: string}  The pair
                 *
                 * @since   1.0.0
                 */
                public function describedShapeReturn(): array
                {
                    return ['a' => 1, 'b' => ''];
                }

                /**
                 * Do something.
                 *
                 * @return  void
                 *
                 * @since   1.0.0
                 */
                public function bareVoid(): void
                {
                }

                /**
                 * Join something.
                 *
                 * @param   string    $glue   What goes between
                 * @param   string[]  $parts
                 *
                 * @return  string  The joined parts
                 *
                 * @since   1.0.0
                 */
                public function bareParam(string $glue, array $parts): string
                {
                    return implode($glue, $parts);
                }

                /**
                 * Collect something.
                 *
                 * @param   array<string, int>  &$counts  Running totals
                 * @param   string              ...$keys  The keys to count
                 *
                 * @return  void
                 *
                 * @since   1.0.0
                 */
                public function describedParams(array &$counts, string ...$keys): void
                {
                }

                /**
                 * Inherit the rest.
                 *
                 * @inheritDoc
                 */
                public function inherited(int $count): void
                {
                }
            }
            PHP;

        $this->assertSame(
            [
                'class Sample: no @since',
                'property $loose: no docblock',
                'const LIMIT: no @var',
                'function compact: no description',
                'function compact: no blank line between @return and @since',
                'function compact: @param [] vs signature ["$count"]',
                'function bare: no docblock',
                'function noBlankAfterParam: no blank line between @param and @return',
                'function bareReturn: no @return description',
                'function bareGenericReturn: no @return description',
                'function bareParam: no description for @param $parts',
                'function inherited: no @since',
            ],
            self::entries(self::audit($sample))
        );
    }

    /**
     * No method's parameter tags disagree with its signature, anywhere in the
     * suite. This does not rest on the debt list: a mismatch is never allowed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('No @param list disagrees with its signature')]
    public function testNoParamListDisagreesWithItsSignature(): void
    {
        $found = [];

        foreach (self::suite() as $file => $declarations) {
            foreach (self::entries($declarations) as $entry) {
                if (str_contains($entry, ': @param ')) {
                    $found[] = $file . ': ' . $entry;
                }
            }
        }

        foreach (self::debt() as $file => $entries) {
            foreach (array_keys($entries) as $entry) {
                if (str_contains($entry, ': @param ')) {
                    $found[] = $file . ': ' . $entry . ' (listed as debt)';
                }
            }
        }

        $this->assertSame([], $found, 'Each @param names one parameter, in signature order, and the list names them all.');
    }

    /**
     * Every gap in the suite is one docblock-debt/ already lists. A new test,
     * or a changed one, is written with its docblocks complete.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('No docblock gap is new')]
    public function testNoDocblockGapIsNew(): void
    {
        $debt = self::debt();
        $new  = [];

        foreach (self::suite() as $file => $declarations) {
            foreach (array_count_values(self::entries($declarations)) as $entry => $count) {
                for ($i = $debt[$file][$entry] ?? 0; $i < $count; $i++) {
                    $new[] = $file . ': ' . $entry;
                }
            }
        }

        $this->assertSame(
            [],
            $new,
            'Write these docblocks in full, as the "Docblocks: test code too" section of CLAUDE.md describes. '
            . 'The debt list in tests/unit/Repo/docblock-debt/ only ever shrinks.'
        );
    }

    /**
     * Every docblock-debt/ entry still matches a gap, so a fixed docblock comes
     * off the list in the same change.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('Every debt entry still matches a gap')]
    public function testEveryDebtEntryStillMatchesAGap(): void
    {
        if (getenv('PROCLAIM_SHRINK_DOCBLOCK_DEBT') === '1') {
            self::shrinkDebt();
        }

        $suite = self::suite();
        $paid  = [];

        foreach (self::debt() as $file => $entries) {
            if (!isset($suite[$file])) {
                continue;
            }

            $owed     = array_count_values(self::entries($suite[$file]));
            $declared = array_flip(array_map(static fn (array $d): string => $d['kind'] . ' ' . $d['name'], $suite[$file]));

            foreach ($entries as $entry => $count) {
                if (!isset($declared[strstr($entry, ':', true)])) {
                    continue;
                }

                for ($i = $owed[$entry] ?? 0; $i < $count; $i++) {
                    $paid[] = $file . ': ' . $entry;
                }
            }
        }

        $this->assertSame(
            [],
            $paid,
            'These docblocks are complete now. Delete their entries from tests/unit/Repo/docblock-debt/, '
            . 'or run this test with PROCLAIM_SHRINK_DOCBLOCK_DEBT=1.'
        );
    }

    /**
     * Audit one PHP source: every class, property, constant and method it
     * declares, anonymous classes' members included, with what each docblock
     * lacks.
     *
     * @param   string  $source  The PHP source
     *
     * @return  list<array{kind: string, name: string, line: int, gaps: list<string>}>  One entry per declaration
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function audit(string $source): array
    {
        $tokens       = token_get_all($source);
        $count        = \count($tokens);
        $found        = [];
        $doc          = null;
        $depth        = 0;
        $classDepths  = [];
        $pendingClass = false;
        $parens       = 0;

        for ($i = 0; $i < $count; $i++) {
            $t  = $tokens[$i];
            $id = \is_array($t) ? $t[0] : $t;

            if ($id === '{' || $id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) {
                $depth++;

                if ($pendingClass && $id === '{') {
                    $classDepths[] = $depth;
                    $pendingClass  = false;
                }

                $doc = null;

                continue;
            }

            if ($id === '}') {
                if (end($classDepths) === $depth) {
                    array_pop($classDepths);
                }

                $depth--;
                $doc = null;

                continue;
            }

            if ($id === '(' || $id === ')') {
                $parens += $id === '(' ? 1 : -1;

                continue;
            }

            if ($id === ';') {
                $doc = null;

                continue;
            }

            if ($id === T_DOC_COMMENT) {
                $doc = $t[1];

                continue;
            }

            if ($id === T_COMMENT || \in_array($id, self::MODIFIERS, true)) {
                continue;
            }

            if ($id === T_ATTRIBUTE) {
                for ($d = 1; $d > 0 && ++$i < $count;) {
                    $d += match ($tokens[$i]) {
                        '['     => 1,
                        ']'     => -1,
                        default => 0,
                    };
                }

                continue;
            }

            $inClassBody = $classDepths !== [] && end($classDepths) === $depth;
            $line        = \is_array($t) ? $t[2] : 0;

            $prev = $i - 1;

            while ($prev > 0 && \is_array($tokens[$prev]) && $tokens[$prev][0] === T_WHITESPACE) {
                $prev--;
            }

            $afterColons = $prev >= 0 && \is_array($tokens[$prev]) && $tokens[$prev][0] === T_DOUBLE_COLON;

            if (!$afterColons && \in_array($id, [T_CLASS, T_TRAIT, T_INTERFACE, T_ENUM], true)) {
                $pendingClass = true;
                $j            = self::nextSolid($tokens, $i);

                if (!\is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING) {
                    $doc = null; // an anonymous class

                    continue;
                }

                $kind = 'class';
                $name = $tokens[$j][1];
            } elseif ($id === T_FUNCTION && ($inClassBody || ($classDepths === [] && $depth === 0))) {
                $j = self::nextSolid($tokens, $i);

                if (!\is_array($tokens[$j])) {
                    $doc = null;

                    continue;
                }

                $kind = 'function';
                $name = $tokens[$j][1];
            } elseif ($id === T_CONST && $inClassBody) {
                $kind = 'const';
                $name = null;

                for ($k = $i + 1; $k < $count && $tokens[$k] !== '='; $k++) {
                    if (\is_array($tokens[$k]) && $tokens[$k][0] === T_STRING) {
                        $name = $tokens[$k][1];
                    }
                }
            } elseif ($id === T_VARIABLE && $inClassBody && $parens === 0) {
                $kind = 'property';
                $name = $t[1];
            } elseif ($inClassBody && \in_array($id, self::TYPE_TOKENS, true)) {
                continue;
            } else {
                $doc = null;

                continue;
            }

            $found[] = ['kind' => $kind, 'name' => (string) $name, 'line' => $line, 'gaps' => self::gaps($kind, (string) $name, $doc, $tokens, $j ?? $i)];
            $doc     = null;
        }

        return $found;
    }

    /**
     * What one declaration's docblock lacks.
     *
     * @param   string       $kind    class, function, const or property
     * @param   string       $name    The declaration's name
     * @param   string|null  $doc     Its docblock, or null when it has none
     * @param   array        $tokens  The source's tokens
     * @param   int          $at      The index of a function's name token
     *
     * @return  list<string>  The gaps, in the order the audit reports them
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function gaps(string $kind, string $name, ?string $doc, array $tokens, int $at): array
    {
        if ($doc === null) {
            return ['no docblock'];
        }

        $gaps    = [];
        $inherit = (bool) preg_match('~@inheritDoc~i', $doc);
        $rows    = array_values(array_filter(
            array_map(static fn (string $r): string => trim(preg_replace('~^\s*/?\*+/?~', '', $r)), explode("\n", $doc)),
            static fn (string $r): bool => $r !== ''
        ));

        if (!$inherit && (($rows[0] ?? '@')[0] === '@')) {
            $gaps[] = 'no description';
        }

        if (!preg_match('~@since\s+\S~', $doc)) {
            $gaps[] = 'no @since';
        }

        if ($kind === 'function' && !$inherit) {
            if ($name !== '__construct' && !preg_match('~@return\s+\S~', $doc)) {
                $gaps[] = 'no @return';
            }

            if (preg_match('~@return[ \t]+([^\n]*)~', $doc, $r)) {
                [$type, $description] = self::splitType(rtrim($r[1]));

                if ($type !== 'void' && $description === '') {
                    $gaps[] = 'no @return description';
                }
            }

            if (preg_match('~@return[^\n]*\n\s*\*\s*@since~', $doc)) {
                $gaps[] = 'no blank line between @return and @since';
            }

            if (preg_match('~@param[^\n]*\n\s*\*\s*@return~', $doc)) {
                $gaps[] = 'no blank line between @param and @return';
            }

            preg_match_all('~@param\b[^$\n]*?(\$\w+)~', $doc, $m);

            $params = self::signature($tokens, $at);

            if ($m[1] !== $params) {
                $gaps[] = '@param ' . json_encode($m[1]) . ' vs signature ' . json_encode($params);
            }

            preg_match_all('~@param[ \t]+([^\n]*)~', $doc, $tags);

            foreach ($tags[1] as $text) {
                [$variable, $description] = self::splitParam(rtrim($text));

                if ($variable !== '' && $description === '') {
                    $gaps[] = 'no description for @param ' . $variable;
                }
            }
        }

        if (\in_array($kind, ['property', 'const'], true) && !preg_match('~@var\s+\S~', $doc)) {
            $gaps[] = 'no @var';
        }

        return $gaps;
    }

    /**
     * The parameter names in a function's signature.
     *
     * @param   array  $tokens  The source's tokens
     * @param   int    $at      The index of the function's name token
     *
     * @return  list<string>  The names, $ included, in order
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function signature(array $tokens, int $at): array
    {
        $k = $at;

        while ($tokens[$k] !== '(') {
            $k++;
        }

        $params = [];

        for ($d = 0, $count = \count($tokens); $k < $count; $k++) {
            $x = $tokens[$k];

            if ($x === '(') {
                $d++;
            } elseif ($x === ')') {
                if (--$d === 0) {
                    break;
                }
            } elseif (\is_array($x) && $x[0] === T_VARIABLE && $d === 1) {
                $params[] = $x[1];
            }
        }

        return $params;
    }

    /**
     * Split the text after a param tag into its variable and its description.
     * The type before the variable is skipped as splitType() reads it.
     *
     * @param   string  $text  The rest of the tag's line
     *
     * @return  array{0: string, 1: string}  The variable, then the description; both empty when there is no variable
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function splitParam(string $text): array
    {
        if ($text !== '' && !\in_array($text[0], ['$', '&', '.'], true)) {
            [, $text] = self::splitType($text);
        }

        return preg_match('~^(&?(?:\.\.\.)?\$\w+)\s*(.*)$~', $text, $m) === 1 ? [$m[1], trim($m[2])] : ['', ''];
    }

    /**
     * Split a tag's text into the type it starts with and the rest. Brackets
     * are counted, so a type such as array<string, int> stays whole.
     *
     * @param   string  $text  The rest of the tag's line
     *
     * @return  array{0: string, 1: string}  The type, then the rest, which is empty when nothing follows
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function splitType(string $text): array
    {
        $depth  = 0;
        $length = \strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $c = $text[$i];

            if (str_contains('<{([', $c)) {
                $depth++;
            } elseif (str_contains('>})]', $c)) {
                $depth--;
            } elseif ($depth === 0 && ($c === ' ' || $c === "\t")) {
                break;
            }
        }

        return [substr($text, 0, $i), trim(substr($text, $i))];
    }

    /**
     * The index of the next token after $i that is not whitespace.
     *
     * @param   array  $tokens  The source's tokens
     * @param   int    $i       Where to start
     *
     * @return  int  The index
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function nextSolid(array $tokens, int $i): int
    {
        $j = $i + 1;

        while (\is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }

        return $j;
    }

    /**
     * One "kind name: gap" entry for each gap the declarations have.
     *
     * @param   list<array{kind: string, name: string, line: int, gaps: list<string>}>  $declarations  An audit's result
     *
     * @return  list<string>  The entries, in source order
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function entries(array $declarations): array
    {
        $entries = [];

        foreach ($declarations as $d) {
            foreach ($d['gaps'] as $gap) {
                $entries[] = $d['kind'] . ' ' . $d['name'] . ': ' . $gap;
            }
        }

        return $entries;
    }

    /**
     * The audit of every PHP file under tests/ that git tracks, except the
     * excluded folders.
     *
     * @return  array<string, list<array{kind: string, name: string, line: int, gaps: list<string>}>>  By path
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function suite(): array
    {
        if (self::$suite !== null) {
            return self::$suite;
        }

        $listed = (string) shell_exec(
            'git -C ' . escapeshellarg(self::repoRoot()) . ' ls-files -- ' . escapeshellarg('tests/*.php') . ' 2>/dev/null'
        );

        self::$suite = [];

        foreach (preg_split('/\R/', trim($listed), -1, PREG_SPLIT_NO_EMPTY) as $file) {
            foreach (array_keys(self::EXCLUDED) as $folder) {
                if (str_starts_with($file, $folder)) {
                    continue 2;
                }
            }

            self::$suite[$file] = self::audit((string) file_get_contents(self::repoRoot() . '/' . $file));
        }

        return self::$suite;
    }

    /**
     * Every docblock-debt/ list, merged: how many of each entry each file may
     * still owe.
     *
     * @return  array<string, array<string, int>>  Entry counts, by path
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    private static function debt(): array
    {
        $debt = [];

        foreach (self::debtFiles() as $list) {
            foreach (json_decode((string) file_get_contents($list), true, 512, JSON_THROW_ON_ERROR) as $file => $entries) {
                foreach ($entries as $entry) {
                    $debt[$file][$entry] = ($debt[$file][$entry] ?? 0) + 1;
                }
            }
        }

        return $debt;
    }

    /**
     * Rewrite each docblock-debt/ list to the entries the suite still owes.
     * Entries are only ever removed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    private static function shrinkDebt(): void
    {
        $owed = array_map(static fn (array $declarations): array => array_count_values(self::entries($declarations)), self::suite());

        foreach (self::debtFiles() as $list) {
            $kept = [];

            foreach (json_decode((string) file_get_contents($list), true, 512, JSON_THROW_ON_ERROR) as $file => $entries) {
                foreach ($entries as $entry) {
                    if (($owed[$file][$entry] ?? 0) > 0) {
                        $owed[$file][$entry]--;
                        $kept[$file][] = $entry;
                    }
                }
            }

            ksort($kept);

            foreach ($kept as &$entries) {
                sort($entries);
            }

            unset($entries);

            file_put_contents($list, json_encode($kept, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
        }
    }

    /**
     * The docblock-debt/ lists.
     *
     * @return  list<string>  Their paths
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function debtFiles(): array
    {
        return glob(__DIR__ . '/docblock-debt/*.json') ?: [];
    }

    /**
     * The repository root.
     *
     * @return  string  Its absolute path
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function repoRoot(): string
    {
        return \dirname(__DIR__, 3);
    }
}
