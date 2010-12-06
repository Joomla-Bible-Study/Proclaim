<?php defined('_JEXEC') or die('Restricted access'); 
$path1 = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'image.php');?>


<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'JBS_CMN_ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th width="20" align="center">
				<?php echo JText::_( 'JBS_CMN_PUBLISHED' ); ?>
			</th>	
            <th width="50" align="left">
            	<?php echo JText::_( 'JBS_CMN_IMAGE' );?>
             </th>
             <th align="left">
				<?php echo JText::_( 'JBS_CMN_MEDIA' ); ?>
			</th>	
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_biblestudy&controller=mediaedit&task=edit&cid[]='. $row->id );
		$published 	= JHTML::_('grid.published', $row, $i );
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td >
				<?php echo $row->id; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td  align="center">
				<?php echo $published; ?>
			</td>
            <td align="center">
            	<?php
				if (!$row->path2) { $i_path = '../'.$row->media_image_path; }
			if ($row->path2 && !$this->admin_params->get('media_imagefolder')) { $i_path = '../components/com_biblestudy/images/'.$row->path2; }
			if ($row->path2 && $this->admin_params->get('media_imagefolder')) { $i_path = '../images/'.$this->admin_params->get('media_imagefolder').DS.$row->path2;}
			$image = getImage($i_path);
			if ($image)
				{
					echo '<img src="'.$image->path.'" height="'.$image->height.'" width="'.$image->width.'" alt="'.$row->media_image_name.'">';
				}
			?>
            </td>
			<td >
				<a href="<?php echo $link; ?>"><?php echo $row->media_image_name; ?></a>
			</td>
            
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="mediaedit" />
</form>
