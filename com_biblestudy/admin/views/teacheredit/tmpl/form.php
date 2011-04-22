<?php
/**
 * @version     $Id: form.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('restricted access');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="width-100">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('list_show'); ?>
                    <?php echo $this->form->getInput('list_show'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('ordering'); ?>
                    <?php echo $this->form->getInput('ordering'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('teachername'); ?>
                    <?php echo $this->form->getInput('teachername'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </li>
                <li>
                    
                    <?php echo $this->form->getLabel('teacher_image'); ?>
                    <?php // teachername is required; fill in default if empty and leave value otherwise
                          echo $this->form->getInput('teacher_image', null, empty($this->item->teachername) ? $this->admin->params['default_teacher_image'] : $this->item->teacher_image); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('teacher_thumbnail'); ?>
                    <?php echo $this->form->getInput('teacher_thumbnail'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('image'); ?>
                    <?php echo $this->form->getInput('image'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('thumb'); ?>
                    <?php echo $this->form->getInput('thumb'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('phone'); ?>
                    <?php echo $this->form->getInput('phone'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('email'); ?>
                    <?php echo $this->form->getInput('email'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('website'); ?>
                    <?php echo $this->form->getInput('website'); ?>
                </li>
                <li>
                   
                </li>
                
            </ul>
            <div class="clr"></div>
             <?php echo $this->form->getLabel('short'); ?>
             <div class="clr"></div>
             <?php echo $this->form->getInput('short'); ?>
        </fieldset>
    </div>  
    <div>
    <fieldset class="panelform">
    <legend><?php echo JText::_('JBS_TCH_INFORMATION'); ?></legend>
    
            
             <?php echo $this->form->getInput('information'); ?>
        
    </fieldset>
    </div> 
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
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>