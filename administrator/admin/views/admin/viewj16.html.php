<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.stats.class.php');

class biblestudyViewadmin extends JView
{
	protected $form;
	function display($tpl = null)
	{

        $this->form = $this->get("Form");
      //  $this->form = &JForm::getInstance('myform', JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'models' .DS. 'forms' .DS. 'admin.xml');
        $db = JFactory::getDBO();
        JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		$admin		=& $this->get('Data');
		$this->assignRef('admin', $admin);
		JToolBarHelper::title(   JText::_( 'JBS_CMN_ADMINISTRATION' ), 'administration');
		JToolBarHelper::save();
		JToolBarHelper::apply();
        JToolBarHelper::cancel();
		JToolBarHelper::custom( 'resetHits', 'reset.png', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false, false );
		JToolBarHelper::custom( 'resetDownloads', 'download.png', 'Reset All Download Hits', 'JBS_ADM_RESET_ALL_DOWNLOAD_HITS', false, false );
		JToolBarHelper::custom( 'resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false, false );
        JToolBarHelper::help('biblestudy', true );
		$paramsdata = $admin->params;
		$paramsdefs = JPATH_COMPONENT.DS.'models'.DS.'admin.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);

        $stats = new jbStats();
        $playerstats = $stats->players(); 
        $this->assignRef('playerstats',$playerstats);
        
        $popups = $stats->popups();
        $this->assignRef('popups', $popups);
        
		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('study_images');
        $checkstories = JFolder::folders($studypath,'.');
        if (!$checkstories){$studypath = JPATH_SITE.DS.'images';}
		$javascript			= 'onchange="changeDisplayImage();"';
		$fileList 	= JFolder::files($studypath);
		if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal2[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal2, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_NO_IMAGE').' -', 'value', 'value'));
    		$lists['study'] = JHTML::_('select.genericlist',  $folderfinal2, 'study', 'class="inputbox"', 'value', 'value', $admin->study );
        }


		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('series_imagefolder'); 
        $checkstories = JFolder::folders($studypath,'.');
        if (!$checkstories){$studypath = JPATH_SITE.DS.'images';}
		$fileList 	= JFolder::files($studypath); 
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal3[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal3, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_NO_IMAGE').' -', 'value', 'value'));
    		$lists['series'] = JHTML::_('select.genericlist',  $folderfinal3, 'series', 'class="inputbox"', 'value', 'value', $admin->series );
        }


		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('media_imagefolder', '../components/com_biblestudy/images');
        
		$fileList 	= JFolder::files($studypath);
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal4[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal4, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_NO_IMAGE').' -', 'value', 'value'));
    		$lists['media'] = JHTML::_('select.genericlist',  $folderfinal4, 'media', 'class="inputbox"', 'value', 'value', $admin->media );
        }


		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('media_imagefolder', '../components/com_biblestudy/images');
		$fileList 	= JFolder::files($studypath);
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal8[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal8, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_DEFAULT_IMAGE').' -', 'value', 'value'));
    		$lists['main'] = JHTML::_('select.genericlist',  $folderfinal8, 'main', 'class="inputbox"', 'value', 'value', $admin->main );
        }


		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('teachers_imagefolder');
        $checkstories = JFolder::folders($studypath,'.');
        if (!$checkstories){$studypath = JPATH_SITE.DS.'images';}
		$fileList 	= JFolder::files($studypath);
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal5[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal5, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_NO_IMAGE').' -', 'value', 'value'));
    		$lists['teacher'] = JHTML::_('select.genericlist',  $folderfinal5, 'teacher', 'class="inputbox"', 'value', 'value', $admin->teacher );

        }

		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('podcast_imagefolder');
        $checkstories = JFolder::folders($studypath,'.');
        if (!$checkstories){$studypath = JPATH_SITE.DS.'images';}
		$fileList 	= JFolder::files($studypath);
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal6[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal6, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_NO_IMAGE').' -', 'value', 'value'));
    		$lists['podcast'] = JHTML::_('select.genericlist',  $folderfinal6, 'podcast', 'class="inputbox"', 'value', 'value', $admin->podcast );
        }


		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('media', '../components/com_biblestudy/images');
		$javascript			= 'onchange="changeDisplayImage();"';
		$fileList 	= JFolder::files($studypath);
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal7[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal7, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_NO_IMAGE').' -', 'value', 'value'));
    		$lists['download'] = JHTML::_('select.genericlist',  $folderfinal7, 'download', 'class="inputbox"', 'value', 'value', $admin->download );
        }


		$studypath = JPATH_SITE.DS.'images'.DS.$params->get('media_imagefolder', '../components/com_biblestudy/images');
		$fileList 	= JFolder::files($studypath);
        if ($fileList)
        {
            foreach($fileList as $key=>$value)
    		{
    			$folderfinal1 = new JObject();
    			$folderfinal1->value = $value;
    			$folderfinal1->id = $key;
    			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
    			else {$folderfinal9[] = $folderfinal1;}
    		}
    		array_unshift($folderfinal9, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_DEFAULT_IMAGE').' -', 'value', 'value'));
    		$lists['showhide'] = JHTML::_('select.genericlist',  $folderfinal9, 'showhide', 'class="inputbox"', 'value', 'value', $admin->showhide );
        }


		jimport( 'joomla.i18n.help' );
		$this->assignRef('lists', $lists);

		
		parent::display($tpl);
	}
}
?>
