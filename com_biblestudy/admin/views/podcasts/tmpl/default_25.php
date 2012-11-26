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
JHtml::_('script', 'system/multiselect.js', false, true);
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=podcasts'); ?>"
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
    <div class="clr"></div>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%"><input type="checkbox" name="checkall-toggle"
                                      value="" onclick="checkAll(this)" />
                </th>
                <th width="8%">
                    <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'podcast.published', $listDirn, $listOrder); ?>
                </th>
                <th align="center">
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_PODCAST', 'podcast.title', $listDirn, $listOrder); ?>
                </th>
                <th align="center">
                    <?php echo(JText::_('JBS_CMN_DESCRIPTION')); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                </th>
                <th width="1%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'podcast.id', $listDirn, $listOrder); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="6">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            foreach ($this->items as $i => $item) :
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="center">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td class="center">
                        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'podcasts.', true, 'cb', '', ''); ?>
                    </td>
                    <td class="center"><a
                            href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=podcast.edit&id=' . (int) $item->id); ?>">
                            <?php echo $this->escape($item->title); ?> </a>
                    </td>
                    <td class="center">
                        <?php echo $this->escape($item->description); ?>
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
        </tbody>
    </table>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden"name="filter_order" value="<?php echo $listOrder; ?> " />
        <input type="hidden" name="filter_order_Dir"value="<?php echo $listDirn; ?> " />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>