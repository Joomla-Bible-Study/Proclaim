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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.multiselect');

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$columns   = 4;

$sortFields = $this->getSortFields();
?>
<form action="<?php echo JRoute::_('index.php?option=com_proclaim&view=cwmtopics'); ?>" method="post" name="adminForm"
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
				<table class="table table-striped adminlist" id="topics">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<input type="checkbox" name="checkall-toggle" value=""
							       title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
							       onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'topic.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('grid.sort', 'JBS_CMN_TOPICS', 'topic.topic_text', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'topic.id', $listDirn, $listOrder); ?>
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
						$link = Route::_('index.php?option=com_proclaim&task=topic.edit&id=' . (int) $item->id);
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_proclaim.topic.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.topic.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_proclaim.topic.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">

							<td class="center hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'topics.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>

							<td class="nowrap has-context">
								<div class="pull-left">

									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmtopic.edit&id=' . (int) $item->id); ?>">
											<?php echo $this->escape($item->topic_text); ?>
										</a>

									<?php else : ?>
										<span
												title="<?php echo $this->escape($item->topic_text); ?>"><?php echo $this->escape($item->topic_text); ?></span>
									<?php endif; ?>
								</div>
								<div class="pull-left">
									<?php
									// Create dropdown items
									HTMLHelper::_('dropdown.edit', $item->id, 'cwmtopic.');
									HTMLHelper::_('dropdown.divider');
									if ($item->published) :
										HTMLHelper::_('dropdown.unpublish', 'cb' . $i, 'cwmtopics.');
									else :
										HTMLHelper::_('dropdown.publish', 'cb' . $i, 'cwmtopics.');
									endif;

									HTMLHelper::_('dropdown.divider');

									if ($archived) :
										HTMLHelper::_('dropdown.unarchive', 'cb' . $i, 'cwmtopics.');
									else :
										HTMLHelper::_('dropdown.archive', 'cb' . $i, 'cwmtopics.');
									endif;

									if ($trashed) :
										HTMLHelper::_('dropdown.untrash', 'cb' . $i, 'cwmtopics.');
									else :
										HTMLHelper::_('dropdown.trash', 'cb' . $i, 'cwmtopics.');
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
			<?php endif; ?>
			<?php echo $this->pagination->getListFooter(); ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
