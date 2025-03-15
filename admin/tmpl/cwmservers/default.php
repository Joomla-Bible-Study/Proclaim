<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Component\ComponentHelper;
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
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$columns   = 4;

$workflow_enabled  = ComponentHelper::getParams('com_proclaim')->get('workflow_enabled');
$workflow_state    = false;
$workflow_featured = false;
if ($workflow_enabled) :

// @todo move the script to a file
    $js = <<<JS
	(function() {
		document.addEventListener('DOMContentLoaded', function() {
		  var elements = [].slice.call(document.querySelectorAll('.message-status'));
	
		  elements.forEach(function (element) {
			element.addEventListener('click', function(event) {
				event.stopPropagation();
			});
		  });
		});
	})();
	JS;

    $wa->getRegistry()->addExtensionRegistryFile('com_workflow');
    $wa->useScript('com_workflow.admin-items-workflow-buttons')
        ->addInlineScript($js, [], ['type' => 'module']);


    $workflow_state    = Factory::getApplication()->bootComponent('com_proclaim')->isFunctionalityUsed(
        'core.state',
        'com_proclaim.server'
    );
    $workflow_featured = Factory::getApplication()->bootComponent('com_prcolaim')->isFunctionalityUsed(
        'core.featured',
        'com_proclaim.server'
    );
endif;

$sortFields = $this->getSortFields();
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmservers'); ?>" method="post" name="adminForm"
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
                    <table class="table itemList" id="messagesList">
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
                            <th class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JPUBLISHED',
                                    'cwmservers.published',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" style="min-width:100px">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JBS_CMN_SERVERS',
                                    'cwmservers.server_name',
                                    $listDirn,
                                    $listOrder
                                ); ?>
                            </th>
                            <th scope="col" class="w-3 d-none d-lg-table-cell">
                                <?php
                                echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'JGRID_HEADING_ID',
                                    'cwmservers.id',
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
                        <tbody>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $item->max_ordering = 0;
                            $canCreate = $user->authorise('core.create');
                            $canEdit = $user->authorise('core.edit', 'com_proclaim.cwmserver.' . $item->id);
                            $canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.cwmserver.' . $item->id);
                            $canChange = $user->authorise('core.edit.state', 'com_proclaim.cwmserver.' . $item->id);
                            ?>
                            <tr class="row<?php
                            echo $i % 2; ?>">
                                <td class="center d-none d-md-table-cell">
                                    <?php
                                    echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'cwmservers.',
                                        'disabled'    => $workflow_state || !$canChange,
                                        'id'          => 'state-' . $item->id
                                    ];

                                    echo (new PublishedButton)->render((int)$item->published, $i, $options, '', '');
                                    ?>
                                </td>
                                <td class="nowrap has-context">
                                    <div class="float-left">

                                        <?php
                                        if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php
                                            echo Route::_(
                                                'index.php?option=com_proclaim&task=cwmserver.edit&id=' . (int)$item->id
                                            ); ?>"
                                               title="<?php
                                               echo Text::_('JACTION_EDIT'); ?>">
                                                <?php
                                                echo $this->escape($item->server_name); ?></a>
                                        <?php
                                        else : ?>
                                            <span
                                                    title="<?php
                                                    echo Text::sprintf(
                                                        'JFIELD_ALIAS_LABEL',
                                                        $this->escape($item->server_name)
                                                    ); ?>"><?php
                                                echo $this->escape($item->server_name); ?></span>
                                        <?php
                                        endif; ?>
                                    </div>
                                </td>
                                <td class="center d-none d-md-table-cell">
                                    <?php
                                    echo (int)$item->id; ?>
                                </td>
                            </tr>
                        <?php
                        endforeach; ?>
                        </tbody>
                    </table>
                <?php
                endif; ?>
                <?php
                echo $this->pagination->getListFooter(); ?>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php
                echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
