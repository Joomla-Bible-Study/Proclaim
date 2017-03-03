<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('dropdown.init');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering', 'location.id'));
$listDirn  = $this->escape($this->state->get('list.direction', 'desc'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'location.ordering';
$columns   = 5;

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_biblestudy&task=location.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'locationsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=locations'); ?>" method="post" name="adminForm"
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
				<table class="table table-striped adminlist" id="locationsList">
					<thead>
					<tr>
						<th width="1%" class="hidden-phone">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width:55px;" class="nowrap center">
							<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'location.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'JBS_CMN_LOCATIONS', 'location.locations_text', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'location.id', $listDirn, $listOrder); ?>
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
						$item->max_ordering = 0;
						$ordering = ($listOrder == 'location.ordering');
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_biblestudy.location.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.location.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_biblestudy.location.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">
							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'locations.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=location.edit&id=' . (int) $item->id); ?>"
										   title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->location_text); ?></a>
									<?php else : ?>
										<span
											title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->location_text)); ?>"><?php echo $this->escape($item->location_text); ?></span>
									<?php endif; ?>
								</div>
								<div class="pull-left">
									<?php
									// Create dropdown items
									JHtml::_('dropdown.edit', $item->id, 'location.');
									JHtml::_('dropdown.divider');
									if ($item->published) :
										JHtml::_('dropdown.unpublish', 'cb' . $i, 'locations.');
									else :
										JHtml::_('dropdown.publish', 'cb' . $i, 'locations.');
									endif;

									JHtml::_('dropdown.divider');

									if ($archived) :
										JHtml::_('dropdown.unarchive', 'cb' . $i, 'locations.');
									else :
										JHtml::_('dropdown.archive', 'cb' . $i, 'locations.');
									endif;

									if ($trashed) :
										JHtml::_('dropdown.untrash', 'cb' . $i, 'locations.');
									else :
										JHtml::_('dropdown.trash', 'cb' . $i, 'locations.');
									endif;

									// Render dropdown list
									echo JHtml::_('dropdown.render');
									?>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php // Load the batch processing form. ?>
				<?php if ($user->authorise('core.create', 'com_biblestudy')
					&& $user->authorise('core.edit', 'com_biblestudy')
					&& $user->authorise('core.edit.state', 'com_biblestudy')
				) : ?>
					<?php echo JHtml::_(
						'bootstrap.renderModal',
						'collapseModal',
						array(
							'title'  => JText::_('JBS_CMN_BATCH_OPTIONS'),
							'footer' => $this->loadTemplate('batch_footer')
						),
						$this->loadTemplate('batch_body')
					); ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php echo $this->pagination->getListFooter(); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
