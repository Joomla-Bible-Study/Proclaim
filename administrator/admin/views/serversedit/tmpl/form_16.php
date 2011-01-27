<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('Restricted access'); 
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('server_name'); ?>
					<?php echo $this->form->getInput('server_name'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('server_path'); ?>
					<?php echo $this->form->getInput('server_path'); ?>
				</li> 
			</ul>

		</fieldset>
	</div>
  <input type="hidden" name="task" value="" />
  <?php echo JHtml::_('form.token'); ?>
</form>
