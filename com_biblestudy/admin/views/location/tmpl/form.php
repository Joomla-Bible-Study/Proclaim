<?php
/**
 * Form
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm">
    <div class="width-60 fltlft">
        <fieldset class="panelform">
            <legend>

				<?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
					<?php echo $this->form->getLabel('published'); ?>

					<?php echo $this->form->getInput('published'); ?></li>
                <li>
					<?php echo $this->form->getLabel('location_text'); ?>

					<?php echo $this->form->getInput('location_text'); ?></li>
                <li>
					<?php echo $this->form->getLabel('landing_show'); ?>

					<?php echo $this->form->getInput('landing_show'); ?></li>
                <li>
                <li>
					<?php echo $this->form->getLabel('language'); ?>

					<?php echo $this->form->getInput('language'); ?></li>
                <li>
            </ul>

        </fieldset>
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
    <input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>