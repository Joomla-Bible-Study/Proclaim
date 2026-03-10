<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\View\Cwmanalytics;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlangHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmanalyticsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Analytics dashboard view — supports overview + drill-down levels.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var string Active date preset key ('7d', '30d', '90d', '1y', or 'custom') @since 10.1.0 */
    public string $preset = '30d';

    /** @var string Date range start (Y-m-d) @since 10.1.0 */
    public string $dateStart = '';

    /** @var string Date range end (Y-m-d) @since 10.1.0 */
    public string $dateEnd = '';

    /** @var int Active campus filter; 0 = all @since 10.1.0 */
    public int $locationId = 0;

    /** @var string Current drill-down level: '', 'series', 'message', 'media' @since 10.1.0 */
    public string $drilldown = '';

    /** @var int Drill-down entity ID (series_id or study_id) @since 10.1.0 */
    public int $drilldownId = 0;

    // --- Overview data ---
    /** @var array{views: int, plays: int, downloads: int, sessions: int} @since 10.1.0 */
    public array $kpi = ['views' => 0, 'plays' => 0, 'downloads' => 0, 'sessions' => 0];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $timeSeries = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $topStudies = [];

    /** @var array<int, array<string, mixed>> Combined local + platform engagement @since 10.1.0 */
    public array $topStudiesCombined = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $referrerBreakdown = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $deviceBreakdown = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $browserBreakdown = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $osBreakdown = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $languageBreakdown = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $utmBreakdown = [];

    // --- Series drill-down ---
    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $seriesList = [];

    /** @var object|null @since 10.1.0 */
    public ?object $seriesInfo = null;

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $seriesMessages = [];

    // --- Messages list drill-down ---
    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $messagesList = [];

    /** @var string Search term for messages list filter @since 10.1.0 */
    public string $messagesSearch = '';

    /** @var int Current page number (1-based) for messages list @since 10.1.0 */
    public int $messagesPage = 1;

    /** @var int Total number of messages matching current filters @since 10.1.0 */
    public int $messagesTotal = 0;

    /** @var int Messages per page @since 10.1.0 */
    public int $messagesPerPage = 25;

    // --- Message drill-down ---
    /** @var object|null @since 10.1.0 */
    public ?object $studyInfo = null;

    /** @var array{views: int, plays: int, downloads: int, sessions: int} @since 10.1.0 */
    public array $studyKpi = ['views' => 0, 'plays' => 0, 'downloads' => 0, 'sessions' => 0];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $studyTimeSeries = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $studyMedia = [];

    // --- Media type drill-down ---
    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $mediaTypeBreakdown = [];

    // --- Platform stats ---
    /** @var array<int, array<string, mixed>> Summary by platform @since 10.1.0 */
    public array $platformStats = [];

    /** @var array<int, array<string, mixed>> Stats-capable servers for sync button @since 10.1.0 */
    public array $videoServers = [];

    /** @var array<int, array<string, mixed>> Per-media platform stats for message drill-down @since 10.1.0 */
    public array $studyPlatformStats = [];

    // --- Shared ---
    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $locations = [];

    /** @var bool True if the current user is a super-admin @since 10.1.0 */
    public bool $isSuperAdmin = false;

    /** @var bool True when the campus dropdown should be displayed @since 10.1.0 */
    public bool $showCampusDropdown = false;

    /** @var string Export URL for CSV download @since 10.1.0 */
    public string $exportUrl = '';

    /** @var string Status filter: '', 'published', or 'archived' @since 10.1.0 */
    public string $statusFilter = '';

    /** @var bool True when rendering the print-optimized report @since 10.1.0 */
    public bool $isPrint = false;

    /** @var array{views: int, plays: int, downloads: int} All-time totals from monthly aggregates @since 10.1.0 */
    public array $legacyKpi = ['views' => 0, 'plays' => 0, 'downloads' => 0];

    /** @var bool True if the raw events table has at least one real tracked event @since 10.1.0 */
    public bool $hasTrackedEvents = false;

    /** @var array{views: int, plays: int, downloads: int} All-time totals from live record counters @since 10.1.0 */
    public array $recordTotals = ['views' => 0, 'plays' => 0, 'downloads' => 0];

    /** @var string Date of the earliest tracked event, or '' if none @since 10.1.0 */
    public string $firstEventDate = '';

    /**
     * Execute and display the analytics dashboard.
     *
     * @param   string  $tpl  Template override name.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.1.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $user  = $app->getIdentity();

        $this->isSuperAdmin = $user->authorise('core.admin');

        // --- Date range ---
        $preset       = $input->getString('preset', '30d');
        $this->preset = $preset;
        $today        = date('Y-m-d');

        switch ($preset) {
            case '7d':
                $this->dateStart = date('Y-m-d', strtotime('-6 days'));
                $this->dateEnd   = $today;
                break;
            case '90d':
                $this->dateStart = date('Y-m-d', strtotime('-89 days'));
                $this->dateEnd   = $today;
                break;
            case '1y':
                $this->dateStart = date('Y-m-d', strtotime('-364 days'));
                $this->dateEnd   = $today;
                break;
            case 'custom':
                $this->dateStart = $input->getString('date_start', date('Y-m-d', strtotime('-29 days')));
                $this->dateEnd   = $input->getString('date_end', $today);
                break;
            default: // 30d
                $this->dateStart = date('Y-m-d', strtotime('-29 days'));
                $this->dateEnd   = $today;
                break;
        }

        if ($this->dateStart > $this->dateEnd) {
            $this->dateStart = $this->dateEnd;
        }

        // --- Campus filter ---
        $this->locationId = $input->getInt('location_id', 0);

        // --- Status filter ---
        $this->statusFilter = $input->getCmd('status', '');

        // --- Drill-down params ---
        $this->drilldown   = $input->getCmd('drilldown', '');
        $this->drilldownId = $input->getInt('id', 0);

        /** @var CwmanalyticsModel $model */
        $model = $this->getModel();

        // Non-super-admin: resolve accessible locations via CwmlocationHelper
        if (!$this->isSuperAdmin && CwmlocationHelper::isEnabled() && $this->locationId === 0) {
            $userLocationIds = CwmlocationHelper::getUserLocations((int) $user->id);

            if (!empty($userLocationIds)) {
                $this->locationId = $userLocationIds[0];
            }
        }

        // Load locations for dropdown: super-admin sees all, others see their accessible set
        $this->locations = $model->getLocations($this->isSuperAdmin ? [] : $user->getAuthorisedViewLevels());

        // Show campus dropdown to super-admins or multi-campus users
        $this->showCampusDropdown = $this->isSuperAdmin
            || (\count($this->locations) > 1 && CwmlocationHelper::isEnabled());

        $s = $this->dateStart;
        $e = $this->dateEnd;
        $l = $this->locationId;

        // --- Always load: legacy / record totals (used in overview and compact KPI strip) ---
        $this->hasTrackedEvents = $model->hasTrackedEvents();
        $this->firstEventDate   = $model->getFirstEventDate();
        $this->legacyKpi        = $model->getLegacyKpiTotals($l);
        $this->recordTotals     = $model->getRecordTotals($l);

        // --- Platform stats (always load for overview + sync button) ---
        $this->videoServers  = $model->getStatsCapableServers();
        $this->platformStats = $model->getPlatformStatsSummary($l);

        // --- Load data for the active drill-down level ---
        if ($this->drilldown === 'series' && $this->drilldownId > 0) {
            // Series detail: messages inside one series
            $this->seriesInfo     = $model->getSeriesInfo($this->drilldownId);
            $this->seriesMessages = $model->getSeriesMessages($this->drilldownId, $s, $e);
        } elseif ($this->drilldown === 'series') {
            // Series list
            $this->seriesList = $model->getSeriesList($s, $e, $l, $this->statusFilter);
        } elseif ($this->drilldown === 'messages') {
            // Messages list (all messages regardless of series) — paginated
            $this->messagesSearch = $input->getString('search', '');
            $this->messagesPage   = max(1, $input->getInt('page', 1));
            $this->messagesTotal  = $model->getMessagesCount($s, $e, $l, $this->messagesSearch, $this->statusFilter);
            $offset               = ($this->messagesPage - 1) * $this->messagesPerPage;
            $this->messagesList   = $model->getMessagesList(
                $s,
                $e,
                $l,
                $this->messagesSearch,
                $this->messagesPerPage,
                $offset,
                $this->statusFilter
            );
        } elseif ($this->drilldown === 'message' && $this->drilldownId > 0) {
            // Message detail
            $this->studyInfo          = $model->getStudyInfo($this->drilldownId);
            $this->studyKpi           = $model->getStudyKpi($this->drilldownId, $s, $e);
            $this->studyTimeSeries    = $model->getStudyTimeSeries($this->drilldownId, $s, $e);
            $this->studyMedia         = $model->getStudyMediaFiles($this->drilldownId, $s, $e);
            $this->studyPlatformStats = $model->getPlatformStatsForStudy($this->drilldownId);
        } elseif ($this->drilldown === 'media') {
            // Media type breakdown
            $this->mediaTypeBreakdown = $model->getMediaTypeBreakdown($s, $e, $l);
        } else {
            // Overview
            $this->kpi                = $model->getKpiTotals($s, $e, $l);
            $this->timeSeries         = $model->getTimeSeries($s, $e, $l);
            $this->topStudies         = $model->getTopStudies($s, $e, 10, $l);
            $this->topStudiesCombined = $model->getTopStudiesCombined($s, $e, 10, $l);
            $this->referrerBreakdown  = $model->getReferrerBreakdown($s, $e, $l);
            $this->deviceBreakdown    = $model->getDeviceBreakdown($s, $e, $l);
            $this->browserBreakdown   = $model->getBrowserBreakdown($s, $e, $l);
            $this->osBreakdown        = $model->getOsBreakdown($s, $e, $l);
            $this->languageBreakdown  = $model->getLanguageBreakdown($s, $e, $l);
            $this->utmBreakdown       = $model->getUtmBreakdown($s, $e, $l);
        }

        // --- Export URL (false = raw &, not &amp; — template handles escaping) ---
        $this->exportUrl = Route::_(
            'index.php?option=com_proclaim&task=cwmanalytics.exportCsv' .
            '&date_start=' . urlencode($s) .
            '&date_end=' . urlencode($e) .
            '&location_id=' . (int) $l .
            ($this->statusFilter !== '' ? '&status=' . urlencode($this->statusFilter) : ''),
            false
        );

        // --- Print mode detection ---
        $this->isPrint = $input->getBool('print', false);

        $wa = $this->getDocument()->getWebAssetManager();

        if ($this->isPrint) {
            // Print report: load on all media (not just @media print) for the popup
            $wa->useStyle('com_proclaim.print-report');
        } else {
            // Normal dashboard: toolbar + chart assets
            ToolbarHelper::title(Text::_('JBS_ANA_ANALYTICS_DASHBOARD'), 'bar-chart');
            ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_proclaim'));

            $wa->useScript('com_proclaim.chart.js')
                ->useScript('com_proclaim.cwmanalytics')
                ->useScript('com_proclaim.platform-stats')
                ->useStyle('com_proclaim.general');

            CwmlangHelper::registerAllForJs();
            Text::script('JCANCEL');
            Text::script('JLIB_HTML_PLEASE_WAIT');
        }

        parent::display($tpl);
    }
}
