<?php

/**
 * Analytics Dashboard Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

$input    = Factory::getApplication()->getInput();
$presets  = ['7d' => 'JBS_ANA_LAST_7_DAYS', '30d' => 'JBS_ANA_LAST_30_DAYS', '90d' => 'JBS_ANA_LAST_90_DAYS', '1y' => 'JBS_ANA_LAST_YEAR'];
$baseUrl  = 'index.php?option=com_proclaim&view=cwmanalytics';
$token    = '&' . Session::getFormToken() . '=1';

// Prepare time-series JSON for Chart.js
$labels         = [];
$viewsData      = [];
$playsData      = [];
$downloadsData  = [];

foreach ($this->timeSeries as $row) {
    $labels[]       = htmlspecialchars($row['period'] ?? '', ENT_QUOTES);
    $viewsData[]    = (int) ($row['views'] ?? 0);
    $playsData[]    = (int) ($row['plays'] ?? 0);
    $downloadsData[] = (int) ($row['downloads'] ?? 0);
}

$timeSeriesJson = json_encode([
    'labels'   => $labels,
    'datasets' => [
        ['label' => Text::_('JBS_ANA_VIEWS'), 'data' => $viewsData, 'key' => 'views'],
        ['label' => Text::_('JBS_ANA_PLAYS'), 'data' => $playsData, 'key' => 'plays'],
        ['label' => Text::_('JBS_ANA_DOWNLOADS'), 'data' => $downloadsData, 'key' => 'downloads'],
    ],
], JSON_THROW_ON_ERROR);

// Referrer doughnut
$refLabels = [];
$refCounts = [];

foreach ($this->referrerBreakdown as $row) {
    $refLabels[] = Text::_('JBS_ANA_REF_' . strtoupper((string) ($row['referrer_type'] ?? 'other')));
    $refCounts[] = (int) ($row['count'] ?? 0);
}

$referrerJson = json_encode(['labels' => $refLabels, 'data' => $refCounts], JSON_THROW_ON_ERROR);

// Device doughnut
$devLabels = [];
$devCounts = [];

foreach ($this->deviceBreakdown as $row) {
    $devLabels[] = Text::_('JBS_ANA_DEV_' . strtoupper((string) ($row['device_type'] ?? 'unknown')));
    $devCounts[] = (int) ($row['count'] ?? 0);
}

$deviceJson = json_encode(['labels' => $devLabels, 'data' => $devCounts], JSON_THROW_ON_ERROR);

// Top studies bar chart
$studyLabels = [];
$studyTotals = [];

foreach ($this->topStudies as $row) {
    $title         = htmlspecialchars(substr((string) ($row['title'] ?? 'ID #' . $row['study_id']), 0, 40), ENT_QUOTES);
    $studyLabels[] = $title;
    $studyTotals[] = (int) ($row['total'] ?? 0);
}

$topStudiesJson = json_encode(['labels' => $studyLabels, 'data' => $studyTotals], JSON_THROW_ON_ERROR);
?>
<div class="container-fluid p-3">

    <!-- View navigation tabs -->
    <?php
    $isOverview = $this->drilldown === '';
    $isSeries   = $this->drilldown === 'series' || $this->drilldown === 'message';
    $isMedia    = $this->drilldown === 'media';
    $navParams  = '&preset=' . htmlspecialchars($this->preset, ENT_QUOTES) . '&location_id=' . (int) $this->locationId;
    ?>
    <ul class="nav nav-tabs mb-0" style="border-bottom:0">
        <li class="nav-item">
            <a href="<?php echo Route::_($baseUrl . $navParams); ?>"
               class="nav-link<?php echo $isOverview ? ' active' : ''; ?>">
                <i class="icon-chart-line me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_OVERVIEW'); ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo Route::_($baseUrl . '&drilldown=series' . $navParams); ?>"
               class="nav-link<?php echo $isSeries ? ' active' : ''; ?>">
                <i class="icon-list me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_DRILL_SERIES'); ?>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo Route::_($baseUrl . '&drilldown=media' . $navParams); ?>"
               class="nav-link<?php echo $isMedia ? ' active' : ''; ?>">
                <i class="icon-play me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_DRILL_MEDIA'); ?>
            </a>
        </li>
    </ul>

    <!-- Filter bar -->
    <div class="card mb-3" style="border-top-left-radius:0">
        <div class="card-body py-2">
            <form method="get" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="option" value="com_proclaim">
                <input type="hidden" name="view" value="cwmanalytics">
                <?php if ($this->drilldown !== '') : ?>
                    <input type="hidden" name="drilldown" value="<?php echo htmlspecialchars($this->drilldown, ENT_QUOTES); ?>">
                <?php endif; ?>
                <input type="hidden" name="preset" id="cwm-ana-preset-input" value="custom">

                <!-- Preset buttons -->
                <div class="btn-group" role="group" aria-label="<?php echo Text::_('JBS_ANA_DATE_PRESET'); ?>">
                    <?php foreach ($presets as $key => $label) : ?>
                        <?php
                        $presetHref = Route::_($baseUrl . '&preset=' . $key . '&location_id=' . $this->locationId . ($this->drilldown !== '' ? '&drilldown=' . htmlspecialchars($this->drilldown, ENT_QUOTES) : ''));
                        ?>
                        <a href="<?php echo $presetHref; ?>"
                           class="btn <?php echo $key === $this->preset ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                            <?php echo Text::_($label); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Custom date range -->
                <label class="ms-2 me-1 fw-semibold small"><?php echo Text::_('JBS_ANA_FROM'); ?></label>
                <input type="date" name="date_start" class="form-control form-control-sm" style="width:140px"
                       value="<?php echo htmlspecialchars($this->dateStart, ENT_QUOTES); ?>">
                <label class="me-1 fw-semibold small"><?php echo Text::_('JBS_ANA_TO'); ?></label>
                <input type="date" name="date_end" class="form-control form-control-sm" style="width:140px"
                       value="<?php echo htmlspecialchars($this->dateEnd, ENT_QUOTES); ?>">

                <!-- Campus filter (super-admin only) -->
                <?php if ($this->isSuperAdmin && !empty($this->locations)) : ?>
                    <label class="ms-2 me-1 fw-semibold small"><?php echo Text::_('JBS_ANA_CAMPUS'); ?></label>
                    <select name="location_id" class="form-select form-select-sm" style="width:160px">
                        <option value="0"><?php echo Text::_('JBS_ANA_ALL_CAMPUSES'); ?></option>
                        <?php foreach ($this->locations as $loc) : ?>
                            <option value="<?php echo (int) $loc->id; ?>"
                                <?php echo $this->locationId === (int) $loc->id ? ' selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc->name, ENT_QUOTES); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="icon-search me-1" aria-hidden="true"></i><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
                </button>

                <a href="<?php echo htmlspecialchars($this->exportUrl . $token, ENT_QUOTES); ?>"
                   class="btn btn-sm btn-outline-success ms-auto">
                    <i class="icon-download me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_EXPORT_CSV'); ?>
                </a>
            </form>
        </div>
    </div>

    <?php
    // Three display states:
    //   $showImportPanel  — no real events yet AND seed not run → show full "Getting started" card
    //   $showSeededNotice — seed run but no real events yet     → show compact success note
    //   $showPeriodStrip  — real events exist                   → show event-based period strip
    $legacySeeded    = ($this->legacyKpi['views'] + $this->legacyKpi['plays'] + $this->legacyKpi['downloads']) > 0;
    $hasRealEvents   = $this->hasTrackedEvents;
    $showImportPanel = !$hasRealEvents && !$legacySeeded;
    $showSeededNotice = $legacySeeded && !$hasRealEvents;
    $showPeriodStrip = $hasRealEvents;
    ?>

    <!-- ── All-Time KPI Cards (always from record counters) ─────────────── -->
    <div class="row g-3 mb-3">
        <?php
        $kpiCards = [
            ['icon' => 'icon-eye',      'label' => 'JBS_ANA_TOTAL_VIEWS',     'value' => $this->recordTotals['views'],     'class' => 'text-primary'],
            ['icon' => 'icon-play',     'label' => 'JBS_ANA_TOTAL_PLAYS',     'value' => $this->recordTotals['plays'],     'class' => 'text-success'],
            ['icon' => 'icon-download', 'label' => 'JBS_ANA_TOTAL_DOWNLOADS', 'value' => $this->recordTotals['downloads'], 'class' => 'text-warning'],
        ];
        ?>
        <?php foreach ($kpiCards as $card) : ?>
            <div class="col-6 col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="<?php echo $card['icon']; ?> fa-2x <?php echo $card['class']; ?> mb-2" aria-hidden="true"></i>
                        <div class="fw-bold fs-4 <?php echo $card['class']; ?>"><?php echo number_format($card['value']); ?></div>
                        <div class="text-muted small"><?php echo Text::_($card['label']); ?></div>
                        <span class="text-muted" style="font-size:.7rem"><?php echo Text::_('JBS_ANA_ALL_TIME'); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($showSeededNotice) : ?>
    <!-- ── Seeded notice — seed run, awaiting first real event ───────────── -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 p-2 bg-body-secondary rounded small text-muted">
        <i class="icon-check-circle text-success" aria-hidden="true"></i>
        <span><?php echo Text::sprintf('JBS_ANA_SEEDED_NOTICE',
            number_format($this->legacyKpi['views']),
            number_format($this->legacyKpi['plays']),
            number_format($this->legacyKpi['downloads'])
        ); ?></span>
        <span class="ms-auto">
            <form method="post" action="index.php" class="d-inline">
                <input type="hidden" name="option" value="com_proclaim">
                <input type="hidden" name="task" value="cwmanalytics.seedLegacy">
                <?php echo HTMLHelper::_('form.token'); ?>
                <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none p-0 border-0">
                    <i class="icon-refresh me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_RESEED_LEGACY'); ?>
                </button>
            </form>
        </span>
    </div>
    <?php endif; ?>

    <?php if ($showPeriodStrip) : ?>
    <!-- ── Period strip — event-based totals for selected date range ─────── -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3 p-2 bg-body-secondary rounded small">
        <span class="fw-semibold text-muted">
            <?php echo Text::_('JBS_ANA_PERIOD_ANALYTICS'); ?>
            (<?php echo htmlspecialchars($this->dateStart, ENT_QUOTES); ?> – <?php echo htmlspecialchars($this->dateEnd, ENT_QUOTES); ?>):
        </span>
        <span class="text-primary">
            <i class="icon-eye me-1" aria-hidden="true"></i><?php echo number_format($this->kpi['views']); ?> <?php echo Text::_('JBS_ANA_VIEWS'); ?>
        </span>
        <span class="text-success">
            <i class="icon-play me-1" aria-hidden="true"></i><?php echo number_format($this->kpi['plays']); ?> <?php echo Text::_('JBS_ANA_PLAYS'); ?>
        </span>
        <span class="text-warning">
            <i class="icon-download me-1" aria-hidden="true"></i><?php echo number_format($this->kpi['downloads']); ?> <?php echo Text::_('JBS_ANA_DOWNLOADS'); ?>
        </span>
        <span class="text-info">
            <i class="icon-user me-1" aria-hidden="true"></i><?php echo number_format($this->kpi['sessions']); ?> <?php echo Text::_('JBS_ANA_UNIQUE_SESSIONS'); ?>
        </span>
        <span class="ms-auto">
            <i class="icon-check-circle text-success me-1" aria-hidden="true"></i>
            <?php echo Text::_('JBS_ANA_TRACKING_SINCE'); ?>
            <strong><?php echo htmlspecialchars($this->firstEventDate, ENT_QUOTES); ?></strong>
            &nbsp;·&nbsp;
            <form method="post" action="index.php" class="d-inline">
                <input type="hidden" name="option" value="com_proclaim">
                <input type="hidden" name="task" value="cwmanalytics.seedLegacy">
                <?php echo HTMLHelper::_('form.token'); ?>
                <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none p-0 border-0">
                    <i class="icon-refresh me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_RESEED_LEGACY'); ?>
                </button>
            </form>
        </span>
    </div>
    <?php endif; ?>

    <?php if ($showImportPanel) : ?>
    <!-- ── TRANSITION PANEL ──────────────────────────────────────────────── -->
    <!-- Shown only until the legacy seed has been run (then replaced by the compact seeded notice above) -->
    <div class="card border-primary mb-3">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="icon-rocket me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_TRANSITION_TITLE'); ?>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Old system status -->
                <div class="col-md-6">
                    <div class="d-flex align-items-start gap-2">
                        <i class="icon-check-circle text-success mt-1" aria-hidden="true"></i>
                        <div>
                            <strong><?php echo Text::_('JBS_ANA_OLD_SYSTEM_LABEL'); ?></strong>
                            <p class="text-muted small mb-0"><?php echo Text::_('JBS_ANA_OLD_SYSTEM_DESC'); ?></p>
                        </div>
                    </div>
                </div>
                <!-- New system status -->
                <div class="col-md-6">
                    <div class="d-flex align-items-start gap-2">
                        <i class="icon-clock text-warning mt-1" aria-hidden="true"></i>
                        <div>
                            <strong><?php echo Text::_('JBS_ANA_NEW_SYSTEM_LABEL'); ?></strong>
                            <p class="text-muted small mb-0"><?php echo Text::_('JBS_ANA_NEW_SYSTEM_DESC'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <p class="mb-2"><?php echo Text::_('JBS_ANA_IMPORT_PROMPT'); ?></p>
            <form method="post" action="index.php" class="d-inline">
                <input type="hidden" name="option" value="com_proclaim">
                <input type="hidden" name="task" value="cwmanalytics.seedLegacy">
                <?php echo HTMLHelper::_('form.token'); ?>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="icon-database me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_SEED_LEGACY'); ?>
                </button>
            </form>
            <?php if ($this->legacyKpi['views'] > 0 || $this->legacyKpi['plays'] > 0) : ?>
                <span class="text-success small ms-3">
                    <i class="icon-check" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_ANA_IMPORT_DONE'); ?>
                    <?php echo number_format($this->legacyKpi['views']); ?> <?php echo Text::_('JBS_ANA_VIEWS'); ?>
                    / <?php echo number_format($this->legacyKpi['plays']); ?> <?php echo Text::_('JBS_ANA_PLAYS'); ?>
                    / <?php echo number_format($this->legacyKpi['downloads']); ?> <?php echo Text::_('JBS_ANA_DOWNLOADS'); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <!-- ── END TRANSITION PANEL ──────────────────────────────────────────── -->
    <?php endif; ?>

    <!-- Line Chart: Views / Plays / Downloads over time -->
    <div class="card mb-3">
        <div class="card-header fw-semibold">
            <i class="icon-chart-line me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_ENGAGEMENT_OVER_TIME'); ?>
        </div>
        <div class="card-body">
            <canvas id="cwm-chart-timeseries" height="80"
                    data-cwm-chart="line"
                    data-cwm-chart-data="<?php echo htmlspecialchars($timeSeriesJson, ENT_QUOTES); ?>">
            </canvas>
        </div>
    </div>

    <!-- Bar Chart + Table: Top 10 sermons -->
    <?php if (!empty($this->topStudies)) : ?>
    <div class="card mb-3">
        <div class="card-header fw-semibold">
            <i class="icon-star me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_TOP_SERMONS'); ?>
        </div>
        <div class="card-body">
            <canvas id="cwm-chart-topstudies" height="80"
                    data-cwm-chart="bar"
                    data-cwm-chart-data="<?php echo htmlspecialchars($topStudiesJson, ENT_QUOTES); ?>">
            </canvas>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead><tr>
                    <th><?php echo Text::_('JBS_ANA_MESSAGE_TITLE'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_COUNT'); ?></th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($this->topStudies as $row) : ?>
                    <?php $drillUrl = Route::_($baseUrl . '&drilldown=message&id=' . (int) $row['study_id'] . '&preset=' . htmlspecialchars($input->getString('preset', '30d'), ENT_QUOTES) . '&location_id=' . (int) $this->locationId); ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($row['title'] ?? 'ID #' . $row['study_id']), ENT_QUOTES); ?></td>
                        <td class="text-end"><?php echo number_format((int) ($row['total'] ?? 0)); ?></td>
                        <td class="text-end"><a href="<?php echo $drillUrl; ?>" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2"><?php echo Text::_('JBS_ANA_DRILL_VIEW'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Doughnut Charts Row -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">
                    <i class="icon-share me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_TRAFFIC_SOURCES'); ?>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <?php if (!empty($this->referrerBreakdown)) : ?>
                        <canvas id="cwm-chart-referrer" style="max-height:260px"
                                data-cwm-chart="doughnut"
                                data-cwm-chart-data="<?php echo htmlspecialchars($referrerJson, ENT_QUOTES); ?>">
                        </canvas>
                    <?php else : ?>
                        <p class="text-muted my-auto"><?php echo Text::_('JBS_ANA_NO_DATA'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">
                    <i class="icon-laptop me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_DEVICE_BREAKDOWN'); ?>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <?php if (!empty($this->deviceBreakdown)) : ?>
                        <canvas id="cwm-chart-device" style="max-height:260px"
                                data-cwm-chart="doughnut"
                                data-cwm-chart-data="<?php echo htmlspecialchars($deviceJson, ENT_QUOTES); ?>">
                        </canvas>
                    <?php else : ?>
                        <p class="text-muted my-auto"><?php echo Text::_('JBS_ANA_NO_DATA'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Browser + OS + Language Tables Row -->
    <div class="row g-3 mb-3">
        <?php if (!empty($this->browserBreakdown)) : ?>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-header fw-semibold"><?php echo Text::_('JBS_ANA_BROWSERS'); ?></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th><?php echo Text::_('JBS_ANA_BROWSER'); ?></th><th class="text-end"><?php echo Text::_('JBS_ANA_COUNT'); ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($this->browserBreakdown as $row) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($row['browser'] ?? 'other'), ENT_QUOTES); ?></td>
                                <td class="text-end"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($this->osBreakdown)) : ?>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-header fw-semibold"><?php echo Text::_('JBS_ANA_OS'); ?></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th><?php echo Text::_('JBS_ANA_OPERATING_SYSTEM'); ?></th><th class="text-end"><?php echo Text::_('JBS_ANA_COUNT'); ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($this->osBreakdown as $row) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($row['os'] ?? 'other'), ENT_QUOTES); ?></td>
                                <td class="text-end"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($this->languageBreakdown)) : ?>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-header fw-semibold"><?php echo Text::_('JBS_ANA_LANGUAGES'); ?></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th><?php echo Text::_('JBS_ANA_LANGUAGE'); ?></th><th class="text-end"><?php echo Text::_('JBS_ANA_COUNT'); ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($this->languageBreakdown as $row) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($row['language'] ?? ''), ENT_QUOTES); ?></td>
                                <td class="text-end"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- UTM Campaigns Table -->
    <?php if (!empty($this->utmBreakdown)) : ?>
    <div class="card mb-3">
        <div class="card-header fw-semibold">
            <i class="icon-tag me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_UTM_CAMPAIGNS'); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th><?php echo Text::_('JBS_ANA_UTM_SOURCE'); ?></th>
                        <th><?php echo Text::_('JBS_ANA_UTM_MEDIUM'); ?></th>
                        <th><?php echo Text::_('JBS_ANA_UTM_CAMPAIGN'); ?></th>
                        <th class="text-end"><?php echo Text::_('JBS_ANA_COUNT'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->utmBreakdown as $row) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($row['utm_source'] ?? ''), ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars((string) ($row['utm_medium'] ?? ''), ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars((string) ($row['utm_campaign'] ?? ''), ENT_QUOTES); ?></td>
                        <td class="text-end"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Breadcrumb for sub-levels (series detail, message detail) -->
    <?php if (($this->drilldown === 'series' && $this->drilldownId > 0) || $this->drilldown === 'message') : ?>
    <?php echo $this->loadTemplate('breadcrumb'); ?>
    <?php endif; ?>

    <?php if ($this->drilldown === 'series' && $this->drilldownId === 0) : ?>
        <?php echo $this->loadTemplate('series_list'); ?>
    <?php elseif ($this->drilldown === 'series' && $this->drilldownId > 0) : ?>
        <?php echo $this->loadTemplate('series_detail'); ?>
    <?php elseif ($this->drilldown === 'message' && $this->drilldownId > 0) : ?>
        <?php echo $this->loadTemplate('message'); ?>
    <?php elseif ($this->drilldown === 'media') : ?>
        <?php echo $this->loadTemplate('media'); ?>
    <?php else : ?>
    <!-- GDPR notice (overview only) -->
    <p class="text-muted small mt-3">
        <i class="icon-lock me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_GDPR_NOTICE'); ?>
    </p>
    <?php endif; ?>

</div>
