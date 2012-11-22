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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_studytitle"><?php echo JText::_('JBS_CMN_STUDY_TITLE'); ?>:
            </label> <input type="text" name="filter_studytitle" id="filter_studytitle"
                            value="<?php echo $this->escape($this->state->get('filter.studytitle')); ?>"
                            title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>" />
            <button type="submit" class="btn">

                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button"
                    onclick="document.id('filter_studytitle').value='';this.form.submit();">

                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_book" class="inputbox"
                    onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JBS_CMN_SELECT_BOOK'); ?></option>
                <?php echo JHtml::_('select.options', $this->books, 'value', 'text', $this->state->get('filter.book')); ?>
            </select> <select name="filter_teacher" class="inputbox"
                              onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JBS_CMN_SELECT_TEACHER'); ?></option>
                <?php echo JHtml::_('select.options', $this->teachers, 'value', 'text', $this->state->get('filter.teacher')); ?>
            </select> <select name="filter_series" class="inputbox"
                              onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JBS_CMN_SELECT_SERIES'); ?></option>
                <?php echo JHtml::_('select.options', $this->series, 'value', 'text', $this->state->get('filter.series')); ?>
            </select> <select name="filter_message_type"
                              class="inputbox" onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JBS_CMN_SELECT_MESSAGE_TYPE'); ?></option>
                <?php echo JHtml::_('select.options', $this->messageTypes, 'value', 'text', $this->state->get('filter.messageType')); ?>
            </select> <select name="filter_year" class="inputbox"
                              onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JBS_CMN_SELECT_YEAR'); ?></option>
                <?php echo JHtml::_('select.options', $this->years, 'value', 'text', $this->state->get('filter.year')); ?>
            </select> <select name="filter_published" class="inputbox"
                              onchange="this.form.submit()">
                <option value="">
                    <?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true); ?>
            </select>
            <select name="filter_language" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
            </select>
        </div>
    </fieldset>
    <div class="clr"></div>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%"><input type="checkbox" name="checkall-toggle"
                                      value="" onclick="checkAll(this)" />
                </th>
                <th width="8%">
                    <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'study.published', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_DATE', 'study.studydate', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_SCRIPTURE', 'book.bookname', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_TEACHER', 'teacher.teachername', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_MESSAGE_TYPE', 'messageType.message_type', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_SERIES', 'series.series_text', $listDirn, $listOrder); ?>
                </th>
                <th align="center">
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_HITS', 'study.hits', $listDirn, $listOrder); ?>
                </th>
                <th align="center">
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_PLAYS', 'mediafile.plays', $listDirn, $listOrder); ?>
                </th>
                <th align="center">
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_DOWNLOADS', 'mediafile.downloads', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                </th>
                <th width="1%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'mediafile.id', $listDirn, $listOrder); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="13">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <?php
        foreach ($this->items as $i => $item) :
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'messages.', true, 'cb', '', ''); ?>
                </td>
                <td class="center">
                    <?php echo JHtml::_('date', $item->studydate, JText::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td class="center">
                    <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=message.edit&id=' . (int) $item->id); ?>">
                        <?php echo $this->escape($item->studytitle); ?>
                    </a>
                    <p class="smallsub">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->bookname) . ' ' . $this->escape($item->chapter_begin) . ':' . $this->escape($item->verse_begin); ?>
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

                <td class="center">
                    <?php echo $this->escape($item->hits); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->totalplays); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->totaldownloads); ?>
                </td>
                <td class="center">
                    <?php if ($item->language == '*'): ?>
                        <?php echo JText::alt('JALL', 'language'); ?>
                    <?php else: ?>
                        <?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
                    <?php endif; ?>
                </td>
                <td class="center">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>