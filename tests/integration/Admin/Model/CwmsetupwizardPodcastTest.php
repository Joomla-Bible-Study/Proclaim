<?php

/**
 * Integration tests for the podcast the setup wizard creates.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmsetupwizardModel;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The setup wizard's podcast step falls back to the site root when no live_site
 * is configured — the normal case. That fallback called a Joomla 3 method that
 * does not exist on the versions Proclaim supports, so the wizard fataled and
 * no test noticed, because nothing ran the method.
 *
 * @since  10.7.0
 */
class CwmsetupwizardPodcastTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.0
     */
    private ?DatabaseInterface $db = null;

    /**
     * Factory::$language as it was before setUp() pinned it, for tearDown() to restore.
     *
     * @var    mixed
     * @since  10.7.0
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * The site's live_site setting, which setUp() empties and tearDown() restores.
     *
     * @var    mixed
     * @since  10.7.0
     */
    private mixed $previousLiveSite = null;

    /**
     * Empty live_site, so the wizard takes its site-root fallback, and open the
     * transaction the test writes into.
     *
     * @return  void
     *
     * @since   10.7.0
     * @throws  \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $app                    = Factory::getApplication();
        $this->previousLiveSite = $app->get('live_site', '');
        // The bug only shows when live_site is empty, so the root fallback runs.
        $app->set('live_site', '');

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);

        // The wizard skips its podcast when one already exists, so clear them
        // inside the transaction to reach the insert.
        $this->db->setQuery('DELETE FROM ' . $this->db->quoteName('#__bsms_podcast'))->execute();
    }

    /**
     * Roll back the test's rows, and restore live_site and the language.
     *
     * @return  void
     *
     * @since   10.7.0
     * @throws  \Exception
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away.
            }
        }

        Factory::getApplication()->set('live_site', $this->previousLiveSite);
        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * With live_site empty, the wizard's podcast takes the site root as its website.
     *
     * @return  void
     *
     * @since   10.7.0
     * @throws  \ReflectionException
     */
    #[TestDox('createPodcastRecord() falls back to the site root when live_site is empty')]
    public function testCreatePodcastRecordFallsBackToSiteRoot(): void
    {
        $id = $this->createPodcastRecord();

        $this->assertGreaterThan(0, $id, 'The podcast row should have been created');

        $website = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('website'))
                ->from($this->db->quoteName('#__bsms_podcast'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadResult();

        // Before the fix this line was never reached: the undefined Joomla 3
        // method threw first.
        $this->assertNotSame('', (string) $website, 'The podcast website should carry the site root');
    }

    /**
     * The wizard asks for no artwork. Its podcast must store it blank, not NULL:
     * the feed build can't take a NULL, and would skip the podcast until someone
     * edited it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('The podcast the wizard creates has everything its feed needs to be built')]
    public function testCreatedPodcastCanBeBuilt(): void
    {
        $id = $this->createPodcastRecord();

        $podcast = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName(array_keys(Cwmpodcast::FEED_SETTINGS)))
                ->from($this->db->quoteName('#__bsms_podcast'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadObject();

        $this->assertSame([], Cwmpodcast::unbuildableSettings($podcast), 'The feed build would skip the podcast the wizard created');
        $this->assertSame('', $podcast->podcastimage, 'The wizard asks for no artwork, so it should store a blank one');
    }

    /**
     * Run the wizard's podcast step with a title and an editor email.
     *
     * @return  int  The new podcast id, or 0 when the wizard skipped it
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    private function createPodcastRecord(): int
    {
        return (int) (new \ReflectionMethod(CwmsetupwizardModel::class, 'createPodcastRecord'))->invoke(
            (new \ReflectionClass(CwmsetupwizardModel::class))->newInstanceWithoutConstructor(),
            ['podcast_title' => 'zz wizard podcast', 'podcast_email' => 'zz@example.org']
        );
    }
}
