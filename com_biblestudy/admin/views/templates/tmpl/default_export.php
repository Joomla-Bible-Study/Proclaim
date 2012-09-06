<?php
/**
 * Form for exporting and importing template settings and files
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die; ?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=templates'); ?>" method="post" name="adminForm" id="adminForm">

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
        form.task = 'templates.export_import';
        form.submit();
    }
</script>
<div class="width-100 fltlft">
    <fieldset class="panelform">
        <legend><?php echo JText::_('JBS_CMN_EXPORT'); ?></legend>
        <ul>
            <li><?php echo $this->templates; ?></td><?php  ?>
            <input type="submit" value="Submit" onclick="Joomla.submitbutton('templates.template_export')"/></li>
        </ul>
    </fieldset>
</div>
<div class="width-100 fltlft">
    <fieldset class="panelform">
        <legend><?php echo JText::_('JBS_CMN_IMPORT'); ?></legend>
        <ul>
            <li><input class="input_box" id="template_import" name="template_import" type="file" size="57" />
            <input type="submit" value="Submit" onclick="Joomla.submitbutton('templates.template_import')"/></li>
        </ul>
    </fieldset>
</div>
<input type="hidden" name="task" value=""/>
        
        <?php echo JHtml::_('form.token'); ?>
</form>


