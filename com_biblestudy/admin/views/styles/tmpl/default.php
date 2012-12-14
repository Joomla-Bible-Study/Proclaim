<?php
/**
 * Default
 * @since 7.1.0
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
if (BIBLESTUDY_CHECKREL) {
    JHtml::_('bootstrap.tooltip');
    JHtml::_('dropdown.init');
    JHtml::_('formbehavior.chosen', 'select');
}
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=styles'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty($this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
            <?php endif; ?>
            <div id="filter-bar" class="btn-toolbar">
                <?php if (!BIBLESTUDY_CHECKREL) { ?>
                    <div class="filter-select fltrt">

                        <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                            <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                            <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
                        </select>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"> </div>

            <table class="table table-striped adminlist" id="articleList">
                <thead>
                    <tr>
                        <th width="1%">
                <input type="checkbox" name="checkall-toggle" value=""
                       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
            </th>
                        <th width="1%" style="min-width:55px" class="nowrap center">
                            <?php echo JText::_('JBS_CMN_PUBLISHED'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('JBS_STYLE_FILENAME'); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JText::_('JGRID_HEADING_ID'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($this->items as $i => $item) :
                        $link = JRoute::_('index.php?option=com_biblestudy&task=style.edit&id=' . (int) $item->id);
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td width="20">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td width="20" align="center">
                                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'styles.', true, 'cb', '', ''); ?>
                            </td>
                            <td>
                                <a href="<?php echo $link; ?>"><?php echo $item->filename; ?></a>
                            </td>
                            <td>
                                <?php echo $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo $this->pagination->getListFooter(); ?>

            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
</form>