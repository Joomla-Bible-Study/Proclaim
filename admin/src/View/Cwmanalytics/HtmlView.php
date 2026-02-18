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

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Analytics dashboard view.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var string Date range start (Y-m-d) @since 10.1.0 */
    public string $dateStart = '';

    /** @var string Date range end (Y-m-d) @since 10.1.0 */
    public string $dateEnd = '';

    /** @var int Active campus filter; 0 = all @since 10.1.0 */
    public int $locationId = 0;

    /** @var array{views: int, plays: int, downloads: int, sessions: int} @since 10.1.0 */
    public array $kpi = ['views' => 0, 'plays' => 0, 'downloads' => 0, 'sessions' => 0];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $timeSeries = [];

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $topStudies = [];

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

    /** @var array<int, array<string, mixed>> @since 10.1.0 */
    public array $locations = [];

    /** @var bool True if the current user is a super-admin @since 10.1.0 */
    public bool $isSuperAdmin = false;

    /** @var string Export URL for CSV download @since 10.1.0 */
    public string $exportUrl = '';

    /** @var array{views: int, plays: int, downloads: int} All-time totals from monthly aggregates (includes legacy seeded data) @since 10.1.0 */
    public array $legacyKpi = ['views' => 0, 'plays' => 0, 'downloads' => 0];

    /** @var bool True if the raw events table has at least one real tracked event @since 10.1.0 */
    public bool $hasTrackedEvents = false;


    /** @var array{views: int, plays: int, downloads: int} All-time totals from live record counters (old system) @since 10.1.0 */
    public array $recordTotals = ['views' => 0, 'plays' => 0, 'downloads' => 0];

    /** @var string Date of the earliest tracked event, or '' if none @since 10.1.0 */
    public string $firstEventDate = '';

    /**
     * Load all published locations, optionally restricted to specific view levels.
     *
     * @param   int[]  $viewLevels  Empty array = all locations (super-admin).
     *
     * @return  object[]
     *
     * @since   10.1.0
     */
    private static function loadLocations(array $viewLevels = []): array
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('name')])
                ->from($db->quoteName('#__bsms_locations'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('name') . ' ASC');

            if (!empty($viewLevels)) {
                $query->whereIn($db->quoteName('access'), $viewLevels);
            }

            $db->setQuery($query);

            return (array) ($db->loadObjectList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

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
        $preset = $input->getString('preset', '30d');
        $today  = date('Y-m-d');

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

        // Clamp to valid dates
        if ($this->dateStart > $this->dateEnd) {
            $this->dateStart = $this->dateEnd;
        }

        // --- Campus filter ---
        $this->locationId = $input->getInt('location_id', 0);

        // Non-super-admin: force to their first authorised campus if they try 0
        if (!$this->isSuperAdmin && $this->locationId === 0) {
            $locations = self::loadLocations($user->getAuthorisedViewLevels());

            if (!empty($locations)) {
                $this->locationId = (int) $locations[0]->id;
            }
        }

        // --- Load campus list for filter dropdown ---
        $this->locations = self::loadLocations($this->isSuperAdmin ? [] : $user->getAuthorisedViewLevels());

        // --- Query analytics data ---
        $s = $this->dateStart;
        $e = $this->dateEnd;
        $l = $this->locationId;

        $this->kpi               = CwmanalyticsHelper::getKpiTotals($s, $e, $l);
        $this->timeSeries        = CwmanalyticsHelper::getTimeSeries($s, $e, $l);
        $this->topStudies        = CwmanalyticsHelper::getTopStudies($s, $e, 10);
        $this->referrerBreakdown = CwmanalyticsHelper::getReferrerBreakdown($s, $e, $l);
        $this->deviceBreakdown   = CwmanalyticsHelper::getDeviceBreakdown($s, $e, $l);
        $this->browserBreakdown  = CwmanalyticsHelper::getBrowserBreakdown($s, $e, $l);
        $this->osBreakdown       = CwmanalyticsHelper::getOsBreakdown($s, $e, $l);
        $this->languageBreakdown = CwmanalyticsHelper::getLanguageBreakdown($s, $e, $l);
        $this->utmBreakdown      = CwmanalyticsHelper::getUtmBreakdown($s, $e, $l);

        // --- Legacy / historical data ---
        $this->hasTrackedEvents = CwmanalyticsHelper::hasTrackedEvents();
        $this->firstEventDate   = CwmanalyticsHelper::getFirstEventDate();
        $this->legacyKpi        = CwmanalyticsHelper::getLegacyKpiTotals($l);
        $this->recordTotals     = CwmanalyticsHelper::getRecordTotals($l);

        // --- Export URL ---
        $this->exportUrl = Route::_(
            'index.php?option=com_proclaim&task=cwmanalytics.exportCsv' .
            '&date_start=' . urlencode($s) .
            '&date_end=' . urlencode($e) .
            '&location_id=' . (int) $l
        );

        // --- Toolbar ---
        ToolbarHelper::title(Text::_('JBS_ANA_ANALYTICS_DASHBOARD'), 'bar-chart');
        ToolbarHelper::back('JTOOLBAR_BACK', Route::_('index.php?option=com_proclaim&view=cwmadmin'));

        $wa = $this->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.chart.js')
            ->useScript('com_proclaim.cwmanalytics')
            ->useStyle('com_proclaim.general');

        parent::display($tpl);
    }
}
