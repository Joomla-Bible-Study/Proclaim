<?php
/**
 * @version     $Id$
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
    <div class="width-65 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_DETAILS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('createdate'); ?>
                    <?php echo $this->form->getInput('createdate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('study_id'); ?>
                    <?php echo $this->form->getInput('study_id'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('podcast_id'); ?>
                    <?php echo $this->form->getInput('podcast_id'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('link_type'); ?>
                    <?php echo $this->form->getInput('link_type'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('ordering'); ?>
                    <?php echo $this->form->getInput('ordering'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('comment'); ?>
                    <?php echo $this->form->getInput('comment'); ?>
                </li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_LINKER'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('docMan_id'); ?>
                    <?php echo $this->form->getInput('docMan_id'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('article_id'); ?>
                    <?php echo $this->form->getInput('article_id'); ?>
                    <select id="categoryItems" name="categoryItem"></select>

                </li>
            </ul>
        </fieldset>
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES_SETTINGS'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('player'); ?>
                    <?php echo $this->form->getInput('player'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('popup'); ?>
                    <?php echo $this->form->getInput('popup'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('mediacode'); ?>
                    <?php echo $this->form->getInput('mediacode'); ?>
                </li>
            </ul>
        </fieldset>

        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_USE_VIRTUEMART'); ?></legend>
            <ul class="adminformlist">
                <li>VM is not yet compatible with Joomla 1.6
                    <?php //echo $this->form->getLabel('virtuemart_categories');  ?>
                    <?php //echo $this->form->getInput('virtuemart_categories'); ?>
                </li>
                <li>
                    <?php //echo $this->form->getLabel('virtuemart_id');  ?>
                    <?php //echo $this->form->getInput('virtuemart_id'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <div class="width-35 fltrt">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_FILES'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('plays'); ?>
                    <?php echo $this->form->getInput('plays'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('downloads'); ?>
                    <?php echo $this->form->getInput('downloads'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('spacer'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('server'); ?>
                    <?php echo $this->form->getInput('server'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('path'); ?>
                    <?php echo $this->form->getInput('path'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('filename'); ?>
                    <?php echo $this->form->getInput('filename'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('size'); ?>
                    <?php echo $this->form->getInput('size'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('special'); ?>
                    <?php echo $this->form->getInput('special'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <div class="width-35 fltrt">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_MED_MEDIA_TYPE'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('media_image'); ?>
                    <?php echo $this->form->getInput('media_image'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('mime_type'); ?>
                    <?php echo $this->form->getInput('mime_type'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <div class="width-35 fltrt">
        <?php
                    echo JHtml::_('sliders.start', 'biblestudy-slider');
                    foreach ($params as $name => $fieldset):
                        echo JHtml::_('sliders.panel', JText::_($fieldset->label), $name . '-params');
                        if (isset($fieldset->description) && trim($fieldset->description)): ?>
                            <p class="tip">
            <?php echo $this->escape(JText::_($fieldset->description)); ?>
                        </p>
        <?php endif; ?>
                            <fieldset class="panelform" >
                                <legend><?php echo JText::_('JBS_CMN_PARAMETERS'); ?></legend>
                                <ul class="adminformlist">
                <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                                <li><?php echo $field->label; ?><?php echo $field->input; ?></li>
                <?php endforeach; ?>
                            </ul>
                        </fieldset>
        <?php endforeach; ?>
        <?php echo JHtml::_('sliders.end'); ?>
                            </div>

                            <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

