<?php
/**
 * Modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

HtmlHelper::_('behavior.multiselect');
$input = new Input;
$function = $input->get('function', 'jSelectMessagetype', 'cmd');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder  = $listOrder === 'messagetype.ordering';
?>
<form
	action="<?php echo Route::_('index.php?option=com_proclaim&view=messagetypes&layout=modal&tmpl=component&function=' . $function . '&' . Session::getFormToken() . '=1'); ?>"
	method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo HtmlHelper::_('select.options', HtmlHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
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
				<?php echo HtmlHelper::_('searchtools.sort', 'JBS_CMN_PUBLISHED', 'messagetype.publish', $listDirn, $listOrder); ?>
			</th>

			<th width="10%">
				<?php echo HtmlHelper::_('searchtools.sort', 'JBS_CMN_ORDERING', 'messagetype.ordering', $listDirn, $listOrder); ?>
				<?php if ($saveOrder) : ?>
					<?php echo HtmlHelper::_('grid.order', $this->items, 'filesave.png', 'messagetype.saveorder'); ?>
				<?php endif; ?>
			</th>
			<th>
				<?php echo HtmlHelper::_('searchtools.sort', 'JBS_CMN_MESSAGETYPE', 'messagetype.message_type', $listDirn, $listOrder); ?>
			</th>
			<th width="1%" class="nowrap">
				<?php echo HtmlHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'messagetype.id', $listDirn, $listOrder); ?>
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
			$link     = Route::_('index.php?option=com_proclaim&task=messagetype.edit&id=' . (int) $item->id);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo HtmlHelper::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<?php echo HtmlHelper::_('jgrid.published', $item->published, $i, 'messagetypes.', true, 'cb', '', ''); ?>
				</td>
				<td class="order">
					<?php if ($listDirn === 'asc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, ($item->id == @$this->items[$i - 1]->id), 'messagetypes.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $n, ($this->pagination->total == @$this->items[$i + 1]->id), 'messagetypes.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php elseif ($listDirn === 'desc') : ?>
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
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
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
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>

</form>
