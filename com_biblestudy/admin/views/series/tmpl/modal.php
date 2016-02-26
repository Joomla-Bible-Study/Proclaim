<?php
/**
 * Modal
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$app = JFactory::getApplication();

if ($app->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');

$function = $app->input->getCmd('function', 'jSelectStudy');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=series&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
	method="post" name="adminForm" id="adminForm" class="form-inline">
	<fieldset id="filter clearfix">
		<div class="btn-toolbar">
			<div class="btn-group pull-left">
				<select name="filter_language" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
					<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
				</select>
			</div>
	</fieldset>
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th class="title">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
			</th>
			<th width="5%" class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
			</th>
			<th width="1%" class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'series.id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php if ($item->language && JLanguageMultilang::isEnabled())
			{
				$tag = strlen($item->language);

				if ($tag == 5)
				{
					$lang = substr($item->language, 0, 2);
				}
				elseif ($tag == 6)
				{
					$lang = substr($item->language, 0, 3);
				}
				else
				{
					$lang = "";
				}
			}
			elseif (!JLanguageMultilang::isEnabled())
			{
				$lang = "";
			}
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a class="pointer"
					   onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->series_text)); ?>');">
						<?php echo $this->escape($item->series_text); ?>
					</a>
				</td>
				<td class="center">
					<?php if ($item->language == '*'): ?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php else: ?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
				</td>
				<td align="center">
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
