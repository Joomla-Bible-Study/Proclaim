<?php
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ss2jbs'.DS.'jbs_ss_convert.php');

?>


	<div class="col100">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Conversion Results'); ?></legend>
		    <table class="admintable">
		    	<tr>
			        <td width="200" align="right" class="key">
                                        <label for="results"> <?php echo JText::_('Results of the Conversion Process'); ?></label>
			        </td>
			        <td>
		        		<?php 
                        $convert = new JBSConvert();
                        $conversion = $convert->convertSS();
                        echo $conversion;
                        ?>
		       		</td>
		     	</tr>
		      	
			</table>
		</fieldset>
	</div>

	

	

