<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">

<?php //echo $this->lists['studyid']; ?>
<div id="editcell">
	<table class="adminlist">

      <thead>
        <tr> 
       
          <th width="5"> <?php echo JText::_( 'JBS_CMN_ROW' ); ?> </th>
          <th width="20"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /> </th>
          <th width="20" align="center"> <?php echo JHTML::_('grid.sort',  'JBS_CMN_PUBLISHED', 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>
          <th width = "20"> <?php echo JHTML::_('grid.sort',  'JBS_CMN_ID', 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>

          <th width="200"> <?php  echo JHTML::_('grid.sort',  'JBS_CMN_STUDY_TITLE', 's.studytitle', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
          <th width = "100"><?php echo JText::_('JBS_CMT_FULL_NAME'); ?></th>
          <th width = "100">  <?php echo JHTML::_('grid.sort',  'JBS_CMT_COMMENT_DATE', 'c.comment_date', $this->lists['order_Dir'], $this->lists['order'] ); ?> </th>
          
        </tr>
      </thead>
      <?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&controller=commentsedit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );
		?>
      <tr class="<?php echo "row$k"; ?>"> 
        <td> <?php echo $this->pagination->getRowOffset( $i ); ?> </td>
        <td width="20"> <?php echo $checked; ?> </td>
        <td align="center" width="20"> <?php echo $published; ?> </td>
        <td width="20"><?php echo $row->id; ?> </td>
        
        <td> <a href="<?php echo $link; ?>"><?php echo $row->studytitle.' - '.$row->bookname.' '.$row->chapter_begin; ?></a> </td>
        <td> <?php echo $row->full_name; ?> </td>
        <td> <?php echo $row->comment_date; ?> </td>
        
      </tr>
      <?php
		$k = 1 - $k;
	}
	?>
    
      <tfoot><tr>
      <td colspan="10"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
    </table>


</div>
    <!--<table>    <tr><td>Pagination: <?php //print_r($this->pagination);?></td></tr></table>-->
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="commentsedit" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
