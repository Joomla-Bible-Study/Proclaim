<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <div class="width-65 fltlft">
        <fieldset class="panelform">
            <legend><?php echo JText::_('JBS_STY_DETAILS'); ?></legend>
            <ul>
                <li>
                    <?php echo $this->form->getLabel('studytitle'); ?>
                    <?php echo $this->form->getInput('studytitle'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('studynumber'); ?>
                    <?php echo $this->form->getInput('studynumber'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('studyintro'); ?>
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
                <li>
                    <label><?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></label>
                    <div class="inlineFields">
                        <div>
                            <?php echo $this->form->getLabel('booknumber'); ?><br/>
                            <?php echo $this->form->getInput('booknumber'); ?>
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
                </li>
                <li>
                    <label><?php echo JText::_('JBS_CMN_SCRIPTURE2'); ?></label>
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
                </li>
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
                    <?php echo $this->form->getInput('teacher_id'); ?>
                        </li>
                        <li>
                    <?php echo $this->form->getLabel('location_id'); ?>
                    <?php echo $this->form->getInput('location_id'); ?>
                        </li>
                        <li>
                    <?php echo $this->form->getLabel('series_id'); ?>
                    <?php echo $this->form->getInput('series_id'); ?>
                        </li>
                        <li>
                    <?php echo $this->form->getLabel('topics_id'); ?>
                    <?php echo $this->form->getInput('topics_id'); ?>
                        </li>
                        <li>
                    <?php echo $this->form->getLabel('messagetype'); ?>
                    <?php echo $this->form->getInput('messagetype'); ?>
                        </li>
                        <li>
                    <?php echo $this->form->getLabel('thumbnailm'); ?>
                    <?php echo $this->form->getInput('thumbnailm'); ?>
                    </li>
                    <li>
                    <?php echo $this->form->getLabel('studytext'); ?>
                        </li>
                    </ul>
                    <div class="clr"></div>
            <?php echo $this->form->getInput('studytext'); ?>
                        </fieldset>
                    </div>
                    <div class="width-35 fltrt">
                        <fieldset class="panelform">
                            <legend><?php echo JText::_('JBS_PUBLISHING_OPTIONS'); ?></legend>
                            <ul>
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
                    <?php echo $this->form->getInput('user_id'); ?>
                        </li>
                        <li>
                    <?php echo $this->form->getLabel('show_level'); ?>
                    <?php echo $this->form->getInput('show_level'); ?>
                        </li>
                    </ul>
                </fieldset>
            </div>
            <div class="width-35 fltrt">
                <fieldset class="panelform">
                    <legend><?php echo JText::_('JBS_STY_MEDIA_THIS_STUDY'); ?></legend>
                    <table class="adminlist">
                        <thead>
                            <tr>
                                <th align="center"><?php echo JText::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
                                <th align="center"><?php echo JText::_('JBS_CMN_MEDIA_CREATE_DATE'); ?></th>
                                <th align="center"><?php echo JText::_('JBS_CMN_SCRIPTURE'); ?></th>
                                <th align="center"><?php echo JText::_('JBS_CMN_TEACHER'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                            if (count($this->mediafiles) > 0) :
                                foreach ($this->mediafiles as $i => $item) :
                    ?>
                                    <tr class="row<?php echo $i % 2; ?>">
                                        <td align="center">
                                            <a href="<?php echo JRoute::_("index.php?option=com_biblestudy&task=mediafilesedit.edit&id=".(int)$item->id); ?>">
                                                <?php echo $this->escape($item->filename); ?>
                                            </a>
                                        </td>
                                        <td align="center">
                                            <?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
                                        </td>
                                        <td>???</td>
                                        <td>???</td>
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
                                <tfoot>
                                    <tr>
                                        <td colspan="4"><a href="<?php echo JRoute::_('index.php?option=com_biblestudy&view=mediafileslist'); ?>">View All Media Files</a></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </fieldset>
                        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>