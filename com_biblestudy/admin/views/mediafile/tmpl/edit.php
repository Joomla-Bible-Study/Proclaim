<?php
/**
 * @version     $Id: form.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
jimport('joomla.application.component.helper');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
$params = $this->form->getFieldsets('params');
?>
<script language="javascript" type="text/javascript">
    function sizebutton(remotefilesize)
    {
        var objTB = document.getElementById("size");
        objTB.value = remotefilesize;
    }
    
function showupload() {
    var id = 'SWFUpload_0';
	if (document.adminForm.upload_server.value != '' && document.adminForm.upload_folder.value != '')
		{document.getElementById(id).style.display = 'inline';}
		else {document.getElementById(id).style.display = 'none';}
	}

if (window.addEventListener){
 window.addEventListener('load', showupload, false);
} else if (window.attachEvent){
 window.attachEvent('load', showupload);
}

</script>
<form
    action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="width-65 fltlft">
        <fieldset class="panelform">
            <legend>

                <?php echo JText::_('JBS_MED_MEDIA_FILES_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>

                    <?php echo $this->form->getInput('published'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('createdate'); ?>

                    <?php echo $this->form->getInput('createdate'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('study_id'); ?>

                    <?php echo $this->form->getInput('study_id'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('podcast_id'); ?>

                    <?php echo $this->form->getInput('podcast_id', null, empty($this->item->study_id) ? $this->admin->params['podcast'] : null); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('link_type'); ?>

                    <?php echo $this->form->getInput('link_type', null, empty($this->item->study_id) ? $this->admin->params['download'] : $this->item->link_type); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('ordering'); ?>

                    <?php echo $this->form->getInput('ordering'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('comment'); ?>

                    <?php echo $this->form->getInput('comment'); ?></li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend>

                <?php echo JText::_('JBS_MED_MEDIA_FILES_LINKER'); ?></legend>
            <ul class="adminformlist">
                
                    <li>
                        <?php echo $this->form->getLabel('docMan_id'); ?>

                        <?php echo $this->form->getInput('docMan_id'); ?></li>
                
                <li>
                    <?php echo $this->form->getLabel('article_id'); ?>

                    <?php echo $this->form->getInput('article_id'); ?></li>
               
                    <li>
                        <?php echo $this->form->getLabel('virtueMart_id'); ?>

                        <?php echo $this->form->getInput('virtueMart_id'); ?></li>
               
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend>

                <?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('player'); ?>

                    <?php echo $this->form->getInput('player'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('popup'); ?>

                    <?php echo $this->form->getInput('popup'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('mediacode'); ?>

                    <?php echo $this->form->getInput('mediacode'); ?></li>
            </ul>
        </fieldset>


    </div>
    <div class="width-35 fltrt">
        <fieldset class="panelform">
            <legend>

                <?php echo JText::_('JBS_MED_MEDIA_FILES'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('plays'); ?>

                    <?php echo $this->form->getInput('plays'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('downloads'); ?>

                    <?php echo $this->form->getInput('downloads'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('spacer'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('server'); ?>

                    <?php echo $this->form->getInput('server', null, empty($this->item->study_id) ? $this->admin->params['server'] : $this->item->server); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('path'); ?>

                    <?php echo $this->form->getInput('path', null, empty($this->item->study_id) ? $this->admin->params['path'] : $this->item->path); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('filename'); ?>

                    <?php echo $this->form->getInput('filename'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('size'); ?>

                    <?php echo $this->form->getInput('size'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('special'); ?>

                    <?php echo $this->form->getInput('special', null, empty($this->item->study_id) ? $this->admin->params['target'] : $this->item->special); ?>
                </li>
            </ul>
            <table class="adminlist">
                        <thead>
                        <th align="center" colspan="2"><?php echo JText::_('JBS_STY_UPLOAD');?></th>
                        </thead>
                        <tbody>
                        <tr><td>
                             <?php echo $this->upload_server;?></td>
                        </td></tr>
                        <tr><td>
                             <?php echo $this->upload_folder;?></td>
                        </td></tr>
                            <tr>
                                <td> <?php if ($this->admin->params['uploadtype'] == 1){ ?>
                                    <div id="swfuploader">
                                    <div class="fieldset flash" id="fsUploadProgress">
                                    </div> 	
                                    <div>
                                    <span id="spanButtonPlaceHolder"></span>
                                            <input id="btnCancel" type="button" value="<?php echo JText::_('JBS_STY_CANCEL');?>" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" />
                                             
                                    </div>
                                        </div> <?php } ?>
                                  <?php if ($this->admin->params['uploadtype'] == 0){ ?>
                                    <input type="file" name ="uploadfile" value="" /><button type="button" onclick="submitbutton('upload')"> 
                                        <?php echo JText::_('JBS_STY_UPLOAD_BUTTON');?> </button> <?php } ?>
                                    </td><td></td>
                            </tr>
                        </tbody>
                    </table>
        </fieldset>
    </div>
    <div class="width-35 fltrt">
        <fieldset class="panelform">
            <legend>

                <?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('media_image'); ?>

                    <?php echo $this->form->getInput('media_image'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('mime_type'); ?>

                    <?php echo $this->form->getInput('mime_type', null, empty($this->item->study_id) ? $this->admin->params['mime'] : $this->item->mime_type); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <div class="width-35 fltrt">


        <?php foreach ($params as $name => $fieldset):
            if (isset($fieldset->description) && trim($fieldset->description)): ?>
                <p class="tip">


                    <?php echo $this->escape(JText::_($fieldset->description)); ?>
                </p>




            <?php endif; ?>
            <fieldset class="panelform" >
                <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
                <ul class="adminformlist">
                    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                        <li><?php echo $field->label; ?><?php echo $field->input; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </fieldset>
        <?php endforeach; ?>
    </div>
    <div class="clr"></div>





    <?php if ($this->canDo->get('core.admin')): ?>
        <div class="width-100 fltlft">
            <?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

            <?php echo JHtml::_('sliders.panel', JText::_('JBS_CMN_FIELDSET_RULES'), 'access-rules'); ?>

            <fieldset class="panelform">
                <?php echo $this->form->getLabel('rules'); ?>
                <?php echo $this->form->getInput('rules'); ?>
            </fieldset>

            <?php echo JHtml::_('sliders.end'); ?>
        </div>
    <?php endif; ?>
    <input type="hidden" name="flupfile" value ="" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>