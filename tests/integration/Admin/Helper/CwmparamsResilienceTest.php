<?php

/**
 * Integration tests for Cwmparams' behaviour on corrupt/missing data.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1567.
 *
 * 1. setCompParams() built `new Registry($table->params)` unguarded. The
 *    comment there claimed Registry tolerates a malformed params column; it
 *    does not. Registry only shrugs off a value it doesn't recognise as JSON
 *    at all -- a truncated write still starts with '{', and
 *    Json::stringToObject() throws \RuntimeException for exactly that shape.
 *    Its only caller, CwmlicenseController::accept(), has no try/catch, so a
 *    corrupt row produced a fatal that hard-blocked every admin action gated
 *    behind license acceptance.
 *
 * 2. getAdmin() assigned loadObject()'s null straight to the non-nullable
 *    `public static object $admin` when the singleton #__bsms_admin row was
 *    missing, raising "TypeError: Cannot assign null to property ... of type
 *    object" and breaking every getAdmin()->params caller.
 *
 * Note on testing finding 2: getAdmin() memoises into a typed static, and PHP
 * offers no way to return a typed static to its uninitialised state (verified:
 * ReflectionProperty::setValue(null, null) raises TypeError). The missing-row
 * branch therefore cannot be re-entered once any earlier test in the process
 * has called getAdmin(). It is covered here by the runtime invariant every
 * caller depends on, plus a structural pin on the guard itself.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmparams::class)]
class CwmparamsResilienceTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- malformed params must not be fatal
    // -------------------------------------------------------------------------

    /**
     * The corruption shapes an interrupted or truncated write actually leaves
     * behind. Registry throws for these because they still parse as attempted
     * JSON; a value it cannot recognise as JSON at all is silently tolerated,
     * which is why the original "Registry tolerates malformed params" comment
     * looked true and was not.
     *
     * @return array<string, array{0: string}>
     */
    public static function corruptParamsProvider(): array
    {
        return [
            'truncated mid-object' => ['{"analytics_enabled":"1"'],
            'truncated mid-string' => ['{"org_name":"First Bapt'],
            'json-ish garbage'     => ['{not json at all}'],
        ];
    }

    #[DataProvider('corruptParamsProvider')]
    #[TestDox('A corrupt params column is recovered rather than fatal')]
    public function testSetCompParamsRecoversFromCorruptParams(string $corrupt): void
    {
        $extensionId = $this->ensureExtensionRow();
        $this->writeParams($extensionId, $corrupt);

        // Pre-fix this threw RuntimeException straight out of the helper and
        // out of CwmlicenseController::accept(), which does not catch it --
        // hard-blocking every admin action behind license acceptance.
        Cwmparams::setCompParams(['jbs_license' => '1']);

        $params = new Registry($this->readParams($extensionId));

        $this->assertSame(
            '1',
            $params->get('jbs_license'),
            'The write must land, repairing the row rather than failing on it — see #1567'
        );
    }

    #[TestDox('Valid params are merged, not replaced')]
    public function testSetCompParamsPreservesExistingValidParams(): void
    {
        $extensionId = $this->ensureExtensionRow();
        $this->writeParams($extensionId, '{"existing_setting":"keep-me"}');

        Cwmparams::setCompParams(['newly_added' => 'added']);

        $stored = new Registry($this->readParams($extensionId));

        $this->assertSame(
            'keep-me',
            $stored->get('existing_setting'),
            'A readable params column must be merged into, never discarded'
        );
        $this->assertSame('added', $stored->get('newly_added'));
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- a missing admin row must not be a TypeError
    // -------------------------------------------------------------------------

    #[TestDox('getAdmin() always returns an object whose params is a Registry')]
    public function testGetAdminAlwaysReturnsAUsableShape(): void
    {
        $admin = Cwmparams::getAdmin();

        $this->assertIsObject($admin);
        $this->assertObjectHasProperty(
            'params',
            $admin,
            'Every caller does getAdmin()->params->get(...)'
        );
        $this->assertInstanceOf(
            Registry::class,
            $admin->params,
            'params must be a Registry on both the real and fallback paths — see #1567'
        );
        $this->assertObjectHasProperty(
            'user_id',
            $admin,
            'user_id was previously only set inside the params branch, so some shapes lacked it'
        );
    }

    #[TestDox('getAdmin() guards against assigning a falsy row to the typed static')]
    public function testGetAdminGuardsTheMissingRowCase(): void
    {
        // The memoised typed static cannot be reset (PHP has no way to
        // uninitialise one), so pin the guard structurally: without it,
        // loadObject()'s null reaches `self::$admin = $admin` and PHP raises
        // "Cannot assign null to property ... of type object".
        $ref   = new \ReflectionMethod(Cwmparams::class, 'getAdmin');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertMatchesRegularExpression(
            '/if\s*\(!\$admin\)\s*\{/',
            $body,
            'getAdmin() must handle a missing #__bsms_admin row before assigning — see #1567'
        );
        $this->assertStringContainsString(
            'new Registry()',
            $body,
            'The fallback must supply an empty Registry so ->params->get() keeps working'
        );

        // The guard has to precede the assignment to be worth anything.
        $this->assertLessThan(
            strpos($body, 'self::$admin = $admin'),
            strpos($body, 'if (!$admin)'),
            'The missing-row guard must run before the static is assigned'
        );
    }

    #[TestDox('The typed static genuinely rejects null, so the guard is load-bearing')]
    public function testTypedStaticRejectsNull(): void
    {
        // Documents why the guard exists: this is the exact failure the
        // unguarded assignment produced.
        $probe = new class () {
            public static object $admin;
        };

        $this->expectException(\TypeError::class);

        (new \ReflectionProperty($probe::class, 'admin'))->setValue(null, null);
    }

    // -------------------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------------------

    /**
     * @param   int     $extensionId  Row to update
     * @param   string  $params       Raw params column value
     *
     * @return  void
     */
    private function writeParams(int $extensionId, string $params): void
    {
        $update               = new \stdClass();
        $update->extension_id = $extensionId;
        $update->params       = $params;

        $this->db->updateObject('#__extensions', $update, 'extension_id');
    }

    /**
     * @param   int  $extensionId  Row to read
     *
     * @return  string
     */
    private function readParams(int $extensionId): string
    {
        return (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('params'))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('extension_id') . ' = ' . $extensionId)
        )->loadResult();
    }

    /**
     * Find or create the com_proclaim #__extensions row, inside the test
     * transaction. CI's fixture imports raw SQL rather than running the Joomla
     * installer, so the row is absent there.
     *
     * @return  int  extension_id
     */
    private function ensureExtensionRow(): int
    {
        $row = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('extension_id'))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_proclaim'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
        )->loadResult();

        if ($row) {
            return (int) $row;
        }

        $extRow = (object) [
            'name'           => 'com_proclaim',
            'type'           => 'component',
            'element'        => 'com_proclaim',
            'folder'         => '',
            'client_id'      => 1,
            'enabled'        => 1,
            'manifest_cache' => '',
            'params'         => '{}',
            'custom_data'    => '',
        ];
        $this->db->insertObject('#__extensions', $extRow);

        return (int) $this->db->insertid();
    }
}
