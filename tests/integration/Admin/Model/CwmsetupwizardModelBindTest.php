<?php

/**
 * The setup wizard's default-creation loops build a bound COUNT before each
 * insert. Both bind the row type inside a foreach — the failure mode a source-
 * text test cannot see. These invoke the two private loops against the real DB
 * so a mis-bound placeholder throws instead of passing silently.
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
class CwmsetupwizardModelBindTest extends IntegrationTestCase
{
    /**
     * @var DatabaseInterface|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseInterface $db = null;

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

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        // Both methods insert when nothing exists yet; roll it all back.
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

        parent::tearDown();
    }

    /**
     * Neither method touches $this, so an unconstructed instance is enough to
     * reach the private loop.
     *
     * @param   string  $method  Private method name.
     * @param   mixed   ...$args Arguments.
     *
     * @return  mixed
     *
     * @since __DEPLOY_VERSION__
     */
    private function invoke(string $method, ...$args): mixed
    {
        $model = (new \ReflectionClass(CwmsetupwizardModel::class))->newInstanceWithoutConstructor();
        $ref   = new \ReflectionMethod(CwmsetupwizardModel::class, $method);

        return $ref->invoke($model, ...$args);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("createDefaultServers()'s bound per-type existence check executes")]
    public function testCreateDefaultServersBoundTypeCheckExecutes(): void
    {
        // A broken ':type' bind makes the COUNT query throw; a clean run
        // returns the array of created (or already-present, hence skipped)
        // servers.
        $created = $this->invoke('createDefaultServers', 'local', []);

        $this->assertIsArray($created);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("registerScheduledTasks()'s bound per-type existence check executes")]
    public function testRegisterScheduledTasksBoundTypeCheckExecutes(): void
    {
        $created = $this->invoke('registerScheduledTasks', ['backup']);

        $this->assertIsArray($created);
    }
}
