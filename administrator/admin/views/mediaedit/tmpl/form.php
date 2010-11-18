<?php defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			if (pressbutton == 'cancel')
			{
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.media_image_name.value == "")
			{
				alert( "<?php echo JText::_( 'Please fill in an image name.', true ); ?>" );
			}
			else
			{
				submitform( pressbutton );
			}
		}
        </script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>


    <table class="admintable">
      <tr><td ></td>
        <td align="left" ><label for="media"><strong> <?php echo JText::_( 'Media Extensions and Images' ); ?> </strong>
          </label></td>

      </tr>
      <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_PUBLISHED');?></td>
        <td>
		<?php echo $this->lists['published'];?>

        </td>
      </tr>
      <tr>
        <td class="key"><?php echo JText::_( 'Name for Image');?></td>
        <td> <input class="text_area" type="text" name="media_image_name" id="media_image_name" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_image_name;?>" />
        </td>
      </tr>
       <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_DESCRIPTION');?></td>
        <td> <input class="text_area" type="text" name="media_text" id="media_text" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_text;?>" />
        </td>
      </tr>
      <tr><td class="key"><?php echo JText::_('Choose an image');?></td><td><?php echo $this->lists['media']; echo '  '.JText::_('Current folder').': '.$this->directory.' -  <a  href="index.php?option=com_biblestudy&view=admin&layout=form" target="_blank">'.JText::_('Set default folder here').'</a>';?><br /><?php echo JText::_('This field will be used instead of below if image selected');?></td>
      </tr>
      <tr><td class="key"><?php echo JText::_('path to image (start from Joomla root - no leading /)');?></td><td><input class="text_area" type="text" name="media_image_path" id="media_image_path" size="150" value="<?php echo $this->mediaedit->media_image_path;?>" /></td></tr>
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
