<?php

/**
 * The location wizard writes component params and asset rules through bound
 * UPDATEs. All three sites live in methods with no test caller, so these invoke
 * them against the real DB — a mis-bound placeholder throws instead of passing.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmlocationwizardModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmlocationwizardBindTest extends IntegrationTestCase
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
        // Every method here UPDATEs #__extensions params or #__assets rules.
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
     * @param   string  $method  Method name.
     * @param   mixed   ...$args Arguments.
     *
     * @return  mixed
     *
     * @since __DEPLOY_VERSION__
     */
    private function invoke(string $method, ...$args): mixed
    {
        $model = (new \ReflectionClass(CwmlocationwizardModel::class))->newInstanceWithoutConstructor();
        $ref   = new \ReflectionMethod(CwmlocationwizardModel::class, $method);

        return $ref->invoke($model, ...$args);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("dismiss()'s bound :params UPDATE executes")]
    public function testDismissBindExecutes(): void
    {
        $this->invoke('dismiss');

        $this->assertTrue(true, 'dismiss() ran without a bind error');
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("applyWizard()'s bound :params UPDATE executes")]
    public function testApplyWizardBindExecutes(): void
    {
        $this->invoke('applyWizard', [], []);

        $this->assertTrue(true, 'applyWizard() ran without a bind error');
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox("applyPermissions()'s bound :rules UPDATE executes against the component asset")]
    public function testApplyPermissionsBindExecutes(): void
    {
        // 'editor' is a real preset; the com_proclaim asset exists, so this
        // reaches the rules UPDATE rather than the no-asset early return.
        $this->invoke('applyPermissions', [1 => 'editor']);

        $this->assertTrue(true, 'applyPermissions() ran without a bind error');
    }
}
