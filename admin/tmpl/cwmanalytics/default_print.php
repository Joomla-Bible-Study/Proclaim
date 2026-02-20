<?php

/**
 * Analytics Print Report — clean, paper-optimized report.
 *
 * Renders all analytics data as plain HTML tables without Chart.js canvases.
 * Opened via tmpl=component&print=1 popup from the dashboard.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

// Site name + default study image for report branding
$app      = Factory::getApplication();
$siteName = $app->get('sitename', '');
$admin    = Cwmparams::getAdmin();
$params   = new Registry($admin->params ?? '');
$logoPath = $params->get('default_study_image', '');

// Build full URL for the logo if set
$logoUrl = '';

if ($logoPath !== '') {
    $logoUrl = Uri::root() . ltrim($logoPath, '/');
}

// Resolve campus name for report header
$campusName = '';

if ($this->locationId > 0 && !empty($this->locations)) {
    foreach ($this->locations as $loc) {
        if ((int) $loc->id === $this->locationId) {
            $campusName = (string) $loc->name;

            break;
        }
    }
}

// Human-readable date range
$dateRange = Text::sprintf(
    'JBS_ANA_REPORT_PERIOD',
    htmlspecialchars($this->dateStart, ENT_QUOTES),
    htmlspecialchars($this->dateEnd, ENT_QUOTES)
);

$generated = Text::sprintf('JBS_ANA_REPORT_GENERATED', date('Y-m-d H:i'));
?>
<div class="proclaim-analytics-report">

    <!-- ── Report Header ──────────────────────────────────────── -->
    <div class="report-header">
        <div class="report-header-row">
            <?php if ($logoUrl !== '') : ?>
                <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES); ?>"
                     alt="" class="report-logo">
            <?php endif; ?>
            <div class="report-header-text">
                <?php if ($siteName !== '') : ?>
                    <div class="report-site-name"><?php echo htmlspecialchars($siteName, ENT_QUOTES); ?></div>
                <?php endif; ?>
                <h1><?php echo Text::_('JBS_ANA_REPORT_TITLE'); ?></h1>
                <p class="report-subtitle">
                    <?php echo $dateRange; ?>
                    <?php if ($campusName !== '') : ?>
                        <br><?php echo Text::sprintf('JBS_ANA_REPORT_CAMPUS', htmlspecialchars($campusName, ENT_QUOTES)); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <?php if ($this->drilldown === '' ) : ?>
    <!-- ════════════════════════════════════════════════════════
         OVERVIEW
         ════════════════════════════════════════════════════════ -->

    <!-- All-Time Totals -->
    <h2><?php echo Text::_('JBS_ANA_REPORT_ALLTIME'); ?></h2>
    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->recordTotals['views']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_TOTAL_VIEWS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->recordTotals['plays']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_TOTAL_PLAYS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->recordTotals['downloads']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_TOTAL_DOWNLOADS'); ?></div>
        </div>
    </div>

    <!-- Period Totals -->
    <h2><?php echo Text::_('JBS_ANA_REPORT_PERIOD_TOTALS'); ?></h2>
    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->kpi['views']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_VIEWS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->kpi['plays']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_PLAYS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->kpi['downloads']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->kpi['sessions']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_UNIQUE_SESSIONS'); ?></div>
        </div>
    </div>

    <!-- Top Studies -->
    <?php if (!empty($this->topStudies)) : ?>
    <h2><?php echo Text::_('JBS_ANA_REPORT_TOP_STUDIES'); ?></h2>
    <table class="report-table">
        <thead>
            <tr>
                <th class="col-rank"><?php echo Text::_('JBS_ANA_REPORT_RANK'); ?></th>
                <th><?php echo Text::_('JBS_ANA_MESSAGE_TITLE'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_REPORT_ENGAGEMENT'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->topStudies as $i => $row) : ?>
            <tr>
                <td class="col-rank"><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars((string) ($row['title'] ?? 'ID #' . $row['study_id']), ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['total'] ?? 0)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Referrer Breakdown -->
    <?php if (!empty($this->referrerBreakdown)) : ?>
    <h2><?php echo Text::_('JBS_ANA_REPORT_REFERRERS'); ?></h2>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_TRAFFIC_SOURCES'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_COUNT'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->referrerBreakdown as $row) : ?>
            <tr>
                <td><?php echo Text::_('JBS_ANA_REF_' . strtoupper((string) ($row['referrer_type'] ?? 'other'))); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Device Breakdown -->
    <?php if (!empty($this->deviceBreakdown)) : ?>
    <h2><?php echo Text::_('JBS_ANA_REPORT_DEVICES'); ?></h2>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_DEVICE_BREAKDOWN'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_COUNT'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->deviceBreakdown as $row) : ?>
            <tr>
                <td><?php echo Text::_('JBS_ANA_DEV_' . strtoupper((string) ($row['device_type'] ?? 'unknown'))); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Platform Video Stats -->
    <?php if (!empty($this->platformStats)) : ?>
    <h2><?php echo Text::_('JBS_ANA_PLATFORM_STATS'); ?></h2>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_PLATFORM'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_VIDEOS_SYNCED'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_VIEWS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_LIKES'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_LAST_SYNCED'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->platformStats as $row) : ?>
            <tr>
                <td><?php echo htmlspecialchars(ucfirst((string) ($row['platform'] ?? '')), ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['media_count'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['total_views'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['total_plays'] ?? 0)); ?></td>
                <td class="col-number"><?php echo $row['total_likes'] !== null ? number_format((int) $row['total_likes']) : '—'; ?></td>
                <td class="col-number"><?php echo $row['last_synced'] ? htmlspecialchars(substr((string) $row['last_synced'], 0, 16), ENT_QUOTES) : '—'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Browser & OS (side by side) -->
    <?php if (!empty($this->browserBreakdown) || !empty($this->osBreakdown)) : ?>
    <div class="report-columns">
        <?php if (!empty($this->browserBreakdown)) : ?>
        <div class="report-col">
            <h2><?php echo Text::_('JBS_ANA_BROWSERS'); ?></h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th><?php echo Text::_('JBS_ANA_BROWSER'); ?></th>
                        <th class="col-number"><?php echo Text::_('JBS_ANA_COUNT'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($this->browserBreakdown, 0, 5) as $row) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($row['browser'] ?? 'other'), ENT_QUOTES); ?></td>
                        <td class="col-number"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($this->osBreakdown)) : ?>
        <div class="report-col">
            <h2><?php echo Text::_('JBS_ANA_OS'); ?></h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th><?php echo Text::_('JBS_ANA_OPERATING_SYSTEM'); ?></th>
                        <th class="col-number"><?php echo Text::_('JBS_ANA_COUNT'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_slice($this->osBreakdown, 0, 5) as $row) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) ($row['os'] ?? 'other'), ENT_QUOTES); ?></td>
                        <td class="col-number"><?php echo number_format((int) ($row['count'] ?? 0)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php elseif ($this->drilldown === 'series' && $this->drilldownId === 0) : ?>
    <!-- ════════════════════════════════════════════════════════
         SERIES LIST
         ════════════════════════════════════════════════════════ -->
    <h2><?php echo Text::_('JBS_ANA_ALL_SERIES'); ?></h2>

    <?php if (empty($this->seriesList)) : ?>
        <p class="report-empty"><?php echo Text::_('JBS_ANA_REPORT_NO_DATA'); ?></p>
    <?php else : ?>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_SERIES_TITLE'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_MESSAGES'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_VIEWS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->seriesList as $row) : ?>
            <tr>
                <td><?php echo htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo (int) ($row['message_count'] ?? 0); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['views'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['plays'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['downloads'] ?? 0)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php elseif ($this->drilldown === 'series' && $this->drilldownId > 0) : ?>
    <!-- ════════════════════════════════════════════════════════
         SERIES DETAIL
         ════════════════════════════════════════════════════════ -->
    <?php $seriesTitle = htmlspecialchars((string) ($this->seriesInfo->title ?? ''), ENT_QUOTES); ?>
    <h2><?php echo Text::sprintf('JBS_ANA_SERIES_MESSAGES_HEADER', $seriesTitle); ?></h2>

    <?php if (empty($this->seriesMessages)) : ?>
        <p class="report-empty"><?php echo Text::_('JBS_ANA_REPORT_NO_DATA'); ?></p>
    <?php else : ?>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_MESSAGE_TITLE'); ?></th>
                <th><?php echo Text::_('JBS_ANA_DATE'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_ALL_TIME_VIEWS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_VIEWS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->seriesMessages as $row) : ?>
            <tr>
                <td><?php echo htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars(substr((string) ($row['study_date'] ?? ''), 0, 10), ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['all_time_views'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['views'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['plays'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['downloads'] ?? 0)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php elseif ($this->drilldown === 'message' && $this->drilldownId > 0) : ?>
    <!-- ════════════════════════════════════════════════════════
         MESSAGE DETAIL
         ════════════════════════════════════════════════════════ -->
    <?php
    $study       = $this->studyInfo;
    $studyTitle  = $study ? htmlspecialchars((string) $study->title, ENT_QUOTES) : '';
    $studyDate   = $study ? substr((string) ($study->study_date ?? ''), 0, 10) : '';
    $allTimeViews = $study ? (int) ($study->all_time_views ?? 0) : 0;
    ?>
    <h2><?php echo $studyTitle; ?></h2>
    <?php if ($studyDate) : ?>
        <p class="report-meta"><?php echo Text::_('JBS_ANA_DATE'); ?>: <?php echo $studyDate; ?>
        <?php if ($allTimeViews) : ?>
            &nbsp;&middot;&nbsp;<?php echo Text::_('JBS_ANA_ALL_TIME_VIEWS'); ?>: <?php echo number_format($allTimeViews); ?>
        <?php endif; ?>
        </p>
    <?php endif; ?>

    <!-- Period KPIs -->
    <h3><?php echo Text::_('JBS_ANA_REPORT_PERIOD_TOTALS'); ?></h3>
    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->studyKpi['views']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_VIEWS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->studyKpi['plays']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_PLAYS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->studyKpi['downloads']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></div>
        </div>
        <div class="kpi-box">
            <div class="kpi-value"><?php echo number_format($this->studyKpi['sessions']); ?></div>
            <div class="kpi-label"><?php echo Text::_('JBS_ANA_UNIQUE_SESSIONS'); ?></div>
        </div>
    </div>

    <!-- Media Files -->
    <?php if (!empty($this->studyMedia)) : ?>
    <h3><?php echo Text::_('JBS_ANA_MEDIA_FILES'); ?></h3>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_SERVER'); ?></th>
                <th><?php echo Text::_('JBS_ANA_MEDIA_LABEL'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PERIOD_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_ALL_TIME_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PERIOD_DOWNLOADS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_ALL_TIME_DOWNLOADS'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->studyMedia as $mf) : ?>
            <?php
            $mparams    = new Registry($mf['media_params'] ?? '');
            $mediaLabel = $mparams->get('media_button_text', '') ?: '#' . (int) $mf['media_id'];
            ?>
            <tr>
                <td><?php echo htmlspecialchars((string) ($mf['server_name'] ?? ''), ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars((string) $mediaLabel, ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo number_format((int) ($mf['period_plays'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($mf['all_time_plays'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($mf['period_downloads'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($mf['all_time_downloads'] ?? 0)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Platform Stats for this message -->
    <?php if (!empty($this->studyPlatformStats)) : ?>
    <h3><?php echo Text::_('JBS_ANA_PLATFORM_STATS'); ?></h3>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_PLATFORM'); ?></th>
                <th><?php echo Text::_('JBS_ANA_SERVER'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_VIEWS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_LIKES'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLATFORM_LAST_SYNCED'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->studyPlatformStats as $ps) : ?>
            <tr>
                <td><?php echo htmlspecialchars(ucfirst((string) ($ps['platform'] ?? '')), ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars((string) ($ps['server_name'] ?? ''), ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo number_format((int) ($ps['view_count'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($ps['play_count'] ?? 0)); ?></td>
                <td class="col-number"><?php echo $ps['like_count'] !== null ? number_format((int) $ps['like_count']) : '—'; ?></td>
                <td class="col-number"><?php echo $ps['synced_at'] ? htmlspecialchars(substr((string) $ps['synced_at'], 0, 16), ENT_QUOTES) : '—'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if (empty($this->studyMedia) && empty($this->studyPlatformStats) && !array_sum($this->studyKpi)) : ?>
        <p class="report-empty"><?php echo Text::_('JBS_ANA_REPORT_NO_DATA'); ?></p>
    <?php endif; ?>

    <?php elseif ($this->drilldown === 'media') : ?>
    <!-- ════════════════════════════════════════════════════════
         MEDIA TYPE BREAKDOWN
         ════════════════════════════════════════════════════════ -->
    <h2><?php echo Text::_('JBS_ANA_MEDIA_TYPES'); ?></h2>

    <?php if (empty($this->mediaTypeBreakdown)) : ?>
        <p class="report-empty"><?php echo Text::_('JBS_ANA_REPORT_NO_DATA'); ?></p>
    <?php else : ?>
    <?php
    $grandTotal = array_sum(array_column($this->mediaTypeBreakdown, 'plays'))
        + array_sum(array_column($this->mediaTypeBreakdown, 'downloads'));
    ?>
    <table class="report-table">
        <thead>
            <tr>
                <th><?php echo Text::_('JBS_ANA_SERVER'); ?></th>
                <th><?php echo Text::_('JBS_ANA_SERVER_TYPE'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_PLAYS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_UNIQUE_MEDIA'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_MESSAGES'); ?></th>
                <th class="col-number"><?php echo Text::_('JBS_ANA_REPORT_PERCENTAGE'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->mediaTypeBreakdown as $row) : ?>
            <?php
            $rowTotal = (int) ($row['plays'] ?? 0) + (int) ($row['downloads'] ?? 0);
            $pct      = $grandTotal > 0 ? round($rowTotal / $grandTotal * 100, 1) : 0;
            ?>
            <tr>
                <td><?php echo htmlspecialchars((string) ($row['server_name'] ?? 'Unknown'), ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars((string) ($row['server_type'] ?? ''), ENT_QUOTES); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['plays'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['downloads'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['media_count'] ?? 0)); ?></td>
                <td class="col-number"><?php echo number_format((int) ($row['study_count'] ?? 0)); ?></td>
                <td class="col-number"><?php echo $pct; ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php endif; ?>

    <!-- ── Report Footer ──────────────────────────────────────── -->
    <div class="report-footer">
        <p><?php echo Text::_('JBS_ANA_REPORT_FOOTER'); ?> &middot; <?php echo $generated; ?></p>
    </div>

    <!-- Print button (hidden in actual print output) -->
    <div class="proclaim-no-print" style="text-align:center; margin-top:20px">
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <i class="icon-print me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_PRINT_REPORT'); ?>
        </button>
    </div>

</div>
