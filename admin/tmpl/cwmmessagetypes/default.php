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

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$columns   = 4;

$sortFields = $this->getSortFields();
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmessagetypes'); ?>" method="post"
      name="adminForm" id="adminForm">
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
					<table class="table itemList" id="messagetypeslist">
						<thead>
						<tr>
							<th class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'messagetypes.published', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="min-width:100px">
								<?php echo HTMLHelper::_('grid.sort', 'JBS_CMN_MESSAGETYPES', 'messagetypes.message_type', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'messagetypes.id', $listDirn, $listOrder); ?>
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
							$canEdit = $user->authorise('core.edit', 'com_proclaim.messagetype.' . $item->id);
							$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.messagetype.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_proclaim.messagetype.' . $item->id);
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="center hidden-phone">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="center">
									<div class="btn-group">
										<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'messagetypes.', $canChange, 'cb', '', ''); ?>
									</div>
								</td>
								<td class="nowrap has-context">
									<div class="pull-left">

										<?php if ($canEdit || $canEditOwn) : ?>
											<a href="<?php echo Route::_('index.php?option=com_proclaim&task=cwmmessagetype.edit&id=' . (int) $item->id); ?>"
											   title="<?php echo JText::_('JACTION_EDIT'); ?>">
												<?php echo $this->escape($item->message_type); ?></a>
										<?php else : ?>
											<span
													title="<?php echo Text::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->messsage_type)); ?>"><?php echo $this->escape($item->message_type); ?></span>
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
				<?php // Load the batch processing form. ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
