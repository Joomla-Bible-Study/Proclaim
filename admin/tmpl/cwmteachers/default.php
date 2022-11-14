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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect')
	->useStyle('com_proclaim.cwmcore')
	->useScript('com_proclaim.cwmcorejs');

$app        = Factory::getApplication();
$user       = $app->getIdentity();
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
	$saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmteachers.saveOrderAjax&tmpl=component';
	HtmlHelper::_('sortablelist.sortable', 'teachers', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmteachers'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span
								class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table itemList" id="teachers">
						<caption class="visually-hidden">
							<?php echo Text::_('JBS_STY_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
						<tr>
							<th class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_PUBLISHED', 'cwmteacher.published', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="min-width:100px">
								<?php echo HtmlHelper::_('grid.sort', 'JBS_CMN_TEACHER', 'cwmteacher.teachername', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HtmlHelper::_('grid.sort', 'JGRID_HEADING_ACCESS', 'cwmteacher.access', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HtmlHelper::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'cwmlanguage', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo Text::_('JBS_TCH_SHOW_LIST'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo Text::_('JBS_TCH_SHOW_LANDING_PAGE'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HtmlHelper::_('grid.sort', 'JGRID_HEADING_ID', 'cwmteacher.id', $listDirn, $listOrder); ?>
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
							$canEdit = $user->authorise('core.edit', 'com_proclaim.teacher.' . $item->id);
							$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.teacher.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_proclaim.teacher.' . $item->id);
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HtmlHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<div class="btn-group">
										<?php echo HtmlHelper::_('jgrid.published', $item->published, $i, 'teachers.', $canChange, 'cb', '', ''); ?>
									</div>
								</td>
								<td class="nowrap has-context">
									<div class="pull-left">
										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmteacher.edit&id=' . (int) $item->id); ?>">
												<?php echo($this->escape($item->teachername) ? $this->escape($item->teachername) : 'ID: ' . $this->escape($item->id)); ?>
											</a>
										<?php else : ?>
											<?php echo($this->escape($item->teachername) ? $this->escape($item->teachername) : 'ID: ' . $this->escape($item->id)); ?>
										<?php endif; ?>
										<span class="small">
										<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									</span>
									</div>
								</td>
								<td class="small hidden-phone">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<div class="pull-left">
										<?php if ($item->language == '*'): ?>
											<?php echo Text::alt('JALL', 'language'); ?>
										<?php else: ?>
											<?php echo $item->language_title ? $this->escape($item->language_title) : Text::_('JUNDEFINED'); ?>
										<?php endif; ?>
									</div>
								</td>
								<td class="small d-none d-md-table-cell">
									<div class="pull-left">
										<?php if (!$item->list_show)
										{
											echo Text::_('JNO');
										}
										if ($item->list_show > 0)
										{
											echo Text::_('JYES');
										} ?>
									</div>
								</td>
								<td class="small d-none d-md-table-cell">
									<div class="pull-left">
										<?php if (!$item->landing_show)
										{
											echo Text::_('JNO');
										}
										if ($item->landing_show > 0)
										{
											echo Text::_('JYES');
										}
										if ($item->landing_show == '1')
										{
											echo ' - ' . Text::_('JBS_TCH_ABOVE');
										}
										elseif ($item->landing_show == '2')
										{
											echo ' - ' . Text::_('JBS_TCH_BELOW');
										} ?>
									</div>
								</td>
								<td class="d-none d-lg-table-cell">
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
								'title'  => Text::_('COM_CONTENT_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer'),
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php echo $this->pagination->getListFooter(); ?>
				<?php //Load the batch processing form. ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HtmlHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
