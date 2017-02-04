<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(BIBLESTUDY_PATH_ADMIN . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$app        = JFactory::getApplication();
$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$archived   = $this->state->get('filter.published') == 2 ? true : false;
$trashed    = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder  = $listOrder == 'ordering';
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		var table = document.getElementById("sortTable");
		var direction = document.getElementById("directionTable");
		var order = table.options[table.selectedIndex].value;
		var dirn;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=commentlist'); ?>" method="post"
      name="adminForm" id="adminForm">
	<div id="j-main-container">

		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search"
				       class="element-invisible"><?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?></label>
				<input type="text" name="filter_search"
				       placeholder="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>" id="filter_search"
				       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				       title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit"
				        title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button"
				        onclick="document.id('filter_search').value='';this.form.submit();"
				        title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="clearfix"></div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit"
					       class="element-invisible"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable"
					       class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
					<select name="directionTable" id="directionTable" class="input-medium"
					        onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option
								value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JBS_CMN_ASCENDING'); ?></option>
						<option
								value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JBS_CMN_DESCENDING'); ?></option>
					</select>
				</div>
			<div class="btn-group pull-right">
				<label for="sortTable"
				       class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<table class="table table-striped adminlist" id="comments">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_PUBLISHED', 'comment.published', $listDirn, $listOrder); ?>
				</th>

				<th>
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
				</th>

				<th>
					<?php echo JHtml::_('grid.sort', 'JBS_CMT_FULL_NAME', 'comment.full_name', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo JText::_('JBS_CMT_TEXT'); ?>
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JBS_CMT_CREATE_DATE', 'comment.studydate', $listDirn, $listOrder); ?>
				</th>

				<th>
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'comment.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->items as $i => $item) :
				$link       = 'index.php?option=com_biblestudy&task=commentform.edit&a_id=' . (int) $item->id;
				$canCreate  = $user->authorise('core.create');
				$canEdit    = $user->authorise('core.edit', 'com_biblestudy.comment.' . $item->id);
				$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.comment.' . $item->id);
				$canChange  = $user->authorise('core.edit.state', 'com_biblestudy.comment.' . $item->id);
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">

					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'comments.', $canChange, 'cb', '', ''); ?>
						</div>
					</td>

					<td class=" has-context">
						<div class="pull-left">
							<?php if ($canEdit || $canEditOwn) : ?>
								<a href="<?php echo $link; ?>"><?php echo $this->escape($item->studytitle) . ' - ' .
										JText::_($item->bookname) . ' ' . $item->chapter_begin; ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->studytitle) . ' - ' . JText::_($item->bookname) . ' ' . $item->chapter_begin; ?>
							<?php endif; ?>
						</div>
						<div class="pull-left">
							<?php
								// Create dropdown items
								JHtml::_('dropdown.edit', $item->id, 'comment.');
								JHtml::_('dropdown.divider');
								if ($item->published) :
									JHtml::_('dropdown.unpublish', 'cb' . $i, 'comments.');
								else :
									JHtml::_('dropdown.publish', 'cb' . $i, 'comments.');
								endif;

								JHtml::_('dropdown.divider');

								if ($archived) :
									JHtml::_('dropdown.unarchive', 'cb' . $i, 'comments.');
								else :
									JHtml::_('dropdown.archive', 'cb' . $i, 'comments.');
								endif;

								if ($trashed) :
									JHtml::_('dropdown.untrash', 'cb' . $i, 'comments.');
								else :
									JHtml::_('dropdown.trash', 'cb' . $i, 'comments.');
								endif;

								// Render dropdown list
								echo JHtml::_('dropdown.render');
							?>
						</div>
					</td>

					<td class=" has-context">
						<div class="pull-left">
							<?php echo $item->full_name; ?>
						</div>
					</td>
					<td>
						<div class="pull-left">
							<?php echo substr($item->comment_text, 0, 50); ?>
						</div>
					</td>
					<td class=" has-context">
						<div class="pull-left">
							<?php echo $item->comment_date; ?>
						</div>
					</td>

					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php //Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
