<?php
/**
 * Form
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="width-65 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_PODCAST_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('id'); ?>

                    <?php echo $this->form->getInput('id'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('published'); ?>

                    <?php echo $this->form->getInput('published'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('title'); ?>

                    <?php echo $this->form->getInput('title'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('description'); ?>

                    <?php echo $this->form->getInput('description'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('website'); ?>

                    <?php echo $this->form->getInput('website'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('author'); ?>

                    <?php echo $this->form->getInput('author'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('editor_name'); ?>

                    <?php echo $this->form->getInput('editor_name'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('editor_email'); ?>

                    <?php echo $this->form->getInput('editor_email'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('spacer'); ?>

                    <?php echo $this->form->getInput('spacer'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('podcastsearch'); ?>

                    <?php echo $this->form->getInput('podcastsearch'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('language'); ?>

                    <?php echo $this->form->getInput('language'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('podcastlanguage'); ?>

                    <?php echo $this->form->getInput('podcastlanguage'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('detailstemplateid'); ?>

                    <?php echo $this->form->getInput('detailstemplateid'); ?></li>
            </ul>
        </fieldset>
    </div>
    <div class="width-35 fltrt">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_PODCAST_IMAGES'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('image'); ?>

                    <?php echo $this->form->getInput('image'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('podcastimage'); ?>

                    <?php echo $this->form->getInput('podcastimage'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('podcast_image_subscribe'); ?>

                    <?php echo $this->form->getInput('podcast_image_subscribe'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('podcast_subscribe_desc'); ?>

                    <?php echo $this->form->getInput('podcast_subscribe_desc'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('alternatelink'); ?>
                    <?php echo $this->form->getInput('alternatelink'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('alternateimage'); ?>
                    <?php echo $this->form->getInput('alternateimage'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('podcast_subscribe_show'); ?>
                    <?php echo $this->form->getInput('podcast_subscribe_show'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('alternatewords'); ?>
                    <?php echo $this->form->getInput('alternatewords'); ?>
                </li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_XML_FILE'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('filename'); ?>

                    <?php echo $this->form->getInput('filename'); ?></li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_PDC_EPISODES'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('podcastlimit'); ?>

                    <?php echo $this->form->getInput('podcastlimit'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('episodetitle'); ?>

                    <?php echo $this->form->getInput('episodetitle'); ?></li>
                <li>
                    <?php echo $this->form->getLabel('custom'); ?>

                    <?php echo $this->form->getInput('custom'); ?></li>
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
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>