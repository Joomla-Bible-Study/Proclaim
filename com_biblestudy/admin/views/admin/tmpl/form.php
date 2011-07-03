<?php
/**
 * @version     $Id: form.php 1467 2011-01-31 23:20:10Z tomfuller2 $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

$params = $this->form->getFieldsets();
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
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=admin&layout=form&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
   <?php echo JHtml::_('tabs.start'); ?>
    
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_ADM_ADMIN_PARAMS'), 'admin-settings'); ?>
    <div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_COMPONENT_SETTINGS'); ?></legend>
                <ul>
                    
                    <li>
                        <?php echo $this->form->getLabel('compat_mode', 'params'); ?>
                        <?php echo $this->form->getInput('compat_mode', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('drop_tables'); ?>
                        <?php echo $this->form->getInput('drop_tables'); ?>
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
              
    </div>
    <div class="clr"></div>
    <?php echo JHtml::_('tabs.panel', JText::_('JBS_ADM_SYSTEM_DEFAULTS'), 'admin-system-defaults'); ?>
		<div class="width-100">
        <div class="width-60 fltlft">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_CMN_DEFAULT_IMAGES'); ?></legend>
                <ul>
                    <li>
                        <?php echo $this->form->getLabel('default_main_image', 'params'); ?>
                        <?php echo $this->form->getInput('default_main_image', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('default_series_image', 'params'); ?>
                        <?php echo $this->form->getInput('default_series_image', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('default_teacher_image', 'params'); ?>
                        <?php echo $this->form->getInput('default_teacher_image', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('default_download_image', 'params'); ?>
                        <?php echo $this->form->getInput('default_download_image', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('default_showHide_image', 'params'); ?>
                        <?php echo $this->form->getInput('default_showHide_image', 'params'); ?>
                    </li>
                </ul>
            </fieldset>
        </div>
        <div class="width-40 fltrt">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_AUTO_FILL_STUDY_REC'); ?></legend>
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
                    <li>
                        <?php echo $this->form->getLabel('default_study_image', 'params'); ?>
                        <?php echo $this->form->getInput('default_study_image', 'params'); ?>
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
  	<?php echo JHtml::_('tabs.panel', JText::_('JBS_ADM_PLAYER_SETTINGS'), 'admin-player-settings'); ?>
  	<div class="width-100">
				<div class="width-50 fltlft">
						<fieldset class="panelform">
								<legend><?php echo JText::_('JBS_CMN_MEDIA_FILES'); ?></legend>
								<ul>
										<li>
                        <?php echo JText::_('JBS_ADM_MEDIA_PLAYER_STAT'); ?><br/>
                        <?php echo $this->playerstats; ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('from', 'params'); ?>
                        <?php echo $this->form->getInput('from', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('to', 'params'); ?>
                        <?php echo $this->form->getInput('to', 'params'); ?>
                    </li>
                    <li>
                        <input type="submit" value="Submit" onclick="Joomla.submitbutton3()"/>
                    </li>
                </ul>
            </fieldset>
        </div>        
    		<div class="width-50 fltrt">
            <fieldset class="panelform">
                <legend><?php echo JText::_('JBS_ADM_POPUP_OPTIONS'); ?></legend>
                <ul>
                    <li>
                        <?php echo JText::_('JBS_ADM_MEDIA_PLAYER_POPUP_STAT'); ?><br/>
                        <?php echo $this->popups; ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('pFrom', 'params'); ?>
                        <?php echo $this->form->getInput('pFrom', 'params'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('pTo', 'params'); ?>
                        <?php echo $this->form->getInput('pTo', 'params'); ?>
                    </li>
                    <li>
                        <input type="submit" value="Submit" onclick="Joomla.submitbutton4()"/>
                    </li>
                </ul>
            </fieldset>
    		</div>
    </div>
    <div class="clr"></div>
   
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="tooltype" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
    <?php echo JHtml::_('tabs.end');
                       

