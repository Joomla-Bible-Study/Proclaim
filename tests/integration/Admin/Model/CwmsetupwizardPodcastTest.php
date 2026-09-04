<?php

/**
 * The setup wizard's podcast step falls back to the site root when no live_site
 * is configured — the normal case. That fallback called a Joomla 3 method that
 * does not exist on the versions Proclaim supports, so the wizard fataled and
 * no test noticed, because nothing ran the method.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmsetupwizardModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmsetupwizardPodcastTest extends IntegrationTestCase
{
    /**
     * @var DatabaseInterface|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseInterface $db = null;

    /**
     * @var mixed
     * @since __DEPLOY_VERSION__
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @var mixed
     * @since __DEPLOY_VERSION__
     */
    private mixed $previousLiveSite = null;

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
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
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
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
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('createPodcastRecord() falls back to the site root when live_site is empty')]
    public function testCreatePodcastRecordFallsBackToSiteRoot(): void
    {
        // The method skips when a podcast already exists, so clear them inside
        // the transaction to reach the insert.
        $this->db->setQuery('DELETE FROM ' . $this->db->quoteName('#__bsms_podcast'))->execute();

        $ref = new \ReflectionMethod(CwmsetupwizardModel::class, 'createPodcastRecord');
        $id  = $ref->invoke(
            (new \ReflectionClass(CwmsetupwizardModel::class))->newInstanceWithoutConstructor(),
            ['podcast_title' => 'zz wizard podcast', 'podcast_email' => 'zz@example.org']
        );

        $this->assertGreaterThan(0, $id, 'The podcast row should have been created');

        $website = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('website'))
                ->from($this->db->quoteName('#__bsms_podcast'))
                ->where($this->db->quoteName('id') . ' = ' . (int) $id)
        )->loadResult();

        // Before the fix this line was never reached: the undefined Joomla 3
        // method threw first.
        $this->assertNotSame('', (string) $website, 'The podcast website should carry the site root');
    }
}
