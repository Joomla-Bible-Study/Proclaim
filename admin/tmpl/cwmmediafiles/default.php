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

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app       = Factory::getApplication();
$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder === 'mediafile.ordering';
$columns   = 10;

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_proclaim&task=cwmmediafiles.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'mediafileList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<form action="<?php echo JRoute::_('index.php?option=com_proclaim&view=cwmmediafiles'); ?>" method="post"
      name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php //echo $this->sidebar; ?>
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
				<table class="table table-striped adminlist" id="mediafileList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'cwmmediafile.ordering', $listDirn,
								$listOrder, null, 'desc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>

						<th class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'cwmmediafile.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_('JBS_MED_RESOURCE_NAME'); ?>
						</th>
						<th class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JBS_CMN_STUDY_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JBS_MED_CREATE_DATE', 'cwmmediafile.createdate', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JBS_MED_ACCESS', 'cwmmediafile.access', $listDirn, $listOrder); ?>
						</th>
						<th colspan="2" width="5%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JBS_MED_MEDIA_FILES_STATS', 'cwmmediafile.plays', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo $columns; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						$item->max_ordering = 0;
						$ordering = ($listOrder === 'mediafile.ordering');
						$canCreate = $user->authorise('core.create');
						$canEdit = $user->authorise('core.edit', 'com_proclaim.mediafile.' . $item->id);
						$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own', 'com_proclaim.mediafile.' . $item->id);
						$canChange = $user->authorise('core.edit.state', 'com_proclaim.mediafile.' . $item->id);
						$label = $this->escape($item->serverConfig->name->__toString()) . ' - ';
						$label .= $this->escape($item->params[$item->serverConfig->config->media_resource->__toString()])
							? $item->serverConfig->config->media_resource->__toString() : 'mediacode';
						?>
						<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->study_id ?>">
							<td class="order nowrap center hidden-phone">
								<?php
								$iconClass = '';
								if (!$canChange)
								{
									$iconClass = ' inactive';
								}
								elseif (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler hasTooltip <?php echo $iconClass ?>">
                                    <i class="icon-menu"></i>
                                </span><?php if ($canChange && $saveOrder) : ?>
									<input type="text" style="display:none;" name="order[]" size="5"
									       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php endif; ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo JHtml::_('jgrid.published', $item->published, $i, 'cwmmediafiles.', $canChange, 'cb', null, null); ?>
									<?php
									// Create dropdown items
									$action = $archived ? 'unarchive' : 'archive';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'cwmmediafiles');

									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'cwmmediafiles');

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($label));
									?>
								</div>
							</td>
							<td class="has-context">
								<div class="pull-left">
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'cwmmediafiles.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($item->language == '*'): ?>
										<?php $language = JText::alt('JALL', 'language'); ?>
									<?php else: ?>
										<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
									<?php endif; ?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_proclaim&task=cwmmediafile.edit&id=' . (int) $item->id); ?>">
											<span class="label pull-left"><?php echo $this->escape($label); ?></span>
										</a>
									<?php else : ?>
										<span
											title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>">
										<?php echo $label; ?>
				                    </span>
									<?php endif; ?>
								</div>
								<div class="clearfix"></div>
								<div class="pull-left">
									<a href="<?php echo JRoute::_('index.php?option=com_proclaim&task=cwmmediafile.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->params[$item->serverConfig->config->media_resource->__toString()]); ?>
									</a>
								</div>
							</td>
							<td class="center">
								<?php echo $this->escape($item->studytitle); ?>
							</td>
							<td class="hidden-phone">
								<?php echo $this->escape($item->serverConfig->name->__toString()); ?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo $this->escape($item->access_level); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo $this->escape($item->plays); ?>
							</td>
							<td class="center hidden-phone">
								<?php echo $this->escape($item->downloads); ?>
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
					<?php echo JHtml::_(
						'bootstrap.renderModal',
						'collapseModal',
						array(
							'title'  => JText::_('JBS_CMN_BATCH_OPTIONS'),
							'footer' => $this->loadTemplate('batch_footer')
						),
						//$this->loadTemplate('batch_body')
					); ?>
				<?php endif; ?>
			<?php endif; ?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
