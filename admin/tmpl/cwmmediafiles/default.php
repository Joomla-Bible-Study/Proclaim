<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder === 'mediafile.ordering';
$columns   = 10;

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmmediafiles.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'mediafileList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmmediafiles'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php
                if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span
                                class="visually-hidden"><?php
                            echo Text::_('INFO'); ?></span>
                        <?php
                        echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php
                else : ?>
                    <table class="table itemList" id="mediafileList">
                        <caption class="visually-hidden">
                            <?php
                            echo Text::_('JBS_STY_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php
                                echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php
                                echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <th class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    '',
                                    'cwmmediafile.ordering',
                                    $listDirn,
                                    $listOrder,
                                    null,
                                    'asc',
                                    'JGRID_HEADING_ORDERING',
                                    'icon-menu-2'
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JSTATUS',
                                    'cwmmediafile.published',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo Text::_('JBS_MED_RESOURCE_NAME'); ?>
                            </th>
                            <th scope="col" style="min-width:100px">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_STUDY_TITLE',
                                    'study.studytitle',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo Text::_('JBS_MED_MEDIA_TYPE'); ?>
                            </th>
                            <th scope="col" class="w-1 d-none d-md-table-cell text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_MED_CREATE_DATE',
                                    'cwmmediafile.createdate',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_MED_ACCESS',
                                    'cwmmediafile.access',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo Text::_('JBS_MED_MEDIA_FILES_STATS'); ?>
                            </th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <td colspan="<?php
                            echo $columns; ?>">
                            </td>
                        </tr>
                        </tfoot>
                        <tbody>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $item->max_ordering = 0;
                            $ordering = ($listOrder === 'mediafile.ordering');
                            $canCreate = $user->authorise('core.create');
                            $canEdit = $user->authorise('core.edit', 'com_proclaim.mediafile.' . $item->id);
                            $canCheckin = $user->authorise(
                                    'core.manage',
                                    'com_checkin'
                                ) || $item->checked_out == $userId || $item->checked_out == 0;
                            $canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.mediafile.' . $item->id);
                            $canChange = $user->authorise('core.edit.state', 'com_proclaim.mediafile.' . $item->id);
                            $label = $this->escape($item->serverConfig->name->__toString()) . ' - ';
                            $label .= $this->escape(
                                $item->params[$item->serverConfig->config->media_resource->__toString()]
                            )
                                ? $item->serverConfig->config->media_resource->__toString() : 'mediacode';
                            ?>
                            <tr class="row<?php
                            echo $i % 2; ?>" data-draggable-group="<?php
                            echo $item->study_id ?>">
                                <td class="text-center">
                                    <?php
                                    echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $iconClass = '';
                                    if (!$canChange) {
                                        $iconClass = ' inactive';
                                    } elseif (!$saveOrder) {
                                        $iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText(
                                                'JORDERINGDISABLED'
                                            );
                                    }
                                    ?>
                                    <span class="sortable-handler<?php
                                    echo $iconClass ?>">
                                        <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                    </span>
                                    <?php
                                    if ($canChange && $saveOrder) : ?>
                                        <input type="text" name="order[]" size="5"
                                               value="<?php
                                               echo $item->ordering; ?>"
                                               class="width-20 text-area-order hidden"/>
                                    <?php
                                    endif; ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <div class="btn-group">
                                        <?php
                                        echo HTMLHelper::_(
                                            'jgrid.published',
                                            $item->published,
                                            $i,
                                            'cwmmediafiles.',
                                            $canChange,
                                            'cb',
                                            null,
                                            null
                                        ); ?>
                                    </div>
                                </td>
                                <td class="nowrap has-context">
                                    <div class="pull-left">
                                        <?php
                                        if ($item->checked_out) : ?>
                                            <?php
                                            echo HTMLHelper::_(
                                                'jgrid.checkedout',
                                                $i,
                                                $item->editor,
                                                $item->checked_out_time,
                                                'cwmmediafiles.',
                                                $canCheckin
                                            ); ?>
                                        <?php
                                        endif; ?>
                                        <?php
                                        if ($item->language == '*'): ?>
                                            <?php
                                            $language = Text::alt('JALL', 'language'); ?>
                                        <?php
                                        else: ?>
                                            <?php
                                            $language = $item->language_title ? $this->escape(
                                                $item->language_title
                                            ) : Text::_('JUNDEFINED'); ?>
                                        <?php
                                        endif; ?>
                                        <?php
                                        if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php
                                            echo Route::_(
                                                'index.php?option=com_proclaim&task=cwmmediafile.edit&id=' . (int)$item->id
                                            ); ?>">
                                                <span class="label pull-left"><?php
                                                    echo $this->escape($label); ?></span>
                                            </a>
                                        <?php
                                        else : ?>
                                            <span
                                                    title="<?php
                                                    echo Text::sprintf(
                                                        'JFIELD_ALIAS_LABEL',
                                                        $this->escape($item->alias)
                                                    ); ?>">
										<?php
                                        echo $label; ?>
				                    </span>
                                        <?php
                                        endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="pull-left">
                                        <a href="<?php
                                        echo Route::_(
                                            'index.php?option=com_proclaim&task=cwmmediafile.edit&id=' . (int)$item->id
                                        ); ?>">
                                            <?php
                                            echo $this->escape(
                                                $item->params[$item->serverConfig->config->media_resource->__toString()]
                                            ); ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $this->escape($item->studytitle); ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $this->escape($item->serverConfig->name->__toString()); ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo HTMLHelper::_('date', $item->createdate, Text::_('DATE_FORMAT_LC4')); ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $this->escape($item->access_level); ?>
                                </td>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php
                                    echo $this->escape($item->plays); ?>
                                    / <?php
                                    echo $this->escape($item->downloads); ?>
                                </td>
                            </tr>
                        <?php
                        endforeach; ?>
                        </tbody>
                    </table>

                    <?php
                    echo $this->pagination->getListFooter(); ?>

                    <?php
                    // Load the batch processing form. ?>
                    <?php
                    if ($user->authorise('core.create', 'com_proclaim')
                        && $user->authorise('core.edit', 'com_proclaim')
                        && $user->authorise('core.edit.state', 'com_proclaim')
                    ) : ?>
                        <?php
                        echo HTMLHelper::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            array(
                                'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
                                'footer' => $this->loadTemplate('batch_footer')
                            ),
                        //$this->loadTemplate('batch_body')
                        ); ?>
                    <?php
                    endif; ?>
                <?php
                endif; ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php
                echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
