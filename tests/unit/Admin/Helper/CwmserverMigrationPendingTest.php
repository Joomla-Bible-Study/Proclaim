<?php

/**
 * The count behind the "servers awaiting migration" notice.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmserverMigrationPendingTest extends ProclaimTestCase
{
    /**
     * @var  \Joomla\Database\DatabaseDriver|null
     * @since __DEPLOY_VERSION__
     */
    private $db = null;

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = $GLOBALS['__proclaim_test_db'] ?? null;

        if ($this->db === null) {
            $this->markTestSkipped('Database connection not available');
        }

        $this->db->transactionStart();
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost
            }
        }

        parent::tearDown();
    }

    /**
     * Insert a legacy server in a given published state, with media rows on it.
     *
     * @param   int  $published   Joomla state: 1 published, 0 unpublished, -2 trashed.
     * @param   int  $mediaCount  How many media rows to point at it.
     *
     * @return  int  The new server id.
     *
     * @since __DEPLOY_VERSION__
     */
    private function insertLegacyServer(int $published, int $mediaCount = 2): int
    {
        $db = $this->db;

        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__bsms_servers')
            . ' (' . $db->quoteName('server_name') . ', ' . $db->quoteName('published') . ', '
            . $db->quoteName('type') . ', ' . $db->quoteName('params') . ', ' . $db->quoteName('media') . ')'
            . ' VALUES (' . $db->quote('Pending-count fixture') . ', ' . $published . ', '
            . $db->quote('legacy') . ", '', '')"
        )->execute();

        $serverId = (int) $db->insertid();

        for ($i = 0; $i < $mediaCount; $i++) {
            // metadata and language are NOT NULL with no default on this
            // table, so they have to be given explicitly rather than left to
            // the schema.
            $db->setQuery(
                'INSERT INTO ' . $db->quoteName('#__bsms_mediafiles')
                . ' (' . $db->quoteName('server_id') . ', ' . $db->quoteName('published') . ', '
                . $db->quoteName('metadata') . ', ' . $db->quoteName('language') . ')'
                . ' VALUES (' . $serverId . ", 1, '', " . $db->quote('*') . ')'
            )->execute();
        }

        return $serverId;
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The pending count reports both servers and the media rows on them')]
    public function testCountShape(): void
    {
        $result = CwmserverMigrationHelper::countPendingMigration();

        $this->assertArrayHasKey('servers', $result);
        $this->assertArrayHasKey('media', $result);
        $this->assertIsInt($result['servers']);
        $this->assertIsInt($result['media']);
        $this->assertGreaterThanOrEqual(0, $result['servers']);
        $this->assertGreaterThanOrEqual(0, $result['media']);
    }

    /**
     * ⚠️ The positive control. Every assertion below is that some server is
     * *not* counted, and all of them would pass just as well if the fixture
     * never reached the database or the helper always returned zero. This is
     * the one that proves the mechanism works, so the others mean something.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A published legacy server IS counted, and its media with it')]
    public function testPublishedLegacyServerIsCounted(): void
    {
        $before = CwmserverMigrationHelper::countPendingMigration();

        $this->insertLegacyServer(1, 3);

        $after = CwmserverMigrationHelper::countPendingMigration();

        $this->assertSame(
            $before['servers'] + 1,
            $after['servers'],
            'A published legacy server is exactly what this notice exists to report.'
        );
        $this->assertSame(
            $before['media'] + 3,
            $after['media'],
            'Its media rows are the reason migrating it matters.'
        );
    }

    /**
     * The bug this test exists for: a migration signs off by calling
     * unpublishEmptyLegacyServers(), which sets published = 0 and leaves
     * `type` as 'legacy' for ever. Counting by type alone meant the notice
     * still reported every server anyone had ever migrated, so it could not be
     * cleared by doing the work it asked for — only by deleting the rows.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A retired (unpublished) legacy server is not still reported as pending')]
    public function testUnpublishedLegacyServerIsNotCounted(): void
    {
        $before = CwmserverMigrationHelper::countPendingMigration();

        $this->insertLegacyServer(0, 3);

        $after = CwmserverMigrationHelper::countPendingMigration();

        $this->assertSame(
            $before['servers'],
            $after['servers'],
            'A server retired by the migration must stop being reported, or the notice never clears.'
        );
        $this->assertSame(
            $before['media'],
            $after['media'],
            'Media on a retired server is not waiting on anything either.'
        );
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A trashed legacy server is not reported as pending')]
    public function testTrashedLegacyServerIsNotCounted(): void
    {
        $before = CwmserverMigrationHelper::countPendingMigration();

        $this->insertLegacyServer(-2, 4);

        $after = CwmserverMigrationHelper::countPendingMigration();

        $this->assertSame(
            $before['servers'],
            $after['servers'],
            'Nagging an administrator to migrate something they have thrown away has no action behind it.'
        );
    }

    /**
     * The two numbers have to describe the same population. Counting media
     * across all legacy servers while counting only the published ones could
     * put "0 servers, 400 media" on screen — rows attributed to servers the
     * same notice has just said are not waiting on anything.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Media is only counted for servers that are themselves counted')]
    public function testMediaMatchesTheServersReported(): void
    {
        $this->insertLegacyServer(0, 5);
        $this->insertLegacyServer(-2, 5);

        $result = CwmserverMigrationHelper::countPendingMigration();

        if ($result['servers'] === 0) {
            $this->assertSame(
                0,
                $result['media'],
                'With no server reported there is nothing for these media rows to be waiting on.'
            );

            return;
        }

        // ⚠️ Not a silent pass. If the fixture database has published legacy
        // servers of its own, assert the branch that actually ran: the media
        // reported must be only theirs, never the ten rows just parked on
        // retired servers.
        $db = $this->db;
        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__bsms_mediafiles', 'm')
            . ' INNER JOIN ' . $db->quoteName('#__bsms_servers', 's')
            . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.server_id')
            . ' WHERE ' . $db->quoteName('s.type') . ' = ' . $db->quote('legacy')
            . ' AND ' . $db->quoteName('s.published') . ' = 1'
        );

        $this->assertSame(
            (int) $db->loadResult(),
            $result['media'],
            'The media count must cover exactly the published legacy servers, and nothing else.'
        );
    }

    /**
     * The reported server count must match what the database holds — measured
     * with the same predicate the helper uses, not with "every legacy row",
     * which is the assertion that let the original bug through.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The server count matches the published legacy servers in the database')]
    public function testCountMatchesTheDatabase(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__bsms_servers')
            . ' WHERE ' . $db->quoteName('type') . ' = ' . $db->quote('legacy')
            . ' AND ' . $db->quoteName('published') . ' = 1'
        );

        $expected = (int) $db->loadResult();
        $result   = CwmserverMigrationHelper::countPendingMigration();

        $this->assertSame($expected, $result['servers']);

        if ($expected === 0) {
            $this->assertSame(
                0,
                $result['media'],
                'With nothing to migrate the media count must be 0, not the whole media table.'
            );
        }
    }

    /**
     * ⚠️ This check runs from a page render, from a restore finishing, and
     * (from 10.6.0) from a scheduled task. Anything reaching for the identity,
     * the session or the request works in the browser and dies under cron --
     * the failure mode recorded for api.php. Reading the source is cruder than
     * running it headless, but it runs in CI on every change, which the
     * headless case does not.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The count asks nothing about the current user, session or request')]
    public function testCountIsContextFree(): void
    {
        $method = new \ReflectionMethod(CwmserverMigrationHelper::class, 'countPendingMigration');
        $file   = (string) file_get_contents((string) $method->getFileName());
        $lines  = explode("\n", $file);
        $body   = implode(
            "\n",
            \array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1)
        );

        $this->assertNotSame('', trim($body), 'Failed to read the method body — this test would pass on nothing.');

        foreach (['getIdentity', 'getSession', 'getInput', 'enqueueMessage', 'Route::', 'Uri::'] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $body,
                $forbidden . ' ties this count to a web request. It also has to answer from a scheduled task.'
            );
        }
    }
}
