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
				alert( "<?php echo JText::_( 'JBS_MED_ENTER_IMAGE_NAME', true ); ?>" );
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
					<label for="media"><strong> <?php echo JText::_( 'JBS_MED_EXTENSIONS_IMAGES' ); ?> </strong>
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
			</ul>
	</fieldset>
</div>
<div class="col100">
	<fieldset class="panelform">
			<table summary="">
				<tr><td class="key"><?php echo JText::_('JBS_MED_CHOOSE_IMAGE');?></td><td><?php echo $this->lists['media']; echo '  '.JText::_('JBS_CMN_CURRENT_FOLDER').': '.$this->directory.' -  <a  href="index.php?option=com_biblestudy&view=admin&layout=form" target="_blank">'.JText::_('JBS_CMN_SET_DEFAULT_FOLDER').'</a>';?><br /><?php echo JText::_('JBS_CMN_THIS_FIELD_IS_USED_INSTEAD_BELOW');?></td>
				</tr>
			</table>
	</fieldset>
 </div>
 <div class="col100">
	<fieldset class="panelform">
		<ul>
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

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->mediaedit->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="mediaedit" />
</form>
