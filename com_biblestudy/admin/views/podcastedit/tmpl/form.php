<?php
/**
 * @version     $Id
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="width-65 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_PODCAST_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('website'); ?>
                    <?php echo $this->form->getInput('website'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('author'); ?>
                    <?php echo $this->form->getInput('author'); ?>
                </li>                
                <li>
                    <?php echo $this->form->getLabel('editor_name'); ?>
                    <?php echo $this->form->getInput('editor_name'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('editor_email'); ?>
                    <?php echo $this->form->getInput('editor_email'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('spacer'); ?>
                    <?php echo $this->form->getInput('spacer'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('podcastsearch'); ?>
                    <?php echo $this->form->getInput('podcastsearch'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('language'); ?>
                    <?php echo $this->form->getInput('language'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('detailstemplateid'); ?>
                    <?php echo $this->form->getInput('detailstemplateid'); ?>
                </li>                
            </ul>
        </fieldset>
    </div>
    <div class="width-35 fltrt">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_PODCAST_IMAGES'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('image'); ?>
                    <?php echo $this->form->getInput('image'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('podcastimage'); ?>
                    <?php echo $this->form->getInput('podcastimage'); ?>
                </li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_XML_FILE'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('filename'); ?>
                    <?php echo $this->form->getInput('filename'); ?>
                </li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_EPISODES'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('podcastlimit'); ?>
                    <?php echo $this->form->getInput('podcastlimit'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('episodetitle'); ?>
                    <?php echo $this->form->getInput('episodetitle'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('custom'); ?>
                    <?php echo $this->form->getInput('custom'); ?>
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
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>