<?php

/**
 * Integration tests for the control panel's pending-review count.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The control panel used to count every study with published = 0 and call them
 * "awaiting editorial review". An administrator unpublishing a draft or retiring
 * an old sermon leaves exactly the same state, so the notice reported content
 * nobody had submitted and could never reach zero on a site that unpublishes
 * routinely.
 *
 * The API now marks what it force-unpublishes, and only marked records count.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmcountHelper::class)]
class CwmpendingReviewTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  int[]
     */
    private array $created = [];

    /**
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        // Table::publish() stamps via Factory::getDate().
        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();

        // The helper caches per request, and these tests change what it counts.
        $this->resetStaticCache(CwmcountHelper::class, 'cache');
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }

            try {
                $this->purge();
            } catch (\Throwable) {
                // Best effort; the assertions have already run.
            }
        }

        $this->resetStaticCache(CwmcountHelper::class, 'cache');
        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    #[TestDox('A study unpublished without being submitted is not counted as awaiting review')]
    public function testPlainlyUnpublishedStudyIsNotCounted(): void
    {
        $before = $this->pendingCount();

        // A draft, or a sermon an administrator retired. Same published = 0 as a
        // submission, which is exactly why the old count was wrong.
        $this->insertStudy(0, '{}');

        $this->assertSame($before, $this->pendingCount(), 'Unpublished alone must not mean awaiting review');
    }

    #[TestDox('A study the API marked and force-unpublished is counted')]
    public function testMarkedStudyIsCounted(): void
    {
        $before = $this->pendingCount();

        $this->insertStudy(0, '{"pending_review":1}');

        $this->assertSame($before + 1, $this->pendingCount());
    }

    #[TestDox('A marked study that has been published is not counted')]
    public function testMarkedButPublishedStudyIsNotCounted(): void
    {
        $before = $this->pendingCount();

        $this->insertStudy(1, '{"pending_review":1}');

        $this->assertSame($before, $this->pendingCount(), 'Publishing answers the review');
    }

    #[TestDox('Publishing a marked study clears the mark, so it cannot return to the count')]
    public function testPublishingClearsTheMark(): void
    {
        $id = $this->insertStudy(0, '{"pending_review":1,"keepme":"yes"}');

        $table = new CwmmessageTable($this->db);
        $this->assertTrue($table->publish([$id], 1), 'Publish should succeed');

        $params = (string) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('params') . ' FROM ' . $this->db->quoteName('#__bsms_studies')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
        )->loadResult();

        $this->assertStringNotContainsString('pending_review', $params, 'The mark must be cleared on publish');
        $this->assertStringContainsString('keepme', $params, 'Other params must survive');
    }

    #[TestDox('Unpublishing does not add a mark, so ordinary unpublishing stays uncounted')]
    public function testUnpublishingDoesNotMark(): void
    {
        $id = $this->insertStudy(1, '{}');

        $table = new CwmmessageTable($this->db);
        $table->publish([$id], 0);

        $params = (string) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('params') . ' FROM ' . $this->db->quoteName('#__bsms_studies')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
        )->loadResult();

        $this->assertStringNotContainsString('pending_review', $params);
    }

    /**
     * @return  int
     */
    private function pendingCount(): int
    {
        $this->resetStaticCache(CwmcountHelper::class, 'cache');

        return CwmcountHelper::getPendingReviewCount(null);
    }

    /**
     * @return  void
     */
    private function purge(): void
    {
        foreach ($this->created as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__assets')
                . ' WHERE ' . $this->db->quoteName('name') . ' = '
                . $this->db->quote('com_proclaim.message.' . $id)
            )->execute();
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_studies')
                . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
            )->execute();
        }

        $this->created = [];
    }

    /**
     * @param   int     $published  Published state.
     * @param   string  $params     Params JSON.
     *
     * @return  int  Study ID.
     */
    private function insertStudy(int $published, string $params): int
    {
        $row = (object) [
            'studytitle'  => 'Pending review fixture ' . uniqid('', true),
            'alias'       => 'pending-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'messagetype' => '1',
            'booknumber'  => 101,
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => $params,
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        $id              = (int) $this->db->insertid();
        $this->created[] = $id;

        return $id;
    }
}
