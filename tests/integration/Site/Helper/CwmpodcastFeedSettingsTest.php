<?php

/**
 * Integration tests for building the feed of a podcast that is missing a setting.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A podcast row the edit form never saw — restored, migrated or written by SQL
 * — can hold NULL where the feed needs a string, and the build reported PHP's
 * TypeError text for it. It now names the setting instead.
 *
 * A blank value is a different case: podcasts saved before 10.3.0 made these
 * settings required can carry one, and their feeds have always built. Those
 * must keep building.
 *
 * @since  10.7.9
 */
#[CoversClass(Cwmpodcast::class)]
class CwmpodcastFeedSettingsTest extends IntegrationTestCase
{
    use PodcastFeedFixtures;

    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseDriver|null
     * @since  10.7.9
     */
    private ?DatabaseDriver $db = null;

    /**
     * Factory::$language as it was before setUp() pinned it, for tearDown() to restore.
     *
     * @var    mixed
     * @since  10.7.9
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * Open the transaction the test seeds into, and give the feed's date code
     * the language tag it reads.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    /**
     * Roll back everything the test seeded, and restore the language.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have been lost — nothing to roll back.
            }
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * A NULL website can't be passed to the feed's URL helper, so the podcast is
     * skipped with the website named.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    #[TestDox('A podcast with no website is skipped by name, and the feeds beside it are still written')]
    public function testNullWebsiteIsSkippedByName(): void
    {
        $this->assertSkippedByName('website', null, 'JBS_PDC_VALIDATE_WEBSITE_REQUIRED');
    }

    /**
     * A NULL image can't be passed to the artwork helper, so the podcast is
     * skipped with the artwork named.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    #[TestDox('A podcast with no artwork value at all is skipped by name')]
    public function testNullImageIsSkippedByName(): void
    {
        $this->assertSkippedByName('podcastimage', null, 'JBS_PDC_VALIDATE_IMAGE_REQUIRED');
    }

    /**
     * Without a file name there is nothing to write the feed to, blank or NULL.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    #[TestDox('A podcast with a blank file name is skipped by name')]
    public function testBlankFilenameIsSkippedByName(): void
    {
        $this->assertSkippedByName('filename', '', 'JBS_PDC_VALIDATE_FILENAME_REQUIRED');
    }

    /**
     * A blank website and blank artwork still build: podcasts saved before 10.3.0
     * can carry them, and skipping those would stop feeds that reach subscribers.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    #[TestDox('A blank website and blank artwork still build, as they did before the form required them')]
    public function testBlankWebsiteAndImageStillBuild(): void
    {
        $podcast = $this->seedPodcast('Settings blank');

        $this->setColumn($podcast['id'], 'website', '');
        $this->setColumn($podcast['id'], 'podcastimage', '');

        $build = $this->buildFeeds();

        $this->assertArrayHasKey(
            $podcast['filename'],
            $build['written'],
            'A podcast with a blank website stopped being rebuilt; podcasts saved before 10.3.0 can have one'
        );
        $this->assertSame(0, $build['failed']);
    }

    /**
     * A podcast with no media has nothing to build, so it is reported as having
     * none and is not counted as a failed build, whatever settings it lacks.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    #[TestDox('A podcast with no media is reported as having none, not as missing a setting')]
    public function testNoMediaIsReportedBeforeAMissingSetting(): void
    {
        $podcastId = $this->insertPodcast($this->uniqueAlias('feed') . '.xml', null, 'Settings no media');

        $this->setColumn($podcastId, 'website', null);

        $build = $this->buildFeeds();

        $this->assertStringContainsString(Text::_('JBS_CMN_NO_MEDIA_FILES') . ' (Settings no media)', $build['result']);
        $this->assertSame(0, $build['failed'], 'A podcast with nothing to build has not failed to build');
    }

    /**
     * Build with one podcast missing a setting between two complete ones, and
     * check that it alone was skipped, by name.
     *
     * @param   string       $column  The setting to take away
     * @param   string|null  $value   What it holds instead
     * @param   string       $key     The language key naming it
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    private function assertSkippedByName(string $column, ?string $value, string $key): void
    {
        // The run has no ORDER BY. With a complete podcast on each side, one of
        // them is built after the skipped one whichever way rows come back.
        $before  = $this->seedPodcast('Settings before');
        $missing = $this->seedPodcast('Settings missing ' . $column);
        $after   = $this->seedPodcast('Settings after');

        $this->setColumn($missing['id'], $column, $value);

        $build   = $this->buildFeeds();
        $message = Text::_($key);

        $this->assertNotSame($key, $message, 'The language file did not load, so the message could not be checked');
        $this->assertStringContainsString('Settings missing ' . $column . ' - ', $build['result'], 'The run should name the podcast');
        $this->assertStringContainsString($message, $build['result'], 'The run should name the missing setting');
        $this->assertStringNotContainsString('must be of type', $build['result'], "The administrator should read the setting, not PHP's TypeError");
        $this->assertSame(1, $build['failed'], 'The Build feeds button flags a warning from this count');
        $this->assertArrayHasKey($before['filename'], $build['written'], 'A feed beside the skipped one was not rebuilt');
        $this->assertArrayHasKey($after['filename'], $build['written'], 'A feed beside the skipped one was not rebuilt');

        // A blank file name leaves nothing to look the feed up by; the count covers it.
        if ($column !== 'filename') {
            $this->assertArrayNotHasKey($missing['filename'], $build['written'], 'The skipped podcast should not have written a feed');
        }
    }

    /**
     * Build every published feed, keeping what would be written instead of writing it.
     *
     * @return  array{result: string, written: array<string, string>, failed: int}  The run's report, each feed it would have written by file name, and how many it skipped
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    private function buildFeeds(): array
    {
        $builder = new class () extends Cwmpodcast {
            /**
             * Each feed the build would have written, by file name.
             *
             * @var    array<string, string>
             * @since  10.7.9
             */
            public array $written = [];

            /**
             * Keep the feed instead of writing it to the site.
             *
             * @param   string  $file         Where the feed would have been written
             * @param   string  $filecontent  The feed's XML
             *
             * @return  bool  Always true, as a successful write would be
             *
             * @since   10.7.9
             */
            public function writeFile(string $file, string $filecontent): bool
            {
                $this->written[basename($file)] = $filecontent;

                return true;
            }
        };

        $result = $builder->makePodcasts();

        return ['result' => $result, 'written' => $builder->written, 'failed' => $builder->getFailedFeedCount()];
    }

    /**
     * Write one column of a podcast row, NULL included.
     *
     * @param   int          $podcastId  The podcast
     * @param   string       $column     The column
     * @param   string|null  $value      Its new value
     *
     * @return  void
     *
     * @since   10.7.9
     */
    private function setColumn(int $podcastId, string $column, ?string $value): void
    {
        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_podcast'))
                ->set($this->db->quoteName($column) . ' = ' . ($value === null ? 'NULL' : $this->db->quote($value)))
                ->where($this->db->quoteName('id') . ' = ' . $podcastId)
        )->execute();
    }
}
