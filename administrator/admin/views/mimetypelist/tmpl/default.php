<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th width="20" align="center">
				<?php echo JText::_( 'Published' ); ?>
			</th>			
			<th>
				<?php echo JText::_( 'Mime Type' ); ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&controller=mimetypeedit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="5">
				<?php //echo $row->id; ?>
			 <?php echo $this->pagination->getRowOffset( $i ); ?> </td>
			<td width="20">
				<?php echo $checked; ?>
			</td>
			<td width="20" align="center">
				<?php echo $published; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->mimetext; ?></a>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
    <tfoot>
      <td colspan="12"> <?php echo $this->pagination->getListFooter(); ?> </td></tfoot>
	</table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="mimetypeedit" />
</form>
