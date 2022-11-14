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
$sortFields = $this->getSortFields();
$columns    = 4;

?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmtemplates'); ?>" method="post"
      name="adminForm"
      id="adminForm">
	<div id="row">
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
					<table class="table itemList" id="templatelist">
						<thead>
						<tr>
							<th class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'template.published', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="min-width:100px">
								<?php echo HTMLHelper::_('grid.sort', 'JBS_TPL_TEMPLATE_ID', 'template.title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-3 d-none d-lg-table-cell">
								<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'template.id', $listDirn, $listOrder); ?>
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
							$link = Route::_('index.php?option=com_proclaim&task=cwmtemplate.edit&id=' . (int) $item->id);
							$item->max_ordering = 0;
							$canCreate = $user->authorise('core.create');
							$canEdit = $user->authorise('core.edit', 'com_proclaim.template.' . $item->id);
							$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.template.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_proclaim.template.' . $item->id);
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'templates.', $canChange, 'cb', '', ''); ?>
								</td>
								<td class="nowrap has-context">
									<div class="pull-left">

										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmtemplate.edit&id=' . (int) $item->id); ?>">
												<?php echo $this->escape($item->title); ?>
											</a>

										<?php else : ?>
											<span
													title="<?php echo $this->escape($item->title); ?>"><?php echo $this->escape($item->title); ?></span>
										<?php endif; ?>
									</div>
								</td>
								<td class="d-none d-lg-table-cell">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
				<?php echo $this->pagination->getListFooter(); ?>
				<?php // Load the batch processing form. ?>
				<?php // echo $this->loadTemplate('batch'); ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
