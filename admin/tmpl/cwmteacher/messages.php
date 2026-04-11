<?php

/**
 * Teacher edit — Messages tab partial (lazy-loaded via AJAX).
 *
 * Variables available:
 *   $id       int   Teacher id
 *   $messages array Message rows (from CwmteacherModel::getMessages())
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var int $id */
/** @var array $messages */
/** @var int $totalCount */

$messages   = $messages ?? [];
$id         = (int) ($id ?? 0);
$shownCount = \count($messages);
$totalCount = (int) ($totalCount ?? $shownCount);
$escape     = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<?php if (!empty($messages)) : ?>
<table class="table">
    <thead>
        <tr>
            <th class="w-5 text-center"><?php echo Text::_('JSTATUS'); ?></th>
            <th><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
            <th class="w-15"><?php echo Text::_('JBS_CMN_DATE'); ?></th>
            <th class="w-15"><?php echo Text::_('JBS_CMN_SERIES'); ?></th>
            <th class="w-15"><?php echo Text::_('JBS_CMN_LOCATION'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $i => $msg) : ?>
        <tr>
            <td class="text-center">
                <?php echo HTMLHelper::_('jgrid.published', $msg->published, $i, '', false); ?>
            </td>
            <td>
                <a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmmessage.edit&id=' . (int) $msg->id); ?>">
                    <?php echo $escape($msg->studytitle); ?>
                </a>
            </td>
            <td>
                <?php echo HTMLHelper::_('date', $msg->studydate, Text::_('DATE_FORMAT_LC4')); ?>
            </td>
            <td>
                <?php echo $escape($msg->series_text ?? ''); ?>
            </td>
            <td>
                <?php echo $escape($msg->location_text ?? ''); ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="d-flex align-items-center justify-content-between mt-2">
    <small class="text-muted">
        <?php echo Text::sprintf('JBS_TCH_MESSAGES_SHOWING', $shownCount, $totalCount); ?>
    </small>
    <a class="btn btn-secondary btn-sm"
       href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmessages&filter[teacher]=' . $id); ?>">
        <?php echo Text::_('JBS_TCH_VIEW_ALL_MESSAGES'); ?>
    </a>
</div>
<?php else : ?>
<div class="alert alert-info">
    <?php echo Text::_('JBS_TCH_NO_MESSAGES'); ?>
</div>
<?php endif; ?>
