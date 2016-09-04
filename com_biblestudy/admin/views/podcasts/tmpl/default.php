<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
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
$columns   = 6;

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
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=podcasts'); ?>" method="post" name="adminForm"
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
				<table class="table table-striped adminlist" id="podcasts">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
							       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" style="min-width:55px;" class="nowrap center">
							<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'podcast.published', $listDirn, $listOrder); ?>
						</th>
						<th align="center">
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_PODCAST', 'podcast.title', $listDirn, $listOrder); ?>
						</th>
						<th class="center nowrap"
						<?php echo JHtml::_('grid.sort', 'JBS_PDC_XML_TTITLE', 'podcast.filename', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'podcast.id', $listDirn, $listOrder); ?>
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
						$item->max_ordering = 0; //??
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_biblestudy.podcast.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.podcast.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_biblestudy.podcast.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">

							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'podcasts.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">

									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=podcast.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->title); ?> </a>

									<?php else : ?>
										<span
											title="<?php echo $this->escape($item->title); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
								</div>
								<div class="pull-left">
									<?php
									// Create dropdown items
									JHtml::_('dropdown.edit', $item->id, 'podcast.');
									JHtml::_('dropdown.divider');
									if ($item->published) :
										JHtml::_('dropdown.unpublish', 'cb' . $i, 'podcasts.');
									else :
										JHtml::_('dropdown.publish', 'cb' . $i, 'podcasts.');
									endif;

									JHtml::_('dropdown.divider');

									if ($archived) :
										JHtml::_('dropdown.unarchive', 'cb' . $i, 'podcasts.');
									else :
										JHtml::_('dropdown.archive', 'cb' . $i, 'podcasts.');
									endif;

									if ($trashed) :
										JHtml::_('dropdown.untrash', 'cb' . $i, 'podcasts.');
									else :
										JHtml::_('dropdown.trash', 'cb' . $i, 'podcasts.');
									endif;

									// Render dropdown list
									echo JHtml::_('dropdown.render');
									?>
								</div>
							</td>
							<td class="center nowrap hidden-phone">
								<a href="<?php echo JRoute::_(JUri::root() . $this->escape($item->filename)); ?>"
								   target="_blank">
									<?php echo JText::_('JBS_PDC_XML'); ?>
								</a>
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
							<td class="center hidden-phone">
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
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
