<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
JHtml::_('script', 'system/multiselect.js', false, true);
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'mediafile.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=mediafileslist'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_filename"><?php echo JText::_('JBS_MED_FILENAME'); ?>: </label>
            <input type="text" name="filter_filename" id="filter_filename" value="<?php echo $this->escape($this->state->get('filter.filename')); ?>" title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>" />
            <label class="filter-search-lbl" for="filter_studytitle"><?php echo JText::_('JBS_CMN_STUDY_TITLE'); ?>: </label>
            <input type="text" name="filter_studytitle" id="filter_studytitle" value="<?php echo $this->escape($this->state->get('filter.studytitle')); ?>" title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>" />

            <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_filename').value='';document.id('filter_studytitle').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_mediatypeId" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JBS_MED_SELECT_MEDIA_TYPE'); ?></option>
                <?php echo JHtml::_('select.options', $this->mediatypes, 'value', 'text', $this->state->get('filter.mediatypeId')); ?>
            </select>
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
        </div>
    </fieldset>
    <div class="clr"></div>

    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)"/>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'mediafile.published', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php
                        echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'mediafile.ordering', $listDirn, $listOrder);
                        echo JHtml::_('grid.order', $this->items, 'filesave.png', 'mediafileslist.saveorer');
                    ?>
                </th>
                <th width="20%">
                    <?php echo JHtml::_('grid.sort', 'JBS_MED_FILENAME', 'mediafile.filename', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
                    <?php echo JHtml::_('grid.sort', 'JBS_CMN_STUDY_TITLE', 'study.studytitle', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
                    <?php echo JHtml::_('grid.sort', 'JBS_MED_MEDIA_TYPE', 'mediatype.media_text', $listDirn, $listOrder); ?>
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
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="9">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <?php
        foreach($this->items as $i => $item) :
            $ordering = ($listOrder == 'mediafile.ordering');
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="center">
                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
            </td>
            <td class="center">
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'mediafileslist.', true, 'cb', '', ''); ?>
            </td>
            <td class="order">
                <?php if($listDirn == 'asc') : ?>
                    <span><?php echo $this->pagination->orderUpIcon($i, ($item->studytitle == @$this->items[$i-1]->studytitle), 'mediafileslist.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->studytitle == @$this->items[$i-1]->studytitle), 'mediafileslist.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                <?php elseif ($listDirn == 'desc') : ?>
                    <span><?php echo $this->pagination->orderUpIcon($i, ($item->studytitle == @$this->items[$i-1]->studytitle), 'mediafileslist.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->studytitle == @$this->items[$i-1]->studytitle), 'mediafileslist.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                <?php endif; ?>
                <?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
		<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
            </td>
            <td class="center">
                <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&task=mediafilesedit.edit&id='.(int)$item->id); ?>">
                    <?php echo ($this->escape($item->filename) ?  $this->escape($item->filename) : 'ID: '.$this->escape($item->id)); ?>
                </a>
            </td>
            <td class="center">
                <?php echo $this->escape($item->studytitle); ?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->mediaType); ?>
            </td>
            <td class="center">
                <?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->plays);?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->downloads);?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
