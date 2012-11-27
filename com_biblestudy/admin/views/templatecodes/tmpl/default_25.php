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
JHtml::_('script', 'system/multiselect.js', false, true);
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state');
$saveOrder = $listOrder == 'location.ordering';
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=templatecodes'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-select fltrt">

            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
            </select>
        </div>
    </fieldset>
    <div id="editcell">
        <table class="adminlist">
            <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                    </th>
                    <th width="20" align="center">
                        <?php echo JText::_('JBS_CMN_PUBLISHED'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JBS_TPLCODE_FILENAME'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JBS_TPLCODE_TYPE'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JGRID_HEADING_ID'); ?>
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

            <?php
            foreach ($this->items as $i => $item) :
                $link = JRoute::_('index.php?option=com_biblestudy&task=templatecode.edit&id=' . (int) $item->id);
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td width="20">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td width="20" align="center">
                        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'templatecodes.', true, 'cb', '', ''); ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>"><?php echo $item->filename; ?></a>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>"><?php echo $item->typetext; ?></a>
                    </td>
                    <td>
                        <?php echo $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>