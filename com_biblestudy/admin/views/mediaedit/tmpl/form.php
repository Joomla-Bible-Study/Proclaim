<?php
/**
 * @version     $Id: form.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
 ?>
<script language="javascript" type="text/javascript">
		<!--
		function submitbutton(pressbutton)
		{
			var form = document.adminForm;
			if (pressbutton == 'cancel')
			{
				submitform( pressbutton );
				return;
			}
			// do field validation
			if (form.media_image_name.value == "")
			{
				alert( "<?php echo JText::_( 'JBS_MDI_ENTER_IMAGE_NAME', true ); ?>" );
			}
			else
			{
				submitform( pressbutton );
			}
		}
        </script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="panelform">
		<legend><?php echo JText::_( 'JBS_CMN_DETAILS' ); ?></legend>
			<ul>
				<li>
					<label for="media"><strong> <?php echo JText::_( 'JBS_MDI_EXTENSIONS_IMAGES' ); ?> </strong>
					</label>
				</li>
				<li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
				</li>
				<li>
                    <?php echo $this->form->getLabel('media_image_name'); ?>
                    <?php echo $this->form->getInput('media_image_name'); ?>
				</li>
				<li>
                    <?php echo $this->form->getLabel('media_text'); ?>
                    <?php echo $this->form->getInput('media_text'); ?>
				</li>
			
                <li>
                    <?php echo $this->form->getLabel('path2'); ?>
                    <?php echo $this->form->getInput('path2'); ?>
                    
                </li>
			<li>
                <?php echo $this->form->getLabel('media_image_path'); ?>
                <?php echo $this->form->getInput('media_image_path'); ?>
			</li>
			<li>
                <?php echo $this->form->getLabel('media_alttext'); ?>
                <?php echo $this->form->getInput('media_alttext'); ?>
			</li>
		</ul>
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
 <input type="hidden" name="task" value=""/>
  <?php echo JHtml::_('form.token'); ?>
</form>
