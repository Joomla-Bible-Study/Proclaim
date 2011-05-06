<?php
/**
 * @version     $Id: default.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
 ?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=commentslist'); ?>" method="post" name="adminForm" id="adminForm">

<?php //echo $this->lists['studyid']; ?>
<div id="editcell">
	<table class="adminlist">

      <thead>
        <tr> 
          <th width="1"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /> </th>
          <th width="20" align="center"> <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?> </th>
          <th width="200"> <?php echo $this->escape($this->state->get('filter.studytitle')); ?> <?php echo JText::_('JBS_CMN_STUDY_TITLE'); ?></th>
          <th width = "100"><?php echo JText::_('JBS_CMT_FULL_NAME'); ?></th>
          <th width = "100">  <?php echo $this->escape($this->state->get('filter.studydate')); ?> <?php echo JText::_('JBS_CMN_STUDY_DATE'); ?> </th>       
        </tr>
      </thead>
      <?php
foreach ($this->items as $i => $item) :
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&task=commentsedit.edit&id='. (int) $item->id );
		?>
<tr class="row<?php echo $i % 2; ?>">
                                <td width="1">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
        <td align="center" width="20">
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'commentslist.', true, 'cb', '', ''); ?>
        </td>
        <td> <a href="<?php echo $link; ?>"><?php echo $item->studytitle.' - '.JText::_($item->bookname).' '.$item->chapter_begin; ?></a> </td>
        <td> <?php echo $item->full_name; ?> </td>
        <td> <?php echo $item->comment_date; ?> </td>
      </tr>
      <?php endforeach; ?>
    
      <tfoot>
            <tr>
                <td colspan="9">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>


</div>
<input type="hidden" name="task" value=""/>
                    <input type="hidden" name="boxchecked" value="0"/>
                    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
                    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
