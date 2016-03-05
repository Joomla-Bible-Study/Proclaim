<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$app = JFactory::getApplication();
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.published') == 2 ? true : false;
$trashed = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'series.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_biblestudy&task=series.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'seriesList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
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
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=series'); ?>" method="post" name="adminForm"
      id="adminForm">
<?php if (!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
	<hr/>
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
		<label for="limit" class="element-invisible"
		       id="limit"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	<div class="btn-group pull-right hidden-phone">
		<label for="directionTable"
		       class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
		<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
			<option
				value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JBS_CMN_ASCENDING'); ?></option>
			<option
				value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JBS_CMN_DESCENDING'); ?></option>
		</select>
	</div>
	<div class="btn-group pull-right">
		<label for="sortTable" class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
		<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></option>
			<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
		</select>
	</div>
</div>
<div class="clearfix"></div>

<table class="table table-striped adminlist" id="series">
	<thead>
	<tr>
		<th width="1%" class="nowrap center hidden-phone">
			<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'series.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
		</th>
		<th width="1%">
			<input type="checkbox" name="checkall-toggle" value=""
			       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
		</th>
		<th width="1%" style="min-width:55px;" class="nowrap center">
			<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'series.published', $listDirn, $listOrder); ?>
		</th>
		<th>
			<?php echo JHtml::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
		</th>
		<th width="10%" class="nowrap hidden-phone">
			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'series.access', $listDirn, $listOrder); ?>
		</th>
		<th width="5%" class="nowrap hidden-phone">
			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
		</th>
		<th width="1%" class="nowrap hidden-phone">
			<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'series.id', $listDirn, $listOrder); ?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($this->items as $i => $item) :
		$item->max_ordering = 0; //??
		$ordering           = ($listOrder == 'series.ordering');
		$canCreate          = $user->authorise('core.create');
		$canEdit            = $user->authorise('core.edit', 'com_biblestudy.serie.' . $item->id);
		$canEditOwn         = $user->authorise('core.edit.own', 'com_biblestudy.serie.' . $item->id);
		$canChange          = $user->authorise('core.edit.state', 'com_biblestudy.serie.' . $item->id);
		?>
		<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo '1' ?>">
			<td class="order nowrap center hidden-phone">
				<?php
				if ($canChange) :
					$disableClassName = '';
					$disabledLabel    = '';

					if (!$saveOrder) :
						$disabledLabel    = JText::_('JORDERINGDISABLED');
						$disableClassName = 'inactive tip-top';
					endif;
					?>
					<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
					      title="<?php echo $disabledLabel ?>">
                                            <i class="icon-menu"></i>
                                        </span>
					<input type="text" style="display:none;" name="order[]" size="5"
					       value="<?php echo $item->ordering; ?>" class="width-10 text-area-order "/>
				<?php else : ?>
					<span class="sortable-handler inactive">
                                            <i class="icon-menu"></i>
                                        </span>
				<?php endif; ?>
			</td>
			<td class="center hidden-phone">
				<?php echo JHtml::_('grid.id', $i, $item->id); ?>
			</td>
			<td class="center">
				<div class="btn-group">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'series.', $canChange, 'cb', '', ''); ?>
				</div>
			</td>
			<td class="nowrap has-context">
				<div class="pull-left">
					<?php if ($item->language == '*'): ?>
						<?php $language = JText::alt('JALL', 'language'); ?>
					<?php else: ?>
						<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif; ?>
					<?php if ($canEdit || $canEditOwn) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=serie.edit&id=' . (int) $item->id); ?>"
						   title="<?php echo JText::_('JACTION_EDIT'); ?>">
							<?php echo $this->escape($item->series_text); ?></a>
					<?php else : ?>
						<span
							title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->series_text); ?></span>
					<?php endif; ?>
				</div>
				<div class="pull-left">
					<?php
						// Create dropdown items
						JHtml::_('dropdown.edit', $item->id, 'serie.');
						JHtml::_('dropdown.divider');
						if ($item->published) :
							JHtml::_('dropdown.unpublish', 'cb' . $i, 'series.');
						else :
							JHtml::_('dropdown.publish', 'cb' . $i, 'series.');
						endif;

						JHtml::_('dropdown.divider');

						if ($archived) :
							JHtml::_('dropdown.unarchive', 'cb' . $i, 'series.');
						else :
							JHtml::_('dropdown.archive', 'cb' . $i, 'series.');
						endif;

						if ($trashed) :
							JHtml::_('dropdown.untrash', 'cb' . $i, 'series.');
						else :
							JHtml::_('dropdown.trash', 'cb' . $i, 'series.');
						endif;

						// Render dropdown list
						echo JHtml::_('dropdown.render');
					?>
				</div>
			</td>
			<td class="small hidden-phone">
				<?php echo $this->escape($item->access_level); ?>
			</td>
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
