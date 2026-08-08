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

/** @var CWM\Component\Proclaim\Administrator\View\Cwmepisodeaudit\HtmlView $this */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>

<div class="card">
    <div class="card-body">
        <p><?php echo Text::_('JBS_EPA_EPISODE_AUDIT_DESC'); ?></p>

        <?php if (empty($this->duplicates)) : ?>
            <div class="alert alert-success">
                <span class="icon-ok" aria-hidden="true"></span>
                <?php echo Text::_('JBS_EPA_NO_DUPLICATES'); ?>
            </div>
        <?php else : ?>
            <table class="table">
                <caption class="visually-hidden"><?php echo Text::_('JBS_EPA_EPISODE_AUDIT'); ?></caption>
                <thead>
                    <tr>
                        <th scope="col"><?php echo Text::_('JBS_CMN_SERIES'); ?></th>
                        <th scope="col"><?php echo Text::_('JBS_CMN_STUDYNUMBER'); ?></th>
                        <th scope="col"><?php echo Text::_('JBS_EPA_CONFLICTING_MESSAGES'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->duplicates as $row) :
                        $ids    = explode(',', $row->message_ids);
                        $titles = explode(' | ', $row->titles);
                    ?>
                        <tr>
                            <td>
                                <a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmserie.edit&id=' . (int) $row->series_id); ?>">
                                    <?php echo $this->escape($row->series_text); ?>
                                </a>
                            </td>
                            <td><?php echo $this->escape($row->studynumber); ?></td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($ids as $i => $id) : ?>
                                        <li>
                                            <a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmmessage.edit&id=' . (int) $id); ?>">
                                                <?php echo $this->escape($titles[$i] ?? ('#' . (int) $id)); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
