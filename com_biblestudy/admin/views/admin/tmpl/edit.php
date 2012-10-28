<?php
/**
 * Admin Form
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');;
if (BIBLESTUDY_CHECKREL) {
    JHtml::_('formbehavior.chosen', 'select');
}

$app = JFactory::getApplication();
$input = $app->input;
?>
<script type="text/javascript">
    Joomla.submitbutton3 = function(pressbutton) {
        var form = document.getElementById('adminForm');
        form.tooltype.value = 'players';
        form.task = 'tools';
        form.submit();
    }

    Joomla.submitbutton4 = function(pressbutton) {
        var form = document.getElementById('adminForm');
        form.tooltype.value = 'popups';
        form.task = 'tools';
        form.submit();
    }
</script>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php //echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=admin&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="row-fluid">
        <!-- Begin Content -->
        <div class="span10 form-horizontal">
            <?php if (BIBLESTUDY_CHECKREL) { ?>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#admin" data-toggle="tab"><?php echo JText::_('JBS_ADM_ADMIN_PARAMS'); ?></a></li>
                    <li><a href="#defaults" data-toggle="tab"><?php echo JText::_('JBS_ADM_SYSTEM_DEFAULTS'); ?></a></li>
                    <li><a href="#playersettings" data-toggle="tab"><?php echo JText::_('JBS_ADM_PLAYER_SETTINGS'); ?></a></li>
                    <li><a href="#assets" data-toggle="tab"><?php echo JText::_('JBS_ADM_DB'); ?></a></li>
                    <li><a href="#backup" data-toggle="tab"><?php echo JText::_('JBS_IBM_BACKUP'); ?></a></li>
                    <?php if ($this->form->getValue('jbsmigrationshow', 'params') == 1) : ?>
                        <li><a href="#migration" data-toggle="tab"><?php echo JText::_('JBS_IBM_MIGRATE'); ?></a></li>
                    <?php endif; ?>
                </ul>
                <?php
            }
            if (!BIBLESTUDY_CHECKREL) {
                echo JHtml::_('tabs.start', 'com_biblestudy_admin_' . $this->item->id, array('useCookie' => 1));
                ?>
                <?php
                echo JHtml::_('tabs.panel', JText::_('JBS_ADM_ADMIN_PARAMS'), 'admin-settings');
            }
            ?>

            <div class="tab-content">

                <!-- Begin Tabs -->
                <div class="tab-pane active" id="admin">
                    <div class="row-fluid">
                        <h4><?php echo JText::_('JBS_ADM_COMPONENT_SETTINGS'); ?></h4>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('jbsmigrationshow', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('jbsmigrationshow', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label style="max-width: 100%; padding: 0 5px 0 0;">
                                <a href="index.php?option=com_biblestudy&view=admin&layout=edit&task=admin.aliasUpdate">
                                    <?php echo JText::_('JBS_ADM_RESET_ALIAS') ?>
                                </a>
                            </label>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('metakey', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('metakey', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('metadesc', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('metadesc', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('compat_mode', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('compat_mode', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('drop_tables'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('drop_tables'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('studylistlimit', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('studylistlimit', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('uploadtype', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('uploadtype', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('show_location_media', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('show_location_media', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('popular_limit', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('popular_limit', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('character_filter', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('character_filter', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('format_popular', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('format_popular', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('socialnetworking', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('socialnetworking', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('sharetype', 'params'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('sharetype', 'params'); ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <?php echo $this->form->getLabel('debug'); ?>
                            <div class="controls">
                                <?php echo $this->form->getInput('debug'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!BIBLESTUDY_CHECKREL) {
                    echo '<div style="clear:both"></div>';
                    echo JHtml::_('tabs.panel', JText::_('JBS_ADM_SYSTEM_DEFAULTS'), 'admin-system-defaults');
                }
                ?>
                <div class="tab-pane active" id="defaults">
                    <div class="row-fluid">
                        <div class="span6" >
                            <h4><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES'); ?></h4>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('default_main_image', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('default_main_image', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('default_series_image', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('default_series_image', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('default_teacher_image', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('default_teacher_image', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('default_download_image', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('default_download_image', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('default_showHide_image', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('default_showHide_image', 'params'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="span6">
                            <h4><?php echo JText::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></h4>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('location_id', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('location_id', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('teacher_id', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('teacher_id', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('series_id', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('series_id', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('booknumber', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('booknumber', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('messagetype', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('messagetype', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('default_study_image', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('default_study_image', 'params'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="span6">
                            <h4><?php echo JText::_('JBS_ADM_AUTO_FILL_MEDIA_REC'); ?></h4>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('download', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('download', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('target', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('target', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('server', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('server', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('path', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('path', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('podcast', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('podcast', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('mime', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('mime', 'params'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!BIBLESTUDY_CHECKREL) {
                    echo '
                                            <div class="clr"></div>';
                    echo JHtml::_('tabs.panel', JText::_('JBS_ADM_PLAYER_SETTINGS'), 'admin-player-settings');
                }
                ?>
                <div class="tab-pane active" id="playersettings">
                    <div class="row-fluid">
                        <div class="span6">
                            <h4><?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?></h4>
                            <div class="control-group">
                                <?php echo JText::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?><br/>
                                <div class="controls">
                                    <?php echo $this->playerstats; ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('from', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('from', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('to', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('to', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <input type="submit" value="Submit" onclick="Joomla.submitbutton3()"/>
                            </div>
                        </div>
                        <div class="span6">
                            <h4><?php echo JText::_('JBS_ADM_POPUP_OPTIONS'); ?></h4>
                            <div class="control-group">
                                <?php echo JText::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?><br/>
                                <div class="controls">
                                    <?php echo $this->popups; ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('pFrom', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('pFrom', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('pTo', 'params'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('pTo', 'params'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <input type="submit" value="Submit" onclick="Joomla.submitbutton4()"/>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!BIBLESTUDY_CHECKREL) {
                    echo '<div class="clr"></div>';
                    echo JHtml::_('tabs.panel', JText::_('JBS_ADM_DB'), 'admin-db-settings');
                }
                ?>
                <div class="tab-pane" id="assets">
                    <?php // echo $this->loadTemplate('assets'); ?>
                </div>
                <?php
                if (!BIBLESTUDY_CHECKREL) {
                    echo '
                <div class="clr"></div>';
                    echo JHtml::_('tabs.panel', JText::_('JBS_IBM_BACKUP'), 'admin-backup-settings');
                }
                ?>
                <div class="tab-pane" id="backup">
                    <div class="row-fluid">
                        <h4><?php echo JText::_('JBS_IBM_BACKUP'); ?></h4>
                        <?php //echo $this->loadTemplate('backup'); ?>
                    </div>
                </div>
                <?php if ($this->form->getValue('jbsmigrationshow', 'params') == 1) : ?>
                    <?php
                    if (!BIBLESTUDY_CHECKREL) {
                        echo '<div class="clr"></div>';
                        echo JHtml::_('tabs.panel', JText::_('JBS_IBM_MIGRATE'), 'admin-migrate-settings');
                    }
                    ?>
                    <div class="tab-pane" id="migration">
                        <div class="row-fluid">
                            <h4><?php echo JText::_('JBS_IBM_MIGRATION'); ?></h4>
                            <?php //echo $this->loadTemplate('migrate'); ?>
                        </div>
                    </div>

                <?php endif ?>
                <?php
                if (!BIBLESTUDY_CHECKREL) {
                    echo '<div class="clr"></div>';
                    echo JHtml::_('tabs.panel', JText::_('JBS_ADM_DATABASE'), 'admin-database');
                }
                ?>
                <div class="tab-pane" id="database">
                    <div class="row-fluid">
                        <h4><?php echo JText::_('JBS_ADM_DATABASE'); ?></h4>
                        <?php //echo $this->loadTemplate('database'); ?>
                    </div>
                </div>
                <div class="clr"></div>
                <?php
                if (!BIBLESTUDY_CHECKREL) {
                    echo '<div class="clr"></div>';
                    echo JHtml::_('tabs.panel', JText::_('JBS_IBM_CONVERSION'), 'admin-conversion-settings');
                }
                ?>
                <div class="tab-pane" id="convert">
                    <div class="row-fluid">
                        <h4><?php echo JText::_('JBS_IBM_CONVERT'); ?></h4>
                        <div> <?php echo $this->ss; ?> </div>
                        <div> <?php echo $this->pi; ?> </div>
                    </div>
                </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>