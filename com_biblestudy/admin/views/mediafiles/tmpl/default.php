<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if (BIBLESTUDY_CHECKREL) {
    JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
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
$saveOrder = $listOrder == 'mediafile.ordering';

if ($saveOrder && BIBLESTUDY_CHECKREL) {
    $saveOrderingUrl = 'index.php?option=com_biblestudy&task=mediafiles.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'mediafileList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
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
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=mediafiles'); ?>" method="post"
      name="adminForm" id="adminForm">
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
        <label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
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
    <?php if (!BIBLESTUDY_CHECKREL): ?>
        <div class="clearfix"></div>
        <div class="btn-group pull-right">
            <label for="filter_published" id="filter_published"
                   class="element-invisible"><?php echo JText::_('JBS_CMN_SELECT_BY'); ?></label>
            <select name="filter_published" class="input-medium" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
            </select>
        </div>

    <?php endif; ?>
</div>
<div class="clearfix"></div>

<table class="table table-striped adminlist" id="mediafileList">
    <thead>
    <tr>
        <th width="1%" class="nowrap center">
            <?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'mediafile.ordering', $listDirn, $listOrder, null, 'desc', 'JGRID_HEADING_ORDERING'); ?>
        </th>
        <th width="1%" class="nowrap">
            <input type="checkbox" name="checkall-toggle" value=""
                   title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
        </th>

        <th class="nowrap center">
            <?php echo JHtml::_('grid.sort', 'JBS_CMN_PUBLISHED', 'mediafile.published', $listDirn, $listOrder); ?>
        </th>
        <th>
            <?php echo JText::_('JBS_MED_RESOURCE_NAME'); ?>
        </th>
        <th>
            <?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
        </th>
        <!--        <th>-->
        <!--            --><?php //echo JHtml::_('grid.sort', 'JBS_MED_MEDIA_TYPE', 'mediatype.media_text', $listDirn, $listOrder); ?>
        <!--        </th>-->
        <th>
            <?php echo JHtml::_('grid.sort', 'JBS_CMN_CREATED', 'mediafile.createdate', $listDirn, $listOrder); ?>
        </th>
        <th>
            <?php echo JHtml::_('grid.sort', 'JBS_CMN_MODIFIED', 'mediafile.modified', $listDirn, $listOrder); ?>
        </th>
        <th>
            <?php echo JHtml::_('grid.sort', 'JBS_MED_ACCESS', 'mediafile.plays', $listDirn, $listOrder); ?>
        </th>
        <th>
            <?php echo JHtml::_('grid.sort', 'JBS_MED_MEDIA_FILES_STATS', 'mediafile.plays', $listDirn, $listOrder); ?>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($this->items as $i => $item) :
        $ordering = ($listOrder == 'mediafile.ordering');
        $canCreate = $user->authorise('core.create');
        $canEdit = $user->authorise('core.edit', 'com_biblestudy.mediafile.' . $item->id);
        $canEditOwn = $user->authorise('core.edit.own', 'com_biblestudy.mediafile.' . $item->id);
        $canChange = $user->authorise('core.edit.state', 'com_biblestudy.mediafile.' . $item->id);
        ?>
        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->study_id ?>">
            <td class="center">
                <?php
                if ($canChange) :
                    $disableClassName = '';
                    $disabledLabel = '';
                    if (!$saveOrder) :
                        $disabledLabel = JText::_('JORDERINGDISABLED');
                        $disableClassName = 'inactive tip-top';
                    endif;
                    ?>
                    <span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
                          title="<?php echo $disabledLabel ?>">
                                            <i class="icon-menu"></i>
                                        </span>
                    <input type="text" style="display:none" name="order[]" size="5"
                           value="<?php echo $item->ordering; ?>"
                           class="width-10 text-area-order "/>
                <?php else : ?>
                    <span class="sortable-handler inactive">
                                            <i class="icon-menu"></i>
                                        </span>
                <?php endif; ?>
            </td>
            <td class="center">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="center">
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'mediafiles.', $canChange, 'cb', '', ''); ?>
            </td>
            <td>
                <div class="pull-left">
                    <span class="label">
                        <?php echo($this->escape($item->serverConfig->name->__toString() . ' - ' . $item->serverConfig->config->media_resource->__toString())); ?>
                    </span>
                    <?php if ($canEdit || $canEditOwn) : ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=mediafile.edit&id=' . (int)$item->id); ?>">
                            <?php echo($this->escape($item->params[$item->serverConfig->config->media_resource->__toString()])); ?>
                        </a>
                    <?php else : ?>
                        <?php echo($this->escape($item->params[$item->serverConfig->config->media_resource->__toString()])); ?>
                    <?php endif; ?>
                </div>
                <div class="pull-left">
                    <?php
                    if (BIBLESTUDY_CHECKREL) {
                        // Create dropdown items
                        JHtml::_('dropdown.edit', $item->id, 'mediafile.');
                        JHtml::_('dropdown.divider');
                        if ($item->published) :
                            JHtml::_('dropdown.unpublish', 'cb' . $i, 'mediafiles.');
                        else :
                            JHtml::_('dropdown.publish', 'cb' . $i, 'mediafiles.');
                        endif;

                        JHtml::_('dropdown.divider');

                        if ($archived) :
                            JHtml::_('dropdown.unarchive', 'cb' . $i, 'mediafiles.');
                        else :
                            JHtml::_('dropdown.archive', 'cb' . $i, 'mediafiles.');
                        endif;

                        if ($trashed) :
                            JHtml::_('dropdown.untrash', 'cb' . $i, 'mediafiles.');
                        else :
                            JHtml::_('dropdown.trash', 'cb' . $i, 'mediafiles.');
                        endif;

                        // Render dropdown list
                        echo JHtml::_('dropdown.render');
                    }
                    ?>
                </div>
            </td>
            <td>
                <?php echo $this->escape($item->studytitle); ?>
            </td>
            <td>
                <?php echo $this->escape($item->serverConfig->name->__toString()); ?>
            </td>
            <td>
                <?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
            </td>
            <td>
                <?php echo JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC4')); ?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->access_level); ?>
            </td>
            <td>
                <?php echo $this->escape($item->plays); ?>
            </td>
            <td>
                <?php echo $this->escape($item->downloads); ?>
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
