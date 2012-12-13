<?php
/**
 * Form
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<div class="edit">
    <form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&a_id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
        <div class="width-100 fltlft">
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
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('studytitle'); ?>
                        <?php echo $this->form->getInput('studytitle'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('alias'); ?>
                        <?php echo $this->form->getInput('alias'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('studynumber'); ?>
                        <?php echo $this->form->getInput('studynumber'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('studyintro'); ?>
                        <div class="clr"></div>
                        <?php echo $this->form->getInput('studyintro'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('script1'); ?>
                        <?php echo $this->form->getInput('script1'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('script2'); ?>
                        <?php echo $this->form->getInput('script1'); ?>
                    </li>
                </ul>
            </fieldset>
        </div>
        <div class="width-100 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></legend>
                <div class="inlineFields">
                    <div>
                        <?php echo $this->form->getLabel('booknumber'); ?><br/>
                        <?php
                        // studytitle is required; fill in default if empty and leave value otherwise
                        echo $this->form->getInput('booknumber', null, empty($this->item->studytitle) ? $this->admin->params['booknumber'] : $this->item->booknumber);
                        ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('chapter_begin'); ?><br/>
                        <?php echo $this->form->getInput('chapter_begin'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('verse_begin'); ?><br/>
                        <?php echo $this->form->getInput('verse_begin'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('chapter_end'); ?><br/>
                        <?php echo $this->form->getInput('chapter_end'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('verse_end'); ?><br/>
                        <?php echo $this->form->getInput('verse_end'); ?>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="width-100 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_CMN_SCRIPTURE2'); ?></legend>
                <div class="inlineFields">
                    <div>
                        <?php echo $this->form->getLabel('booknumber2'); ?><br/>
                        <?php echo $this->form->getInput('booknumber2'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('chapter_begin2'); ?><br/>
                        <?php echo $this->form->getInput('chapter_begin2'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('verse_begin2'); ?><br/>
                        <?php echo $this->form->getInput('verse_begin2'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('chapter_end2'); ?><br/>
                        <?php echo $this->form->getInput('chapter_end2'); ?>
                    </div>
                    <div>
                        <?php echo $this->form->getLabel('verse_end2'); ?><br/>
                        <?php echo $this->form->getInput('verse_end2'); ?>
                    </div>
                </div>
            </fieldset>
            <fieldset class="panelform">
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('secondary_reference'); ?>
                        <?php echo $this->form->getInput('secondary_reference'); ?>
                    </li>
                    <li>
                        <label><?php echo JText::_('JBS_CMN_DURATION'); ?></label>
                        <div class="inlineFields">
                            <div>
                                <?php echo $this->form->getLabel('media_hours'); ?><br/>
                                <?php echo $this->form->getInput('media_hours'); ?>
                            </div>
                            <div>
                                <?php echo $this->form->getLabel('media_minutes'); ?><br/>
                                <?php echo $this->form->getInput('media_minutes'); ?>
                            </div>
                            <div>
                                <?php echo $this->form->getLabel('media_seconds'); ?><br/>
                                <?php echo $this->form->getInput('media_seconds'); ?>
                            </div>
                        </div>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('teacher_id'); ?>
                        <?php echo $this->form->getInput('teacher_id', null, empty($this->item->studytitle) ? $this->admin->params['teacher_id'] : $this->item->teacher_id) ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('location_id'); ?>
                        <?php echo $this->form->getInput('location_id', null, empty($this->item->studytitle) ? $this->admin->params['location_id'] : $this->item->location_id) ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('series_id'); ?>
                        <?php echo $this->form->getInput('series_id', null, empty($this->item->studytitle) ? $this->admin->params['series_id'] : $this->item->series_id) ?>
                    </li>
                </ul>
                <?php echo $this->form->getLabel('topics'); ?>
                <div class="clr"></div>
                <?php echo $this->form->getInput('topics'); ?>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('messagetype'); ?>
                        <?php echo $this->form->getInput('messagetype', null, empty($this->item->studytitle) ? $this->admin->params['messagetype'] : $this->item->messagetype) ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('thumbnailm'); ?>
                        <?php echo $this->form->getInput('thumbnailm', null, empty($this->item->studytitle) ? $this->admin->params['default_study_image'] : $this->item->thumbnailm) ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('studytext'); ?>
                    </li>
                </ul>
                <div class="clr"></div>
                <?php echo $this->form->getInput('studytext'); ?>
            </fieldset>
        </div>
        <div style="clear: both;"></div>
        <div class="width-100 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_CMN_PUBLISHING_OPTIONS'); ?></legend>
                <ul>
                    <li>
                        <?php echo JText::_('JBS_STY_HITS'); ?>
                        <?php echo $this->item->hits; ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('published'); ?>
                        <?php echo $this->form->getInput('published'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('studydate'); ?>
                        <?php echo $this->form->getInput('studydate'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('comments'); ?>
                        <?php echo $this->form->getInput('comments'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('user_id'); ?>
                        <?php
                        // fill in actual user if empty
                        echo $this->form->getInput('user_id', null, empty($this->item->studytitle) ? $this->admin->user_id : $this->item->user_id)
                        ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('access'); ?>
                        <?php echo $this->form->getInput('access'); ?></li>
                    <li>
                        <?php echo $this->form->getLabel('language'); ?>
                        <?php echo $this->form->getInput('language'); ?></li>
                    <li>
                        <?php echo $this->form->getLabel('id'); ?>
                        <?php echo $this->form->getInput('id'); ?></li>
                    <?php if ($this->canDo->get('core.admin')): ?>
                        <li><span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
                            <div class="button2-left"><div class="blank">
                                    <button type="button" onclick="document.location.href='#access-rules';">
                                        <?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
                                    </button>
                                </div></div>
                        </li>
                    <?php endif; ?>
                </ul>
            </fieldset>
        </div>
        <div class="width-100 fltlft">
            <?php
            foreach ($params as $name => $fieldset):
                if (isset($fieldset->description) && trim($fieldset->description)):
                    ?>
                    <p class="tip">
                        <?php echo $this->escape(JText::_($fieldset->description)); ?>
                    </p>
                <?php endif; ?>
                <fieldset class="panelform" >
                    <legend><?php echo JText::_('JBS_STY_METADATA'); ?></legend>
                    <ul class="adminformlist">
                        <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                            <li><?php echo $field->label; ?><?php echo $field->input; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
            <?php endforeach; ?>
        </div>
        <div class="width-100 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_STY_MEDIA_THIS_STUDY'); ?></legend>
                <table class="adminlist">
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
                                        <?php $link = 'index.php?option=com_biblestudy&amp;task=mediafile.edit&amp;id=' . (int) $item->id . '&amp;tmpl=component&amp;layout=modal'; ?>
                                        <a class="modal" href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 900, y: 550}}" title="<?php echo $this->escape($item->filename) ? $this->escape($item->filename) : 'ID: ' . $this->escape($item->id); ?>">
                                            <?php echo ($this->escape($item->filename) ? $this->escape($item->filename) : 'ID: ' . $this->escape($item->id)); ?>
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
                    <?php //if (! empty($this->item->studytitle)) : ?>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <?php $link = 'index.php?option=com_biblestudy&amp;task=mediafile.edit&amp;id=0&amp;tmpl=component&amp;view=mediafile&amp;layout=modal'; ?>
                                <?php
                                if (empty($this->item->id)) {
                                    ?> <a onClick="Joomla.submitbutton('message.apply');" href="#"> <?php echo JText::_('JBS_STY_SAVE_FIRST'); ?> </a> <?php
                            } else {
                                    ?>
                                    <a class="modal" href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 1000, y: 550}}" title="<?php echo JText::_('JBS_STY_ADD_MEDIA_FILE'); ?>">
                                        <?php echo JText::_('JBS_STY_ADD_MEDIA_FILE'); ?></a> <?php
                                }
                                    ?>

                            </td>
                        </tr>
                    </tfoot>
                    <?php //endif;    ?>
                </table>
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
</div>