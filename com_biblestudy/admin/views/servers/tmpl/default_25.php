<?php
/**
 * Default
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=servers'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-select fltrt">
            <select name="filter_published" class="inputbox"
                    onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?>
                </option>

                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
            </select>
        </div>
    </fieldset>
    <div id="editcell">
        <table class="adminlist">
            <thead>
                <tr>
                    <th width="1%">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                    </th>
                    <th width="8%" align="center">
                        <?php echo JText::_('JBS_CMN_PUBLISHED'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JBS_CMN_SERVER'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JBS_SVR_SERVER_NAME'); ?>
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
                $row = &$this->items[$i];
                $checked = JHTML::_('grid.id', $i, $row->id);
                $link = JRoute::_('index.php?option=com_biblestudy&task=server.edit&id=' . (int) $item->id);
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td>
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td align="center">
                        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'servers.', true, 'cb', '', ''); ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>"><?php echo $row->server_path; ?></a>
                    </td>
                    <td>
                        <?php echo $row->server_name; ?>
                    </td>
                    <td>
                        <?php echo $item->id; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden"name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>

</form>