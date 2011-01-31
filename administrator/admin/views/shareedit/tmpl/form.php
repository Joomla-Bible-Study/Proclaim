<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php'); 
$params = $this->form->getFieldsets('params');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>
			<ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
					<?php echo $this->form->getInput('published'); ?>
                </li>
				<li>
					<?php echo $this->form->getLabel('name'); ?>
					<?php echo $this->form->getInput('name'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('shareimage'); ?>
					<?php echo $this->form->getInput('shareimage'); ?>
				</li> 
			</ul>
		</fieldset>
       <fieldset class="panelform" >
       <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
       
           <ul class="adminformlist">
           <?php foreach ($params as $name => $fieldset): ?>
                <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                                <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
                <?php endforeach; ?>
           <?php endforeach; ?>
           </ul>
       </fieldset>
     </div>
  <input type="hidden" name="task" value="" />
  <?php echo JHtml::_('form.token'); ?>
</form>
