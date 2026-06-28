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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmplaylists\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering', 'playlist.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction', 'asc'));
$saveOrder = $listOrder === 'playlist.ordering';
$columns   = 9;

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmplaylists.saveOrderAjax&tmpl=component';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmplaylists'); ?>" method="post" name="adminForm"
      id="adminForm">
    <div id="j-main-container">
        <?php
        echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php
        if (empty($this->items)) : ?>
            <?php echo LayoutHelper::render('html.empty_state'); ?>
        <?php else : ?>
            <table class="table table-striped adminlist" id="playlistsList">
                <caption class="visually-hidden">
                    <?php echo Text::_('JBS_CMN_PLAYLISTS'); ?>
                </caption>
                <thead>
                <tr>
                    <td class="w-1 text-center">
                        <?php echo HTMLHelper::_('searchtools.sort', '', 'playlist.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                    </td>
                    <th class="w-1 text-center">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th class="w-1 text-center nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'playlist.published', $listDirn, $listOrder); ?>
                    </th>
                    <th>
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'playlist.title', $listDirn, $listOrder); ?>
                    </th>
                    <th class="w-15 nowrap d-none d-md-table-cell">
                        <?php echo Text::_('JBS_PLAYLIST_SERVER'); ?>
                    </th>
                    <th class="w-10 text-center nowrap d-none d-md-table-cell">
                        <?php echo Text::_('JBS_PLAYLIST_SYNC'); ?>
                    </th>
                    <th class="w-15 nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JBS_PLAYLIST_LAST_SYNC', 'playlist.last_sync', $listDirn, $listOrder); ?>
                    </th>
                    <th class="w-10 nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                    </th>
                    <th class="w-1 text-center nowrap d-none d-md-table-cell">
                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'playlist.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
                </thead>
                <tbody <?php if ($saveOrder) : ?>class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
                <?php
                foreach ($this->items as $i => $item) :
                    $canCheckin = $user->authorise('core.manage', 'com_checkin')
                        || $item->checked_out == $userId || \is_null($item->checked_out);
                    $canEdit    = $user->authorise('core.edit', 'com_proclaim.playlist.' . $item->id);
                    $canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.playlist.' . $item->id);
                    $canChange  = $user->authorise('core.edit.state', 'com_proclaim.playlist.' . $item->id);
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0">
                        <td class="text-center">
                            <?php
                            $iconClass = '';

                            if (!$saveOrder) {
                                $iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
                            } elseif (!$canChange) {
                                $iconClass = ' inactive';
                            }
                            ?>
                            <span class="sortable-handler<?php echo $iconClass; ?>">
                                <span class="icon-ellipsis-v" aria-hidden="true"></span>
                            </span>
                            <?php if ($saveOrder && $canChange) : ?>
                                <input type="text" class="hidden" name="order[]" size="5"
                                       value="<?php echo (int) $item->ordering; ?>">
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php
                            $options = [
                                'task_prefix' => 'cwmplaylists.',
                                'disabled'    => !$canChange,
                                'id'          => 'state-' . $item->id,
                            ];
                            echo (new PublishedButton())->render((int) $item->published, $i, $options);
                            ?>
                        </td>
                        <td class="nowrap has-context">
                            <?php if ($item->checked_out) : ?>
                                <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'cwmplaylists.', $canCheckin); ?>
                            <?php endif; ?>
                            <?php if ($canEdit || $canEditOwn) : ?>
                                <a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmplaylist.edit&id=' . (int) $item->id); ?>"
                                   title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                    <?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                                <span><?php echo $this->escape($item->title); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item->remote_playlist_id)) : ?>
                                <div class="small text-muted"><?php echo $this->escape($item->remote_playlist_id); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->server_name ?? ''); ?>
                        </td>
                        <td class="text-center small d-none d-md-table-cell">
                            <?php if ((int) $item->sync_enabled === 1) : ?>
                                <span class="badge bg-success"><?php echo Text::_('JENABLED'); ?></span>
                            <?php else : ?>
                                <span class="badge bg-secondary"><?php echo Text::_('JDISABLED'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php
                            echo $item->last_sync && $item->last_sync !== Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class)->getNullDate()
                                ? HTMLHelper::_('date', $item->last_sync, Text::_('DATE_FORMAT_LC4'))
                                : '<span class="text-muted">' . Text::_('JBS_PLAYLIST_NEVER_SYNCED') . '</span>'; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <td class="text-center d-none d-md-table-cell">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php echo $this->pagination->getListFooter(); ?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
