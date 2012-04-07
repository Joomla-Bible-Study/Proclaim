<?php
/**
 * @version     $Id: default.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
$listDirn = $this->state->get('list.direction');
$listOrder = $this->state->get('list.ordering');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=comment'); ?>"
    method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-select fltrt">
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
            </select>
            <select name="filter_language" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
            </select>
        </div>
    </fieldset>
    <div id="editcell">
        <table class="adminlist">

            <thead>
                <tr>
                    <th width="1"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                    </th>
                    <th width="20" align="center"> <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
                    </th>
                    <th width="200"> <?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
                    </th>
                    <th width="100"><?php echo JText::_('JBS_CMT_FULL_NAME'); ?></th>
                    <th width="100">   <?php echo JHtml::_('grid.sort', 'JBS_CMT_CREATE_DATE', 'study.studydate', $listDirn, $listOrder); ?>
                    </th>
                    <th width="5%">
                        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>

            <?php
            foreach ($this->items as $i => $item) :
                $link = JRoute::_('index.php?option=com_biblestudy&task=comment.edit&id=' . (int) $item->id);
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td width="1">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td align="center" width="20">
                        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'comments.', true, 'cb', '', ''); ?>
                    </td>
                    <td> <a href="<?php echo $link; ?>"><?php echo $item->studytitle . ' - ' . JText::_($item->bookname) . ' ' . $item->chapter_begin; ?></a> </td>
                    <td> <?php echo $item->full_name; ?> </td>
                    <td> <?php echo $item->comment_date; ?> </td>
                    <td class="center">
                        <?php if ($item->language == '*'): ?>
                            <?php echo JText::alt('JALL', 'language'); ?>
                        <?php else: ?>
                            <?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tfoot><tr>
                    <td colspan="10"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
        </table>


    </div>
    <input type="hidden" name="task" value="" /> <input type="hidden"
                                                        name="boxchecked" value="0" /> <input type="hidden"
                                                        name="filter_order" value="<?php echo $listOrder; ?>" /> <input
                                                        type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />




    <?php echo JHtml::_('form.token'); ?>
</form>