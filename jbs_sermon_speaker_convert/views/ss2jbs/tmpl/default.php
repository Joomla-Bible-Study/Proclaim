<?php
defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="col100">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Conversion Form'); ?></legend>
		    <table class="admintable">
            <tr>
                <td class="key">
                <label for="version"><?php echo JText::_('Version check');?></label>
                </td>
                
                <td>
                <?php echo JText::_('Joomla Bible Study Version found').': '.$this->jbsversion; echo ' - '.JText::_('Minimum Version Required is 6.2.0');?>
                <br />
                <?php echo JText::_('Sermon Speaker Version found').': '.$this->ssversion; echo ' - '.JText::_('Minimum Version Required is 3.4.2');?>
                </td>
            </tr>
		    	<tr>
			        <td width="100" align="right" class="key">
                                        <label for="conversion"> <?php echo JText::_('Perform the Conversion'); ?></label>
			        </td>
			        <td>
		        		<?php echo '<a href="index.php?option=com_ss2jbs&view=ss2jbs&layout=results">Click here to convert</a>';?>
                        
		       		</td>
		     	</tr>
		      	
			</table>
		</fieldset>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="option" value="com_ss2jbs" />
	
	<input type="hidden" name="task" value="" />
	
</form>
