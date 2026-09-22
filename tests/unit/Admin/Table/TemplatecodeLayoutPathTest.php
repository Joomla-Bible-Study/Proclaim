<?php

/**
 * Where template code is written has to be where the front end reads.
 *
 * The two are connected by nothing but a string. The layout folders went lower
 * case in 2022 — "Renaming tmpl folders as joomla wants them small case (like
 * com_contact). Found this out because it wouldn't work on Dreamhost" — and the
 * map that writes into them was not renamed with them. On macOS, where this is
 * developed, both spellings are the same file and nothing looked wrong; on the
 * Linux hosts the rename was made for, saving template code wrote a file the
 * site never loaded.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class TemplatecodeLayoutPathTest extends ProclaimTestCase
{
    /**
     * A site-root path mapped back onto this repository's layout.
     *
     * The map is written as the site sees it — `components/com_proclaim/tmpl/x`
     * and `modules/mod_proclaim/tmpl` — which is `site/tmpl/x` and
     * `modules/site/mod_proclaim/tmpl` in the package.
     *
     * @param   string  $directory  A directory from the map
     *
     * @return  string  Absolute path in this repository
     *
     * @since __DEPLOY_VERSION__
     */
    private static function repositoryPath(string $directory): string
    {
        $base = \dirname(__DIR__, 4);

        if (str_starts_with($directory, 'components/com_proclaim/tmpl/')) {
            return $base . '/site/tmpl/' . basename($directory);
        }

        if ($directory === 'modules/mod_proclaim/tmpl') {
            return $base . '/modules/site/mod_proclaim/tmpl';
        }

        return $base . '/' . $directory;
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Every directory the map writes to is one the package actually ships')]
    public function testEveryMappedDirectoryIsShipped(): void
    {
        $map = CwmtemplatecodeTable::LAYOUT_DIRECTORIES;

        // ⚠️ Not a silent pass over an empty map.
        $this->assertGreaterThanOrEqual(7, \count($map), 'The layout map is missing entries.');

        $missing = [];

        foreach ($map as $type => $directory) {
            $path = self::repositoryPath($directory);

            if (!is_dir($path)) {
                $missing[] = \sprintf('type %d -> %s (looked in %s)', $type, $directory, $path);
            }
        }

        $this->assertSame(
            [],
            $missing,
            "These layout directories are written to but not shipped, so the front end will never read them:\n"
            . implode("\n", $missing)
        );
    }

    /**
     * The specific regression. A capitalised segment resolves on the developer's
     * machine and silently does not on the host.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('No mapped directory carries a capitalised path segment')]
    public function testNoMappedDirectoryIsCapitalised(): void
    {
        $offenders = [];

        foreach (CwmtemplatecodeTable::LAYOUT_DIRECTORIES as $type => $directory) {
            if ($directory !== strtolower($directory)) {
                $offenders[] = \sprintf('type %d -> %s', $type, $directory);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Joomla resolves layouts by the lower-case folder the package ships:\n" . implode("\n", $offenders)
        );
    }

    /**
     * ⚠️ The map existed in three places — store(), delete() and the backup
     * controller — and all three named the pre-2022 folders. One copy is why
     * the rename could be missed twice; this asserts the other two are gone
     * rather than trusting that they were removed.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Nothing outside the table hard-codes a layout directory again')]
    public function testTheMapIsNotCopied(): void
    {
        $base    = \dirname(__DIR__, 4);
        $checked = 0;
        $copies  = [];

        foreach (['admin/src', 'site/src', 'api/src'] as $root) {
            if (!is_dir($base . '/' . $root)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base . '/' . $root));

            foreach ($it as $file) {
                $path = str_replace('\\', '/', $file->getPathname());

                if (!str_ends_with($path, '.php')) {
                    continue;
                }

                $checked++;
                $source = (string) file_get_contents($path);

                // The table itself is where the map belongs.
                if (str_ends_with($path, 'Table/CwmtemplatecodeTable.php')) {
                    continue;
                }

                // ⚠️ Site layouts only. JPATH_ADMINISTRATOR . '/components/
                // com_proclaim/tmpl/…' is the admin view tree, which has
                // nothing to do with where template code is written.
                if (preg_match('#JPATH_(?:ROOT|SITE)\s*\.\s*[\'"][^\'"]*com_proclaim/tmpl/cwm#i', $source)) {
                    $copies[] = substr($path, \strlen($base) + 1);
                }
            }
        }

        $this->assertGreaterThan(100, $checked, 'Too few source files scanned; the scan is broken.');

        $this->assertSame(
            [],
            $copies,
            "These files build a layout path of their own instead of using "
            . "CwmtemplatecodeTable::LAYOUT_DIRECTORIES:\n" . implode("\n", $copies)
        );
    }
}
