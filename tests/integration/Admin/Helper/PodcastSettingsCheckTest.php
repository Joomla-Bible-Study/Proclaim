<?php

/**
 * Integration tests for the podcast-settings health check.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Health\Check\PodcastSettingsCheck;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Driven against the real database. Every podcast already there is unpublished
 * inside the test's transaction, so the site's own podcasts can't decide a
 * result; the rollback restores them.
 *
 * The by-name assertions read the fingerprint, which carries the podcast ids
 * and the columns they lack and is the same in any language.
 *
 * @since  10.7.9
 */
#[CoversClass(PodcastSettingsCheck::class)]
class PodcastSettingsCheckTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseDriver|null
     * @since  10.7.9
     */
    private ?DatabaseDriver $db = null;

    /**
     * Factory::$language as it was before a test pinned it, for tearDown() to restore.
     *
     * @var    mixed
     * @since  10.7.9
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * Whether this test pinned Factory::$language, which may have been null before.
     *
     * @var    bool
     * @since  10.7.9
     */
    private bool $languagePinned = false;

    /**
     * Open the transaction, and unpublish the site's own podcasts inside it.
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

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_podcast'))
                ->set($this->db->quoteName('published') . ' = 0')
        )->execute();
    }

    /**
     * Roll back the test's rows and the unpublishing, and restore the language.
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

        if ($this->languagePinned) {
            Factory::$language = $this->previousFactoryLanguage;
        }

        parent::tearDown();
    }

    /**
     * A published podcast with every setting its feed needs is not a finding.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('A published podcast with every setting passes')]
    public function testCompletePodcastIsOk(): void
    {
        $this->insertPodcast([]);

        $this->assertSame(HealthStatus::Ok, $this->check()->status);
    }

    /**
     * A podcast the build skips is a Warning, and links to the podcasts list.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('A podcast whose feed is not being rebuilt is a Warning')]
    public function testUnbuildablePodcastIsAWarning(): void
    {
        $id     = $this->insertPodcast(['website' => null]);
        $result = $this->check();

        $this->assertSame(HealthStatus::Warning, $result->status);
        $this->assertSame($id . ':website', $result->fingerprint);
        $this->assertSame('index.php?option=com_proclaim&view=cwmpodcasts', $result->actionLink);
    }

    /**
     * A podcast that still builds, but without settings directories require,
     * is a Notice.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('A podcast that still builds without settings directories need is a Notice')]
    public function testIncompletePodcastIsANotice(): void
    {
        $id     = $this->insertPodcast(['description' => '', 'editor_email' => '']);
        $result = $this->check();

        $this->assertSame(HealthStatus::Notice, $result->status);
        $this->assertSame($id . ':description,editor_email', $result->fingerprint);
    }

    /**
     * A blank website still builds, so on its own it is a Notice. The line
     * between Warning and Notice is the build's own rule; if this reads Warning,
     * the check and the build disagree about what stops a feed.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('A blank website still builds, so on its own it is a Notice')]
    public function testBlankWebsiteIsANotice(): void
    {
        $id     = $this->insertPodcast(['website' => '']);
        $result = $this->check();

        $this->assertSame(HealthStatus::Notice, $result->status);
        $this->assertSame($id . ':website', $result->fingerprint);
    }

    /**
     * With one podcast skipped and another only incomplete, the result is a
     * Warning that still names both.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('A feed that is not being rebuilt outranks one that is only incomplete')]
    public function testWarningOutranksNotice(): void
    {
        $unbuildable = $this->insertPodcast(['podcastimage' => null]);
        $incomplete  = $this->insertPodcast(['description' => '']);
        $result      = $this->check();

        $this->assertSame(HealthStatus::Warning, $result->status);
        $this->assertSame($unbuildable . ':podcastimage;' . $incomplete . ':description', $result->fingerprint);
    }

    /**
     * An unpublished podcast is in no feed, so what it lacks is not a finding.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('An unpublished podcast is in no feed, so it is not a finding')]
    public function testUnpublishedPodcastIsIgnored(): void
    {
        $this->insertPodcast(['website' => null, 'published' => 0]);

        $this->assertSame(HealthStatus::Ok, $this->check()->status);
    }

    /**
     * The finding's sentence names each podcast and the setting it lacks. It is
     * translated, so this test reads it through a Language of its own: loading
     * the strings into the application's would reach later tests that expect
     * bare keys.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    #[TestDox('The finding names each podcast and what it is missing')]
    public function testDetailNamesThePodcastAndTheSetting(): void
    {
        $language = new Language('en-GB');
        $language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);

        $this->previousFactoryLanguage = Factory::$language;
        $this->languagePinned          = true;
        Factory::$language             = $language;

        $id     = $this->insertPodcast(['title' => 'Settings detail podcast', 'website' => null]);
        $detail = $this->check()->detail;

        $this->assertStringContainsString('Settings detail podcast (#' . $id . '): Website URL is required', $detail);
        $this->assertStringContainsString('not being rebuilt', $detail);
    }

    /**
     * Insert a published podcast with every feed setting, changed by $overrides.
     *
     * insertObject() leaves out NULL properties, so a NULL override leaves the
     * column at its default, which for these columns is NULL.
     *
     * @param   array<string, mixed>  $overrides  Columns to change
     *
     * @return  int  The new podcast id
     *
     * @since   10.7.9
     */
    private function insertPodcast(array $overrides): int
    {
        $row = (object) array_merge(
            [
                'title'        => 'Settings check podcast',
                'description'  => '<p>Sermons</p>',
                'editor_name'  => 'Podcast Editor',
                'editor_email' => 'editor@example.org',
                'website'      => 'example.org',
                'filename'     => $this->uniqueAlias('settings-check') . '.xml',
                'podcastimage' => 'images/podcast.jpg',
                // Older dev databases hold this NOT NULL with no default.
                'podcastlink' => '',
                'published'   => 1,
                'access'      => 1,
                'language'    => '*',
            ],
            $overrides
        );

        $this->db->insertObject('#__bsms_podcast', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Run the check under test.
     *
     * @return  HealthResult  Its result
     *
     * @since   10.7.9
     */
    private function check(): HealthResult
    {
        return (new PodcastSettingsCheck())->run();
    }
}
