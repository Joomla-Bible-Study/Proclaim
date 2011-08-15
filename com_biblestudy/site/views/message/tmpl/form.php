<?php
/**
 * @version     $Id: form.php 1396 2011-01-17 23:12:12Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
JHtml::_('behavior.formvalidation');

?>
<div class="edit">
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	<div class="formelm-buttons">
			<button type="button" onclick="Joomla.submitbutton('message.save')">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="Joomla.submitbutton('message.cancel')">
				<?php echo JText::_('JCANCEL') ?>
			</button>
			</div>
   
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_STY_DETAILS'); ?></legend>
            <div class="formelm">
                    <?php echo $this->form->getLabel('studytitle'); ?>
                    <?php echo $this->form->getInput('studytitle'); ?>
            </div>
            <div class="formelm">
                    <?php echo $this->form->getLabel('studynumber'); ?>
                    <?php echo $this->form->getInput('studynumber'); ?>
             </div>
             <div class="formelm">
                    <?php echo $this->form->getLabel('studyintro'); ?>
                    <div class="clr"></div>
                    <?php echo $this->form->getInput('studyintro'); ?>
               </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('script1'); ?>
                    <?php echo $this->form->getInput('script1'); ?>
               </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('script2'); ?>
                    <?php echo $this->form->getInput('script1'); ?>
               </div>
  
   </fieldset>
   <fieldset class="panelform">
               <div class="inlineFields">
               <legend><?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></legend>
                    
              <strong> <label><?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></label></strong>
                        <div>
                            <?php echo $this->form->getLabel('booknumber'); ?>
                            <?php // studytitle is required; fill in default if empty and leave value otherwise
                                  echo $this->form->getInput('booknumber', null, empty($this->item->studytitle) ? $this->admin->params['booknumber'] : $this->item->booknumber); ?>
                        </div>
                        <div>
                            <?php echo $this->form->getLabel('chapter_begin'); ?>
                            <?php echo $this->form->getInput('chapter_begin'); ?>

                            <?php echo $this->form->getLabel('verse_begin'); ?>
                            <?php echo $this->form->getInput('verse_begin'); ?>

                            <?php echo $this->form->getLabel('chapter_end'); ?>
                            <?php echo $this->form->getInput('chapter_end'); ?>

                            <?php echo $this->form->getLabel('verse_end'); ?>
                            <?php echo $this->form->getInput('verse_end'); ?>
                        </div>
                  <br />
               
                  <strong>  <label><?php echo JText::_('JBS_CMN_SCRIPTURE2'); ?></label></strong>
                    <div class="inlineFields">
                        <div>
                            <?php echo $this->form->getLabel('booknumber2'); ?>
                            <?php echo $this->form->getInput('booknumber2'); ?>
                        </div>
                        <div>
                            <?php echo $this->form->getLabel('chapter_begin2'); ?>
                            <?php echo $this->form->getInput('chapter_begin2'); ?>

                            <?php echo $this->form->getLabel('verse_begin2'); ?>
                            <?php echo $this->form->getInput('verse_begin2'); ?>

                            <?php echo $this->form->getLabel('chapter_end2'); ?>
                            <?php echo $this->form->getInput('chapter_end2'); ?>

                            <?php echo $this->form->getLabel('verse_end2'); ?>
                            <?php echo $this->form->getInput('verse_end2'); ?>
                        </div>
                    </div>
               <div class="formelm">
               <br />
                    <?php echo $this->form->getLabel('secondary_reference'); ?>
                    <?php echo $this->form->getInput('secondary_reference'); ?>
                        </div>
   </fieldset>
   
   <fieldset class="panelform">
              
               <legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
              <strong> <label><?php echo JText::_('JBS_CMN_DURATION'); ?></label></strong><br />
              
                            <div class="inlineFields">
                                    <?php echo $this->form->getLabel('media_hours'); ?>
                                    <?php echo $this->form->getInput('media_hours'); ?>

                                    <?php echo $this->form->getLabel('media_minutes'); ?>
                                    <?php echo $this->form->getInput('media_minutes'); ?>
                                    <?php echo $this->form->getLabel('media_seconds'); ?>
                                    <?php echo $this->form->getInput('media_seconds'); ?>
                            </div>
               <br />
               <div class="formelm">
                    <?php echo $this->form->getLabel('teacher_id'); ?>
                    <?php echo $this->form->getInput('teacher_id', null, empty($this->item->studytitle) ? $this->admin->params['teacher_id'] : $this->item->teacher_id) ?>
                      </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('location_id'); ?>
                    <?php echo $this->form->getInput('location_id', null, empty($this->item->studytitle) ? $this->admin->params['location_id'] : $this->item->location_id) ?>
                        </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('series_id'); ?>
                    <?php echo $this->form->getInput('series_id', null, empty($this->item->studytitle) ? $this->admin->params['series_id'] : $this->item->series_id) ?>
                       </div>
               <div class="formelm">
                        <?php echo $this->form->getLabel('topics'); ?>
                        <div class="clr"></div>
                        <?php echo $this->form->getInput('topics'); ?>
                        </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('messagetype'); ?>
                    <?php echo $this->form->getInput('messagetype', null, empty($this->item->studytitle) ? $this->admin->params['messagetype'] : $this->item->messagetype) ?>
                        </div>
               <div class="formelm">
                    <?php echo $this->form->getLabel('thumbnailm'); ?>
                    <?php echo $this->form->getInput('thumbnailm', null, empty($this->item->studytitle) ? $this->admin->params['default_study_image'] : $this->item->thumbnailm) ?>
                   </div>
</fieldset>
<fieldset class="panelform">
        <div class="formelm">
                 <strong>   <?php echo $this->form->getLabel('studytext'); ?></strong>
        </div>
        <div class="formelm">
            <?php echo $this->form->getInput('studytext'); ?>
        </div>
</fieldset>
                    
<fieldset class="panelform">
        
            <legend><?php echo JText::_('JBS_CMN_PUBLISHING_OPTIONS'); ?></legend>
                           
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </div>
                <div class="formelm-area">    
                    <?php echo $this->form->getLabel('studydate'); ?>
                    <?php echo $this->form->getInput('studydate'); ?>
               </div>
               <div class="formelm-area">
                    <?php echo $this->form->getLabel('comments'); ?>
                    <?php echo $this->form->getInput('comments'); ?>
               <div class="formelm-area">     
                    <?php echo $this->form->getLabel('user_id'); ?>
                    <?php echo $this->form->getInput('user_id', null, empty($this->item->studytitle) ? $this->admin->user_id : $this->item->user_id); ?>
               </div>
               <div class="formelm-area">      
                    <?php echo $this->form->getLabel('access'); ?>
                    <?php echo $this->form->getInput('access'); ?>
               </div>     
              
        
</fieldset>
<fieldset class="panelform">
          
            <div >
                
                    <legend><?php echo JText::_('JBS_STY_MEDIA_THIS_STUDY'); ?></legend>
                    <table class="adminlist" width="100%">
                        <thead>
                            <tr>
                                <th align="center"><?php echo JText::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
                                <th align="center"><?php echo JText::_('JBS_CMN_MEDIA_CREATE_DATE'); ?></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                            if (count($this->mediafiles) > 0) :
                                foreach ($this->mediafiles as $i => $item) :
                    ?>
                                    <tr class="row<?php echo $i % 2; ?>">
                                        <td align="center">
                                            <a href="<?php echo JRoute::_("index.php?option=com_biblestudy&task=mediafile.edit&id=".(int)$item->id); ?>">
                                                <?php echo ($this->escape($item->filename) ?  $this->escape($item->filename) : 'ID: '.$this->escape($item->id)); ?>
                                            </a>
                                        </td>
                                        <td align="center">
                                            <?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
                                        </td>
                                        
                                    </tr>
                    <?php
                                    endforeach;
                                else:
                    ?>
                                    <tr>
                                        <td colspan="4" align="center"><?php echo JText::_('JBS_STY_NO_MEDIAFILES'); ?></td>
                                    </tr>
                    <?php endif; ?>
                                </tbody>
                    <?php if (! empty($this->item->studytitle)) : ?>
                                <tfoot>
                                    <tr>
                                        <td colspan="4"><a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=mediafile&layout=form').'">'.JText::_('JBS_STY_NEW_MEDIAFILE'); ?></a></td>
                                    </tr>
                                </tfoot>
                    <?php endif; ?>
                            </table>
                        </fieldset>
 <div class="clr"></div>
	
	<?php if ($this->canDo->get('core.admin')): ?>
		<div class="width-100 fltlft">
			<?php echo JHtml::_('sliders.start','permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

				<?php echo JHtml::_('sliders.panel',JText::_('JBS_CMN_FIELDSET_RULES'), 'access-rules'); ?>

				<fieldset class="panelform">
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>

			<?php echo JHtml::_('sliders.end'); ?>
		</div>
	<?php endif; ?>
                        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
</div>