<?php

/*
 * @since 7.1.0
 * @author Tom Fuller
 * @desc Form for exporting and importing template settings and files
 */
//No Direct Access
defined('_JEXEC') or die;

?>
<form enctype="multipart/form-data" action="index.php" method="post"
      name="adminForm">
   <div class="width-100 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_EXPORT'); ?></legend>
                <ul>
                    <li><?php echo $this->form->getLabel('template_export', 'params'); ?></td><?php echo $this->form->getInput('template_export', 'params'); ?></li>
                </ul>
            </fieldset>
   <div class="width-100 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_TPL_IMPORT'); ?></legend>
                <ul>
                    <li> <input class="input_box" id="template_import" name="template_import" type="file" size="57" /></li>
                </ul>
            </fieldset>                      
       
   <input type="submit" name="Submit" value="Submit" />
    <input type="hidden" name="option" value="com_biblestudy" />
    <input type="hidden" name="task" value="template.exportimport" />
    <input type="hidden" name="controller" value="template" />

</form>