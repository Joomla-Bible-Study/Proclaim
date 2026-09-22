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
 * `$deleteFiles` and `$deleteFolders` in the install script name paths the
 * build no longer ships, and postflight deletes them from upgraded sites.
 *
 * ⚠️ A path that is still shipped must never appear there. Until the lists
 * were wired up nothing enforced that, and `/media/com_proclaim/backup` had
 * been sitting on the folder list while `proclaim.xml` shipped it — the folder
 * holding the `.htaccess` and `web.config` that keep backups off the web, and
 * on a live site the archives themselves.
 *
 * @since  __DEPLOY_VERSION__
 */
class ObsoletePathsTest extends ProclaimTestCase
{
    /**
     * Installed path prefix to its source directory in this repository.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const ROOTS = [
        '/administrator/components/com_proclaim' => 'admin',
        '/components/com_proclaim'               => 'site',
        '/media/com_proclaim'                    => 'media',
        '/administrator/modules/'                => 'modules/admin/',
        '/modules/'                              => 'modules/site/',
        '/administrator/language/'               => 'admin/language/',
        '/language/'                             => 'site/language/',
        '/plugins/'                              => 'plugins/',
    ];

    /**
     * Both lists, read from the install script's source.
     *
     * Read rather than instantiated: the script needs the installer to
     * construct, and the lists are plain literals.
     *
     * @return  array<string, string[]>  Keyed by property name
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function lists(): array
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/proclaim.script.php');
        $out    = [];

        foreach (['deleteFiles', 'deleteFolders'] as $property) {
            $start = strpos($source, 'protected $' . $property . ' = [');

            if ($start === false) {
                $out[$property] = [];

                continue;
            }

            $end   = strpos($source, '];', $start);
            $block = substr($source, $start, $end - $start);

            preg_match_all("#'(/[^']+)'#", $block, $matches);
            $out[$property] = $matches[1];
        }

        return $out;
    }

    /**
     * Where a path installs from, or null when it is outside this repository.
     *
     * @param   string  $path  Installed path, rooted at JPATH_ROOT
     *
     * @return  ?string  Repository-relative source path
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function sourcePath(string $path): ?string
    {
        foreach (self::ROOTS as $installed => $repo) {
            if (str_starts_with($path, $installed)) {
                return $repo . substr($path, \strlen($installed));
            }
        }

        return null;
    }

    #[TestDox('No path scheduled for deletion is still shipped')]
    public function testNoShippedPathIsScheduledForDeletion(): void
    {
        $root  = \dirname(__DIR__, 3);
        $lists = self::lists();

        $this->assertNotEmpty($lists['deleteFiles'], 'deleteFiles is empty — this test would pass on nothing.');
        $this->assertNotEmpty($lists['deleteFolders'], 'deleteFolders is empty — this test would pass on nothing.');

        foreach ($lists as $property => $paths) {
            foreach ($paths as $path) {
                $source = self::sourcePath($path);

                if ($source === null) {
                    continue;
                }

                $this->assertFileDoesNotExist(
                    $root . '/' . $source,
                    \sprintf(
                        '%s lists "%s" for deletion, but this build still ships it (%s). Postflight would delete '
                        . 'it from every site that updates.',
                        $property,
                        $path,
                        $source
                    )
                );
            }
        }
    }

    /**
     * Every entry has to map to somewhere, or the check above skips it and
     * reports nothing — the shape of a test that passes by looking away.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Every scheduled path maps to a known install root')]
    public function testEveryPathMapsToAKnownRoot(): void
    {
        foreach (self::lists() as $property => $paths) {
            foreach ($paths as $path) {
                $this->assertNotNull(
                    self::sourcePath($path),
                    \sprintf(
                        '%s lists "%s", which matches no known install root. Add the root to this test, or the '
                        . 'path is silently exempt from the shipped-path check.',
                        $property,
                        $path
                    )
                );
            }
        }
    }
}
