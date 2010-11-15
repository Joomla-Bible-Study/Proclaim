<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'JBS_CSS_CSSEDIT_INTRO' ); ?></legend>

		<table class="admintable">
        <tr>
			<td width="100" align="right" class="key">
				<label for="css">
					<?php echo JText::_( 'JBS_CSS_CSS' ); ?>:
				</label>
			</td>
			<td>
				
				<textarea name="filecontent" id="filecontent" class="inputbox" rows="40" cols="92"><?php echo $this->lists->filecontent;?></textarea>
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<div class="clr">
  
</div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="controller" value="cssedit" />

<input type="hidden" name="view" value="cssedit" />
</form>
