<?php

/**
 * Integration tests executing every date-windowed analytics query.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmanalyticsModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The analytics date windows moved from quote()d literals to bound markers,
 * and a bound marker has a failure mode a literal does not: text that never
 * meets its bind() ships ":windowStart" to MySQL as literal SQL and dies at
 * execution. That exact mistake was made twice mid-conversion — in the two
 * raw-string methods, and in a subquery whose bindings were lost when it was
 * rendered into its outer query's JOIN.
 *
 * ⚠️ Asserting "returns an array" catches none of that here: every one of
 * these methods swallows exceptions and returns an empty default, so a query
 * dying at prepare is indistinguishable from an empty table. Hence the seed —
 * one series, one message, one media file and three events inside the window,
 * every breakdown column populated. A query that executes must find them; a
 * query that died cannot.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmanalyticsModel::class)]
class AnalyticsQueriesExecuteTest extends IntegrationTestCase
{
    private const START = '2030-06-01';

    private const END = '2030-06-30';

    private ?DatabaseDriver $db = null;

    private ?CwmanalyticsModel $model = null;

    private int $seriesId = 0;

    private int $studyId = 0;

    private int $mediaId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        // Constructed directly: the MVCFactory route returns false under the
        // test bootstrap, and what this class exercises is the model's SQL,
        // not the factory wiring the admin app does for real requests.
        $this->model = new CwmanalyticsModel(['ignore_request' => true]);
        $this->model->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));

        $this->seed();
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

    /**
     * One of everything the windowed queries join, dated inside the window.
     *
     * A far-future window (2030) rather than a slice of real time, so rows
     * already in the table cannot satisfy an assertion the seed was meant to.
     *
     * @return  void
     */
    private function seed(): void
    {
        $series = (object) [
            'series_text' => 'analytics fixture series',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
        ];
        $this->db->insertObject('#__bsms_series', $series, 'id');
        $this->seriesId = (int) $this->db->insertid();

        $study = (object) [
            'studytitle' => 'analytics fixture message',
            'series_id'  => $this->seriesId,
            'studydate'  => self::START . ' 09:00:00',
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $this->studyId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $this->studyId,
            'server_id' => 0,
            'published' => 1,
            'access'    => 1,
            'params'    => json_encode(['filename' => '/images/biblestudy/media/fixture.mp3', 'mime_type' => 'audio/mpeg']),
            'metadata'  => '',
            'language'  => '*',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');
        $this->mediaId = (int) $this->db->insertid();

        foreach (['page_view', 'play', 'download'] as $i => $type) {
            $event = (object) [
                'study_id'        => $this->studyId,
                'series_id'       => $this->seriesId,
                'media_id'        => $this->mediaId,
                'event_type'      => $type,
                'referrer_type'   => 'organic',
                'referrer_domain' => 'search.example.org',
                'utm_source'      => 'newsletter',
                'utm_medium'      => 'email',
                'utm_campaign'    => 'june',
                'country_code'    => 'US',
                'device_type'     => 'desktop',
                'browser'         => 'Firefox',
                'os'              => 'macOS',
                'language'        => 'en-US',
                'is_guest'        => 1,
                'session_hash'    => str_repeat((string) $i, 64),
                'created'         => self::START . ' 10:0' . $i . ':00',
            ];
            $this->db->insertObject('#__bsms_analytics_events', $event);
        }
    }

    #[TestDox('the KPI totals count the seeded events')]
    public function testKpiTotals(): void
    {
        $kpi = $this->model->getKpiTotals(self::START, self::END);

        $this->assertSame(1, (int) ($kpi['views'] ?? 0), 'The seeded page_view was not counted.');
        $this->assertSame(1, (int) ($kpi['plays'] ?? 0));
        $this->assertSame(1, (int) ($kpi['downloads'] ?? 0));
    }

    #[TestDox('the time series buckets the seeded day')]
    public function testTimeSeries(): void
    {
        $this->assertNotSame(
            [],
            $this->model->getTimeSeries(self::START, self::END),
            'A window holding three events produced no buckets — the query, or its :bucketFormat bind, died.'
        );
    }

    #[TestDox('the study rankings find the seeded message')]
    public function testTopStudies(): void
    {
        $ids = array_column($this->model->getTopStudies(self::START, self::END), 'study_id');
        $this->assertContains((string) $this->studyId, array_map('strval', $ids));

        // A high limit on purpose: platform_plays is all-time by documented
        // caveat, so real platform-stats rows in the test database outrank a
        // three-event seed inside any top-10.
        $ids = array_column($this->model->getTopStudiesCombined(self::START, self::END, 10000), 'study_id');
        $this->assertContains(
            (string) $this->studyId,
            array_map('strval', $ids),
            'The combined ranking lost the seeded message — its window binds live on the OUTER query, '
            . 'because the subquery is rendered to a string and a string carries no bindings.'
        );
    }

    #[TestDox('every breakdown finds the seeded event')]
    public function testBreakdowns(): void
    {
        foreach (
            [
                'getReferrerBreakdown',
                'getCountryBreakdown',
                'getDeviceBreakdown',
                'getBrowserBreakdown',
                'getOsBreakdown',
                'getLanguageBreakdown',
                'getUtmBreakdown',
                'getMediaTypeBreakdown',
            ] as $method
        ) {
            $this->assertNotSame(
                [],
                $this->model->$method(self::START, self::END),
                "$method returned nothing over a window holding seeded events. These methods swallow "
                . 'exceptions into empty defaults, so this usually means the query itself died.'
            );
        }
    }

    #[TestDox('the drill-down queries find the seeded rows')]
    public function testDrillDowns(): void
    {
        $this->assertNotSame([], $this->model->getSeriesMessages($this->seriesId, self::START, self::END));
        $this->assertNotSame([], $this->model->getStudyTimeSeries($this->studyId, self::START, self::END));
        $this->assertNotSame([], $this->model->getStudyMediaFiles($this->studyId, self::START, self::END));

        $kpi = $this->model->getStudyKpi($this->studyId, self::START, self::END);
        $this->assertSame(1, (int) ($kpi['views'] ?? 0));
    }

    #[TestDox('the raw-SQL lists still find the seeded rows')]
    public function testRawSqlLists(): void
    {
        // These two deliberately stayed quote()d — binding lives on the
        // builder, and their SQL is a plain string. Executed here so the
        // quote-vs-bind split cannot drift into one side silently breaking.
        $series = array_column($this->model->getSeriesList(self::START, self::END), 'series_id');
        $this->assertContains((string) $this->seriesId, array_map('strval', $series));

        $studies = array_column($this->model->getMessagesList(self::START, self::END), 'study_id');
        $this->assertContains((string) $this->studyId, array_map('strval', $studies));
    }

    #[TestDox('the CSV export carries the seeded rows')]
    public function testCsvExport(): void
    {
        // The export is raw event rows, not joined titles — assert on a value
        // only the seed writes.
        $this->assertStringContainsString(
            'search.example.org',
            $this->model->exportCsvString(self::START, self::END)
        );
    }
}
