<?php

/**
 * Version-placeholder stamping contract.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A release replaces the @since placeholder with its version only in the files
 * that versionTracking.substituteTokens in cwm-build.config.json reaches: the
 * listed paths, in the listed file types. A placeholder anywhere else stays a
 * placeholder for good.
 *
 * The placeholder is assembled from two halves in this file. Written out, it
 * would sit in tests/, which the release stamps, and be turned into a version
 * number like any other.
 *
 * @since  __DEPLOY_VERSION__
 */
class SincePlaceholderStampingTest extends ProclaimTestCase
{
    /**
     * The file types a release stamps when the config lists none.
     *
     * @var    list<string>
     * @since  __DEPLOY_VERSION__
     */
    private const array DEFAULT_EXTENSIONS = ['php'];

    /**
     * Directories the release never descends into, whatever the paths say.
     *
     * @var    list<string>
     * @since  __DEPLOY_VERSION__
     */
    private const array NEVER_STAMPED = ['vendor', 'node_modules', '.git'];

    /**
     * The repository is read through git, and this file must be in what git
     * lists. Without it, every check below would pass on nothing.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('git lists the repository, so the checks below read something')]
    public function testGitListsTheRepository(): void
    {
        $self = substr(__FILE__, \strlen(self::repoRoot()) + 1);

        $listed = (string) shell_exec(
            'git -C ' . escapeshellarg(self::repoRoot()) . ' ls-files -- ' . escapeshellarg($self) . ' 2>/dev/null'
        );

        $this->assertSame($self, trim($listed), 'git could not list this repository, so nothing below was checked.');
    }

    /**
     * Every placeholder tag sits where a release replaces it. One that does not
     * keeps the placeholder in the source permanently.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('Every placeholder tag sits in a file the release stamps')]
    public function testEveryPlaceholderTagIsStamped(): void
    {
        $missed = [];

        foreach (self::placeholderLines() as $hit) {
            if (self::isTag($hit['text']) && !self::isStamped($hit['file'])) {
                $missed[] = $hit['file'] . ':' . $hit['line'];
            }
        }

        $this->assertSame(
            [],
            $missed,
            'A release will not replace these placeholders, so they would never get a version. Add the directory or '
            . 'file type to versionTracking.substituteTokens in cwm-build.config.json.'
        );
    }

    /**
     * In a stamped file, the placeholder appears only as a tag. A release
     * replaces every occurrence in the file, so anything else, such as a test
     * asserting on the literal string, would be rewritten to a version number.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('A stamped file holds the placeholder only in a @since or @deprecated tag')]
    public function testStampedFilesHoldThePlaceholderOnlyAsATag(): void
    {
        $rewritten = [];

        foreach (self::placeholderLines() as $hit) {
            if (self::isStamped($hit['file']) && !self::isTag($hit['text'])) {
                $rewritten[] = $hit['file'] . ':' . $hit['line'] . ': ' . trim($hit['text']);
            }
        }

        $this->assertSame(
            [],
            $rewritten,
            'A release would rewrite these to a version number. Assemble the string from two halves instead.'
        );
    }

    /**
     * Every stamped path exists. The release skips a path it can't find with
     * only a warning, so a misspelt one silently stamps nothing.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('Every stamped path exists')]
    public function testEveryStampedPathExists(): void
    {
        $missing = array_values(array_filter(
            self::stamping()['paths'],
            static fn (string $path): bool => !file_exists(self::repoRoot() . '/' . ltrim($path, '/'))
        ));

        $this->assertSame([], $missing, 'These substituteTokens paths do not exist, so the release stamps nothing there.');
    }

    /**
     * No stamped path is, contains or sits inside a submodule. A submodule
     * stamps its own version in its own release; stamping it here would write
     * Proclaim's version into another project, which is why plugins/ is listed
     * one group at a time.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('No stamped path reaches into a submodule')]
    public function testNoStampedPathReachesASubmodule(): void
    {
        $modules = (string) @file_get_contents(self::repoRoot() . '/.gitmodules');

        preg_match_all('/^\s*path\s*=\s*(\S+)\s*$/m', $modules, $m);

        $this->assertNotSame([], $m[1], '.gitmodules lists no submodule, so this check would pass on nothing.');

        $overlaps = [];

        foreach (self::stamping()['paths'] as $path) {
            $path = rtrim(ltrim($path, '/'), '/');

            foreach ($m[1] as $module) {
                if ($module === $path || str_starts_with($module, $path . '/') || str_starts_with($path, $module . '/')) {
                    $overlaps[] = $path . ' reaches ' . $module;
                }
            }
        }

        $this->assertSame([], $overlaps);
    }

    /**
     * Every install script a manifest names is stamped. Joomla runs it on each
     * install and update, so it ships, and the release has to reach it through
     * a listed path. A route that exists only in a working copy, such as
     * dev-setup's admin/ symlink to proclaim.script.php, is missing from a
     * fresh clone.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    #[TestDox('Every install script a manifest names is stamped')]
    public function testEveryInstallScriptIsStamped(): void
    {
        $manifests = preg_split(
            '/\R/',
            trim((string) shell_exec(
                'git -C ' . escapeshellarg(self::repoRoot()) . ' grep -l -F ' . escapeshellarg('<scriptfile>')
                . ' -- ' . escapeshellarg('*.xml') . ' 2>/dev/null'
            )),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $this->assertNotSame([], $manifests, 'No manifest names an install script, so this check would pass on nothing.');

        $unstamped = [];

        foreach ($manifests as $manifest) {
            preg_match('~<scriptfile>\s*([^<\s]+)\s*</scriptfile>~', (string) file_get_contents(self::repoRoot() . '/' . $manifest), $m);

            $dir    = \dirname($manifest);
            $script = ($dir === '.' ? '' : $dir . '/') . ($m[1] ?? '');

            if (!self::isStamped($script)) {
                $unstamped[] = $manifest . ' names ' . $script;
            }
        }

        $this->assertSame(
            [],
            $unstamped,
            'A release would leave the placeholders in these install scripts, which ship. List each one in '
            . 'versionTracking.substituteTokens.paths in cwm-build.config.json.'
        );
    }

    /**
     * The placeholder a release replaces.
     *
     * @return  string  The token
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function placeholder(): string
    {
        return '__DEPLOY' . '_VERSION__';
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

    /**
     * Where a release stamps the placeholder, as cwm-build.config.json says.
     *
     * @return  array{paths: list<string>, extensions: list<string>}  The listed paths and file types
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    private static function stamping(): array
    {
        $config = json_decode((string) file_get_contents(self::repoRoot() . '/cwm-build.config.json'), true, 512, JSON_THROW_ON_ERROR);
        $tokens = $config['versionTracking']['substituteTokens'] ?? [];

        return [
            'paths'      => $tokens['paths'] ?? [],
            'extensions' => $tokens['extensions'] ?? self::DEFAULT_EXTENSIONS,
        ];
    }

    /**
     * Every tracked line holding the placeholder. git grep leaves out
     * submodules and ignored build output, as the release does.
     *
     * @return  list<array{file: string, line: int, text: string}>  One entry per line
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function placeholderLines(): array
    {
        $output = (string) shell_exec(
            'git -C ' . escapeshellarg(self::repoRoot()) . ' grep -I -n -F -e ' . escapeshellarg(self::placeholder()) . ' 2>/dev/null'
        );

        $lines = [];

        foreach (preg_split('/\R/', trim($output)) as $hit) {
            if (preg_match('/^([^:]+):(\d+):(.*)$/', $hit, $m)) {
                $lines[] = ['file' => $m[1], 'line' => (int) $m[2], 'text' => $m[3]];
            }
        }

        return $lines;
    }

    /**
     * Whether a line holds the placeholder as a docblock or comment tag.
     *
     * @param   string  $text  The line
     *
     * @return  bool  True for a @since or @deprecated tag carrying the placeholder
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function isTag(string $text): bool
    {
        return (bool) preg_match(
            '~^\s*(?:/\*\*|\*|#|//)\s*@(?:since|deprecated)\s+' . preg_quote(self::placeholder(), '~') . '(?:\s|$)~',
            $text
        );
    }

    /**
     * Whether a release stamps a file: under a listed path, of a listed type,
     * and outside the directories it never enters.
     *
     * @param   string  $file  The file, relative to the repository root
     *
     * @return  bool  True when the release replaces the placeholder in it
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \JsonException
     */
    private static function isStamped(string $file): bool
    {
        $stamping = self::stamping();

        if (!\in_array(pathinfo($file, PATHINFO_EXTENSION), $stamping['extensions'], true)) {
            return false;
        }

        if (array_intersect(explode('/', \dirname($file)), self::NEVER_STAMPED) !== []) {
            return false;
        }

        foreach ($stamping['paths'] as $path) {
            $path = ltrim($path, '/');

            if (str_ends_with($path, '/') ? str_starts_with($file, $path) : ($file === $path || str_starts_with($file, $path . '/'))) {
                return true;
            }
        }

        return false;
    }
}
