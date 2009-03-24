<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm">
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
            <th width="8%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',  'Order', 'ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				<?php echo JHTML::_('grid.order',  $this->items ); ?>
			</th>			
			<th>
				<?php echo JText::_( 'Teacher' ); ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&controller=teacheredit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );
		$ordering = ($this->lists['order'] == 'ordering');
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="20">
				<?php echo $row->id; ?>
			</td>
			<td width="20">
				<?php echo $checked; ?>
			</td>
			<td width="20" align="center">
				<?php echo $published; ?>
			</td>
            <td width="8%" class="order">
				<span><?php echo $this->pagination->orderUpIcon( $i, ($row->catid == @$this->items[$i-1]->catid),'orderup', 'Move Up', $ordering ); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->catid == @$this->items[$i+1]->catid), 'orderdown', 'Move Down', $ordering ); ?></span>

				<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->teachername; ?></a>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
    <tfoot>
      <tr><td colspan="10"> <?php echo $this->pagination->getListFooter(); ?> </td></tr></tfoot>
	</table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="teacheredit" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
