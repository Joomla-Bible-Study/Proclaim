<?php defined('_JEXEC') or die('Restriced Access'); ?>
<script type="text/javascript" src="components/com_biblestudy/tooltip.js"></script>
<?php

require(dirname(__FILE__).DS.'helper.php');
// Need for inline player
$document =& JFactory::getDocument();
$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');

//require_once(dirname(__FILE__).DS.'helper.php');
$templatemenuid = $params->get('modulemenuid');
//if ($templatemenuid) 
//	{
	$template = modBiblestudyHelper::getTemplate($params);
//		$params = new JParameter($template[0]->params);
		//dump ($params, 'params: ');
	//}

$admin = modBiblestudyHelper::getAdmin();
$admin_params = new JParameter($admin[0]->params);
//dump ($admin_params, 'admin_params: ');
$list = modBiblestudyHelper::getLatest($params);
//$layouttype = $params->get('layouttype');
global $mainframe; 

$document =& JFactory::getDocument();
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
$language =& JFactory::getLanguage();
$language->load('com_biblestudy');
$config =& JComponentHelper::getParams( 'com_biblestudy' );
//we need to load the path to the helper files
$path1 = JPATH_BASE.DS.'components'.DS.'com_biblestudy/helpers/';
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$pageclass_sfx = $params->get('pageclass_sfx');
if ($params->get('useexpert_module')> 0)
     {
     	$layout = 'default_custom';
	 }
else
	{
		$layout = 'default_main';
	}
require(JModuleHelper::getLayoutPath('mod_biblestudy', $layout));
?>