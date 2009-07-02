<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		
    <table class="admintable">
      <tr>
        <td width="100" align="left" class="key"><label for="media"> <?php echo JText::_( 'Media Extensions and Images' ); ?>: 
          </label></td>
        <td width="100" align="Left" ></td>
      </tr>
      <tr>
        <td class="key"><?php echo JText::_( 'Published');?></td>
        <td> <select name="published" id="published">
		<?php if ($this->mediaedit->published == 1) {
		echo '<OPTION value="1"';
		echo 'selected';
		echo '>'.JText::_( 'Yes').'</OPTION>';
		echo '<OPTION value="0">'.JText::_( 'No').'</OPTION>';
		}
		else {
		echo '<OPTION value="0"';
		echo 'selected';
		echo '>'.JText::_( 'No').'</OPTION>';
		echo '<OPTION value="1">'.JText::_( 'Yes').'</OPTION>';
		}
		?>	
		</select> 
        </td>
      </tr>
      <tr>
        <td class="key"><?php echo JText::_( 'Name for Image');?></td>
        <td> <input class="text_area" type="text" name="media_image_name" id="media_image_name" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_image_name;?>" /> 
        </td>
      </tr>
       <tr>
        <td class="key"><?php echo JText::_( 'Description');?></td>
        <td> <input class="text_area" type="text" name="media_text" id="media_text" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_text;?>" /> 
        </td>
      </tr>
      <tr><td class="key"><?php echo JText::_('Choose an image');?></td><td><?php echo $this->lists['media']; echo '  <a  href="index.php?option=com_biblestudy&view=admin&layout=form" target="_blank">'.JText::_('Set default folder here').'</a>';?></td>
      </tr>
      
      <tr>
        <td class="key"><?php echo JText::_( 'ALT Text for Image');?></td>
        <td> <input class="text_area" type="text" name="media_alttext" id="media_alttext" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_alttext;?>" /> 
        </td>
      </tr>
    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->mediaedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="mediaedit" />
</form>
