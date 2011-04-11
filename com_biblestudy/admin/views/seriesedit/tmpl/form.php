<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php'); 
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<fieldset class="panelform">
			<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>
			<ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
					<?php echo $this->form->getInput('published'); ?>
                </li>
				<li>
					<?php echo $this->form->getLabel('series_text'); ?>
					<?php echo $this->form->getInput('series_text'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('teacher'); ?>
					<?php echo $this->form->getInput('teacher'); ?>
				</li> 
                <li>
					<?php echo $this->form->getLabel('series_thumbnail'); ?>
					<?php // series_text is required; fill in default if empty and leave value otherwise
					      echo $this->form->getInput('series_thumbnail', null, empty($this->item->series_text) ? $this->admin->params['default_series_image'] : $this->item->series_thumbnail); ?>
				</li> 
			</ul>
            <div class="clr"></div>
            <?php echo $this->form->getInput('description'); ?>
                        </fieldset>
            </div>
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
