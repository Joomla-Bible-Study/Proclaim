<?php

/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$app        = Factory::getApplication();
$user       = $user = Factory::getApplication()->getSession()->get('user');
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder  = $listOrder == 'ordering';
$sortFields = $this->getSortFields();
$columns    = 9;

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmcomments.saveOrderAjax&tmpl=component';
    HtmlHelper::_('sortablelist.sortable', 'comments', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        '
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != "' . $listOrder . '")
		{
			dirn = "asc";
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, "");
	};
'
    );
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmcomments'); ?>" method="post" name="adminForm"
      id="adminForm">
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
                    <table class="table table-striped itemlist" id="comments">
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
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_PUBLISHED',
                                    'comment.published',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" style="min-width:100px">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_TITLE',
                                    'study.studytitle',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_ACCESS',
                                    'comment.access',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMT_FULL_NAME',
                                    'comment.full_name',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo Text::_('JBS_CMT_TEXT'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMT_CREATE_DATE',
                                    'comment.studydate',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <?php
                            if (Multilanguage::isEnabled()) : ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php
                                    echo HTMLHelper::_(
                                        'searchtools.sort',
                                        'JGRID_HEADING_LANGUAGE',
                                        'language',
                                        $listDirn,
                                        $listOrder
                                    ); ?>
                                </th>
                                <?php
                            endif; ?>
                            <th scope="col" class="w-3 d-none d-lg-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_ID',
                                    'comment.id',
                                    $listDirn,
                                    $listOrder
                                ); ?>
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
                        <tbody<?php
                        if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?php
                        echo $saveOrderingUrl; ?>" data-direction="<?php
                        echo strtolower($listDirn); ?>" data-nested="true"<?php
                        endif; ?>>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $link = Route::_('index.php?option=com_proclaim&task=cwmcomment.edit&id=' . (int)$item->id);
                            $canCreate = $user->authorise('core.create');
                            $canEdit = $user->authorise('core.edit', 'com_proclaim.comment.' . $item->id);
                            $canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.comment.' . $item->id);
                            $canChange = $user->authorise('core.edit.state', 'com_proclaim.comment.' . $item->id);
                            ?>
                            <tr class="row<?php
                            echo $i % 2; ?>" data-draggable-group="<?php
                            echo '1' ?>">
                                <td class="text-center">
                                    <?php
                                    echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'cwmcomments.',
                                        'disabled' => !$canChange,
                                        'id' => 'state-' . $item->id
                                    ];
                                    echo (new PublishedButton())->render((int) $item->published, $i, $options);
                                    ?>
                                </td>
                                <td class="nowrap has-context" style="width:10%;">
                                    <div class="pull-left">
                                        <?php
                                        if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php
                                            echo $link; ?>"><?php
                                                echo $this->escape($item->studytitle) . ' - '
                                                    . Text::_($item->bookname) . ' ' . $item->chapter_begin; ?></a>
                                            <?php
                                        else : ?>
                                            <?php
                                            echo $this->escape($item->studytitle) . ' - ' . Text::_(
                                                $item->bookname
                                            ) . ' ' . $item->chapter_begin; ?>
                                            <?php
                                        endif; ?>
                                    </div>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $this->escape($item->access_level); ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $item->full_name; ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo substr($item->comment_text, 0, 50); ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    echo $item->comment_date; ?>
                                </td>
                                <?php
                                if (Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php
                                        echo LayoutHelper::render('joomla.content.language', $item); ?>
                                    </td>
                                    <?php
                                endif; ?>
                                <td class="d-none d-lg-table-cell">
                                    <?php
                                    echo (int)$item->id; ?>
                                </td>
                            </tr>
                            <?php
                        endforeach; ?>
                        </tbody>
                    </table>

                    <?php
                    // load the pagination. ?>
                    <?php
                    echo $this->pagination->getListFooter(); ?>

                    <?php
                    // Load the batch processing form. ?>
                    <?php
                    if (
                        $user->authorise('core.create', 'com_proclaim')
                        && $user->authorise('core.edit', 'com_proclaim')
                        && $user->authorise('core.edit.state', 'com_proclaim')
                    ) : ?>
                        <?php
                        echo HTMLHelper::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            array(
                                'title'  => Text::_('COM_CONTENT_BATCH_OPTIONS'),
                                'footer' => $this->loadTemplate('batch_footer'),
                            ),
                            $this->loadTemplate('batch_body')
                        ); ?>
                        <?php
                    endif; ?>
                    <?php
                endif; ?>
                <?php
                // Load the batch processing form. ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php
                echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
