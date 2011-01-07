<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

$params = $this->form->getFieldsets();
?>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo JHtml::_('tabs.start'); ?>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_ADMIN_PARAMS'), 'admin-settings'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_COMPONENT_SETTINGS'); ?></legend>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('compat_mode', 'params'); ?>
                        <?php echo $this->form->getInput('compat_mode', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('admin_store', 'params'); ?>
                        <?php echo $this->form->getInput('admin_store', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('studylistlimit', 'params'); ?>
                        <?php echo $this->form->getInput('studylistlimit', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('show_location_media', 'params'); ?>
                        <?php echo $this->form->getInput('show_location_media', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('popular_limit', 'params'); ?>
                        <?php echo $this->form->getInput('popular_limit', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('character_filter', 'params'); ?>
                        <?php echo $this->form->getInput('character_filter', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('format_popular', 'params'); ?>
                        <?php echo $this->form->getInput('format_popular', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('socialnetworking', 'params'); ?>
                        <?php echo $this->form->getInput('socialnetworking', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('sharetype', 'params'); ?>
                        <?php echo $this->form->getInput('sharetype', 'params'); ?>
                    </li>
                </ul>
            </fieldset>

        </div>
        <div class="width-40 fltrt">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_FRONTEND_SUBMISSION'); ?></legend>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('allow_entry_study', 'params'); ?>
                        <?php echo $this->form->getInput('allow_entry_study', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('entry_access', 'params'); ?>
                        <?php echo $this->form->getInput('entry_access', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('study_publish', 'params'); ?>
                        <?php echo $this->form->getInput('study_publish', 'params'); ?>
                    </li>
                </ul>
            </fieldset>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_IMAGE_FOLDERS'); ?> </legend>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('series_imagefolder', 'params'); ?>
                        <?php echo $this->form->getInput('series_imagefolder', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('media_imagefolder', 'params'); ?>
                        <?php echo $this->form->getInput('media_imagefolder', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('teachers_imagefolder', 'params'); ?>
                        <?php echo $this->form->getInput('teachers_imagefolder', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('study_images', 'params'); ?>
                        <?php echo $this->form->getInput('study_images', 'params'); ?>
                    </li>
                </ul>
            </fieldset>
        </div>        
    </div>
    <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_SYSTEM_DEFAULTS'), 'admin-system-defaults'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES'); ?></legend>
                <ul>
                    <li>

                    </li>
                </ul>
            </fieldset>
        </div>
        <div class="width-40 fltrt">
            <fieldset class="panelform">
                <legend><?php echo JText::_('FILLIN-STUDY'); ?></legend>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('location_id', 'params'); ?>
                        <?php echo $this->form->getInput('location_id', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('teacher_id', 'params'); ?>
                        <?php echo $this->form->getInput('teacher_id', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('series_id', 'params'); ?>
                        <?php echo $this->form->getInput('series_id', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('booknumber', 'params'); ?>
                        <?php echo $this->form->getInput('booknumber', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('topic_id', 'params'); ?>
                        <?php echo $this->form->getInput('topic_id', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('messagetype', 'params'); ?>
                        <?php echo $this->form->getInput('messagetype', 'params'); ?>
                    </li>
                </ul>
            </fieldset>
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></legend>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('download', 'params'); ?>
                        <?php echo $this->form->getInput('download', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('target', 'params'); ?>
                        <?php echo $this->form->getInput('target', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('server', 'params'); ?>
                        <?php echo $this->form->getInput('server', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('path', 'params'); ?>
                        <?php echo $this->form->getInput('path', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('podcast', 'params'); ?>
                        <?php echo $this->form->getInput('podcast', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('mime', 'params'); ?>
                        <?php echo $this->form->getInput('mime', 'params'); ?>
                    </li>
                </ul>
            </fieldset>
        </div>
    </div>
    <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_PLAYER_SETTINGS'), 'admin-player-settings'); ?>
    <div class="width-100">
        Player options
    </div>
    <?php echo JHtml::_('tabs.end'); ?>

                                <div class="width-60 fltrt">
                                    <fieldset class="adminform">
                                        <legend><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES'); ?></legend>

                                        <ul>


                                            <li><?php echo JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE'); ?><?php
                                    if ($this->lists['main']) {
                                        echo $this->lists['main'];
                                        echo ' ' . JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE_TT');
                                    } else {
                                        echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
                                    }
        ?></li>
                                <li><?php echo JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE'); ?><?php
                                    if (isset($this->lists['study'])) {
                                        echo $this->lists['study'];
                                        echo ' ' . JText::_('JBS_ADM_DEFAULT_STUDY_IMAGE_TT');
                                    } else {
                                        echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
                                    }
        ?></li>
                                <li><?php echo JText::_('JBS_ADM_DEFAULT_SERIES_IMAGE'); ?><?php
                                    if (isset($this->lists['series'])) {
                                        echo $this->lists['series'];
                                        echo ' ' . JText::_('JBS_ADM_DEFAULT_SERIES_IMAGE_TT');
                                    } else {
                                        echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
                                    }
        ?></li>

                                <li><?php echo JText::_('JBS_ADM_DEFAULT_TEACHER_IMAGE'); ?><?php
                                    if (isset($this->lists['teacher'])) {
                                        echo $this->lists['teacher'];
                                        echo ' ' . JText::_('JBS_ADM_DEFAULT_TEACHER_IMAGE_TT');
                                    } else {
                                        echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
                                    }
        ?></li>
                                <li><?php echo JText::_('JBS_ADM_DOWNLOAD_IMAGE'); ?><?php
                                    if (isset($this->lists['download'])) {
                                        echo $this->lists['download'];
                                        echo ' ' . JText::_('JBS_ADM_DOWNLOAD_IMAGE_TT');
                                    } else {
                                        echo JText::_('JBS_ADM_ERROR_FINDING_LIST');
                                    }
        ?></li>
                                <li><?php echo JText::_('JBS_ADM_DEFAULT_SHOWHIDE_IMAGE_LANDING_PAGE'); ?>
                    <?php echo $this->lists['showhide'];
                                    echo ' ' . JText::_('JBS_ADM_DEFAULT_SHOWHIDE_IMAGE_LANDING_PAGE_TT'); ?>
                                </li>
                            </ul>
                        </fieldset>
                    </div>
                    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
                                </form>



                                <div class="width-100 fltlft">

                                    <form action="index.php" method="post" name="adminForm2" id="adminForm2">

                                        <fieldset class="adminform">
                                            <legend><?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?></legend>
                                            <table class="admintable">
                                                <tr><td class="key"><?php echo JText::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?> </td><td><?php echo $this->playerstats; ?></td> </tr>
                                                <tr>
                                                    <td class="key"><?php echo JText::_('JBS_ADM_CHANGE_PLAYERS'); ?></td>
                                                    <td>

                                                        <select name="from" id="from">
                                                            <option value="x"><?php echo JText::_('JBS_ADM_SELECT_EXISTING_PLAYER'); ?></option>
                                                            <option value="0"><?php echo JText::_('JBS_CMN_DIRECT_LINK'); ?></option>
                                                            <option value="1"><?php echo JText::_('JBS_CMN_INTERNAL_PLAYER'); ?></option>
                                                            <option value="3"><?php echo JText::_('JBS_CMN_AVPLUGIN'); ?></option>
                                                            <option value="7"><?php echo JText::_('JBS_CMN_LEGACY_PLAYER'); ?></option>
                                                            <option value="100"><?php echo JText::_('JBS_NO_PLAYER_LISTED'); ?></option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="to" id="to">
                                                            <option value="x"><?php echo JText::_('JBS_ADM_SELECT_NEW_PLAYER'); ?></option>
                                                            <option value="0"><?php echo JText::_('JBS_CMN_DIRECT_LINK'); ?></option>
                                                            <option value="1"><?php echo JText::_('JBS_CMN_INTERNAL_PLAYER'); ?></option>
                                                            <option value="3"><?php echo JText::_('JBS_CMN_AVPLUGIN'); ?></option>
                                                            <option value="7"><?php echo JText::_('JBS_CMN_LEGACY_PLAYER'); ?></option>

                                                        </select>
                                                        <input type="hidden" name="option" value="com_biblestudy" />
                                                        <input type="hidden" name="task" value="changePlayers" />
                                                        <input type="hidden" name="controller" value="admin" />
                                                        <input type="submit" value="Submit" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>

                                    </form>
                                </div>
                                <div class="width-100 fltlft">

                                    <form action="index.php" method="post" name="adminForm3" id="adminForm3">
                                        <div class="col100">
                                            <fieldset class="adminform">
                                                <legend><?php echo JText::_('JBS_ADM_POPUP_OPTIONS'); ?></legend>
                                                <table class="admintable">
                                                    <tr><td class="key"><?php echo JText::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?> </td><td><?php echo $this->popups; ?></td> </tr>
                                                    <tr>
                                                        <td class="key"><?php echo JText::_('JBS_ADM_CHANGE_POPUP'); ?></td>
                                                        <td>

                                                            <select name="pfrom" id="pfrom">
                                                                <option value="x"><?php echo JText::_('JBS_ADM_SELECT_EXISTING_OPTION'); ?></option>
                                                                <option value="2"><?php echo JText::_('JBS_CMN_INLINE'); ?></option>
                                                                <option value="1"><?php echo JText::_('JBS_CMN_POPUP_NEW_WINDOW'); ?></option>
                                                                <option value="3"><?php echo JText::_('JBS_CMN_USE_GLOBAL'); ?></option>
                                                                <option value="100"><?php echo JText::_('JBS_ADM_NO_OPTION_LISTED'); ?></option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="pto" id="pto">
                                                                <option value="x"><?php echo JText::_('JBS_ADM_SELECT_NEW_OPTION'); ?></option>
                                                                <option value="2"><?php echo JText::_('JBS_CMN_INLINE'); ?></option>
                                                                <option value="1"><?php echo JText::_('JBS_CMN_POPUP_NEW_WINDOW'); ?></option>
                                                                <option value="3"><?php echo JText::_('JBS_CMN_USE_GLOBAL'); ?></option>


                            </select>
                            <input type="hidden" name="option" value="com_biblestudy" />
                            <input type="hidden" name="task" value="changePopup" />
                            <input type="hidden" name="controller" value="admin" />

                            <input type="submit" value="Submit" />
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
    </form>
</div>
