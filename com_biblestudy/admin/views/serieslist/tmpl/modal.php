<?php defined('_JEXEC') or die('Restricted access'); 
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
$function = JRequest::getVar('function', 'jSelectStudy');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=serieslist&layout=modal&tmpl=component'); ?>" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'JBS_CMN_ID' ); ?>
			</th>
				
			<th>
				<?php echo JText::_( 'JBS_CMN_SERIES' ); ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="5">
				<?php echo $row->id; ?>
			</td>
			
			 <td><a class="pointer" onclick="if (window.parent) window.parent.<?php echo $function;?>('<?php echo $row->id; ?>', '<?php echo $row->series_text; ?>');">
		<?php echo $row->series_text; ?></a></td>

		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>


<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
