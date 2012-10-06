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
			if (form.folderpath.value == "")
			{
				alert( "<?php echo JText::_( 'Please fill in a folder path.', true ); ?>" );
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
		<legend><?php echo JText::_( 'Details' ); ?></legend>


    <table class="admintable">
      <tr>
        <td width="100" align="right" class="key"> <label for="folders"> <?php echo JText::_( 'Folder Name' ); ?>
          </label> </td>
        <td> <input class="text_area" type="text" name="foldername" id="foldername" size="100" maxlength="250" value="<?php echo $this->foldersedit->foldername;?>" />
        </td>
      </tr>
      <tr>
        <td align="right" class="key"> <label for="folderpath"><?php echo JText::_( 'Folder Path - use leading and trailing "/"' ); ?></label>
		</td>
        <td> <input class="text_area" type="text" name="folderpath" id="folderpath" size="100" maxlength="250" value="<?php echo $this->foldersedit->folderpath;?>" />
		</td>
      </tr>
    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->foldersedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="foldersedit" />
</form>
