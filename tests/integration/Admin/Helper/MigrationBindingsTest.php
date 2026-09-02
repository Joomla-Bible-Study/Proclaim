<?php

/**
 * Integration tests executing the converted migration-helper queries.
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
 * The migration helper's remaining quote($variable) sites became bound
 * markers, and these routines run at exactly the moment a broken query is
 * most expensive: during an update, on someone else's site. Each converted
 * method executes here against the real database, inside a rolled-back
 * transaction, and has to produce its observable effect — not merely return.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmmigrationHelper::class)]
class MigrationBindingsTest extends IntegrationTestCase
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
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();
    }

    #[TestDox('the translation seed and its bound renames run to completion')]
    public function testSeedBibleTranslations(): void
    {
        // Idempotent by design (INSERT IGNORE + renames), so running it over
        // an already-seeded table is the normal case, not a special one.
        CwmmigrationHelper::seedBibleTranslations();

        $count = (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_bible_translations'))
        )->loadResult();

        $this->assertGreaterThan(0, $count, 'The catalogue is empty after seeding.');
    }

    #[TestDox('reconciliation writes a bound verse count for an installed translation')]
    public function testReconcileBibleTranslations(): void
    {
        CwmmigrationHelper::reconcileBibleTranslations();

        $kjv = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName(['installed', 'verse_count']))
                ->from($this->db->quoteName('#__bsms_bible_translations'))
                ->where($this->db->quoteName('abbreviation') . ' = ' . $this->db->quote('kjv'))
        )->loadObject();

        $this->assertNotNull($kjv, 'kjv is missing from the catalogue.');
        $this->assertSame(1, (int) $kjv->installed);
        $this->assertGreaterThan(30000, (int) $kjv->verse_count, 'The bound verse count did not land.');
    }

    #[TestDox('the legacy-path fixer rewrites through its bound REPLACE')]
    public function testFixMediafileLegacyPaths(): void
    {
        // The path the fixer actually maps — media/com_biblestudy, stored the
        // way json_encode stores it, escaped slashes and all. (It deliberately
        // does not touch images/biblestudy/, which still exists on disk.)
        $params = json_encode(['filename' => 'media/com_biblestudy/zz-legacy.mp3']);
        $media  = (object) [
            'study_id'  => 0,
            'server_id' => 0,
            'published' => 1,
            'access'    => 1,
            'params'    => $params,
            'metadata'  => '',
            'language'  => '*',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');
        $id = (int) $this->db->insertid();

        CwmmigrationHelper::fixMediafileLegacyPaths();

        $stored = (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('params'))
                ->from($this->db->quoteName('#__bsms_mediafiles'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadResult();

        // Whatever mapping the fixer applies, a row seeded with a legacy
        // path must come out changed — an inert REPLACE means the binds died.
        $this->assertNotSame($params, $stored, 'The seeded legacy path was not rewritten.');
        $this->assertStringContainsString('com_proclaim', $stored);
    }

    #[TestDox('a location created from an access level is found again by its bound name')]
    public function testCreateLocationFromAccess(): void
    {
        $level = (object) ['id' => 999, 'title' => "St. Migration's Fixture"];

        $created = CwmmigrationHelper::createLocationFromAccess($level);
        $this->assertGreaterThan(0, $created);

        // The round trip is the binding test: a lookup whose bind mangles the
        // apostrophe creates a duplicate instead of finding the original.
        $this->assertSame($created, CwmmigrationHelper::createLocationFromAccess($level));
    }
}
