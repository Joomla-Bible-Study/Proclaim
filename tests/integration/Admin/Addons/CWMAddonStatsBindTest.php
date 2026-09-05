<?php

/**
 * The platform-stats sync binds two values into builder queries: the sync
 * timestamp on a server UPDATE, and the platform name in a LEFT JOIN condition.
 * Both live in protected static methods with no test caller, so these invoke
 * them against the real DB — a mis-bound placeholder throws instead of passing.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Addons;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CWMAddonStatsBindTest extends IntegrationTestCase
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
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        // updateServerSyncTimestamp() stamps with Factory::getDate(); give the
        // language a tag so that path does not print a warning and fail CI.
        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

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

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * Both methods are protected static and take no $this.
     *
     * @param   string  $method  Method name.
     * @param   mixed   ...$args Arguments.
     *
     * @return  mixed
     *
     * @since __DEPLOY_VERSION__
     */
    private function invoke(string $method, ...$args): mixed
    {
        return (new \ReflectionMethod(CWMAddon::class, $method))->invoke(null, ...$args);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("updateServerSyncTimestamp()'s bound :now UPDATE executes")]
    public function testUpdateServerSyncTimestampBindExecutes(): void
    {
        // No server with this id, so the UPDATE matches nothing -- but a broken
        // :now bind makes it throw before it can match nothing.
        $this->invoke('updateServerSyncTimestamp', 999142);

        $this->assertTrue(true, 'updateServerSyncTimestamp() ran without a bind error');
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("getMediaVideoIds()'s bound :platform LEFT JOIN executes")]
    public function testGetMediaVideoIdsPlatformJoinExecutes(): void
    {
        // A non-empty platform and a batch limit take the LEFT JOIN branch that
        // carries the :platform bind.
        $rows = $this->invoke('getMediaVideoIds', 999142, 'filename', 10, 'youtube', true);

        $this->assertIsArray($rows);
    }
}
