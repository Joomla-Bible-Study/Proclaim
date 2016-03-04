<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.framework');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering', 'study.studydate'));
$listDirn  = $this->escape($this->state->get('list.direction', 'desc'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'study.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_biblestudy&task=message.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
JFactory::getDocument()->addScriptDeclaration('
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
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messages'); ?>" method="post" name="adminForm"
      id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		<hr/>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<?php
			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped" id="articleList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'study.ordering', $listDirn, $listOrder, null, 'desc', 'JGRID_HEADING_ORDERING'); ?>
						</th>
						<th width="1%" class="hidden-phone">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>

						<th width="1%" style="min-width:55px;" class="nowrap center">
							<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_SCRIPTURE', 'book.bookname', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_MESSAGETYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap center hidden-phone hidden-tablet">
							<?php echo JText::_('JBS_CPL_STATISTIC'); ?>
						</th>
						</th>
						<th width="5%" class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'message.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$item->max_ordering = 0;
						$ordering           = ($listOrder == 'study.ordering');
						$canCreate          = $user->authorise('core.create');
						$canEdit            = $user->authorise('core.edit', 'com_biblestudy.message.' . $item->id);
						$canEditOwn         = $user->authorise('core.edit.own', 'com_biblestudy.message.' . $item->id);
						$canChange          = $user->authorise('core.edit.state', 'com_biblestudy.message.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->series_id; ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler hasTooltip <?php echo $iconClass ?>">
                                    <i class="icon-menu"></i>
                                </span><?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none;" name="order[]" size="5"
									       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'messages.', $canChange, 'cb', '', ''); ?><?php

									// Create dropdown items
									$action = $archived ? 'unarchive' : 'archive';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'messages');

									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'messages');

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($item->studytitle));
									?>
								</div>
							</td>
							<td class="small hidden-phone hidden-tablet">
								<?php echo JHtml::_('date', $this->escape($item->studydate, JText::_('DATE_FORMAT_LC4'))); ?>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_(
											'index.php?option=com_biblestudy&task=message.edit&id=' . (int) $item->id
										); ?>">
											<?php echo($this->escape($item->studytitle) ? $this->escape(
												$item->studytitle
											) : 'ID: ' . $this->escape($item->id)); ?>
										</a>
									<?php else : ?>
										<?php echo($this->escape($item->studytitle) ? $this->escape(
											$item->studytitle
										) : 'ID: ' . $this->escape($item->id)); ?>
									<?php endif; ?>
									<?php if ($item->alias) : ?>
										<p class="smallsub">
											<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
									<?php endif; ?>
								</div>
							</td>
							<td class="nowrap hidden-phone hidden-tablet">
								<?php
								if ($item->chapter_begin != 0 && $item->verse_begin != 0)
								{
									echo $this->escape($item->bookname) . ' ' . $this->escape($item->chapter_begin) . ':' . $this->escape($item->verse_begin);
								}
								?>
							</td>
							<td class="small hidden-phone hidden-tablet">
								<?php echo $this->escape($item->teachername); ?>
							</td>
							<td class="small hidden-phone hidden-tablet">
								<?php echo $this->escape($item->messageType); ?>
							</td>
							<td class="small hidden-phone hidden-tablet">
								<?php echo $this->escape($item->series_text); ?>
							</td>
							<td class="center hidden-phone hidden-tablet">
								<button type="button" class="btn btn-mini btn-info hasTooltip" data-placement="top" title="<?php echo $this->escape($item->hits); ?>"><?php echo JText::_('JBS_CMN_HITS'); ?></button>
								<br/>
								<button type="button" class="btn btn-mini btn-info hasTooltip" data-placement="top" title="<?php echo $this->escape($item->totalplays); ?>"><?php echo JText::_('JBS_CMN_PLAYS'); ?></button>
								<br/>
								<button type="button" class="btn btn-mini btn-info hasTooltip" data-placement="top" title="<?php echo $this->escape($item->totaldownloads); ?>"><?php echo JText::_('JBS_CMN_DOWNLOADS'); ?></button>
							</td>
							<td class="small hidden-phone hidden-tablet">
								<?php if ($item->language == '*'): ?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else: ?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php echo $this->pagination->getListFooter(); ?>
			<?php //Load the batch processing form. ?>
			<?php echo $this->loadTemplate('batch'); ?>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
