<?php
/**
 * @version     $Id: form_16.php 1395 2011-01-17 22:43:01Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

$params = $this->form->getFieldsets('params');
?>
<script language="javascript" type="text/javascript">
    function sizebutton(remotefilesize)
    {
        var objTB = document.getElementById("size");
        objTB.value = remotefilesize;
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	<div class="formelm-buttons">
			<button type="button" onclick="Joomla.submitbutton('studiesedit.save')">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="Joomla.submitbutton('studiesedit.cancel')">
				<?php echo JText::_('JCANCEL') ?>
			</button>
			</div>
 <table><tr><td>
    <div class="edit">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_DETAILS'); ?></legend>
            <div class="formelm-area">
                
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('createdate'); ?>
                    <?php echo $this->form->getInput('createdate'); ?>
                </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('study_id'); ?>
                    <?php echo $this->form->getInput('study_id'); ?>
               </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('podcast_id'); ?>
                    <?php echo $this->form->getInput('podcast_id', null, $this->admin->params['podcast']); ?>
                </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('link_type'); ?>
                    <?php echo $this->form->getInput('link_type', null, $this->admin->params['download']); ?>
               </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('ordering'); ?>
                    <?php echo $this->form->getInput('ordering'); ?>
                </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('comment'); ?>
                    <?php echo $this->form->getInput('comment'); ?>
                </div>
</td><td>
   
        <?php
                   
                    foreach ($params as $name => $fieldset):
                      ?>  
                            <fieldset class="panelform" >
                                <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
                                <div class="panelform">
                <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                                <div class="formelm-area"><?php echo $field->label; ?><?php echo $field->input; ?></div>
                <?php endforeach; ?>
                            </div>
                        </fieldset>
        <?php endforeach; ?>
        
</td></tr>
<tr><td>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_LINKER'); ?></legend>
            <div class="formelm-area">
               
                    <?php echo $this->form->getLabel('docMan_id'); ?>
                    <?php echo $this->form->getInput('docMan_id'); ?>
                </div>
                <div class="formelm-area">
                    <?php echo $this->form->getLabel('article_id'); ?>
                    <?php echo $this->form->getInput('article_id'); ?>
                </div>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></legend>
            <div class="formelm-area">
               
                    <?php echo $this->form->getLabel('player'); ?>
                    <?php echo $this->form->getInput('player'); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('popup'); ?>
                    <?php echo $this->form->getInput('popup'); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('mediacode'); ?>
                    <?php echo $this->form->getInput('mediacode'); ?>
                </div>
        </fieldset>

        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_USE_VIRTUEMART'); ?></legend>
            <div class="formelm-area">
                VM is not yet compatible with Joomla 1.6
                    <?php //echo $this->form->getLabel('virtuemart_categories');  ?>
                    <?php //echo $this->form->getInput('virtuemart_categories'); ?>
                </div>
               <div class="formelm-area">
                    <?php //echo $this->form->getLabel('virtuemart_id');  ?>
                    <?php //echo $this->form->getInput('virtuemart_id'); ?>
                </div>
        </fieldset>
    
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES'); ?></legend>
            <div class="formelm-area">
                    <?php echo $this->form->getLabel('plays'); ?>
                    <?php echo $this->form->getInput('plays'); ?>
               </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('downloads'); ?>
                    <?php echo $this->form->getInput('downloads'); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('spacer'); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('server'); ?>
                    <?php echo $this->form->getInput('server', null, $this->admin->params['server']); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('path'); ?>
                    <?php echo $this->form->getInput('path', null, $this->admin->params['path']); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('filename'); ?>
                    <?php echo $this->form->getInput('filename'); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('size'); ?>
                    <?php echo $this->form->getInput('size'); ?>
                </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('special'); ?>
                    <?php echo $this->form->getInput('special', null, $this->admin->params['target']); ?>
                </div>
        </fieldset>
   
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?></legend>
            <div class="formelm-area">
                    <?php echo $this->form->getLabel('media_image'); ?>
                    <?php echo $this->form->getInput('media_image'); ?>
               </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('mime_type'); ?>
                    <?php echo $this->form->getInput('mime_type', null, $this->admin->params['mime']); ?>
                </div>
        </fieldset>

                           

                            <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
    </div>
    </td></tr>
    </table>
</form>

