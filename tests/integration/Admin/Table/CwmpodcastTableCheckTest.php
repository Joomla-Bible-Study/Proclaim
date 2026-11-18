<?php

/**
 * Integration tests for CwmpodcastTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmpodcastTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Language\Text;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmpodcastTable::class)]
class CwmpodcastTableCheckTest extends IntegrationTestCase
{
    private CwmpodcastTable $table;

    private ?string $artworkDir = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmpodcastTable::class);
    }

    protected function tearDown(): void
    {
        if ($this->artworkDir !== null) {
            array_map('unlink', glob(JPATH_ROOT . '/' . $this->artworkDir . '/*'));
            rmdir(JPATH_ROOT . '/' . $this->artworkDir);
        }

        parent::tearDown();
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->title = 'Sunday Sermons Podcast';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenTitleNull(): void
    {
        $this->table->title = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTitleEmpty(): void
    {
        $this->table->title = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTitleWhitespace(): void
    {
        $this->table->title = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckRefusesSmallArtworkPickedWithTheMediaField(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = $this->artwork('podcast art.png', 1, 1)
            . '#joomlaImage://local-images/podcast art.png?width=1&height=1';

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(Text::sprintf('JBS_PDC_VALIDATE_IMAGE_TOO_SMALL', 1, 1));
        $this->table->check();
    }

    public function testCheckRefusesArtworkThatIsNotJpegOrPng(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = $this->artwork('podcast art.gif', 1400, 1400)
            . '#joomlaImage://local-images/podcast art.gif?width=1400&height=1400';

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(Text::_('JBS_PDC_VALIDATE_IMAGE_FORMAT'));
        $this->table->check();
    }

    public function testCheckAcceptsCompliantArtworkPickedWithTheMediaField(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = $this->artwork('podcast art.png', 1400, 1400)
            . '#joomlaImage://local-images/podcast art.png?width=1400&height=1400';

        $this->assertTrue($this->table->check());
    }

    public function testCheckLeavesMissingArtworkToTheReport(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = 'images/tmp-podcast-save-missing/art.png#joomlaImage://local-images/art.png?width=1&height=1';

        $this->assertTrue($this->table->check());
    }

    /**
     * Write a solid image under JPATH_ROOT and return its path as the media
     * field stores it, percent-encoded.
     */
    private function artwork(string $name, int $width, int $height): string
    {
        $this->artworkDir ??= 'tests/tmp-podcast-save-' . bin2hex(random_bytes(4));

        if (!is_dir(JPATH_ROOT . '/' . $this->artworkDir)) {
            mkdir(JPATH_ROOT . '/' . $this->artworkDir, 0755, true);
        }

        $image = imagecreatetruecolor($width, $height);
        $file  = JPATH_ROOT . '/' . $this->artworkDir . '/' . $name;

        str_ends_with($name, '.gif') ? imagegif($image, $file) : imagepng($image, $file);

        // Guard: the file must exist where check() looks, or a miss proves nothing
        $this->assertFileExists($file);

        return $this->artworkDir . '/' . rawurlencode($name);
    }
}
