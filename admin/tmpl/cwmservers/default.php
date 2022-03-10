<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$columns   = 4;

$sortFields = $this->getSortFields();
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmservers'); ?>" method="post" name="adminForm"
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
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			?>

			<table class="table table-striped" id="serversList">
				<thead>
				<tr>

					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
						       title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" style="min-width:55px;" class="nowrap center">
						<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'cwmservers.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('grid.sort', 'JBS_CMN_SERVERS', 'cwmservers.server_name', $listDirn, $listOrder); ?>
					</th>

					<th width="1%" class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'cwmservers.id', $listDirn, $listOrder); ?>
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
					$canCreate = $user->authorise('core.create');
					$canEdit = $user->authorise('core.edit', 'com_proclaim.cwmserver.' . $item->id);
					$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.cwmserver.' . $item->id);
					$canChange = $user->authorise('core.edit.state', 'com_proclaim.cwmserver.' . $item->id);
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->id ?>">

						<td class="center hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'cwmservers.', $canChange, 'cb', '', ''); ?>
							</div>
						</td>
						<td class="nowrap has-context">
							<div class="pull-left">

								<?php if ($canEdit || $canEditOwn) : ?>
									<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmserver.edit&id=' . (int) $item->id); ?>"
									   title="<?php echo Text::_('JACTION_EDIT'); ?>">
										<?php echo $this->escape($item->server_name); ?></a>
								<?php else : ?>
									<span
											title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->server_name)); ?>"><?php echo $this->escape($item->server_name); ?></span>
								<?php endif; ?>
							</div>
							<div class="pull-left">
								<?php
								// Create dropdown items
								HTMLHelper::_('dropdown.edit', $item->id, 'server.');
								HTMLHelper::_('dropdown.divider');
								if ($item->published) :
									HTMLHelper::_('dropdown.unpublish', 'cb' . $i, 'cwmservers.');
								else :
									HTMLHelper::_('dropdown.publish', 'cb' . $i, 'cwmservers.');
								endif;

								HTMLHelper::_('dropdown.divider');

								if ($archived) :
									HTMLHelper::_('dropdown.unarchive', 'cb' . $i, 'cwmservers.');
								else :
									HTMLHelper::_('dropdown.archive', 'cb' . $i, 'cwmservers.');
								endif;

								if ($trashed) :
									HTMLHelper::_('dropdown.untrash', 'cb' . $i, 'cwmservers.');
								else :
									HTMLHelper::_('dropdown.trash', 'cb' . $i, 'cwmservers.');
								endif;

								// Render dropdown list
								echo HTMLHelper::_('dropdown.render');
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
			<?php echo $this->pagination->getListFooter(); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
