<?php

/**
 * Integration tests for the automatic legacy-server migration.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * CwmmigrationHelper::migrateLegacyServers() against a real database.
 *
 * Every row is created inside a transaction that is rolled back, and every
 * assertion follows the fixture rows by id, so servers already present in the
 * database (including ones the migration reuses) do not affect the result.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmmigrationHelper::class)]
class CwmmigrationHelperLegacyServersTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    #[TestDox('A plain file on a remote legacy host migrates to a Direct server with its full URL')]
    public function testRemoteHostFileKeepsItsUrl(): void
    {
        $legacy = $this->insertLegacyServer(['path' => '//legacy-media.invalid/', 'protocol' => 'http://']);
        $mp3    = $this->insertMedia($legacy, ['filename' => '/MediaFiles/2015/a.mp3', 'player' => '7']);
        $pdf    = $this->insertMedia($legacy, ['filename' => '/images/q.pdf', 'player' => '0']);

        $report = CwmmigrationHelper::migrateLegacyServers();

        $this->assertGreaterThanOrEqual(2, $report['migrated']);
        $this->assertSame([], $report['errors']);

        foreach ([$mp3 => 'http://legacy-media.invalid/MediaFiles/2015/a.mp3', $pdf => 'http://legacy-media.invalid/images/q.pdf'] as $id => $url) {
            $row = $this->mediaRow($id);

            $this->assertSame('direct', $row['type']);
            $this->assertSame($url, $row['filename']);
        }
    }

    #[TestDox('A plain file on a legacy server with no host migrates to a Local server unchanged')]
    public function testHostlessLegacyFileGoesToLocal(): void
    {
        $legacy = $this->insertLegacyServer(['path' => '', 'protocol' => 'http://']);
        $id     = $this->insertMedia($legacy, ['filename' => 'media/sermon.mp3', 'player' => '7']);

        CwmmigrationHelper::migrateLegacyServers();

        $row = $this->mediaRow($id);

        $this->assertSame('local', $row['type']);
        $this->assertSame('media/sermon.mp3', $row['filename']);
    }

    #[TestDox('A platform link is unaffected by the legacy host and the legacy server is unpublished')]
    public function testPlatformLinkAndLegacyCleanup(): void
    {
        $legacy = $this->insertLegacyServer(['path' => '//legacy-media.invalid/', 'protocol' => 'http://']);
        $id     = $this->insertMedia($legacy, ['filename' => 'https://youtu.be/PsFo6MhAB9o', 'player' => '1']);

        CwmmigrationHelper::migrateLegacyServers();

        $this->assertSame('youtube', $this->mediaRow($id)['type']);
        $this->assertSame(0, (int) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('published') . ' FROM ' . $this->db->quoteName('#__bsms_servers')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $legacy
        )->loadResult());
    }

    /**
     * @param   array<string, string>  $params  Legacy server params (path, protocol)
     *
     * @return  int
     */
    private function insertLegacyServer(array $params): int
    {
        $db = $this->db;
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__bsms_servers')
            . ' (' . $db->quoteName('server_name') . ', ' . $db->quoteName('published') . ', '
            . $db->quoteName('type') . ', ' . $db->quoteName('params') . ', ' . $db->quoteName('media') . ')'
            . ' VALUES (' . $db->quote('zz legacy migration fixture') . ', 1, ' . $db->quote('legacy') . ', '
            . $db->quote(json_encode($params, JSON_THROW_ON_ERROR)) . ", '')"
        )->execute();

        return (int) $db->insertid();
    }

    /**
     * @param   int                    $serverId  The legacy server
     * @param   array<string, string>  $params    Media file params
     *
     * @return  int
     */
    private function insertMedia(int $serverId, array $params): int
    {
        $db = $this->db;
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__bsms_mediafiles')
            . ' (' . $db->quoteName('server_id') . ', ' . $db->quoteName('published') . ', '
            . $db->quoteName('params') . ', ' . $db->quoteName('metadata') . ', ' . $db->quoteName('language') . ')'
            . ' VALUES (' . $serverId . ', 1, ' . $db->quote(json_encode($params, JSON_THROW_ON_ERROR))
            . ", '', " . $db->quote('*') . ')'
        )->execute();

        return (int) $db->insertid();
    }

    /**
     * @param   int  $id  Media file id
     *
     * @return  array{type: string, filename: string}
     */
    private function mediaRow(int $id): array
    {
        $db  = $this->db;
        $row = $db->setQuery(
            'SELECT s.' . $db->quoteName('type') . ' AS type, m.' . $db->quoteName('params') . ' AS params'
            . ' FROM ' . $db->quoteName('#__bsms_mediafiles', 'm')
            . ' JOIN ' . $db->quoteName('#__bsms_servers', 's') . ' ON s.id = m.server_id'
            . ' WHERE m.id = ' . $id
        )->loadAssoc();

        $this->assertNotNull($row, 'media row ' . $id . ' should exist');

        $params = json_decode((string) $row['params'], true, 512, JSON_THROW_ON_ERROR);

        return ['type' => (string) $row['type'], 'filename' => (string) ($params['filename'] ?? '')];
    }
}
