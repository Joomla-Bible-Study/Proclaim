<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder === 'study.ordering';
$columns   = 12;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('multiselect');
HTMLHelper::_('proclaim.framework');

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


	$workflow_state    = Factory::getApplication()->bootComponent('com_proclaim')->isFunctionalityUsed('core.state', 'com_proclaim.message');
	$workflow_featured = Factory::getApplication()->bootComponent('com_prcolaim')->isFunctionalityUsed('core.featured', 'com_proclaim.message');
endif;

if (strpos($listOrder, 'publish_up') !== false)
{
	$orderingColumn = 'publish_up';
}
elseif (strpos($listOrder, 'publish_down') !== false)
{
	$orderingColumn = 'publish_down';
}
elseif (strpos($listOrder, 'modified') !== false)
{
	$orderingColumn = 'modified';
}
else
{
	$orderingColumn = 'created';
}

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmmessage.saveOrderAjax&tmpl=component';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmessages'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span
								class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table itemList" id="messagesList">
						<caption class="visually-hidden">
							<?php echo Text::_('JBS_STY_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
						<tr>
							<th class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</th>
							<th scope="col" class="w-1 text-center d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', '', 'study.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="min-width:100px">
								<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_MESSAGETYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-3 d-none d-md-table-cell text-center">
								<?php echo Text::_('JBS_CPL_STATISTIC'); ?>
							</th>
							<?php if (Multilanguage::isEnabled()) : ?>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>
							<th scope="col" class="w-3 d-none d-lg-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'study.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="<?php echo $columns; ?>">
							</td>
						</tr>
						</tfoot>
						<tbody<?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php
						foreach ($this->items as $i => $item) :
							$item->max_ordering = 0;
							$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || is_null($item->checked_out);
							$canCreate = $user->authorise('core.create');
							$canEdit = $user->authorise('core.edit', 'com_proclaim.message.' . $item->id);
							$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.message.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_proclaim.message.' . $item->id);
							?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->series_id; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive" title="' . HtmlHelper::tooltipText('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
                                        <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                    </span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" name="order[]" size="5"
										       value="<?php echo $item->ordering; ?>"
										       class="width-20 text-area-order hidden"/>
									<?php endif; ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$options = [
										'task_prefix' => 'messages.',
										'disabled'    => $workflow_state || !$canChange,
										'id'          => 'state-' . $item->id
									];

									echo (new PublishedButton)->render((int) $item->published, $i, $options, $item->publish_up, $item->publish_down);
									?>
								</td>
								<td class="small d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('date', $this->escape($item->studydate, Text::_('DATE_FORMAT_LC4'))); ?>
								</td>
								<td class="nowrap has-context">
									<div class="pull-left">
										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo JRoute::_(
												'index.php?option=com_proclaim&task=cwmmessage.edit&id=' . (int) $item->id
											); ?>">
												<?php echo $this->escape($item->studytitle); ?>
											</a>
										<?php else : ?>
											<?php echo $this->escape($item->studytitle); ?>
										<?php endif; ?>
										<br/>
										<span class="small">
										<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									</span>
									</div>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo $this->escape($item->teachername); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo $this->escape($item->messageType); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo $this->escape($item->series_text); ?>
								</td>
								<td class="small d-none d-md-table-cell text-center">
									<button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top"
									        title="<?php echo $this->escape($item->hits); ?>"><?php echo Text::_('JBS_CMN_HITS'); ?></button>
									<br/>
									<button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top"
									        title="<?php echo $this->escape($item->totalplays); ?>"><?php echo Text::_('JBS_CMN_PLAYS'); ?></button>
									<br/>
									<button type="button" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top"
									        title="<?php echo $this->escape($item->totaldownloads); ?>"><?php echo Text::_('JBS_CMN_DOWNLOADS'); ?></button>
								</td>
								<?php if (Multilanguage::isEnabled()) : ?>
									<td class="small d-none d-md-table-cell">
										<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
									</td>
								<?php endif; ?>
								<td class="d-none d-lg-table-cell">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php // Load the batch processing form. ?>
					<?php if ($user->authorise('core.create', 'com_proclaim')
						&& $user->authorise('core.edit', 'com_proclaim')
						&& $user->authorise('core.edit.state', 'com_proclaim')
					) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title'  => Text::_('COM_CONTENT_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer'),
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php echo $this->pagination->getListFooter(); ?>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
</form>
