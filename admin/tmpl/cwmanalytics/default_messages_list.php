<?php

/**
 * Analytics Messages List Sub-Template
 *
 * Shows all published messages with engagement stats, regardless of series.
 * Searchable by title with pagination. Linked from the "Messages" nav tab.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

$baseUrl   = 'index.php?option=com_proclaim&view=cwmanalytics';
$navParams = '&drilldown=messages&preset=' . htmlspecialchars($this->preset, ENT_QUOTES)
    . '&location_id=' . (int) $this->locationId;

if ($this->preset === 'custom') {
    $navParams .= '&date_start=' . urlencode($this->dateStart)
        . '&date_end=' . urlencode($this->dateEnd);
}

if ($this->messagesSearch !== '') {
    $navParamsWithSearch = $navParams . '&search=' . urlencode($this->messagesSearch);
} else {
    $navParamsWithSearch = $navParams;
}

$totalPages = max(1, (int) ceil($this->messagesTotal / $this->messagesPerPage));
$page       = $this->messagesPage;
?>

<!-- Search bar -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" class="d-flex align-items-center gap-2">
            <input type="hidden" name="option" value="com_proclaim">
            <input type="hidden" name="view" value="cwmanalytics">
            <input type="hidden" name="drilldown" value="messages">
            <input type="hidden" name="preset" value="<?php echo htmlspecialchars($this->preset, ENT_QUOTES); ?>">
            <input type="hidden" name="location_id" value="<?php echo (int) $this->locationId; ?>">
            <?php if ($this->preset === 'custom') : ?>
                <input type="hidden" name="date_start" value="<?php echo htmlspecialchars($this->dateStart, ENT_QUOTES); ?>">
                <input type="hidden" name="date_end" value="<?php echo htmlspecialchars($this->dateEnd, ENT_QUOTES); ?>">
            <?php endif; ?>
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="<?php echo Text::_('JBS_ANA_MESSAGES_SEARCH_PLACEHOLDER'); ?>"
                       value="<?php echo htmlspecialchars($this->messagesSearch, ENT_QUOTES); ?>">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="icon-search" aria-hidden="true"></i>
                </button>
            </div>
            <?php if ($this->messagesSearch !== '') : ?>
                <a href="<?php echo Route::_($baseUrl . $navParams); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="icon-times me-1" aria-hidden="true"></i><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </a>
            <?php endif; ?>
            <span class="text-muted small ms-auto">
                <?php echo number_format($this->messagesTotal); ?> <?php echo Text::_('JBS_ANA_MESSAGES'); ?>
            </span>
        </form>
    </div>
</div>

<!-- Messages table -->
<?php if (empty($this->messagesList)) : ?>
    <div class="alert alert-info">
        <i class="icon-info-circle me-1" aria-hidden="true"></i>
        <?php echo Text::_('JBS_ANA_NO_DATA'); ?>
    </div>
<?php else : ?>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th><?php echo Text::_('JBS_ANA_MESSAGE_TITLE'); ?></th>
                        <th class="d-none d-md-table-cell"><?php echo Text::_('JBS_ANA_DATE'); ?></th>
                        <th class="d-none d-md-table-cell"><?php echo Text::_('JBS_ANA_SERIES_TITLE'); ?></th>
                        <th class="text-end"><?php echo Text::_('JBS_ANA_VIEWS'); ?></th>
                        <th class="text-end"><?php echo Text::_('JBS_ANA_PLAYS'); ?></th>
                        <th class="text-end"><?php echo Text::_('JBS_ANA_DOWNLOADS'); ?></th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->messagesList as $row) : ?>
                    <?php
                    $drillUrl = Route::_(
                        $baseUrl . '&drilldown=message&id=' . (int) $row['study_id']
                        . '&preset=' . htmlspecialchars($this->preset, ENT_QUOTES)
                        . '&location_id=' . (int) $this->locationId
                    );
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $drillUrl; ?>">
                                <?php echo htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES); ?>
                            </a>
                        </td>
                        <td class="small text-muted d-none d-md-table-cell">
                            <?php echo htmlspecialchars((string) ($row['study_date'] ?? ''), ENT_QUOTES); ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo !empty($row['series_title'])
                                ? htmlspecialchars($row['series_title'], ENT_QUOTES)
                                : '<span class="text-muted">' . Text::_('JBS_ANA_MESSAGES_NO_SERIES') . '</span>'; ?>
                        </td>
                        <td class="text-end text-primary"><?php echo number_format((int) ($row['views'] ?? 0)); ?></td>
                        <td class="text-end text-success"><?php echo number_format((int) ($row['plays'] ?? 0)); ?></td>
                        <td class="text-end text-warning"><?php echo number_format((int) ($row['downloads'] ?? 0)); ?></td>
                        <td class="text-end">
                            <a href="<?php echo $drillUrl; ?>" class="btn btn-sm btn-primary py-0 px-2">
                                <?php echo Text::_('JBS_ANA_DRILL_VIEW'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1) : ?>
    <!-- Pagination -->
    <nav aria-label="<?php echo Text::_('JBS_ANA_MESSAGES'); ?>" class="mt-3">
        <ul class="pagination justify-content-center">
            <!-- Previous -->
            <li class="page-item<?php echo $page <= 1 ? ' disabled' : ''; ?>">
                <?php if ($page > 1) : ?>
                    <a class="page-link" href="<?php echo Route::_($baseUrl . $navParamsWithSearch . '&page=' . ($page - 1)); ?>">
                        &laquo;
                    </a>
                <?php else : ?>
                    <span class="page-link">&laquo;</span>
                <?php endif; ?>
            </li>

            <?php
            // Show at most 7 page numbers centred on current page
            $start = max(1, $page - 3);
            $end   = min($totalPages, $page + 3);

            if ($start > 1) : ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo Route::_($baseUrl . $navParamsWithSearch . '&page=1'); ?>">1</a>
                </li>
                <?php if ($start > 2) : ?>
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($p = $start; $p <= $end; $p++) : ?>
                <li class="page-item<?php echo $p === $page ? ' active' : ''; ?>">
                    <?php if ($p === $page) : ?>
                        <span class="page-link"><?php echo $p; ?></span>
                    <?php else : ?>
                        <a class="page-link" href="<?php echo Route::_($baseUrl . $navParamsWithSearch . '&page=' . $p); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                </li>
            <?php endfor; ?>

            <?php if ($end < $totalPages) : ?>
                <?php if ($end < $totalPages - 1) : ?>
                    <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo Route::_($baseUrl . $navParamsWithSearch . '&page=' . $totalPages); ?>"><?php echo $totalPages; ?></a>
                </li>
            <?php endif; ?>

            <!-- Next -->
            <li class="page-item<?php echo $page >= $totalPages ? ' disabled' : ''; ?>">
                <?php if ($page < $totalPages) : ?>
                    <a class="page-link" href="<?php echo Route::_($baseUrl . $navParamsWithSearch . '&page=' . ($page + 1)); ?>">
                        &raquo;
                    </a>
                <?php else : ?>
                    <span class="page-link">&raquo;</span>
                <?php endif; ?>
            </li>
        </ul>
        <p class="text-center text-muted small mb-0">
            <?php
            $from = (($page - 1) * $this->messagesPerPage) + 1;
            $to   = min($page * $this->messagesPerPage, $this->messagesTotal);
            echo Text::sprintf('JLIB_HTML_RESULTS_OF', $from, $to, $this->messagesTotal);
            ?>
        </p>
    </nav>
    <?php endif; ?>
<?php endif; ?>
