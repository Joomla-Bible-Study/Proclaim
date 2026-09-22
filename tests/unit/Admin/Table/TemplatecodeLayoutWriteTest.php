<?php

/**
 * Template code is written to disk as executable PHP, from a filename a user typed.
 *
 * Two things were wrong and they are tested together because one fix covers
 * both. The filename was never constrained, so a name carrying a path separator
 * placed the file outside the layout directories — reachable, because Joomla's
 * `File::write()` creates the intermediate directory on the way. And the
 * `_JEXEC` guard the writers prepended had no opening tag, so it printed as
 * literal text instead of guarding anything.
 *
 * ⚠️ The assertion that matters for the guard is what the file *outputs*, run
 * in a process that has not defined `_JEXEC` — not whether it parses. The old
 * broken guard parses fine the moment it lands mid-file.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class TemplatecodeLayoutWriteTest extends ProclaimTestCase
{
    /**
     * Scratch directory for the generated layouts.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private string $tmpDir = '';

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tmpDir = sys_get_temp_dir() . '/proclaim-layout-' . uniqid('', true);
        mkdir($this->tmpDir, 0o777, true);
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->tmpDir);

        parent::tearDown();
    }

    /**
     * Run a PHP file in a fresh process and return what it printed.
     *
     * @param   string  $contents  The file to run
     * @param   bool    $jexec     Whether to define `_JEXEC` first, as Joomla would
     *
     * @return  string  Everything the file wrote to stdout
     *
     * @since __DEPLOY_VERSION__
     */
    private function runPhp(string $contents, bool $jexec): string
    {
        $target = $this->tmpDir . '/run-' . uniqid('', true) . '.php';
        file_put_contents($target, $contents);

        $prelude = $jexec ? "define('_JEXEC', 1);" : '';

        // ⚠️ A separate process, so `_JEXEC` from this suite's own bootstrap
        // cannot leak in and make an unguarded file look guarded.
        $command = escapeshellarg(\PHP_BINARY) . ' -d error_reporting=E_ALL -r '
            . escapeshellarg($prelude . ' require ' . var_export($target, true) . ';')
            . ' 2>&1';

        $output = shell_exec($command);

        @unlink($target);

        return (string) $output;
    }

    /**
     * Content a layout might realistically hold.
     *
     * @return  array<string, array{0: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function layoutContentProvider(): array
    {
        return [
            'carries its own guard'           => ["<?php\n\\defined('_JEXEC') or die;\n?>\n<p>guarded</p>\n"],
            'opens with a php tag'            => ["<?php\n\$total = 2 + 2;\necho 'sum=' . \$total;\n"],
            'plain html'                      => ["<p>plain html</p>\n"],
            'html then php'                   => ["<h1>Title</h1>\n<?php echo strtoupper('body'); ?>\n<hr>\n"],
            'mentions the guard in a comment' => ["<?php\n// defined('_JEXEC') or die; is added for us\necho 'ok';\n"],
        ];
    }

    /**
     * The guard is invisible. Prepending it must not change a single byte of
     * what the layout renders, whatever the stored code starts with.
     *
     * @param   string  $content  Stored template code
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('layoutContentProvider')]
    #[TestDox('Adding the guard leaves the rendered output byte-identical')]
    public function testGuardDoesNotChangeOutput(string $content): void
    {
        $raw     = $this->runPhp($content, true);
        $guarded = $this->runPhp(CwmtemplatecodeTable::layoutFileContents($content), true);

        $this->assertSame(
            $raw,
            $guarded,
            'The guard changed what the layout renders. It is meant to be invisible when _JEXEC is defined.'
        );
    }

    /**
     * The whole point of the guard. Without `_JEXEC` the file must render
     * nothing at all — no content, and no guard printed as literal text.
     *
     * @param   string  $content  Stored template code
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('layoutContentProvider')]
    #[TestDox('Without _JEXEC a generated layout renders nothing')]
    public function testGuardStopsDirectAccess(string $content): void
    {
        $output = $this->runPhp(CwmtemplatecodeTable::layoutFileContents($content), false);

        $this->assertSame(
            '',
            $output,
            "A layout reached directly rendered this instead of dying:\n" . $output
        );
    }

    /**
     * ⚠️ Guards the regression directly: the old code prepended the guard with
     * no opening tag, so it printed. Asserting the constant opens with `<?php`
     * is cheap and names the exact failure.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The guard is written as real PHP, not as text')]
    public function testGuardOpensWithAPhpTag(): void
    {
        $this->assertStringStartsWith(
            '<?php',
            CwmtemplatecodeTable::LAYOUT_GUARD,
            'The guard must open a PHP block or it is output, not code.'
        );

        $this->assertStringEndsWith(
            "?>\n",
            CwmtemplatecodeTable::LAYOUT_GUARD,
            'The guard must close its block and swallow its own newline.'
        );
    }

    /**
     * Names that must never compose into a path.
     *
     * @return  array<string, array{0: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function rejectedFilenameProvider(): array
    {
        return [
            'forward slash traversal' => ['../../../configuration'],
            'reachable escape'        => ['x/../../../configuration'],
            'backslash traversal'     => ['..\\..\\configuration'],
            'nested path'             => ['sub/evil'],
            'leading slash'           => ['/etc/passwd'],
            'bare dot dot'            => ['..'],
            'leading dot'             => ['.htaccess'],
            'embedded dot dot'        => ['a..b'],
            'null byte'               => ["ok\0.php"],
            'empty'                   => [''],
            'whitespace only'         => ['   '],
            'space in name'           => ['my layout'],
        ];
    }

    /**
     * @param   string  $filename  A filename that must be refused
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('rejectedFilenameProvider')]
    #[TestDox('A filename that could leave the layout directory is refused')]
    public function testUnsafeFilenamesAreRejected(string $filename): void
    {
        $this->assertFalse(
            CwmtemplatecodeTable::isValidLayoutFilename($filename),
            var_export($filename, true) . ' was accepted as a layout filename.'
        );

        $this->assertNull(
            CwmtemplatecodeTable::layoutPathForRecord(1, $filename),
            var_export($filename, true) . ' composed into a writable path.'
        );
    }

    /**
     * Names that must keep working. ⚠️ Not a vacuous list — a fix that rejects
     * everything would pass the test above and break every existing record.
     *
     * @return  array<string, array{0: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function acceptedFilenameProvider(): array
    {
        return [
            'the seeded name' => ['easy'],
            'shipped name'    => ['simple2'],
            'underscored'     => ['my_layout'],
            'hyphenated'      => ['my-layout'],
            'mixed case'      => ['MyLayout'],
            'digits'          => ['layout2024'],
            'a single dot'    => ['layout.v2'],
        ];
    }

    /**
     * @param   string  $filename  A filename that must still work
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[DataProvider('acceptedFilenameProvider')]
    #[TestDox('An ordinary layout filename still composes')]
    public function testOrdinaryFilenamesAreAccepted(string $filename): void
    {
        $this->assertTrue(
            CwmtemplatecodeTable::isValidLayoutFilename($filename),
            var_export($filename, true) . ' was refused, which would make an existing record unsavable.'
        );

        $path = CwmtemplatecodeTable::layoutPathForRecord(1, $filename);

        $this->assertNotNull($path, var_export($filename, true) . ' did not compose into a path.');

        $expected = JPATH_ROOT . '/' . CwmtemplatecodeTable::LAYOUT_DIRECTORIES[1];

        $this->assertSame(
            $expected,
            \dirname((string) $path),
            'The layout landed outside the directory for its type.'
        );

        $this->assertSame(
            'default_' . $filename . '.php',
            basename((string) $path),
            'The composed filename is not what the front end loads.'
        );
    }

    /**
     * An unknown type has no directory, and that is a different fault from a
     * bad filename — the health check reports them separately.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An unknown template type composes no path')]
    public function testUnknownTypeComposesNoPath(): void
    {
        $this->assertNull(CwmtemplatecodeTable::layoutPathForRecord(0, 'easy'));
        $this->assertNull(CwmtemplatecodeTable::layoutPathForRecord(99, 'easy'));

        // ⚠️ And the filename itself is fine, so the two causes stay tellable apart.
        $this->assertTrue(CwmtemplatecodeTable::isValidLayoutFilename('easy'));
    }

    /**
     * ⚠️ The defect in #2097 was that the composition lived at four call sites
     * and the one that skipped the table had no validation. This asserts the
     * composition is not re-created outside the table, the way the directory
     * map already is.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Nothing outside the table composes a layout filename of its own')]
    public function testCompositionIsNotCopied(): void
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

                // The table is where the composition belongs.
                if (str_ends_with($path, 'Table/CwmtemplatecodeTable.php')) {
                    continue;
                }

                $source = (string) file_get_contents($path);

                // Concatenation ('default_' . $x), interpolation ("default_{$x}"
                // and "default_$x") and sprintf('default_%s.php'). ⚠️ A bare
                // literal such as 'default_main.php' is not composition and is
                // deliberately not matched -- SermonsTemplateFileField lists
                // several of those as known layout names.
                if (preg_match('#default_(?:[\'"]\s*\.|%s|\{?\$)#', $source)) {
                    $copies[] = substr($path, \strlen($base) + 1);
                }
            }
        }

        $this->assertGreaterThan(100, $checked, 'Too few source files scanned; the scan is broken.');

        $this->assertSame(
            [],
            $copies,
            'These files build a layout filename instead of calling '
            . "CwmtemplatecodeTable::layoutPathForRecord():\n" . implode("\n", $copies)
        );
    }
}
