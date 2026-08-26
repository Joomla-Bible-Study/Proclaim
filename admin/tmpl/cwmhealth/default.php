<?php

/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmhealth\HtmlView $this */

use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

?>

<div class="card">
    <div class="card-body">
        <p><?php echo Text::_('JBS_HEALTH_DESC'); ?></p>

        <p class="mb-4">
            <?php foreach ([HealthStatus::Warning, HealthStatus::Notice, HealthStatus::Unknown, HealthStatus::Ok] as $status) : ?>
                <span class="badge bg-<?php echo $status->contextClass(); ?> me-2">
                    <?php echo Text::sprintf(
                        'JBS_HEALTH_SUMMARY_COUNT',
                        (int) ($this->summary[$status->value] ?? 0),
                        Text::_($status->labelKey())
                    ); ?>
                </span>
            <?php endforeach; ?>
        </p>

        <?php foreach ($this->report as $groupValue => $rows) :
            $group = HealthGroup::from($groupValue);
            ?>
            <h2 class="h4 mt-4"><?php echo Text::_($group->labelKey()); ?></h2>

            <table class="table">
                <caption class="visually-hidden"><?php echo Text::_($group->labelKey()); ?></caption>
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
                                <?php echo $this->escape($check->getTitle()); ?>
                                <?php if ($row['quiet']) : ?>
                                    <span class="badge bg-secondary ms-1"><?php echo Text::_('JBS_HEALTH_QUIET_BADGE'); ?></span>
                                <?php endif; ?>
                            </th>
                            <td>
                                <span class="badge bg-<?php echo $result->status->contextClass(); ?>">
                                    <?php echo Text::_($result->status->labelKey()); ?>
                                </span>
                                <span class="ms-2"><?php echo $this->escape($result->detail); ?></span>
                            </td>
                            <td>
                                <?php if ($result->actionLink !== null) : ?>
                                    <a class="btn btn-sm btn-primary mb-1"
                                       href="<?php echo Route::_($result->actionLink); ?>">
                                        <?php echo $this->escape((string) $result->actionLabel); ?>
                                    </a>
                                <?php endif; ?>

                                <?php // An active check renders a button rather than a result: opening
                                      // this page must never be what spends a platform's API quota. ?>
                                <?php if (!$check->isPassive()) : ?>
                                    <?php // Confirmed because the click is what costs something --
                                          // on YouTube it spends a unit of the day's quota. ?>
                                    <a class="btn btn-sm btn-secondary mb-1"
                                       href="<?php echo Route::_(
                                           'index.php?option=com_proclaim&task=cwmhealth.test&check='
                                           . urlencode($check->getId())
                                           . '&' . Session::getFormToken() . '=1'
                                       ); ?>"
                                       onclick="return confirm('<?php echo $this->escape(Text::_('JBS_HEALTH_TEST_NOW_CONFIRM')); ?>')">
                                        <?php echo Text::_('JBS_HEALTH_TEST_NOW'); ?>
                                    </a>
                                <?php endif; ?>

                                <?php if ($row['quiet']) : ?>
                                    <a class="btn btn-sm btn-outline-secondary mb-1"
                                       href="<?php echo Route::_(
                                           'index.php?option=com_proclaim&task=cwmhealth.restore&check='
                                           . urlencode($check->getId())
                                           . '&' . Session::getFormToken() . '=1'
                                       ); ?>">
                                        <?php echo Text::_('JBS_HEALTH_RESTORE'); ?>
                                    </a>
                                <?php elseif ($result->fingerprint !== '') : ?>
                                    <a class="btn btn-sm btn-outline-secondary mb-1"
                                       href="<?php echo Route::_(
                                           'index.php?option=com_proclaim&task=cwmhealth.quieten&check='
                                           . urlencode($check->getId())
                                           . '&' . Session::getFormToken() . '=1'
                                       ); ?>"
                                       title="<?php echo Text::_('JBS_HEALTH_QUIETEN_DESC'); ?>">
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
</div>
