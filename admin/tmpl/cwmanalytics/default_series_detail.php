<?php
/**
 * Analytics drill-down: Messages inside one series.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

$baseUrl      = 'index.php?option=com_proclaim&view=cwmanalytics';
$preset       = Factory::getApplication()->getInput()->getString('preset', '30d');
$seriesTitle  = htmlspecialchars((string) ($this->seriesInfo->title ?? ''), ENT_QUOTES);
?>
<div class="card">
    <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
        <span>
            <i class="icon-list-2 me-1" aria-hidden="true"></i>
            <?php echo Text::sprintf('JBS_ANA_SERIES_MESSAGES_HEADER', $seriesTitle); ?>
        </span>
        <span class="text-muted small fw-normal"><?php echo Text::sprintf('JBS_ANA_DATE_RANGE_LABEL', htmlspecialchars($this->dateStart, ENT_QUOTES), htmlspecialchars($this->dateEnd, ENT_QUOTES)); ?></span>
    </div>
    <?php if (empty($this->seriesMessages)) : ?>
        <div class="card-body"><p class="text-muted mb-0"><?php echo Text::_('JBS_ANA_NO_DATA'); ?></p></div>
    <?php else : ?>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th><?php echo Text::_('JBS_ANA_MESSAGE_TITLE'); ?></th>
                    <th><?php echo Text::_('JBS_ANA_DATE'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_ALL_TIME_VIEWS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_VIEWS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_PLAYS'); ?></th>
                    <th class="text-end"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($this->seriesMessages as $row) : ?>
                <?php $drillUrl = Route::_($baseUrl . '&drilldown=message&id=' . (int) $row['study_id'] . '&preset=' . $preset . '&location_id=' . (int) $this->locationId); ?>
                <tr>
                    <td>
                        <a href="<?php echo $drillUrl; ?>" class="fw-semibold text-decoration-none">
                            <?php echo htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES); ?>
                        </a>
                    </td>
                    <td class="text-muted small"><?php echo htmlspecialchars(substr((string) ($row['study_date'] ?? ''), 0, 10), ENT_QUOTES); ?></td>
                    <td class="text-end text-muted small"><?php echo number_format((int) ($row['all_time_views'] ?? 0)); ?></td>
                    <td class="text-end"><?php echo number_format((int) ($row['views'] ?? 0)); ?></td>
                    <td class="text-end text-success"><?php echo number_format((int) ($row['plays'] ?? 0)); ?></td>
                    <td class="text-end text-warning"><?php echo number_format((int) ($row['downloads'] ?? 0)); ?></td>
                    <td class="text-end">
                        <a href="<?php echo $drillUrl; ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <?php echo Text::_('JBS_ANA_DRILL_VIEW'); ?> &rsaquo;
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
