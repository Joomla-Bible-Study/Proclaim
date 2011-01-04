<?php 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
//call to biblestudy.defines.php
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
//require_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.debug.php');
//require_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.version.php');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
// params not working well not sure what carectier is cosing the problem
$params = $this->form->getFieldsets();
$db = JFactory::getDBO();

?>
  <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate"> 
<div class="width-100 fltlft">

<?php echo JHtml::_('sliders.start', 'biblestudy-slider'); ?>
    <?php foreach ($params as $name => $fieldset): ?>
            <?php echo JHtml::_('sliders.panel', JText::_($fieldset->label), $name.'-params');?>
    <?php if (isset($fieldset->description) && trim($fieldset->description)): ?>
            <p class="tip"><?php echo $this->escape(JText::_($fieldset->description));?></p>
    <?php endif;?>
            <fieldset class="panelform" >
                    <ul class="adminformlist">
        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                            <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
        <?php endforeach; ?>
                    </ul>
            </fieldset>
            
            
    <?php endforeach; ?>
 
 <?php echo JHtml::_('sliders.end'); ?>
 <input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->admin->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="admin" />
</form>
	<?php echo JHtml::_('form.token'); ?>
 </div>
</form>