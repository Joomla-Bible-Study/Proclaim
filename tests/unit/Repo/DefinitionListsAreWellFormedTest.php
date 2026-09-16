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
 * Every `<dl>` the package emits must open with a `<dt>`.
 *
 * A definition list whose contents cannot be paired is announced by assistive
 * technology as a list of terms containing no terms — WCAG 2.1.3.1, level A,
 * and invisible to a sighted reader, which is why it lasted.
 *
 * ⚠️ A scan only sees what happened to render. Three templates carried this
 * defect, all copied from Joomla's own `info_block` layout without the `<dt>`
 * that layout has; the WCAG run reported **two**, because the third is a module
 * layout that renders only when that module is published. A file-level check
 * does not care what the site is configured to show.
 *
 * @since __DEPLOY_VERSION__
 */
class DefinitionListsAreWellFormedTest extends ProclaimTestCase
{
    /**
     * Trees whose markup reaches a browser.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array ROOTS = ['site', 'admin/tmpl', 'admin/layouts', 'modules', 'build/media_source'];

    /**
     * Every template and script the package ships.
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

                if (!preg_match('#\.(php|js)$#', $path) || str_contains($path, '/vendor/')) {
                    continue;
                }

                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * ⚠️ Checks the order, not merely the presence of a `<dt>` somewhere in the
     * file. A file can hold a well-formed list and a broken one, and "the file
     * contains a dt" would pass it. Each `<dl>` is examined on its own, and the
     * first tag inside it has to be a `<dt>`.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Every definition list opens with a term before its descriptions')]
    public function testEveryDefinitionListOpensWithATerm(): void
    {
        $base     = \dirname(__DIR__, 3);
        $offences = [];
        $lists    = 0;

        foreach (self::sourceFiles() as $path) {
            $source = (string) file_get_contents($path);

            if (!str_contains($source, '<dl')) {
                continue;
            }

            // Strip PHP and JS comments first: this test's own explanation
            // mentions `<dl>` and `<dd>`, and so do the notes left beside the
            // fixed templates. A comment is not markup.
            $markup = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', $source);

            // Each <dl>…</dl>, or to end of file if it is built in pieces.
            preg_match_all('#<dl\b.*?(?:</dl>|$)#s', (string) $markup, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as [$list, $offset]) {
                $lists++;

                // The first of the two tags to appear must be the term.
                $firstDt = strpos($list, '<dt');
                $firstDd = strpos($list, '<dd');

                if ($firstDd === false) {
                    // Descriptions may be appended elsewhere; nothing to judge.
                    continue;
                }

                if ($firstDt === false || $firstDt > $firstDd) {
                    $line = substr_count(substr((string) $markup, 0, $offset), "\n") + 1;

                    $offences[] = \sprintf(
                        '%s:%d  <dl> reaches a <dd> before any <dt>',
                        substr($path, \strlen($base) + 1),
                        $line
                    );
                }
            }
        }

        // ⚠️ Not a silent pass over an empty scan. If the walk or the pattern
        // breaks, this says so rather than reporting success having found none.
        $this->assertGreaterThan(
            2,
            $lists,
            'Too few definition lists found; the scan is broken, not the markup.'
        );

        $this->assertSame(
            [],
            $offences,
            "A <dl> that reaches a <dd> before a <dt> is announced as a list of terms with no terms\n"
            . "in it (WCAG 2.1.3.1, level A). Joomla's own info_block layout shows the shape:\n"
            . "a <dt class=\"article-info-term\"> first, visually-hidden if it should not be seen.\n"
            . implode("\n", $offences)
        );
    }
}
