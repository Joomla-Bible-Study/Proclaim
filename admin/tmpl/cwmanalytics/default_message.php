<?php
/**
 * Analytics drill-down: Single message detail with media file breakdown.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

$study       = $this->studyInfo;
$studyTitle  = $study ? htmlspecialchars((string) $study->title, ENT_QUOTES) : '';
$studyDate   = $study ? substr((string) ($study->study_date ?? ''), 0, 10) : '';
$allTimeViews = $study ? (int) ($study->all_time_views ?? 0) : 0;

// Prepare time series JSON
$tsLabels     = [];
$tsViews      = [];
$tsPlays      = [];
$tsDownloads  = [];

foreach ($this->studyTimeSeries as $row) {
    $tsLabels[]    = htmlspecialchars($row['period'] ?? '', ENT_QUOTES);
    $tsViews[]     = (int) ($row['views'] ?? 0);
    $tsPlays[]     = (int) ($row['plays'] ?? 0);
    $tsDownloads[] = (int) ($row['downloads'] ?? 0);
}

$tsJson = json_encode([
    'labels'   => $tsLabels,
    'datasets' => [
        ['label' => Text::_('JBS_ANA_VIEWS'),     'data' => $tsViews,     'key' => 'views'],
        ['label' => Text::_('JBS_ANA_PLAYS'),     'data' => $tsPlays,     'key' => 'plays'],
        ['label' => Text::_('JBS_ANA_DOWNLOADS'), 'data' => $tsDownloads, 'key' => 'downloads'],
    ],
], JSON_THROW_ON_ERROR);
?>

<!-- Message header -->
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title mb-1">
            <?php echo $studyTitle; ?>
            <?php if ($study && (int) ($study->published ?? 1) === 2) : ?>
                <span class="badge bg-warning text-dark ms-1"><?php echo Text::_('JBS_ANA_STATUS_ARCHIVED'); ?></span>
            <?php endif; ?>
        </h5>
        <?php if ($studyDate) : ?>
            <p class="text-muted small mb-0"><?php echo Text::_('JBS_ANA_DATE'); ?>: <?php echo $studyDate; ?></p>
        <?php endif; ?>
        <?php if ($allTimeViews) : ?>
            <p class="text-muted small mb-0"><?php echo Text::_('JBS_ANA_ALL_TIME_VIEWS'); ?>: <strong><?php echo number_format($allTimeViews); ?></strong></p>
        <?php endif; ?>
    </div>
</div>

<!-- Period KPI strip -->
<div class="d-flex flex-wrap gap-3 mb-3 p-2 bg-body-secondary rounded small">
    <span class="fw-semibold text-muted"><?php echo Text::_('JBS_ANA_PERIOD_ANALYTICS'); ?> (<?php echo htmlspecialchars($this->dateStart, ENT_QUOTES); ?> – <?php echo htmlspecialchars($this->dateEnd, ENT_QUOTES); ?>):</span>
    <span class="text-primary"><i class="icon-eye me-1" aria-hidden="true"></i><?php echo number_format($this->studyKpi['views']); ?> <?php echo Text::_('JBS_ANA_VIEWS'); ?></span>
    <span class="text-success"><i class="icon-play me-1" aria-hidden="true"></i><?php echo number_format($this->studyKpi['plays']); ?> <?php echo Text::_('JBS_ANA_PLAYS'); ?></span>
    <span class="text-warning"><i class="icon-download me-1" aria-hidden="true"></i><?php echo number_format($this->studyKpi['downloads']); ?> <?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></span>
    <?php if ($this->studyKpi['sessions']) : ?>
    <span class="text-info"><i class="icon-user me-1" aria-hidden="true"></i><?php echo number_format($this->studyKpi['sessions']); ?> <?php echo Text::_('JBS_ANA_UNIQUE_SESSIONS'); ?></span>
    <?php endif; ?>
</div>

<!-- Time series chart -->
<?php if (!empty($this->studyTimeSeries)) : ?>
<div class="card mb-3">
    <div class="card-header fw-semibold">
        <i class="icon-chart-line me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_ENGAGEMENT_OVER_TIME'); ?>
    </div>
    <div class="card-body">
        <canvas id="cwm-chart-msg-timeseries" height="80"
                data-cwm-chart="line"
                data-cwm-chart-data="<?php echo htmlspecialchars($tsJson, ENT_QUOTES); ?>">
        </canvas>
    </div>
</div>
<?php endif; ?>

<!-- Unified media files breakdown (local + platform stats merged) -->
<?php if (!empty($this->studyMedia)) : ?>
<?php
    // Check if any media file has platform stats
    $hasPlatformStats = false;
    $hasExternalMedia = false;
    $allExternal      = true;

    foreach ($this->studyMedia as $mf) {
        if ((int) ($mf['platform_play_count'] ?? 0) > 0) {
            $hasPlatformStats = true;
        }

        if ((int) ($mf['content_origin'] ?? 0) === 1) {
            $hasExternalMedia = true;
        } else {
            $allExternal = false;
        }
    }

    // Build platform stats index by media_id for additional detail columns
    $platformIndex = [];

    if (!empty($this->studyPlatformStats)) {
        foreach ($this->studyPlatformStats as $ps) {
            $platformIndex[(int) $ps['media_id']] = $ps;
        }
    }

    // Build sync-capable media list for JS (Vimeo/Wistia support API sync)
    $syncMedia     = [];
    $syncPlatforms = ['vimeo', 'wistia'];

    foreach ($this->studyMedia as $mf) {
        if ((int) ($mf['content_origin'] ?? 0) === 1) {
            continue;
        }

        $platform = '';

        if (isset($platformIndex[(int) $mf['media_id']])) {
            $platform = strtolower((string) ($platformIndex[(int) $mf['media_id']]['platform'] ?? ''));
        }

        if (in_array($platform, $syncPlatforms, true)) {
            $syncMedia[] = [
                'mediaId'    => (int) $mf['media_id'],
                'platform'   => $platform,
                'canSync'    => true,
                'serverName' => (string) ($mf['server_name'] ?? ''),
            ];
        }
    }

    $syncMediaJson = htmlspecialchars(json_encode($syncMedia, JSON_THROW_ON_ERROR), ENT_QUOTES);
?>
<div class="card mb-3" data-sync-media="<?php echo $syncMediaJson; ?>">
    <div class="card-header fw-semibold d-flex align-items-center">
        <div>
            <i class="icon-play me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_MEDIA_FILES'); ?>
            <span class="text-muted fw-normal small ms-2"><?php echo Text::_('JBS_ANA_MEDIA_HOW_VIBING'); ?></span>
        </div>
        <?php if (!$allExternal && !empty($this->studyInfo)) : ?>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto"
                data-desc-action="true"
                data-study-id="<?php echo (int) $this->studyInfo->id; ?>"
                title="<?php echo Text::_('JBS_MED_SYNC_DESC'); ?>">
            <i class="icon-copy me-1" aria-hidden="true"></i><?php echo Text::_('JBS_MED_COPY_DESC'); ?>
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo Text::_('JBS_ANA_SERVER'); ?></th>
                    <th><?php echo Text::_('JBS_ANA_MEDIA_LABEL'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_MED_LOCAL_PLAYS'); ?></th>
                    <?php if ($hasPlatformStats) : ?>
                    <th class="text-end"><?php echo Text::_('JBS_MED_PLATFORM_PLAYS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_MED_EXTERNAL_PLAYS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_MED_TOTAL_REACH'); ?></th>
                    <?php endif; ?>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PERIOD_PLAYS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_ALL_TIME_DOWNLOADS'); ?></th>
                    <th class="text-center"><?php echo Text::_('JBS_MED_CONTENT_ORIGIN'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->studyMedia as $i => $mf) : ?>
                <?php
                    $mparams         = new Registry($mf['media_params'] ?? '');
                    $mediaLabel      = $mparams->get('media_button_text', '') ?: '#' . (int) $mf['media_id'];
                    $serverName      = htmlspecialchars((string) ($mf['server_name'] ?? '—'), ENT_QUOTES);
                    $isExternal      = (int) ($mf['content_origin'] ?? 0) === 1;
                    $isMediaArchived = (int) ($mf['published'] ?? 1) === 2;
                    $platformPlays   = (int) ($mf['platform_play_count'] ?? 0);
                    $externalPlays   = (int) ($mf['external_plays'] ?? 0);
                    $totalReach      = (int) ($mf['total_reach'] ?? 0);
                    $rowClass        = $isExternal ? 'table-secondary' : ($isMediaArchived ? 'table-warning' : '');
                ?>
                <tr<?php echo $rowClass !== '' ? ' class="' . $rowClass . '"' : ''; ?>>
                    <td class="text-muted"><?php echo $i + 1; ?></td>
                    <td class="small"><?php echo $serverName; ?></td>
                    <td>
                        <?php echo htmlspecialchars((string) $mediaLabel, ENT_QUOTES); ?>
                        <?php if ($isMediaArchived) : ?>
                            <span class="badge bg-warning text-dark ms-1"><?php echo Text::_('JBS_ANA_STATUS_ARCHIVED'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-success fw-semibold"><?php echo number_format((int) ($mf['all_time_plays'] ?? 0)); ?></td>
                    <?php if ($hasPlatformStats) : ?>
                    <td class="text-end text-body"><?php echo $platformPlays > 0 ? number_format($platformPlays) : '—'; ?></td>
                    <td class="text-end text-info fw-semibold"><?php echo $externalPlays > 0 ? number_format($externalPlays) : '—'; ?></td>
                    <td class="text-end fw-bold text-body"><?php echo $totalReach > 0 ? number_format($totalReach) : '—'; ?></td>
                    <?php endif; ?>
                    <td class="text-end text-muted small"><?php echo number_format((int) ($mf['period_plays'] ?? 0)); ?></td>
                    <td class="text-end text-warning"><?php echo number_format((int) ($mf['all_time_downloads'] ?? 0)); ?></td>
                    <td class="text-center">
                        <?php if ($isExternal) : ?>
                            <span class="badge bg-secondary"><?php echo Text::_('JBS_MED_ORIGIN_EXTERNAL'); ?></span>
                        <?php else : ?>
                            <span class="badge bg-success"><?php echo Text::_('JBS_MED_ORIGIN_MINISTRY'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Platform stats detail for this message's media files -->
<?php if (!empty($this->studyPlatformStats)) : ?>
<div class="card mb-3">
    <div class="card-header fw-semibold">
        <i class="icon-globe me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_PLATFORM_STATS'); ?>
        <span class="text-muted fw-normal small ms-2"><?php echo Text::_('JBS_ANA_PLATFORM_AUTO_SYNC_NOTE'); ?></span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th><?php echo Text::_('JBS_ANA_PLATFORM'); ?></th>
                    <th><?php echo Text::_('JBS_ANA_SERVER'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_VIEWS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_PLAYS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_LIKES'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_COMMENTS'); ?></th>
                    <?php
                    // Show Wistia-specific columns only if any row has them
                    $hasWistia = false;
                    foreach ($this->studyPlatformStats as $ps) {
                        if ($ps['load_count'] !== null || $ps['hours_watched'] !== null) {
                            $hasWistia = true;

                            break;
                        }
                    }
                    ?>
                    <?php if ($hasWistia) : ?>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_HOURS_WATCHED'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_ENGAGEMENT'); ?></th>
                    <?php endif; ?>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_LAST_SYNCED'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->studyPlatformStats as $ps) : ?>
                <tr>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst((string) ($ps['platform'] ?? '')), ENT_QUOTES); ?></span></td>
                    <td class="small"><?php echo htmlspecialchars((string) ($ps['server_name'] ?? ''), ENT_QUOTES); ?></td>
                    <td class="text-end text-body"><?php echo number_format((int) ($ps['view_count'] ?? 0)); ?></td>
                    <td class="text-end text-body"><?php echo number_format((int) ($ps['play_count'] ?? 0)); ?></td>
                    <td class="text-end text-body"><?php echo $ps['like_count'] !== null ? number_format((int) $ps['like_count']) : '—'; ?></td>
                    <td class="text-end text-body"><?php echo $ps['comment_count'] !== null ? number_format((int) $ps['comment_count']) : '—'; ?></td>
                    <?php if ($hasWistia) : ?>
                    <td class="text-end text-body"><?php echo $ps['hours_watched'] !== null ? number_format((float) $ps['hours_watched'], 1) : '—'; ?></td>
                    <td class="text-end text-body"><?php echo $ps['engagement'] !== null ? number_format((float) $ps['engagement'], 1) . '%' : '—'; ?></td>
                    <?php endif; ?>
                    <td class="text-end text-muted small"><?php echo $ps['synced_at'] ? htmlspecialchars(substr((string) $ps['synced_at'], 0, 16), ENT_QUOTES) : '—'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (empty($this->studyTimeSeries) && empty($this->studyMedia) && empty($this->studyPlatformStats) && !array_sum($this->studyKpi)) : ?>
<div class="alert alert-light"><?php echo Text::_('JBS_ANA_NO_DATA'); ?></div>
<?php endif; ?>
