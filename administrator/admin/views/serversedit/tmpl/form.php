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
			if (form.server_path.value == "")
			{
				alert( "<?php echo JText::_( 'JBS_SVR_ENTER_SERVER', true ); ?>" );
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
      <tr>
        <td width="100" align="right" class="key"> <label for="servername"> <?php echo JText::_( 'Server Name' ); ?>:
          </label> </td>
        <td> <input class="text_area" type="text" name="server_name" id="server_name" size="100" maxlength="250" value="<?php echo $this->serversedit->server_name;?>" />
        </td>
      </tr>
      <tr>
        <td align="right" class="key"><label for="serverpath"> <?php echo JText::_( 'Server Path (start with www - no trailing /)' ); ?>
          </label></td>
        <td><input class="text_area" type="text" name="server_path" id="server_path" size="100" maxlength="250" value="<?php echo $this->serversedit->server_path;?>" /> </td>
      </tr>



    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->serversedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="serversedit" />
</form>
