<?php

/**
 * Integration tests for building every podcast feed when one of them fails.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * makePodcasts() builds every published podcast in one run. An Error from one
 * podcast used to end the run, so no feed after it was rebuilt (#2365).
 *
 * The failure here is real code meeting a row it cannot read: the failing
 * podcast's episodes come back with no params, so the episode loop calls a
 * method on null. That is an Error, not an Exception, and it happens after the
 * feed's channel header is built, where the #2339 TypeError happened.
 *
 * @since  10.7.9
 */
#[CoversClass(Cwmpodcast::class)]
class CwmpodcastFeedIsolationTest extends IntegrationTestCase
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
     * One podcast's feed throwing an Error is reported and counted, and the
     * feeds built around it are still written, with nothing of it in them.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \Exception
     */
    #[TestDox('A podcast whose feed throws an Error is reported, and the feeds around it are still written')]
    public function testOneFailingPodcastDoesNotStopTheOthers(): void
    {
        // The run has no ORDER BY. With a healthy podcast on each side of the
        // failing one, one of them is built after it whichever way rows come back.
        $before  = $this->seedPodcast('Isolation before');
        $failing = $this->seedPodcast('Isolation failing');
        $after   = $this->seedPodcast('Isolation after');

        $helper = new class () extends Cwmpodcast {
            /**
             * Each feed the build would have written, by file name.
             *
             * @var    array<string, string>
             * @since  10.7.9
             */
            public array $written = [];

            /**
             * The podcast whose episodes come back unreadable.
             *
             * @var    int
             * @since  10.7.9
             */
            public int $failingId = 0;

            /**
             * The real episodes, with the failing podcast's params taken away, so
             * reading them throws an Error partway through its feed.
             *
             * @param   int     $id     The podcast
             * @param   string  $limit  The LIMIT clause, or an empty string
             *
             * @return  array  The podcast's episodes
             *
             * @since   10.7.9
             */
            public function getEpisodes(int $id, string $limit): array
            {
                $episodes = parent::getEpisodes($id, $limit);

                if ($id === $this->failingId) {
                    foreach ($episodes as $episode) {
                        $episode->params = null;
                    }
                }

                return $episodes;
            }

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

        $helper->failingId = $failing['id'];

        $result = $helper->makePodcasts();

        $this->assertArrayHasKey($before['filename'], $helper->written, 'A feed beside the failing one was not rebuilt — see #2365');
        $this->assertArrayHasKey($after['filename'], $helper->written, 'A feed beside the failing one was not rebuilt — see #2365');
        $this->assertArrayNotHasKey($failing['filename'], $helper->written, 'The failing podcast should not have written a feed');

        $this->assertStringContainsString('Isolation failing', $result, 'The run should name the feed that failed');
        $this->assertSame(1, $helper->getFailedFeedCount(), 'The Build feeds button flags a warning from this count');
        $this->assertStringNotContainsString(
            'Isolation failing',
            $helper->written[$after['filename']],
            'Nothing from the failing podcast may reach the next feed'
        );
    }
}
