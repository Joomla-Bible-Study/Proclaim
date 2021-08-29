<?php
/**
 * Modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.multiselect');
$input = new JInput;
$function = $input->get('function', 'jSelectMessagetype', 'cmd');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messagetypes&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
	method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
			</select>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
		<tr>
			<th width="20" class="title">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);"/>
			</th>
			<th width="20" align="center">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_PUBLISHED', 'messagetype.publish', $listDirn, $listOrder); ?>
			</th>

			<th width="10%">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_ORDERING', 'messagetype.ordering', $listDirn, $listOrder); ?>
				<?php if ($saveOrder) : ?>
					<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'messagetype.saveorder'); ?>
				<?php endif; ?>
			</th>
			<th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_MESSAGETYPE', 'messagetype.message_type', $listDirn, $listOrder); ?>
			</th>
			<th width="1%" class="nowrap">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'messagetype.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="10">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		$n = count($this->items);
		foreach ($this->items as $i => $item) :
			$ordering = ($listOrder == 'messagetype.ordering');
			$link     = JRoute::_('index.php?option=com_biblestudy&task=messagetype.edit&id=' . (int) $item->id);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'messagetypes.', true, 'cb', '', ''); ?>
				</td>
				<td class="order">
					<?php if ($listDirn == 'asc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, ($item->id == @$this->items[$i - 1]->id), 'messagetypes.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $n, ($this->pagination->total == @$this->items[$i + 1]->id), 'messagetypes.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php elseif ($listDirn == 'desc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, ($item->id == @$this->items[$i - 1]->id), 'messagetypes.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $n, ($this->pagination->total == @$this->items[$i + 1]->id), 'messagetypes.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php endif; ?>
					<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5"
					       value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text-area-order"/>
				</td>
				<td class="center">
					<a href="<?php echo $link; ?>"><?php echo $item->message_type; ?></a>

					<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>

</form>
