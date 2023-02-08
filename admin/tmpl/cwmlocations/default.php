<?php
/**
 * Default
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering', 'location.id'));
$listDirn  = $this->escape($this->state->get('list.direction', 'desc'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'location.ordering';
$columns   = 5;

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmlocation.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'locationsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmlocations'); ?>" method="post" name="adminForm"
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
				<table class="table table-striped adminlist" id="locationsList">
					<thead>
					<tr>
						<th width="1%" class="hidden-phone">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width:55px;" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'location.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', 'JBS_CMN_LOCATIONS', 'location.locations_text', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'location.id', $listDirn, $listOrder); ?>
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
						$canEdit = $user->authorise('core.edit', 'com_proclaim.location.' . $item->id);
						$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.location.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_proclaim.location.' . $item->id);
						?>
						<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo '1' ?>">
							<td class="center hidden-phone">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'locations.', $canChange, 'cb', '', ''); ?>
								</div>
							</td>
							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmlocation.edit&id=' . (int) $item->id); ?>"
										   title="<?php echo Text::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->location_text); ?></a>
									<?php else : ?>
										<span
												title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->location_text)); ?>"><?php echo $this->escape($item->location_text); ?></span>
									<?php endif; ?>
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
				<?php if ($user->authorise('core.create', 'com_proclaim')
					&& $user->authorise('core.edit', 'com_proclaim')
					&& $user->authorise('core.edit.state', 'com_proclaim')
				) : ?>
					<?php echo HTMLHelper::_(
						'bootstrap.renderModal',
						'collapseModal',
						array(
							'title'  => Text::_('JBS_CMN_BATCH_OPTIONS'),
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
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
