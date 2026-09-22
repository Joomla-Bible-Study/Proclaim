<?php

/**
 * The System Health report.
 *
 * A layout, not a view template: it renders inside the Administration
 * screen's first tab, which is an edit form.
 *
 * A flush list group in a `cwmadmin-panel`, matching the other tools on that
 * tab. A list rather than a table because the data is not tabular.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var array $displayData */
$report  = $displayData['report'] ?? [];
$summary = $displayData['summary'] ?? [];

/**
 * Escape for HTML output.
 *
 * @param   string  $value  Raw text.
 *
 * @return  string
 *
 * @since   10.6.0
 */
$e = static fn(string $value): string => htmlspecialchars($value, ENT_COMPAT, 'UTF-8');

/**
 * A tokenised link back to one of the report's own tasks.
 *
 * @param   string  $task   Controller task name.
 * @param   string  $check  The check id being acted on.
 *
 * @return  string
 *
 * @since   10.6.0
 */
$taskLink = static fn(string $task, string $check): string => Route::_(
    'index.php?option=com_proclaim&task=cwmhealth.' . $task
    . '&check=' . urlencode($check)
    . '&' . Session::getFormToken() . '=1'
);
?>

<div class="cwmadmin-panel mb-4">
    <h3 class="tab-description"><?php echo Text::_('JBS_HEALTH_TITLE'); ?></h3>
    <p class="text-body-secondary"><?php echo Text::_('JBS_HEALTH_DESC'); ?></p>

    <?php // The summary chips double as the filter. They already carry the
          // status names and counts, so a separate filter bar would say the
          // same words twice. Pressing one narrows the list to that status;
          // pressing none shows everything, which is how the panel opens. ?>
    <div class="mb-4 d-flex flex-wrap gap-2 align-items-center js-health-filters"
         role="group"
         aria-label="<?php echo $e(Text::_('JBS_HEALTH_FILTER_GROUP_LABEL')); ?>">
        <?php foreach ([HealthStatus::Warning, HealthStatus::Notice, HealthStatus::Unknown, HealthStatus::Ok] as $status) :
            $count = (int) ($summary[$status->value] ?? 0);

            // A chip reading "0 Worth knowing" is noise.
            if ($count === 0) :
                continue;
            endif;
            ?>
            <button type="button"
                    class="badge bg-<?php echo $status->contextClass(); ?> border-0 js-health-filter"
                    data-status="<?php echo $e($status->value); ?>"
                    aria-pressed="false">
                <?php echo Text::sprintf('JBS_HEALTH_SUMMARY_COUNT', $count, Text::_($status->labelKey())); ?>
            </button>
        <?php endforeach; ?>

        <button type="button" class="btn btn-sm btn-link p-0 ms-1 js-health-filter-reset" hidden>
            <?php echo Text::_('JBS_HEALTH_FILTER_SHOW_ALL'); ?>
        </button>
    </div>

    <?php // Announced on change so a filter is not a silent visual event. ?>
    <p class="visually-hidden js-health-filter-status"
       role="status"
       aria-live="polite"
       data-template="<?php echo $e(Text::_('JBS_HEALTH_FILTER_ANNOUNCE')); ?>"></p>

    <?php foreach ($report as $groupValue => $rows) :
        $group = HealthGroup::from($groupValue);
        ?>
        <div class="js-health-group">
        <h4 class="h6 text-uppercase fw-bold health-section">
            <?php echo Text::_($group->labelKey()); ?>
        </h4>

        <ul class="list-group list-group-flush mb-0">
            <?php foreach ($rows as $row) :
                $check  = $row['check'];
                $result = $row['result'];
                ?>
                <li class="list-group-item bg-transparent px-0 py-2 js-health-row"
                    data-status="<?php echo $e($result->status->value); ?>">
                    <div class="d-flex align-items-start gap-2 flex-wrap">
                        <?php // ⚠️ Inner flex must not wrap, so only the buttons drop to a
                              // second line and every title starts at the same edge.
                              // `min-width: 0` lets the text shrink below its longest
                              // word; without it the row wraps anyway. ?>
                        <div class="d-flex align-items-start gap-2 flex-grow-1" style="min-width: 0;">
                            <?php // Width on the cell, not the badge, so titles align while
                                  // each badge sizes to its own word. ?>
                            <span class="flex-shrink-0" style="min-width: 8.5em;">
                                <span class="badge bg-<?php echo $result->status->contextClass(); ?>">
                                    <?php echo Text::_($result->status->labelKey()); ?>
                                </span>
                            </span>

                            <div class="flex-grow-1" style="min-width: 0;">
                            <div class="fw-semibold">
                                <?php echo $e($check->getTitle()); ?>
                                <?php if ($row['quiet']) : ?>
                                    <span class="badge bg-secondary ms-1">
                                        <?php echo Text::_('JBS_HEALTH_QUIET_BADGE'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                                <div class="small text-body-secondary"><?php echo $e($result->detail); ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-1 flex-shrink-0 ms-auto">
                            <?php if ($result->actionLink !== null) : ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo Route::_($result->actionLink); ?>">
                                    <?php echo $e((string) $result->actionLabel); ?>
                                </a>
                            <?php endif; ?>

                            <?php // ⚠️ An active check offers a button, never a result: opening
                                  // this screen must not spend a platform's API quota. ?>
                            <?php if (!$check->isPassive()) : ?>
                                <a class="btn btn-sm btn-secondary"
                                   href="<?php echo $taskLink('test', $check->getId()); ?>"
                                   onclick="return confirm('<?php echo $e(Text::_('JBS_HEALTH_TEST_NOW_CONFIRM')); ?>')">
                                    <?php echo Text::_('JBS_HEALTH_TEST_NOW'); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($row['quiet']) : ?>
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?php echo $taskLink('restore', $check->getId()); ?>">
                                    <?php echo Text::_('JBS_HEALTH_RESTORE'); ?>
                                </a>
                            <?php elseif ($result->fingerprint !== '') : ?>
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?php echo $taskLink('quieten', $check->getId()); ?>"
                                   title="<?php echo $e(Text::_('JBS_HEALTH_QUIETEN_DESC')); ?>">
                                    <?php echo Text::_('JBS_HEALTH_QUIETEN'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        </div>
    <?php endforeach; ?>

    <p class="text-body-secondary mt-3 mb-0 js-health-filter-empty" hidden>
        <?php echo Text::_('JBS_HEALTH_FILTER_NONE_MATCH'); ?>
    </p>
</div>
