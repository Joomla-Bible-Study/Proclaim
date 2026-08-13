<?php

/**
 * Unit tests for CwmprotectedStorage
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedStorage;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * The directory restricted media lives in, and when its verdict goes stale.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmprotectedStorageTest extends ProclaimTestCase
{
    // -----------------------------------------------------------------
    // where it lives
    // -----------------------------------------------------------------

    /**
     * Under images/biblestudy/ with the rest of the user content, so a backup
     * that collects media collects this too and a host move does not leave a
     * second location to discover.
     */
    public function testItLivesUnderTheUsualMediaTree(): void
    {
        self::assertStringStartsWith(
            'images/biblestudy/',
            CwmprotectedStorage::RELATIVE_PATH,
            'Protected media belongs with the other user content, not in its own tree.'
        );
    }

    public function testPathAndUrlDescribeTheSameLocation(): void
    {
        self::assertStringEndsWith(CwmprotectedStorage::RELATIVE_PATH, CwmprotectedStorage::path());
        self::assertStringEndsWith(CwmprotectedStorage::RELATIVE_PATH, CwmprotectedStorage::url());
    }

    /** No doubled or missing separator, whatever JPATH_ROOT ends with. */
    public function testThePathIsWellFormed(): void
    {
        self::assertStringNotContainsString('//', substr(CwmprotectedStorage::path(), 1));
    }

    // -----------------------------------------------------------------
    // when a verdict is taken again
    // -----------------------------------------------------------------

    /** Never checked is always due — absence of a verdict is not a good one. */
    public function testAVerdictThatWasNeverTakenIsDue(): void
    {
        self::assertTrue(CwmprotectedStorage::isRecheckDue(null, 1_000_000));
        self::assertTrue(CwmprotectedStorage::isRecheckDue(0, 1_000_000));
    }

    public function testAFreshVerdictIsTrusted(): void
    {
        $now = 1_000_000_000;

        self::assertFalse(
            CwmprotectedStorage::isRecheckDue($now - 60, $now),
            'A verdict taken a minute ago should not trigger another HTTP probe.'
        );
    }

    /**
     * The thing being watched for is a host migration or a switch to nginx,
     * neither of which announces itself — so a verdict has a shelf life.
     */
    public function testAStaleVerdictIsRetaken(): void
    {
        $now = 1_000_000_000;

        self::assertTrue(
            CwmprotectedStorage::isRecheckDue($now - CwmprotectedStorage::RECHECK_SECONDS - 1, $now)
        );
        self::assertTrue(
            CwmprotectedStorage::isRecheckDue($now - CwmprotectedStorage::RECHECK_SECONDS, $now),
            'Exactly at the boundary counts as due.'
        );
    }

    /**
     * A timestamp ahead of now means the clock moved or the value was edited.
     * Trusting it would park a stale verdict indefinitely.
     */
    public function testAFutureTimestampIsNotTrusted(): void
    {
        $now = 1_000_000_000;

        self::assertTrue(CwmprotectedStorage::isRecheckDue($now + 86_400, $now));
    }

    // -----------------------------------------------------------------
    // what can be moved in
    // -----------------------------------------------------------------

    /** Only a file we serve can be relocated here. */
    public function testOnlyOurOwnFilesCanBeMovedIn(): void
    {
        self::assertTrue(
            CwmprotectedStorage::canHold(\Joomla\CMS\Uri\Uri::root() . 'images/biblestudy/a.mp3')
        );
        self::assertFalse(
            CwmprotectedStorage::canHold('https://s3.example.org/bucket/a.mp3'),
            'A remote object is not ours to relocate — which is not the same as it being unprotectable.'
        );
    }

    // -----------------------------------------------------------------
    // path containment — the check that must not be decorative
    // -----------------------------------------------------------------

    /**
     * The sibling-prefix case. Without the trailing separator, /var/www-evil
     * passes a prefix test against /var/www and the guard means nothing.
     */
    public function testASiblingDirectoryIsNotInside(): void
    {
        self::assertFalse(CwmprotectedStorage::isInside('/var/www-evil/x.mp3', '/var/www'));
        self::assertFalse(CwmprotectedStorage::isInside('/var/wwwsomething', '/var/www'));
    }

    public function testRealChildrenAreInside(): void
    {
        self::assertTrue(CwmprotectedStorage::isInside('/var/www/x.mp3', '/var/www'));
        self::assertTrue(CwmprotectedStorage::isInside('/var/www/deep/er/x.mp3', '/var/www'));
        self::assertTrue(
            CwmprotectedStorage::isInside('/var/www', '/var/www'),
            'The root itself is inside itself.'
        );
    }

    public function testATrailingSlashOnTheRootDoesNotBreakIt(): void
    {
        self::assertTrue(CwmprotectedStorage::isInside('/var/www/x.mp3', '/var/www/'));
    }

    public function testEmptyPathsAreNeverInside(): void
    {
        self::assertFalse(CwmprotectedStorage::isInside('', '/var/www'));
        self::assertFalse(CwmprotectedStorage::isInside('/var/www/x', ''));
    }

    // -----------------------------------------------------------------
    // collisions — two services, one sermon.mp3
    // -----------------------------------------------------------------

    public function testAFreeNameIsUsedAsIs(): void
    {
        $dir = $this->tempDir();

        try {
            self::assertSame('sermon.mp3', CwmprotectedStorage::uniqueName($dir, 'sermon.mp3'));
        } finally {
            $this->cleanup($dir);
        }
    }

    /** Overwriting would destroy a file this class exists to look after. */
    public function testATakenNameIsNotOverwritten(): void
    {
        $dir = $this->tempDir();
        file_put_contents($dir . '/sermon.mp3', 'first');

        try {
            $name = CwmprotectedStorage::uniqueName($dir, 'sermon.mp3');

            self::assertNotSame('sermon.mp3', $name);
            self::assertStringEndsWith('.mp3', $name, 'The extension has to survive.');
            self::assertFileDoesNotExist($dir . '/' . $name);
        } finally {
            $this->cleanup($dir);
        }
    }

    public function testAnExtensionlessNameStillGetsUniquified(): void
    {
        $dir = $this->tempDir();
        file_put_contents($dir . '/README', 'first');

        try {
            self::assertNotSame('README', CwmprotectedStorage::uniqueName($dir, 'README'));
        } finally {
            $this->cleanup($dir);
        }
    }

    // -----------------------------------------------------------------
    // moving in
    // -----------------------------------------------------------------

    /** The stored filename is administrator-editable, so it is untrusted. */
    public function testItRefusesPathsThatEscapeTheSiteRoot(): void
    {
        foreach (['../../../etc/passwd', '/etc/passwd', ''] as $path) {
            self::assertNull(
                CwmprotectedStorage::moveInto($path),
                \sprintf('%s must not be movable.', $path === '' ? '(empty)' : $path)
            );
        }
    }

    public function testItRefusesAFileThatDoesNotExist(): void
    {
        self::assertNull(CwmprotectedStorage::moveInto('images/biblestudy/nope-' . bin2hex(random_bytes(4)) . '.mp3'));
    }

    /**
     * @return string
     */
    private function tempDir(): string
    {
        $dir = sys_get_temp_dir() . '/cwm-ps-' . bin2hex(random_bytes(6));
        mkdir($dir, 0o777, true);

        return $dir;
    }

    /**
     * @param   string  $dir  Directory to remove.
     *
     * @return void
     */
    private function cleanup(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $f) {
            @unlink($f);
        }

        @rmdir($dir);
    }
}
