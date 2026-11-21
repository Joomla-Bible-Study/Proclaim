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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The settings a podcast can't be stored without, the error naming each one,
 * and the artwork it refuses.
 *
 * @since  10.2.0
 */
#[CoversClass(CwmpodcastTable::class)]
class CwmpodcastTableCheckTest extends IntegrationTestCase
{
    /**
     * Each setting check() requires, with the language key of the error naming it.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array REQUIRED = [
        'title'    => 'JBS_PDC_VALIDATE_TITLE_REQUIRED',
        'website'  => 'JBS_PDC_VALIDATE_WEBSITE_REQUIRED',
        'filename' => 'JBS_PDC_VALIDATE_FILENAME_REQUIRED',
    ];

    /**
     * The table under test, holding a complete podcast when each test starts.
     *
     * @var    CwmpodcastTable
     * @since  10.2.0
     */
    private CwmpodcastTable $table;

    /**
     * The folder under JPATH_ROOT that a test wrote artwork to, removed afterwards.
     *
     * @var    string|null
     * @since  __DEPLOY_VERSION__
     */
    private ?string $artworkDir = null;

    /**
     * Start each test from a complete podcast, so it takes away only the setting
     * it is about. A test that left the others empty would be refused for them
     * and pass anyway.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmpodcastTable::class);

        $this->table->title    = 'Sunday Sermons Podcast';
        $this->table->website  = 'example.org';
        $this->table->filename = 'sunday-sermons.xml';
    }

    /**
     * Remove the artwork a test wrote.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->artworkDir !== null) {
            array_map('unlink', glob(JPATH_ROOT . '/' . $this->artworkDir . '/*'));
            rmdir(JPATH_ROOT . '/' . $this->artworkDir);
        }

        parent::tearDown();
    }

    /**
     * A complete podcast is accepted.
     *
     * @return  void
     *
     * @since   10.2.0
     * @throws  \Exception
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->assertTrue($this->table->check());
    }

    /**
     * Artwork is not required. A feed builds without it, and podcasts saved
     * before 10.3.0 may have none; refusing them here would refuse unrelated
     * batch edits too.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    #[TestDox('A podcast without artwork can still be stored')]
    public function testCheckDoesNotRequireArtwork(): void
    {
        $this->table->podcastimage = '';

        $this->assertTrue($this->table->check());
    }

    /**
     * Each required setting, taken away as NULL, as empty and as whitespace.
     *
     * @return  array<string, array{string, ?string}>  The column, and the value it holds instead
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function missingSettingProvider(): array
    {
        $cases = [];

        foreach (array_keys(self::REQUIRED) as $column) {
            $cases[$column . ' null']       = [$column, null];
            $cases[$column . ' empty']      = [$column, ''];
            $cases[$column . ' whitespace'] = [$column, '   '];
        }

        return $cases;
    }

    /**
     * A podcast missing a required setting is refused, with the error that names it.
     *
     * @param   string       $column  The setting taken away
     * @param   string|null  $value   What it holds instead
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    #[DataProvider('missingSettingProvider')]
    #[TestDox('A podcast missing its $column is refused, and the error names it')]
    public function testCheckRefusesAndNamesAMissingSetting(string $column, ?string $value): void
    {
        $this->table->$column = $value;

        try {
            $this->table->check();
            $this->fail('A podcast without its ' . $column . ' was accepted');
        } catch (\UnexpectedValueException $e) {
            $this->assertSame(Text::_(self::REQUIRED[$column]), $e->getMessage());
        }
    }

    /**
     * With several settings missing, the one error names every one of them.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    #[TestDox('Every missing setting is named in the one error')]
    public function testCheckNamesEveryMissingSetting(): void
    {
        $this->table->website  = null;
        $this->table->filename = '';

        try {
            $this->table->check();
            $this->fail('A podcast without a website or file name was accepted');
        } catch (\UnexpectedValueException $e) {
            $this->assertSame(
                Text::_('JBS_PDC_VALIDATE_WEBSITE_REQUIRED') . '; ' . Text::_('JBS_PDC_VALIDATE_FILENAME_REQUIRED'),
                $e->getMessage()
            );
        }
    }

    /**
     * Artwork picked with the media field is read from the path the field
     * stores, and refused when it is smaller than 1400 pixels square.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testCheckRefusesSmallArtworkPickedWithTheMediaField(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = $this->artwork('podcast art.png', 1, 1)
            . '#joomlaImage://local-images/podcast art.png?width=1&height=1';

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(Text::sprintf('JBS_PDC_VALIDATE_IMAGE_TOO_SMALL', 1, 1));
        $this->table->check();
    }

    /**
     * Artwork that is neither JPEG nor PNG is refused.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testCheckRefusesArtworkThatIsNotJpegOrPng(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = $this->artwork('podcast art.gif', 1400, 1400)
            . '#joomlaImage://local-images/podcast art.gif?width=1400&height=1400';

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(Text::_('JBS_PDC_VALIDATE_IMAGE_FORMAT'));
        $this->table->check();
    }

    /**
     * Compliant artwork picked with the media field is accepted.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testCheckAcceptsCompliantArtworkPickedWithTheMediaField(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = $this->artwork('podcast art.png', 1400, 1400)
            . '#joomlaImage://local-images/podcast art.png?width=1400&height=1400';

        $this->assertTrue($this->table->check());
    }

    /**
     * Artwork that names a missing file is not refused here; System Health
     * reports it instead.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function testCheckLeavesMissingArtworkToTheReport(): void
    {
        $this->table->title        = 'Sunday Sermons Podcast';
        $this->table->podcastimage = 'images/tmp-podcast-save-missing/art.png#joomlaImage://local-images/art.png?width=1&height=1';

        $this->assertTrue($this->table->check());
    }

    /**
     * Write a solid image under JPATH_ROOT and return its path as the media
     * field stores it, percent-encoded.
     *
     * @param   string  $name    The file name; a .gif name writes a GIF, any other a PNG
     * @param   int     $width   The width in pixels
     * @param   int     $height  The height in pixels
     *
     * @return  string  The path relative to JPATH_ROOT, percent-encoded
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
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
