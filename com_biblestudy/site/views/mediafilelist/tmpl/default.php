<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if (BIBLESTUDY_CHECKREL)
{
	JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}
else
{
	JHtml::_('behavior.tooltip');
	JHtml::stylesheet('media/com_biblestudy/css/biblestudy-j2.5.css');
	JHtml::stylesheet('media/com_biblestudy/jui/css/bootstrap.css');
	JHtml::script('media/com_biblestudy/jui/js/jquery.js');
	JHtml::script('media/com_biblestudy/jui/js/jquery-noconflict.js');
	JHtml::script('media/com_biblestudy/jui/js/bootstrap.js');
}
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
    var table;
    var direction;
    var order;
    var dirn;
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
<h2><?php echo JText::_('JBS_CMN_MEDIA'); ?></h2>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=mediafilelist'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div id="j-main-container">
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
                <label class="filter-search-lbl" for="filter_filename"><?php echo JText::_('JBS_MED_FILENAME'); ?>
                    : </label>
                <input type="text" name="filter_filename" id="filter_filename"
                       value="<?php echo $this->escape($this->state->get('filter.filename')); ?>"
                       title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
            </div>
            <div class="btn-group pull-left hidden-phone">
                <button class="btn tip hasTooltip"
                        type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <button class="btn tip hasTooltip"
                        onclick="document.id('filter_filename').value='';document.id('filter_studytitle').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
                    <i class="icon-remove"></i></button>
            </div>
            <div class="btn-group pull-left hidden-phone">
                <label for="limit"
                       class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
            </div>
            <div class="btn-group pull-right hidden-phone">
                <label for="directionTable"
                       class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
                <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
                    <option value="asc" <?php if ($listDirn == 'asc')
					{
						echo 'selected="selected"';
					} ?>><?php echo JText::_('JBS_CMN_ASCENDING'); ?></option>
                    <option value="desc" <?php if ($listDirn == 'desc')
					{
						echo 'selected="selected"';
					} ?>><?php echo JText::_('JBS_CMN_DESCENDING'); ?></option>
                </select>
            </div>
            <div class="btn-group pull-right">
                <label for="sortTable" class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
                <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
                    <option value=""><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
                </select>
            </div>
			<?php if (!BIBLESTUDY_CHECKREL): ?>
            <div class="btn-group pull-right">
                <label for="filter_published" id="filter_published"
                       class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
                <select name="filter_published" class="input-medium" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
					<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
                </select>
            </div>
			<?php endif; ?>
            <div class="btn-group pull-right">
		        <?php echo $this->newlink; ?>
            </div>
        </div>
        <div class="clearfix"></div>

        <table class="table table-striped adminlist" id="articleList">
            <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value=""
                           title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
                </th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'mediafile.published', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
					<?php echo JHtml::_('grid.sort', 'JBS_MED_FILENAME', 'mediafile.filename', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
					<?php echo JHtml::_('grid.sort', 'JBS_MED_MEDIA_TYPE', 'mediaType.media_text', $listDirn, $listOrder); ?>
                </th>
                <th width="15%">
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_MEDIA_CREATE_DATE', 'mediafile.createdate', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_PLAYS', 'mediafile.plays', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_DOWNLOADS', 'mediafile.downloads', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', 'JBS_CMN_ID', 'mediafile.id', $listDirn, $listOrder); ?>
                </th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ($this->items as $i => $item) :
				$item->max_ordering = 0; //??
				$canCreate          = $user->authorise('core.create');
				$canEdit            = $user->authorise('core.edit', 'com_biblestudy.mediafile.' . $item->id);
				$canEditOwn         = $user->authorise('core.edit.own', 'com_biblestudy.mediafile.' . $item->id);
				$canChange          = $user->authorise('core.edit.state', 'com_biblestudy.mediafile.' . $item->id);
				?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center hidden-phone">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'mediafilelist.', true, 'cb', '', ''); ?>
                </td>

                <td class="nowrap has-context">
                    <div class="pull-left">
						<?php if ($canEdit || $canEditOwn) : ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=mediafileform.edit&a_id=' . (int) $item->id); ?>">
							<?php echo ($this->escape($item->filename) ? $this->escape($item->filename) : 'ID: ' . $this->escape($item->id)); ?>
                        </a>
						<?php else : ?>
						<?php echo ($this->escape($item->filename) ? $this->escape($item->filename) : 'ID: ' . $this->escape($item->id)); ?>
						<?php endif; ?>
                    </div>
                    <div class="pull-left">
						<?php
						if (BIBLESTUDY_CHECKREL)
						{
							// Create dropdown items
							if ($item->published) :
								JHtml::_('dropdown.unpublish', 'cb' . $i, 'mediafilelist.');
							else :
								JHtml::_('dropdown.publish', 'cb' . $i, 'mediafilelist.');
							endif;

							JHtml::_('dropdown.divider');

							if ($archived) :
								JHtml::_('dropdown.unarchive', 'cb' . $i, 'mediafilelist.');
							else :
								JHtml::_('dropdown.archive', 'cb' . $i, 'mediafilelsit.');
							endif;

							if ($trashed) :
								JHtml::_('dropdown.untrash', 'cb' . $i, 'mediafilelist.');
							else :
								JHtml::_('dropdown.trash', 'cb' . $i, 'mediafilelist.');
							endif;

							// Render dropdown list
							echo JHtml::_('dropdown.render');
						}
						?>
                    </div>
                </td>
                <td class="nowrap has-context">
                    <div class="pull-left">
						<?php echo $this->escape($item->studytitle); ?>
                    </div>
                </td>
                <td class="small hidden-phone">
					<?php echo $this->escape($item->mediaType); ?>
                </td>
                <td class="small hidden-phone">
					<?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td class="nowrap has-context">
                    <div class="pull-left">
						<?php echo $this->escape($item->plays); ?>
                    </div>
                </td>
                <td class="nowrap has-context">
                    <div class="pull-left">
						<?php echo $this->escape($item->downloads); ?>
                    </div>
                </td>
                <td class="center hidden-phone">
					<?php echo (int) $item->id; ?>
                </td>
            </tr>
				<?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
    </div>
</form>
