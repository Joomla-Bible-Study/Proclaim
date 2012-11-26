<?php
/**
 * Default View
 *
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
if (BIBLESTUDY_CHECKREL) {
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
} else {
	JHtml::_('behavior.tooltip');
}
JHtml::_('behavior.multiselect');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'a.ordering';
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_biblestudy&task=comments.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'commentsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $listOrder; ?>') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
    }
</script>
<form
        action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=comments'); ?>"
        method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
    </div>
        <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
            <?php endif; ?>
    <div id="filter-bar" class="btn-toolbar">
        <div class="filter-search btn-group pull-left">
            <label for="filter_search"
                   class="element-invisible"><?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?></label>
            <input type="text" name="filter_search" placeholder="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"
                   id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
        </div>
        <div class="btn-group pull-left hidden-phone">
            <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
                    class="icon-search"></i></button>
            <button class="btn tip hasTooltip" type="button"
                    onclick="document.id('filter_search').value='';this.form.submit();"
                    title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
        </div>
        <div class="btn-group pull-right hidden-phone">
            <label for="limit"
                   class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
        </div>
        <div class="btn-group pull-right hidden-phone">
            <label for="directionTable"
                   class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
            <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
                <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
                <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
            </select>
        </div>
        <div class="btn-group pull-right">
            <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
            <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                <option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
            </select>
        </div>
		<?php if (!BIBLESTUDY_CHECKREL): ?>
        <div class="btn-group pull-right">
            <label for="filter_published" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
            <select name="filter_published" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
            </select>
        </div>
        <div class="btn-group pull-right">
            <label for="filter_language" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
            <select name="filter_language" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
            </select>
        </div>
		<?php endif; ?>
    </div>

    <div class="clr"></div>
    <table class="table table-striped adminlist" id="commentsList">
        <thead>
        <tr>
            <th width="1">
                <input type="checkbox" name="checkall-toggle" value=""
                       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
            </th>
            <th width="1%" style="min-width:55px" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'comment.published', $listDirn, $listOrder); ?>
            </th>
            <th>
				<?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'comment.studytitle', $listDirn, $listOrder); ?>
            </th>
            <th width="10%">
				<?php echo JText::_('JBS_CMT_FULL_NAME'); ?>
            </th>
            <th width="10%">
				<?php echo JHtml::_('grid.sort', 'JBS_CMT_CREATE_DATE', 'comment.studydate', $listDirn, $listOrder); ?>
            </th>
            <th width="5%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
            </th>
            <th width="1%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'commint.id', $listDirn, $listOrder); ?>
            </th>
        </tr>
        </thead>

		<?php
		foreach ($this->items as $i => $item) :
			$link = JRoute::_('index.php?option=com_biblestudy&task=comment.edit&id=' . (int)$item->id);
			$item->max_ordering = 0; //??
			$ordering = ($listOrder == 'series.ordering');
			$canCreate = $user->authorise('core.create');
			$canEdit = $user->authorise('core.edit', 'com_biblestudy.serie.' . $item->id);
			$canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.serie.' . $item->id);
			$canChange = $user->authorise('core.edit.state', 'com_biblestudy.serie.' . $item->id);
			?>
            <tr class="row<?php echo $i % 2; ?>">
                <td width="1">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td align="center">
                    <div class="btn-group">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'comments.', true, 'cb', '', ''); ?>
                    </div>
                </td>
                <td>
                    <a href="<?php echo $link; ?>"><?php echo $this->escape($item->studytitle) . ' - ' . JText::_($item->bookname) . ' ' . $item->chapter_begin; ?></a>
                </td>
                <td> <?php echo $item->full_name; ?> </td>
                <td> <?php echo $item->comment_date; ?> </td>
                <td class="small hidden-phone">
		            <?php if ($item->language == '*'): ?>
		            <?php echo JText::alt('JALL', 'language'); ?>
		            <?php else: ?>
		            <?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
		            <?php endif; ?>
                </td>
                <td class="center hidden-phone">
		            <?php echo (int) $item->id; ?>
                </td>
            </tr>
			<?php endforeach; ?>
        <tfoot>
        <tr>
            <td colspan="10">
				<?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>