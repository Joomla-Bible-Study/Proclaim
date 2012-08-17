<?php

/**
 * Mod_Biblesutdy core file
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php');
//require_once ( JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'parameter.php' );
//jimport( 'joomla.html.parameter' );
// Need for inline player
$document = JFactory::getDocument();
$document->addScript('media/com_biblestudy/js/tooltip.js');
$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
$templatemenuid = $params->get('t');
$template = modBiblestudyHelper::getTemplate($params);

$admin = modBiblestudyHelper::getAdmin();
$admin_params = new JRegistry($admin[0]->params);
$items = modBiblestudyHelper::getLatest($params);

//attempt to change mysql for error in large select
$db = JFactory::getDBO();
$db->setQuery('SET SQL_BIG_SELECTS=1');
$db->query();

//check permissions for this view by running through the records and removing those the user doesn't have permission to see
$user = JFactory::getUser();
$groups = $user->getAuthorisedViewLevels();
$count = count($items);

for ($i = 0; $i < $count; $i++) {

    if ($items[$i]->access > 1) {
        if (!in_array($items[$i]->access, $groups)) {
            unset($items[$i]);
        }
    }
}
$pagebuilder = new JBSPagebuilder();
foreach ($items AS $item) {
    $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));
    $pelements = $pagebuilder->buildPage($item, $params, $admin_params);
    $item->scripture1 = $pelements->scripture1;
    $item->scripture2 = $pelements->scripture2;
    $item->media = $pelements->media;
    $item->duration = $pelements->duration;
    $item->studydate = $pelements->studydate;
    $item->topics = $pelements->topics;
    $item->study_thumbnail = $pelements->study_thumbnail;
    $item->series_thumbnail = $pelements->series_thumbnail;
    $item->detailslink = $pelements->detailslink;
}
$list = $items;
$link_text = $params->get('pagetext', 'More Bible Studies');
$templatemenuid = $params->get('studielisttemplateid');
if (!$templatemenuid) {
    $templatemenuid = JRequest::getVar('templatemenuid', 1, 'get', 'int');
}
$linkurl = JRoute::_('index.php?option=com_biblestudy&view=sermons&t=' . $templatemenuid);
$link = '<a href="' . $linkurl . '">' . $link_text . '</a>';
$document = JFactory::getDocument();
$css = $params->get('css', 'biblestudy.css');
$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
$language = JFactory::getLanguage();
dump($language, 'Language');
$language->load('com_biblestudy', JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy');
$config = JComponentHelper::getParams('com_biblestudy');
//we need to load the path to the helper files
$path1 = JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy/helpers/';
$url = $params->get('stylesheet');
if ($url) {
    $document->addStyleSheet($url);
}
$pageclass_sfx = $params->get('pageclass_sfx');
/**
 * @todo fork the layout based on params to other custom template files
 */
if ($params->get('useexpert_module') > 0) {
    $layout = 'default_custom';
} else {
    $layout = 'default_main';
}
require(JModuleHelper::getLayoutPath('mod_biblestudy', $layout));