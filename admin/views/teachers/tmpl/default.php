<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$app        = JFactory::getApplication();
$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder  = $listOrder == 'teacher.ordering';
$sortFields = $this->getSortFields();
$columns    = 9;

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_biblestudy&task=teachers.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'teachers', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=teachers'); ?>" method="post" name="adminForm"
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
				<table class="table table-striped adminlist" id="teachers">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'teacher.ordering', $listDirn, $listOrder, null, 'desc', 'JGRID_HEADING_ORDERING'); ?>
						</th>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
							       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_PUBLISHED', 'teacher.published', $listDirn, $listOrder); ?>
						</th>

						<th align="center">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'teacher.access', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_('JBS_TCH_SHOW_LIST'); ?>
						</th>
						<th>
							<?php echo JText::_('JBS_TCH_SHOW_LANDING_PAGE'); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'teacher.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo $columns; ?>">
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						//$item->max_ordering = 0; //??
						$ordering = ($listOrder == 'teacher.ordering');
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_biblestudy.teacher.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.teacher.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_biblestudy.teacher.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel = '';
									if (!$saveOrder) :
										$disabledLabel    = JText::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip<?php echo $disableClassName ?>"
									      title="<?php echo $disabledLabel ?>">
								<i class="icon-menu"></i>
							</span>
									<input type="text" style="display:none;" name="order[]" size="5"
									       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive">
								<i class="icon-menu"></i>
							</span>
								<?php endif; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'teachers.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>

							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=teacher.edit&id=' . (int) $item->id); ?>">
											<?php echo($this->escape($item->teachername) ? $this->escape($item->teachername) : 'ID: ' . $this->escape($item->id)); ?>
										</a>
									<?php else : ?>
										<?php echo($this->escape($item->teachername) ? $this->escape($item->teachername) : 'ID: ' . $this->escape($item->id)); ?>
									<?php endif; ?>
									<span class="small">
					<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
				</span>
								</div>
								<div class="pull-left">
									<?php
									// Create dropdown items
									JHtml::_('dropdown.edit', $item->id, 'teacher.');
									JHtml::_('dropdown.divider');
									if ($item->published) :
										JHtml::_('dropdown.unpublish', 'cb' . $i, 'teachers.');
									else :
										JHtml::_('dropdown.publish', 'cb' . $i, 'teachers.');
									endif;

									JHtml::_('dropdown.divider');

									if ($archived) :
										JHtml::_('dropdown.unarchive', 'cb' . $i, 'teachers.');
									else :
										JHtml::_('dropdown.archive', 'cb' . $i, 'teachers.');
									endif;

									if ($trashed) :
										JHtml::_('dropdown.untrash', 'cb' . $i, 'teachers.');
									else :
										JHtml::_('dropdown.trash', 'cb' . $i, 'teachers.');
									endif;

									// Render dropdown list
									echo JHtml::_('dropdown.render');
									?>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($item->language == '*'): ?>
										<?php echo JText::alt('JALL', 'language'); ?>
									<?php else: ?>
										<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
									<?php endif; ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if (!$item->list_show)
									{
										echo JText::_('JNO');
									}
									if ($item->list_show > 0)
									{
										echo JText::_('JYES');
									} ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if (!$item->landing_show)
									{
										echo JText::_('JNO');
									}
									if ($item->landing_show > 0)
									{
										echo JText::_('JYES');
									}
									if ($item->landing_show == '1')
									{
										echo ' - ' . JText::_('JBS_TCH_ABOVE');
									}
									elseif ($item->landing_show == '2')
									{
										echo ' - ' . JText::_('JBS_TCH_BELOW');
									} ?>
								</div>
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
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
