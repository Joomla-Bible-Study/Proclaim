<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class biblestudyViewadmin extends JView
{
	
	function display($tpl = null)
	{
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		$admin		=& $this->get('Data');
		$this->assignRef('admin', $admin);
	//	$isNew		= ($admin->id < 1);
	//	$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Administration' ), 'administration');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::custom( 'resetHits', 'reset.png', 'Reset All Hits', 'Reset All Hits', false, false );
		JToolBarHelper::custom( 'resetDownloads', 'download.png', 'Reset All Download Hits', 'Reset All Download Hits', false, false );
		JToolBarHelper::custom( 'resetPlays', 'play.png', 'Reset All Plays', 'Reset All Plays', false, false );
		$paramsdata = $admin->params;
		$paramsdefs = JPATH_COMPONENT.DS.'models'.DS.'admin.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
		
		$studypath = JPATH_SITE.'/images/'.$params->get('study_images', 'stories');
		$javascript			= 'onchange="changeDisplayImage();"';
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal2[] = $folderfinal1;}
		}
		array_unshift($folderfinal2, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['study'] = JHTML::_('select.genericlist',  $folderfinal2, 'study', 'class="inputbox"', 'value', 'value', $admin->study );
	
		$studypath = JPATH_SITE.'/images/'.$params->get('series_imagefolder');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal3[] = $folderfinal1;}
		}
		array_unshift($folderfinal3, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['series'] = JHTML::_('select.genericlist',  $folderfinal3, 'series', 'class="inputbox"', 'value', 'value', $admin->series );

		$studypath = JPATH_SITE.'/images/'.$params->get('media_imagefolder', '../components/com_biblestudy/images');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal4[] = $folderfinal1;}
		}
		array_unshift($folderfinal4, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['media'] = JHTML::_('select.genericlist',  $folderfinal4, 'media', 'class="inputbox"', 'value', 'value', $admin->media );
		
		$studypath = JPATH_SITE.'/images/'.$params->get('media_imagefolder', '../components/com_biblestudy/images');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal8[] = $folderfinal1;}
		}
		array_unshift($folderfinal8, JHTML::_('select.option', '0', '- '.JText::_('Default Image').' -', 'value', 'value'));
		$lists['main'] = JHTML::_('select.genericlist',  $folderfinal8, 'main', 'class="inputbox"', 'value', 'value', $admin->main );
		
		$studypath = JPATH_SITE.'/images/'.$params->get('teachers_imagefolder', 'stories');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal5[] = $folderfinal1;}
		}
		array_unshift($folderfinal5, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['teacher'] = JHTML::_('select.genericlist',  $folderfinal5, 'teacher', 'class="inputbox"', 'value', 'value', $admin->teacher );

		$studypath = JPATH_SITE.'/images/'.$params->get('podcast_imagefolder', 'stories');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal6[] = $folderfinal1;}
		}
		array_unshift($folderfinal6, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['podcast'] = JHTML::_('select.genericlist',  $folderfinal6, 'podcast', 'class="inputbox"', 'value', 'value', $admin->podcast );

		$studypath = JPATH_SITE.'/images/'.$params->get('media', '../components/com_biblestudy/images');
		$javascript			= 'onchange="changeDisplayImage();"';
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal7[] = $folderfinal1;}
		}
		array_unshift($folderfinal7, JHTML::_('select.option', '0', '- '.JText::_('No Image').' -', 'value', 'value'));
		$lists['download'] = JHTML::_('select.genericlist',  $folderfinal7, 'download', 'class="inputbox"', 'value', 'value', $admin->download );
		
		$studypath = JPATH_SITE.'/images/'.$params->get('media_imagefolder', '../components/com_biblestudy/images');
		$fileList 	= JFolder::files($studypath);
		foreach($fileList as $key=>$value)
		{
			$folderfinal1 = new JObject();
			$folderfinal1->value = $value;
			$folderfinal1->id = $key;
			if (strtolower($folderfinal1->value) == 'index.html') { unset($folderfinal1->value); unset($folderfinal1->key);}
			else {$folderfinal9[] = $folderfinal1;}
		}
		array_unshift($folderfinal9, JHTML::_('select.option', '0', '- '.JText::_('Default Image').' -', 'value', 'value'));
		$lists['showhide'] = JHTML::_('select.genericlist',  $folderfinal9, 'showhide', 'class="inputbox"', 'value', 'value', $admin->showhide );
		
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.admin', true );
		$this->assignRef('lists', $lists);

		//Version check
		include_once(JPATH_ADMINISTRATOR.'/components/com_biblestudy/helpers/version.php');
		$versioncheck = latestVersion();
		//dump ($versioncheck);
		$this->assignRef('versioncheck', $versioncheck);
		parent::display($tpl);
	}
}
?>