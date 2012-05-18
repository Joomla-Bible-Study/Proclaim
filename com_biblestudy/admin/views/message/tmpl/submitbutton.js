/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

Joomla.submitbutton = function(task)
{
    if (task == '')
    {
        return false;
    }
    else if (task == 'upload')
    {
        if (document.adminForm.path.value == '')
        {
            alert("<?php echo JText::_('JBS_MED_PATH_OR_FOLDER');?>");
        }
        else if (document.adminForm.server.value == '' )
        {
            alert("<?php echo JText::_('JBS_MED_SERVER');?>");
        }
        else {
            Joomla.submitform(task);
            return true;
        }
    }
}