<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
<table>
<tr>
<td>	
		<?php echo $this->lists['podcast_id'];?>
		<?php echo $this->lists['sorting'];?>
        <?php echo $this->lists['studies'];?></td>

</tr>
</table>
<div id="editcell">
	<table class="adminlist">
      <thead>
        <tr> 
          <th width="5"> <?php echo JText::_( 'ID' ); ?> </th>
          <!--<th width="20"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php //echo count( $this->rows ); ?>);" /> 
          </th>
          <th width="20" align="center"> <?php //echo JText::_( 'Row' ); ?> 
          </th>-->
		  <th> <?php echo JText::_( 'Edit Media File Record' ); ?> </th>
          <th><?php echo JText::_( 'Edit Podcast' ); ?></th>
          <th><?php echo JText::_( 'Mime Type' ); ?></th>
          <th><?php echo JText::_( 'Scripture' ); ?></th>
		  <th><?php echo JText::_( 'Edit Study' ); ?></th>
        </tr>
      </thead>
      <?php
	$k = 0;
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	{
		$row = &$this->rows[$i];
		
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=' . $option . '&controller=mediafilesedit&task=edit&cid[]='. $row->id );
		$podlink	= JRoute::_( 'index.php?option=' . $option . '&controller=podcastedit&task=edit&cid[]=' . $row->pid );
		$studylink	= JRoute::_( 'index.php?option=' . $option . '&controller=studiesedit&task=edit&cid[]=' . $row->sid );
		$published 	= JHTML::_('grid.published', $row, $i );
		$date	= JHTML::_('date',  $row->createdate, JText::_('DATE_FORMAT_LC3') , '$offset');
		?>
      <tr class="<?php echo "row$k"; ?>"> 
        <td> <?php echo $this->pagination->getRowOffset( $i ); ?> </td>
        <!--<td> <?php //echo $checked; ?> </td>
        <td align="center"> <?php //echo $published; ?> </td>-->
		<td> <a href="<?php echo $link; ?>"><?php echo $date; ?></a> </td>
        <td> <a href="<?php echo $podlink; ?>"><?php echo $row->ptitle; ?></a></td>
        <td> <?php echo $row->mtext; ?> </td>
        <td><?php echo $row->bname; echo ' '; echo $row->chapter_begin; echo ':'; echo $row->verse_begin; echo '-'; echo $row->chapter_end; echo ':'; echo $row->verse_end;?></td>
		<td> <a href="<?php echo $studylink;?>"><?php echo $row->stitle; ?></a></td>
		
      </tr>
      <?php
		$k = 1 - $k;
	}
	?>

      <tfoot><tr>
      <td colspan="12"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
    </table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="mediafileslist" />
</form>
