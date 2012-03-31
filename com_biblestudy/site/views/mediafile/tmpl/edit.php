<?php
/**
 * @version     $Id: form_16.php 1395 2011-01-17 22:43:01Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;

$params = $this->form->getFieldsets('params');
// echo $this->toolbar;
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

<div class="edit">
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm"enctype="multipart/form-data">
	<div class="formelm-buttons">
			<button type="button" onclick="Joomla.submitbutton('mediafile.save')">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="Joomla.submitbutton('mediafile.cancel')">
				<?php echo JText::_('JCANCEL') ?>
			</button>
			</div>


        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_DETAILS'); ?></legend>
            <div class="formelm">

                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('createdate'); ?>
                    <?php echo $this->form->getInput('createdate'); ?>
                </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('study_id'); ?>
                    <?php echo $this->form->getInput('study_id'); ?><br /><br />
               </div>
                <div class="formelm">
                    <?php echo $this->form->getLabel('podcast_id'); ?>
                    <?php echo $this->form->getInput('podcast_id', null, $this->admin->params['podcast']); ?>
                </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('link_type'); ?>
                    <?php echo $this->form->getInput('link_type', null, $this->admin->params['download']); ?>
               </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('ordering'); ?>
                    <?php echo $this->form->getInput('ordering'); ?>
                </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('comment'); ?>
                    <?php echo $this->form->getInput('comment'); ?>
                </div>
        </fieldset>

<fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></legend>
            <div class="formelm">

                    <?php echo $this->form->getLabel('player'); ?>
                    <?php echo $this->form->getInput('player'); ?>
                </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('popup'); ?>
                    <?php echo $this->form->getInput('popup'); ?>
                </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('mediacode'); ?>
                    <?php echo $this->form->getInput('mediacode'); ?>
                </div>
        </fieldset>


 <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?></legend>
            <div class="formelm">
                    <?php echo $this->form->getLabel('media_image'); ?>
                    <?php echo $this->form->getInput('media_image'); ?>
               </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('mime_type'); ?>
                    <?php echo $this->form->getInput('mime_type', null, $this->admin->params['mime']); ?>
                </div>
        </fieldset>



        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES'); ?></legend>
            <div class="formelm">
                    <?php echo $this->form->getLabel('plays'); ?>
                    <?php echo $this->form->getInput('plays'); ?>
               </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('downloads'); ?>
                    <?php echo $this->form->getInput('downloads'); ?>
                </div>
             <div class="formelm">
                    <?php echo $this->form->getLabel('spacer'); ?>
                </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('server'); ?>
                    <?php echo $this->form->getInput('server', null, $this->admin->params['server']); ?>
                </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('path'); ?>
                    <?php echo $this->form->getInput('path', null, $this->admin->params['path']); ?>
                </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('filename'); ?>
                    <?php echo $this->form->getInput('filename'); ?>
                </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('size'); ?>
                    <?php echo $this->form->getInput('size'); ?><br /><br />
                </div>
              <div class="formelm">
                    <?php echo $this->form->getLabel('special'); ?>
                    <?php echo $this->form->getInput('special', null, $this->admin->params['target']); ?>
                </div>
            
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
            
              <div class="formelm">
              	<?php echo $this->form->getLabel('docMan_id'); ?>
				<?php echo $this->form->getInput('docMan_id'); ?>
              </div>
              <div class="formelm">
              	<?php echo $this->form->getLabel('virtueMart_id'); ?>
				<?php echo $this->form->getInput('virtueMart_id'); ?>
              </div>
              <div class="formelm">
              	<?php echo $this->form->getLabel('article_id'); ?>
				<?php echo $this->form->getInput('article_id'); ?>
              </div>
        </fieldset>

  <fieldset class="panelform">
   <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
        <?php

                    foreach ($params as $name => $fieldset):
                      ?>
                            <fieldset class="panelform" >
                                <legend><?php echo JText::_('JBS_MED_OPTIONS'); ?></legend>
                                <div class="panelform">
                <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                                <div class="formelm-area"><?php echo $field->label; ?><?php echo $field->input; ?></div>
                <?php endforeach; ?>
                            </div>
                        </fieldset>
        <?php endforeach; ?>
 </fieldset>

  <div class="clr"></div>

	

 <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
    </div>

</form>

