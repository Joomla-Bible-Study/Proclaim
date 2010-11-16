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
			if (form.mimetype.value == "")
			{
				alert( "<?php echo JText::_( 'JBS_MMT_ENTER_MIME_TYPE_CODE', true ); ?>" );
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
		<legend><?php echo JText::_( 'JBS_MMT_MIME_TYPE_DETAILS' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="mimetype">
                <?php echo JText::_( 'JBS_MMT_MIME_TYPE' ); ?>
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="mimetext" id="mimetext" size="32" maxlength="100" value="<?php echo $this->mimetypeedit->mimetext;?>" />
			</td>
		</tr>
        <tr>
        <td width="100" align="right" class="key">
            <label for="mimetype">
            <?php echo JText::_( 'JBS_MMT_MIME_TYPE_CODE' ); ?>
            </label>
        </td>
        <td>
        	<input class="text_area" type="text" name="mimetype" id="mimetype" size="32" maxlength="32" value="<?php echo $this->mimetypeedit->mimetype;?>" />
        </td>
        </tr>
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->mimetypeedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="mimetypeedit" />
</form>
