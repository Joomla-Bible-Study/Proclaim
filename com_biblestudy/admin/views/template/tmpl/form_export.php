<?php
/**
 * Form for exporting and importing template settings and files
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
?>
<script type="text/javascript">
    Joomla.submitbutton3 = function(pressbutton) {
        var form = document.getElementById('adminForm');
        form.tooltype.value = 'export';
        form.task = 'template.export_import';
        form.submit();
    }

    Joomla.submitbutton4 = function(pressbutton) {
        var form = document.getElementById('adminForm');
        form.tooltype.value = 'import';
        form.task = 'template.export_import';
        form.submit();
    }
</script>
<div class="width-100 fltlft">
    <fieldset class="panelform">
        <legend><?php echo JText::_('JBS_CMN_EXPORT'); ?></legend>
        <ul>
            <li><?php echo $this->form->getLabel('template_export', 'params'); ?></td><?php echo $this->form->getInput('template_export', 'params'); ?>
            <input type="submit" value="Submit" onclick="Joomla.submitbutton('template.template_export')"/></li>
        </ul>
    </fieldset>
</div>
<div class="width-100 fltlft">
    <fieldset class="panelform">
        <legend><?php echo JText::_('JBS_CMN_IMPORT'); ?></legend>
        <ul>
            <li><input class="input_box" id="template_import" name="template_import" type="file" size="57" />
            <input type="submit" value="Submit" onclick="Joomla.submitbutton('template.template_import')"/></li>
        </ul>
    </fieldset>
</div>



