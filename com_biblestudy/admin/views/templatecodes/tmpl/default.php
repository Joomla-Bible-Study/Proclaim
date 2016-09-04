<?php
/**
 * Default
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
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
$saveOrder  = $listOrder == 'ordering';
$sortFields = $this->getSortFields();
$columns    = 5;

?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		var table = document.getElementById("sortTable");
		var direction = document.getElementById("directionTable");
		var order = table.options[table.selectedIndex].value;
		var dirn;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=templatecodes'); ?>" method="post"
      name="adminForm" id="adminForm">
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
				<table class="table table-striped adminlist" id="templatecodes">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
							       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="8%">
							<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'templatecode.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_('JBS_TPLCODE_FILENAME'); ?>
						</th>
						<th>
							<?php echo JText::_('JBS_TPLCODE_TYPE'); ?>
						</th>
						<th>
							<?php echo JText::_('JGRID_HEADING_ID'); ?>
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
						$link = JRoute::_('index.php?option=com_biblestudy&task=templatecode.edit&id=' . (int) $item->id);
						$item->max_ordering = 0; //??
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_biblestudy.templatcode.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.templatecode.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_biblestudy.templatecode.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">

							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'templatecodes.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>

							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo $link; ?>"><?php echo $item->filename; ?></a>
									<?php else : ?>
										<?php echo $item->filename; ?>
									<?php endif; ?>
									<div class="pull-left">
										<?php
										// Create dropdown items
										JHtml::_('dropdown.edit', $item->id, 'templatecode.');
										JHtml::_('dropdown.divider');
										if ($item->published) :
											JHtml::_('dropdown.unpublish', 'cb' . $i, 'templatecodes.');
										else :
											JHtml::_('dropdown.publish', 'cb' . $i, 'templatecodes.');
										endif;

										JHtml::_('dropdown.divider');

										if ($archived) :
											JHtml::_('dropdown.unarchive', 'cb' . $i, 'templatecodes.');
										else :
											JHtml::_('dropdown.archive', 'cb' . $i, 'templatecodes.');
										endif;

										if ($trashed) :
											JHtml::_('dropdown.untrash', 'cb' . $i, 'templatecodes.');
										else :
											JHtml::_('dropdown.trash', 'cb' . $i, 'templatecodes.');
										endif;

										// Render dropdown list
										echo JHtml::_('dropdown.render');
										?>
									</div>

								</div>

							</td>


							<td class="nowrap has-context">
								<div class="pull-left">
									<a href="<?php echo $link; ?>"><?php echo $item->typetext; ?></a>
								</div>
							</td>

							<td class="nowrap has-context">
								<div class="pull-left">
									<?php echo (int) $item->id; ?>
								</div>
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
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
