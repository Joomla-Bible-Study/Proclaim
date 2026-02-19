<?php
/**
 * Analytics drill-down: Media type / server engagement breakdown.
 *
 * @package    Proclaim.Admin
 * @since      10.1.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

// Doughnut chart data — plays by server
$mediaLabels = [];
$mediaPlays  = [];
$totalPlays  = 0;

foreach ($this->mediaTypeBreakdown as $row) {
    $plays = (int) ($row['plays'] ?? 0);
    if ($plays > 0) {
        $mediaLabels[] = htmlspecialchars((string) ($row['server_name'] ?? 'Unknown'), ENT_QUOTES);
        $mediaPlays[]  = $plays;
        $totalPlays   += $plays;
    }
}

$mediaChartJson = json_encode(['labels' => $mediaLabels, 'data' => $mediaPlays], JSON_THROW_ON_ERROR);
?>
<div class="card mb-3">
    <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
        <span><i class="icon-play me-1" aria-hidden="true"></i><?php echo Text::_('JBS_ANA_MEDIA_TYPES'); ?></span>
        <span class="text-muted small fw-normal"><?php echo Text::sprintf('JBS_ANA_DATE_RANGE_LABEL', htmlspecialchars($this->dateStart, ENT_QUOTES), htmlspecialchars($this->dateEnd, ENT_QUOTES)); ?></span>
    </div>

    <?php if (empty($this->mediaTypeBreakdown)) : ?>
        <div class="card-body"><p class="text-muted mb-0"><?php echo Text::_('JBS_ANA_NO_DATA'); ?></p></div>
    <?php else : ?>

    <!-- Doughnut chart: plays by server type -->
    <?php if (!empty($mediaLabels)) : ?>
    <div class="card-body d-flex justify-content-center">
        <canvas id="cwm-chart-mediatype" style="max-height:260px"
                data-cwm-chart="doughnut"
                data-cwm-chart-data="<?php echo htmlspecialchars($mediaChartJson, ENT_QUOTES); ?>">
        </canvas>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th><?php echo Text::_('JBS_ANA_SERVER'); ?></th>
                    <th><?php echo Text::_('JBS_ANA_SERVER_TYPE'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLAYS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_UNIQUE_MEDIA'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_MESSAGES'); ?></th>
                    <th class="text-end">%</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $grandTotal = array_sum(array_column($this->mediaTypeBreakdown, 'plays')) + array_sum(array_column($this->mediaTypeBreakdown, 'downloads'));
            foreach ($this->mediaTypeBreakdown as $row) :
                $rowTotal = (int) ($row['plays'] ?? 0) + (int) ($row['downloads'] ?? 0);
                $pct      = $grandTotal > 0 ? round($rowTotal / $grandTotal * 100, 1) : 0;
            ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars((string) ($row['server_name'] ?? 'Unknown'), ENT_QUOTES); ?></td>
                    <td class="text-muted small"><?php echo htmlspecialchars((string) ($row['server_type'] ?? ''), ENT_QUOTES); ?></td>
                    <td class="text-end text-success"><?php echo number_format((int) ($row['plays'] ?? 0)); ?></td>
                    <td class="text-end text-warning"><?php echo number_format((int) ($row['downloads'] ?? 0)); ?></td>
                    <td class="text-end text-muted"><?php echo number_format((int) ($row['media_count'] ?? 0)); ?></td>
                    <td class="text-end text-muted"><?php echo number_format((int) ($row['study_count'] ?? 0)); ?></td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-1">
                            <div class="progress flex-grow-1" style="height:6px;min-width:40px;max-width:80px">
                                <div class="progress-bar bg-success" style="width:<?php echo $pct; ?>%"></div>
                            </div>
                            <span class="small"><?php echo $pct; ?>%</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
