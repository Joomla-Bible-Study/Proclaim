<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Regression tests for #1538, found during a Helper-folder-wide bug-hunting
 * review (sequel to #1521-#1528).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmigrationHelperTest extends ProclaimTestCase
{
    /**
     * migrateLegacyServers()'s batch loop advanced `$offset` by a fixed
     * `$limit` each call, but getLegacyMediaFileIds() re-queries
     * `WHERE server_id = ...` fresh every time and migrateMediaBatch()
     * removes successfully-migrated rows from that same pool by changing
     * their server_id. As the pool shrinks, a fixed offset skips rows.
     * Fixed by tracking only failed rows in the offset (they're the ones
     * that stay in the pool), mirroring the pattern already used by
     * server-migration.js for the same interaction.
     *
     * Calls migrateLegacyServers() itself (not a re-implementation of its
     * loop) against a real fixture legacy server with 60 media files -- more
     * than one 25-row batch -- and asserts every one of them ends up moved
     * off that server. Whatever other real legacy servers already exist on
     * the test DB are left alone by the assertion (they may or may not have
     * media of their own); only the fixture server's outcome is checked, so
     * this is safe to run against any DB state.
     */
    public function testMigrateLegacyServersMovesAllFixtureRowsDespiteAShrinkingPool(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->transactionStart();

        try {
            $legacyServer              = new \stdClass();
            $legacyServer->server_name = 'ZZTEST Legacy Server ' . uniqid();
            $legacyServer->type        = 'legacy';
            $legacyServer->params      = '{}';
            $legacyServer->media       = '{}';
            $legacyServer->published   = 1;
            $db->insertObject('#__bsms_servers', $legacyServer, 'id');

            // Pre-seed an existing 'local' target server so getExistingServersByType()
            // finds one to reuse and migrateLegacyServers() never calls
            // createServerForType() -- that method reads
            // Factory::getApplication()->getIdentity()->id unconditionally, which is
            // null in the PHPUnit CLI context (no logged-in user) and triggers a
            // PHP warning that PHPUnit's strict-output-checking treats as a failure.
            // That's a pre-existing gap unrelated to #1538; sidestepping it here
            // keeps this test scoped to the offset/pagination bug it's about.
            $targetServer              = new \stdClass();
            $targetServer->server_name = 'ZZTEST Local Server ' . uniqid();
            $targetServer->type        = 'local';
            $targetServer->params      = '{}';
            $targetServer->media       = '{}';
            $targetServer->published   = 1;
            $db->insertObject('#__bsms_servers', $targetServer, 'id');

            for ($i = 1; $i <= 60; $i++) {
                $row            = new \stdClass();
                $row->study_id  = null;
                $row->server_id = $legacyServer->id;
                $row->metadata  = '';
                $row->ordering  = 0;
                $row->published = 1;
                $row->language  = '*';
                $row->params    = json_encode(['filename' => 'sermon' . $i . '.mp3', 'player' => '']);
                $db->insertObject('#__bsms_mediafiles', $row);
            }

            CwmmigrationHelper::migrateLegacyServers();

            $stillOnLegacyServer = (int) $db->setQuery(
                $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bsms_mediafiles'))
                    ->where($db->quoteName('server_id') . ' = ' . (int) $legacyServer->id)
            )->loadResult();

            $this->assertSame(
                0,
                $stillOnLegacyServer,
                'all 60 fixture rows across 3 batches must be moved off the legacy server -- see #1538'
            );
        } finally {
            $db->transactionRollback();
        }
    }

    /**
     * fixTeacherAliases()'s UPDATE IGNORE silently skipped a dup->keeper
     * junction-row reassignment whenever the study already had a junction
     * row for the keeper, permanently orphaning that row once the dup
     * teacher was deleted a few statements later. Fixed with a DELETE for
     * any junction row still pointing at a dup_id immediately after the
     * UPDATE IGNORE -- provably safe, since the study already retains the
     * relationship via whichever row *did* get remapped to keeper_id.
     * Verified by hand against j5-dev with 3 fixture scenarios (dup
     * colliding with an existing keeper row, two dups + keeper on one
     * study, a dup with no keeper row) before writing this test.
     */
    public function testFixTeacherAliasesDoesNotOrphanJunctionRowsSkippedByUpdateIgnore(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->transactionStart();

        try {
            $sharedName = 'ZZTEST Duplicate Teacher ' . uniqid();

            $keeper              = new \stdClass();
            $keeper->teachername = $sharedName;
            $keeper->alias       = 'zztest-keeper-' . uniqid();
            $keeper->list_show   = 0;
            $keeper->published   = 0;
            $keeper->access      = 1;
            $keeper->language    = '*';
            $keeper->address     = '';
            $db->insertObject('#__bsms_teachers', $keeper, 'id');

            $dup              = new \stdClass();
            $dup->teachername = $sharedName;
            $dup->alias       = 'zztest-dup-' . uniqid();
            $dup->list_show   = 0;
            $dup->published   = 0;
            $dup->access      = 1;
            $dup->language    = '*';
            $dup->address     = '';
            $db->insertObject('#__bsms_teachers', $dup, 'id');

            $this->assertGreaterThan(0, $keeper->id);
            $this->assertGreaterThan($keeper->id, $dup->id, 'fixture must insert the keeper first so it has the lower id');

            $fakeStudyId = 9_999_502;

            // Study already has a junction row for BOTH the keeper and the dup --
            // this is exactly the collision case that orphaned a row pre-fix.
            $keeperLink             = new \stdClass();
            $keeperLink->study_id   = $fakeStudyId;
            $keeperLink->teacher_id = $keeper->id;
            $db->insertObject('#__bsms_study_teachers', $keeperLink);

            $dupLink             = new \stdClass();
            $dupLink->study_id   = $fakeStudyId;
            $dupLink->teacher_id = $dup->id;
            $db->insertObject('#__bsms_study_teachers', $dupLink);

            CwmmigrationHelper::fixTeacherAliases();

            $remaining = $db->setQuery(
                $db->createQuery()
                    ->select($db->quoteName('teacher_id'))
                    ->from($db->quoteName('#__bsms_study_teachers'))
                    ->where($db->quoteName('study_id') . ' = ' . (int) $fakeStudyId)
            )->loadColumn();

            $this->assertSame(
                [$keeper->id],
                array_map('intval', $remaining),
                'exactly one junction row must survive, pointing at the keeper -- see #1538'
            );

            $dupStillExists = (int) $db->setQuery(
                $db->createQuery()
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bsms_teachers'))
                    ->where($db->quoteName('id') . ' = ' . (int) $dup->id)
            )->loadResult();

            $this->assertSame(0, $dupStillExists, 'the duplicate teacher must have been deleted by the merge');
        } finally {
            $db->transactionRollback();
        }
    }
}
