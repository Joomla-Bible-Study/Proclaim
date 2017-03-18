<?php
/**
 * Modal
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('script', 'system/multiselect.js', false, true);
$input = new JInput;
$function = $input->get('function', 'jSelectStudy', 'cmd');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messagelist&layout=modal&tmpl=component&function=' . $function); ?>"
	method="post" name="adminForm" id="adminForm">
	<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>

	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th class="title">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
			</th>
			<th class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
			</th>
			<th width="8%" class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
			</th>
			<th class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_SCRIPTURE', 'book.bookname', $listDirn, $listOrder); ?>
			</th>
			<th class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
			</th>
			<th class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_MESSAGETYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
			</th>
			<th class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="15">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a class="pointer"
					   onclick="if(window.parent) window.parent.<?php echo $function; ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->studytitle)); ?>');">
						<?php echo $this->escape($item->studytitle); ?>
					</a>
				</td>
				<td class="center nowrap">
					<?php echo JHtml::_('date', $item->studydate, JText::_('DATE_FORMAT_LC4')); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'studieslist.', true, 'cb', '', ''); ?>
				</td>
				<td class="center">
					<?php echo JText::sprintf($item->bookname) . ' ' . $this->escape($item->chapter_begin) . ':' . $this->escape($item->verse_begin); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->teachername); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->messageType); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->series_text); ?>
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
