<?php

/**
 * The System Health report.
 *
 * A layout rather than a view template: the report is rendered inside the
 * Administration screen's first tab, which is an edit form, so it has to be a
 * fragment something else owns rather than a page of its own.
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
 * @since   __DEPLOY_VERSION__
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
 * @since   __DEPLOY_VERSION__
 */
$taskLink = static fn(string $task, string $check): string => Route::_(
    'index.php?option=com_proclaim&task=cwmhealth.' . $task
    . '&check=' . urlencode($check)
    . '&' . Session::getFormToken() . '=1'
);
?>

<div class="card card-body mt-3">
    <h2 class="h4"><?php echo Text::_('JBS_HEALTH_TITLE'); ?></h2>
    <p class="text-body-secondary"><?php echo Text::_('JBS_HEALTH_DESC'); ?></p>

    <p class="mb-4">
        <?php foreach ([HealthStatus::Warning, HealthStatus::Notice, HealthStatus::Unknown, HealthStatus::Ok] as $status) :
            $count = (int) ($summary[$status->value] ?? 0);

            // A chip reading "0 Worth knowing" is noise -- it reports the
            // absence of something nobody asked about.
            if ($count === 0) :
                continue;
            endif;
            ?>
            <span class="badge bg-<?php echo $status->contextClass(); ?> me-2">
                <?php echo Text::sprintf('JBS_HEALTH_SUMMARY_COUNT', $count, Text::_($status->labelKey())); ?>
            </span>
        <?php endforeach; ?>
    </p>

    <?php foreach ($report as $groupValue => $rows) :
        $group = HealthGroup::from($groupValue);
        ?>
        <h3 class="h5 mt-4"><?php echo Text::_($group->labelKey()); ?></h3>

        <table class="table">
            <caption class="visually-hidden">
                <?php echo Text::sprintf('JBS_HEALTH_TABLE_CAPTION', Text::_($group->labelKey())); ?>
            </caption>
            <thead>
                <tr>
                    <th scope="col" class="w-25"><?php echo Text::_('JBS_HEALTH_COL_CHECK'); ?></th>
                    <th scope="col"><?php echo Text::_('JBS_HEALTH_COL_STATUS'); ?></th>
                    <th scope="col" class="w-25"><?php echo Text::_('JBS_HEALTH_COL_ACTION'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) :
                    $check  = $row['check'];
                    $result = $row['result'];
                    ?>
                    <tr>
                        <th scope="row" class="fw-normal">
                            <?php echo $e($check->getTitle()); ?>
                            <?php if ($row['quiet']) : ?>
                                <span class="badge bg-secondary ms-1">
                                    <?php echo Text::_('JBS_HEALTH_QUIET_BADGE'); ?>
                                </span>
                            <?php endif; ?>
                        </th>
                        <td>
                            <span class="badge bg-<?php echo $result->status->contextClass(); ?>">
                                <?php echo Text::_($result->status->labelKey()); ?>
                            </span>
                            <span class="ms-2"><?php echo $e($result->detail); ?></span>
                        </td>
                        <td>
                            <?php if ($result->actionLink !== null) : ?>
                                <a class="btn btn-sm btn-primary mb-1" href="<?php echo Route::_($result->actionLink); ?>">
                                    <?php echo $e((string) $result->actionLabel); ?>
                                </a>
                            <?php endif; ?>

                            <?php // An active check renders a button rather than a result: opening
                                  // this page must never be what spends a platform's API quota. ?>
                            <?php if (!$check->isPassive()) : ?>
                                <a class="btn btn-sm btn-secondary mb-1"
                                   href="<?php echo $taskLink('test', $check->getId()); ?>"
                                   onclick="return confirm('<?php echo $e(Text::_('JBS_HEALTH_TEST_NOW_CONFIRM')); ?>')">
                                    <?php echo Text::_('JBS_HEALTH_TEST_NOW'); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($row['quiet']) : ?>
                                <a class="btn btn-sm btn-outline-secondary mb-1"
                                   href="<?php echo $taskLink('restore', $check->getId()); ?>">
                                    <?php echo Text::_('JBS_HEALTH_RESTORE'); ?>
                                </a>
                            <?php elseif ($result->fingerprint !== '') : ?>
                                <a class="btn btn-sm btn-outline-secondary mb-1"
                                   href="<?php echo $taskLink('quieten', $check->getId()); ?>"
                                   title="<?php echo $e(Text::_('JBS_HEALTH_QUIETEN_DESC')); ?>">
                                    <?php echo Text::_('JBS_HEALTH_QUIETEN'); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
