<?php

/**
 * Integration tests for YouTube log rotation and history reading.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeLogHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1586.
 *
 * rotate() called @rename() with the result discarded. Where rename fails --
 * an NFS mount, an SELinux or AppArmor rule, another process holding the
 * destination -- every later log() call re-checked the still-oversized file,
 * re-failed the same rename, and appended anyway, so the file grew without
 * bound and nothing reported it.
 *
 * getEntries() then read only the active file. The moment rotation fired, the
 * admin log viewer went nearly empty while the recent history sat unread in
 * youtube_events.log.1.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmyoutubeLogHelper::class)]
class CwmyoutubeLogRotationTest extends IntegrationTestCase
{
    private string $file = '';

    private string $backup = '';

    /** @var array<string, string|false> */
    private array $saved = [];

    protected function setUp(): void
    {
        parent::setUp();

        $ref          = new \ReflectionClassConstant(CwmyoutubeLogHelper::class, 'LOG_FILE');
        $this->file   = JPATH_CACHE . $ref->getValue();
        $this->backup = $this->file . '.1';

        $dir = \dirname($this->file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Preserve whatever the site already had; these are real log files.
        foreach ([$this->file, $this->backup] as $path) {
            $this->saved[$path] = file_exists($path) ? (string) file_get_contents($path) : false;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->saved as $path => $contents) {
            if ($contents === false) {
                @unlink($path);
            } else {
                file_put_contents($path, $contents);
            }
        }

        parent::tearDown();
    }

    /**
     * @param   string  $message  Message text
     * @param   string  $level    Log level
     *
     * @return  string  One JSON log line
     */
    private function line(string $message, string $level = 'info'): string
    {
        return json_encode(['date' => '2026-08-06 12:00:00', 'level' => $level, 'message' => $message]);
    }

    /**
     * @param   array<int, array>  $entries  Entries returned by getEntries()
     *
     * @return  string[]
     */
    private function messages(array $entries): array
    {
        return array_map(static fn (array $e): string => $e['message'] ?? '', $entries);
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- history survives rotation
    // -------------------------------------------------------------------------

    #[TestDox('Entries are read from the rotated backup once the active file runs out')]
    public function testRotatedBackupIsRead(): void
    {
        file_put_contents($this->file, $this->line('newest') . "\n");
        file_put_contents($this->backup, $this->line('older-1') . "\n" . $this->line('older-2') . "\n");

        $messages = $this->messages(CwmyoutubeLogHelper::getEntries(10));

        $this->assertContains(
            'older-2',
            $messages,
            'Rotation moved the history one file away and the viewer stopped seeing it — see #1586'
        );
        $this->assertSame(
            ['newest', 'older-2', 'older-1'],
            $messages,
            'Newest first: the active file before the backup, each read in reverse'
        );
    }

    #[TestDox('The backup is not read when the active file already satisfies the limit')]
    public function testBackupNotReadWhenLimitMet(): void
    {
        file_put_contents($this->file, $this->line('a') . "\n" . $this->line('b') . "\n");
        file_put_contents($this->backup, $this->line('old') . "\n");

        $this->assertSame(['b', 'a'], $this->messages(CwmyoutubeLogHelper::getEntries(2)));
    }

    #[TestDox('A level filter keeps drawing from the backup to reach the limit')]
    public function testLevelFilterSpansBothFiles(): void
    {
        file_put_contents(
            $this->file,
            $this->line('info-1') . "\n" . $this->line('err-1', 'error') . "\n"
        );
        file_put_contents(
            $this->backup,
            $this->line('err-2', 'error') . "\n" . $this->line('info-2') . "\n"
        );

        $this->assertSame(
            ['err-1', 'err-2'],
            $this->messages(CwmyoutubeLogHelper::getEntries(10, 'error')),
            'Filtering must not stop at the active file when the limit is unmet'
        );
    }

    #[TestDox('A missing backup is not an error')]
    public function testMissingBackupIsHarmless(): void
    {
        file_put_contents($this->file, $this->line('only') . "\n");
        @unlink($this->backup);

        $this->assertSame(['only'], $this->messages(CwmyoutubeLogHelper::getEntries(10)));
    }

    #[TestDox('Corrupt lines are skipped in both files')]
    public function testCorruptLinesSkipped(): void
    {
        file_put_contents($this->file, "not json\n" . $this->line('good-1') . "\n");
        file_put_contents($this->backup, '"a bare string"' . "\n" . $this->line('good-2') . "\n");

        $this->assertSame(['good-1', 'good-2'], $this->messages(CwmyoutubeLogHelper::getEntries(10)));
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- rotation reports, and bounds growth, when rename fails
    // -------------------------------------------------------------------------

    #[TestDox('Rotation moves an oversized file to the backup')]
    public function testRotationMovesTheFile(): void
    {
        $size = (new \ReflectionClassConstant(CwmyoutubeLogHelper::class, 'MAX_FILE_SIZE'))->getValue();

        file_put_contents($this->file, str_repeat('x', $size + 10));
        @unlink($this->backup);

        (new \ReflectionMethod(CwmyoutubeLogHelper::class, 'rotate'))->invoke(null, $this->file);

        $this->assertFileExists($this->backup, 'The oversized file becomes the backup');
        $this->assertLessThan(
            $size,
            file_exists($this->file) ? filesize($this->file) : 0,
            'The active file must no longer be oversized'
        );
    }

    #[TestDox('A file under the threshold is left alone')]
    public function testSmallFileIsNotRotated(): void
    {
        file_put_contents($this->file, $this->line('small') . "\n");
        @unlink($this->backup);

        (new \ReflectionMethod(CwmyoutubeLogHelper::class, 'rotate'))->invoke(null, $this->file);

        $this->assertFileDoesNotExist($this->backup);
        $this->assertSame(['small'], $this->messages(CwmyoutubeLogHelper::getEntries(10)));
    }

    /**
     * The failure path cannot be provoked portably -- rename() succeeding is
     * the normal case and forcing it to fail needs a filesystem this test
     * cannot rely on. What is pinned instead is that the result is acted upon
     * at all, and that growth is bounded rather than left to continue silently.
     */
    #[TestDox('A failed rename is reported and growth is still bounded')]
    public function testRenameFailureIsHandled(): void
    {
        $ref  = new \ReflectionMethod(CwmyoutubeLogHelper::class, 'rotate');
        $body = implode(
            '',
            \array_slice(
                file($ref->getFileName()),
                $ref->getStartLine() - 1,
                $ref->getEndLine() - $ref->getStartLine() + 1
            )
        );

        $this->assertStringContainsString(
            'if (@rename($file, $backup)) {',
            $body,
            'rename() failing silently let the file grow without bound — see #1586'
        );
        $this->assertStringContainsString('error_log(', $body, 'A broken rotation must be visible to an operator');
        $this->assertStringContainsString(
            '@copy($file, $backup)',
            $body,
            'Copy-then-truncate preserves the history that a bare truncate would discard'
        );

        $copyAt     = strpos($body, '@copy($file, $backup)');
        $truncateAt = strrpos($body, "@file_put_contents(\$file, '')");

        $this->assertLessThan($truncateAt, $copyAt, 'Truncation is the last resort, not the first');
    }
}
