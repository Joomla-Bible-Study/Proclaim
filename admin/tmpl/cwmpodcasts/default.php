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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
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
		var table = document.getElementById('sortTable')
		var direction = document.getElementById('directionTable')
		var order = table.options[table.selectedIndex].value
		var dirn
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc'
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value
		}
		Joomla.tableOrdering(order, dirn, '')
	}
</script>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=podcasts'); ?>" method="post" name="adminForm"
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
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<table class="table table-striped adminlist" id="podcasts">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
							       title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" style="min-width:55px;" class="nowrap center">
							<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'podcast.published', $listDirn, $listOrder); ?>
						</th>
						<th align="center">
							<?php echo HTMLHelper::_('grid.sort', 'JBS_CMN_PODCAST', 'podcast.title', $listDirn, $listOrder); ?>
						</th>
						<th class="center nowrap"
						<?php echo HTMLHelper::_('grid.sort', 'JBS_PDC_XML_TTITLE', 'podcast.filename', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'podcast.id', $listDirn, $listOrder); ?>
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
						$canEdit = $user->authorise('core.edit', 'com_proclaim.podcast.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.podcast.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_proclaim.podcast.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo '1' ?>">

							<td class="center hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'podcasts.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">

									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmpodcast.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->title); ?> </a>

									<?php else : ?>
										<span
												title="<?php echo $this->escape($item->title); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
								</div>
								<div class="pull-left">
									<?php
									// Create dropdown items
									HTMLHelper::_('dropdown.edit', $item->id, 'cwmpodcast.');
									HTMLHelper::_('dropdown.divider');
									if ($item->published) :
										HTMLHelper::_('dropdown.unpublish', 'cb' . $i, 'cwmpodcasts.');
									else :
										HTMLHelper::_('dropdown.publish', 'cb' . $i, 'cwmpodcasts.');
									endif;

									HTMLHelper::_('dropdown.divider');

									if ($archived) :
										HTMLHelper::_('dropdown.unarchive', 'cb' . $i, 'cwmpodcasts.');
									else :
										HTMLHelper::_('dropdown.archive', 'cb' . $i, 'cwmpodcasts.');
									endif;

									if ($trashed) :
										HTMLHelper::_('dropdown.untrash', 'cb' . $i, 'cwmpodcasts.');
									else :
										HTMLHelper::_('dropdown.trash', 'cb' . $i, 'cwmpodcasts.');
									endif;

									// Render dropdown list
									echo HTMLHelper::_('dropdown.render');
									?>
								</div>
							</td>
							<td class="center nowrap hidden-phone">
								<a href="<?php echo Route::_(JUri::root() . $this->escape($item->filename)); ?>"
								   target="_blank">
									<?php echo Text::_('JBS_PDC_XML'); ?>
								</a>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($item->language == '*'): ?>
										<?php echo Text::alt('JALL', 'language'); ?>
									<?php else: ?>
										<?php echo $item->language_title ? $this->escape($item->language_title) : Text::_('JUNDEFINED'); ?>
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
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
