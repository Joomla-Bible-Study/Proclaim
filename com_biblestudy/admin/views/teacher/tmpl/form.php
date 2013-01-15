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
<script type="text/javascript">
    function jInsertFieldValue(value, id) {
        var old_id = document.id(id).value;
        if (old_id != id) {
            var elem = document.id(id);
            elem.value = value;
            elem.fireEvent("change");
        }
    }
</script>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="adminForm">
    <div class="width-55 fltlft">
        <fieldset class="panelform">
            <legend>
				<?php echo JText::_('JBS_CMN_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
					<?php echo $this->form->getLabel('contact'); ?>

					<?php echo $this->form->getInput('contact'); ?>
					<?php if ($this->item->contact)
				{
					?>
                    <div class="button2-left">
                        <div class="blank">
                            <a onclick=" document.id('jform_contact_id').value=''; document.id('jform_contact_id').fireEvent('change'); Joomla.submitbutton('teacher.apply'); "
                               title="Clear">
								<?php echo JText::_('JBS_CMN_CLEAR'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="button2-left">
                        <div class="blank">
                            <a href="index.php?option=com_contact&task=contact.edit&id=<?php echo (int) $this->item->contact; ?>"
                               target="blank">'<?php echo JText::_('JBS_TCH_EDIT_THIS_CONTACT'); ?>
                            </a>
                        </div>
                    </div>
					<?php } ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('published'); ?>

					<?php echo $this->form->getInput('published'); ?></li>
                <li>
					<?php echo $this->form->getLabel('list_show'); ?>

					<?php echo $this->form->getInput('list_show'); ?></li>
                <li>
					<?php echo $this->form->getLabel('landing_show'); ?>

					<?php echo $this->form->getInput('landing_show'); ?></li>
                <li>
					<?php echo $this->form->getLabel('language'); ?>

					<?php echo $this->form->getInput('language'); ?></li>
                <li>
					<?php echo $this->form->getLabel('teachername'); ?>

					<?php echo $this->form->getInput('teachername'); ?></li>
                <li>
					<?php echo $this->form->getLabel('alias'); ?>

					<?php echo $this->form->getInput('alias'); ?></li>
                <li>
					<?php echo $this->form->getLabel('title'); ?>

					<?php echo $this->form->getInput('title'); ?></li>
                <li>
					<?php echo $this->form->getLabel('address'); ?>

					<?php echo $this->form->getInput('address'); ?></li>
                <li>
					<?php echo $this->form->getLabel('phone'); ?>

					<?php echo $this->form->getInput('phone'); ?></li>
                <li>
					<?php echo $this->form->getLabel('email'); ?>

					<?php echo $this->form->getInput('email'); ?></li>
                <li>
					<?php echo $this->form->getLabel('id'); ?>

					<?php echo $this->form->getInput('id'); ?></li>
            </ul>
        </fieldset>
    </div>
    <div class="width-45 fltrt">
        <fieldset class="panelform">
            <legend>
				<?php echo JText::_('JBS_TCH_LINKS'); ?></legend>
            <ul class="adminformlist">
                <li>
					<?php echo $this->form->getLabel('website'); ?>
					<?php echo $this->form->getInput('website'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('facebooklink'); ?>
					<?php echo $this->form->getInput('facebooklink'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('twitterlink'); ?>
					<?php echo $this->form->getInput('twitterlink'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('bloglink'); ?>
					<?php echo $this->form->getInput('bloglink'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('link1'); ?>
					<?php echo $this->form->getInput('link1'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('linklabel1'); ?>
					<?php echo $this->form->getInput('linklabel1'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('link2'); ?>
					<?php echo $this->form->getInput('link2'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('linklabel2'); ?>
					<?php echo $this->form->getInput('linklabel2'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('link3'); ?>
					<?php echo $this->form->getInput('link3'); ?>
                </li>
                <li>
					<?php echo $this->form->getLabel('linklabel3'); ?>
					<?php echo $this->form->getInput('linklabel3'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <div class="width-45 fltrt">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_TCH_IMAGES'); ?></legend>
            <ul>
                <li>
					<?php echo $this->form->getLabel('teacher_image'); ?>
					<?php
					// teachername is required; fill in default if empty and leave value otherwise
					echo $this->form->getInput('teacher_image', null, empty($this->item->teachername) ? $this->admin->params['default_teacher_image'] : $this->item->teacher_image);
					?>
                </li>
                <li>
					<?php echo $this->form->getLabel('teacher_thumbnail'); ?>

					<?php echo $this->form->getInput('teacher_thumbnail'); ?></li>
                <li>
					<?php echo $this->form->getLabel('image'); ?>

					<?php echo $this->form->getInput('image'); ?></li>
                <li>
					<?php echo $this->form->getLabel('thumb'); ?>

					<?php echo $this->form->getInput('thumb'); ?></li>
                <li>
            </ul>
    </div>
    <div class="clr"></div>
    <div class="width-100 fltlft">
        <fieldset class="panelform">
            <legend> <?php echo JText::_('JBS_TCH_SHORT'); ?></legend>
			<?php echo $this->form->getInput('short'); ?>
        </fieldset>
    </div>
    <div class="clr"></div>
    <div class="width-100 fltlft">
        <fieldset class="panelform">
            <legend>
				<?php echo JText::_('JBS_TCH_INFORMATION'); ?></legend>
			<?php echo $this->form->getInput('information'); ?>
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
    <input type="hidden" name="controller" value="teacher"/>
	<?php echo JHtml::_('form.token'); ?>
</form>