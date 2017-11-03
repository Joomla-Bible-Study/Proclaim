<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;

$sortFields = $this->getSortFields();
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
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messagetypes'); ?>" method="post"
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
				<table class="table table-striped adminlist" id="messagetypeslist">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
							       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" style="min-width:55px;" class="nowrap center">
							<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'messagetypes.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_MESSAGETYPES', 'messagetypes.message_type', $listDirn, $listOrder); ?>
						</th>

						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'messagetypes.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						$item->max_ordering = 0;
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_biblestudy.messagetype.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.messagetype.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_biblestudy.messagetype.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">

							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'messagetypes.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">

									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=messagetype.edit&id=' . (int) $item->id); ?>"
										   title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->message_type); ?></a>
									<?php else : ?>
										<span
												title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->messsage_type)); ?>"><?php echo $this->escape($item->message_type); ?></span>
									<?php endif; ?>
								</div>
								<div class="pull-left">
									<?php
									// Create dropdown items
									JHtml::_('dropdown.edit', $item->id, 'messagetype.');
									JHtml::_('dropdown.divider');
									if ($item->published) :
										JHtml::_('dropdown.unpublish', 'cb' . $i, 'messagetypes.');
									else :
										JHtml::_('dropdown.publish', 'cb' . $i, 'messagetypes.');
									endif;

									JHtml::_('dropdown.divider');

									if ($archived) :
										JHtml::_('dropdown.unarchive', 'cb' . $i, 'messagetypes.');
									else :
										JHtml::_('dropdown.archive', 'cb' . $i, 'messagetypes.');
									endif;

									if ($trashed) :
										JHtml::_('dropdown.untrash', 'cb' . $i, 'messagetypes.');
									else :
										JHtml::_('dropdown.trash', 'cb' . $i, 'messagetypes.');
									endif;

									// Render dropdown list
									echo JHtml::_('dropdown.render');
									?>
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
			<?php // Load the batch processing form. ?>
			<?php echo $this->loadTemplate('batch'); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
