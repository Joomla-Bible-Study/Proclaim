<?php
defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="col100">
		<fieldset class="adminform">
			<legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
		    <table class="admintable">
		    	<tr>
			        <td width="100" align="right" class="key">
                                        <label for="bookname"> <?php echo JText::_('JBS_BOK_BOOK_NAME'); ?></label>
			        </td>
			        <td>
		        		<input class="text_area" type="text" name="bookname" id="bookname" size="100" maxlength="250" value="<?php echo $this->booksedit->bookname;?>" />
		       		</td>
		     	</tr>
		      	<tr>
		      		<td align="right" class="key">
                                        <label for="booknumber"><?php echo JText::_('JBS_BOK_BOOK_NUMBER'); ?></label>
		      		</td>
		        <td>
		        	<input class="text_area" type="text" name="booknumber" id="booknumber" size="100" maxlength="250" value="<?php echo $this->booksedit->booknumber;?>" />
		        </td>
		     	</tr>
			</table>
		</fieldset>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="id" value="<?php echo $this->booksedit->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="booksedit" />
</form>
