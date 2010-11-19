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
				alert( "<?php echo JText::_( 'JBS_MED_ENTER_IMAGE_NAME', true ); ?>" );
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
        <td align="left" ><label for="media"><strong> <?php echo JText::_( 'JBS_MED_EXTENSIONS_IMAGES' ); ?> </strong>
          </label></td>

      </tr>
      <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_PUBLISHED');?></td>
        <td>
		<?php echo $this->lists['published'];?>

        </td>
      </tr>
      <tr>
        <td class="key"><?php echo JText::_( 'JBS_MED_NAME_IMAGE');?></td>
        <td> <input class="text_area" type="text" name="media_image_name" id="media_image_name" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_image_name;?>" />
        </td>
      </tr>
       <tr>
        <td class="key"><?php echo JText::_( 'JBS_CMN_DESCRIPTION');?></td>
        <td> <input class="text_area" type="text" name="media_text" id="media_text" size="100" maxlength="250" value="<?php echo $this->mediaedit->media_text;?>" />
        </td>
      </tr>
      <tr><td class="key"><?php echo JText::_('JBS_MED_CHOOSE_IMAGE');?></td><td><?php echo $this->lists['media']; echo '  '.JText::_('JBS_CMN_CURRENT_FOLDER').': '.$this->directory.' -  <a  href="index.php?option=com_biblestudy&view=admin&layout=form" target="_blank">'.JText::_('JBS_CMN_SET_DEFAULT_FOLDER').'</a>';?><br /><?php echo JText::_('JBS_CMN_THIS_FIELD_IS_USED_INSTEAD_BELOW');?></td>
      </tr>
      <tr><td class="key"><?php echo JText::_('JBS_MED_PATH_IMAGE');?></td><td><input class="text_area" type="text" name="media_image_path" id="media_image_path" size="150" value="<?php echo $this->mediaedit->media_image_path;?>" /></td></tr>
      <tr>
        <td class="key"><?php echo JText::_( 'JBS_MED_ALT_TEXT_IMAGE');?></td>
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
