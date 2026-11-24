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
 * The settings a podcast can't be stored without, and the error naming each one.
 *
 * @since  10.1.0
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
     * @since  10.1.0
     */
    private CwmpodcastTable $table;

    /**
     * Start each test from a complete podcast, so it takes away only the setting
     * it is about. A test that left the others empty would be refused for them
     * and pass anyway.
     *
     * @return  void
     *
     * @since   10.1.0
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
     * A complete podcast is accepted.
     *
     * @return  void
     *
     * @since   10.1.0
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
}
