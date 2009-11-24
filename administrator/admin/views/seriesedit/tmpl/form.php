<?php defined('_JEXEC') or die('Restricted access'); 
$editor =& JFactory::getEditor();
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable" width="100%">
		<tr>
			<td align="right" class="key">
				<label for="series">
					<?php echo JText::_( 'Series Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="series_text" id="series_text" size="100" maxlength="250" value="<?php echo $this->seriesedit->series_text;?>" />
			</td>
			</tr>
			<tr><td class="key"><?php echo JText::_('Teacher');?></td>
            <td><?php echo $this->lists['teacher'];?></td>
            </tr>
            <tr><td class="key"><?php echo JText::_('Thumbnail');?></td>
            <td>
				<?php echo $this->lists['series_thumbnail'];?>
			</td>
		</tr>
		<tr><td class="key"><?php echo JText::_('Series Thumbnail Image');?></td>
            <td><img id="imgseries_thumbnail" src="<?php echo '../images/'.$this->admin_params->get('series_imagefolder', 'stories').'/'.$this->seriesedit->series_thumbnail;?>" /></td></tr>
        <tr>
				<td class="key"><?php echo JText::_('Description');?></td>
				<td> <?php echo $editor->display('description', $this->seriesedit->description, '100%', '400', '70', '15'); ?></td>
            <td>
            </tr>
            
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->seriesedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="seriesedit" />
</form>
