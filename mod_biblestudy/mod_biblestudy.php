<?php

/**
 * Mod_Biblesutdy core file
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @copyright   (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';
require_once BIBLESTUDY_PATH_MOD . '/helper.php';

// Need for inline player
$document = JFactory::getDocument();

/** @var $params JRegistry */
$templatemenuid = $params->get('t');
$template       = ModJBSMHelper::getTemplate($params);

$admin        = ModJBSMHelper::getAdmin();
$admin_params = new JRegistry($admin[0]->params);
$items        = ModJBSMHelper::getLatest($params);

// Attempt to change mysql for error in large select
$db = JFactory::getDBO();
$db->setQuery('SET SQL_BIG_SELECTS=1');
$db->execute();

// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
$user   = JFactory::getUser();
$groups = $user->getAuthorisedViewLevels();
$count  = count($items);

for ($i = 0; $i < $count; $i++)
{

	if ($items[$i]->access > 1)
	{
		if (!in_array($items[$i]->access, $groups))
		{
			unset($items[$i]);
		}
	}
}
JLoader::discover('JBSM', BIBLESTUDY_PATH_LIB);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_LIB);
JLoader::discover('JBSM', BIBLESTUDY_PATH_HELPERS);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_HELPERS);

if ($params->get('useexpert_module') > 0)
{
$pagebuilder = new JBSMPagebuilder;

foreach ($items AS $item)
{
	$item->slug       = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
		. str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));
	$pelements        = $pagebuilder->buildPage($item, $params, $admin_params);
	$item->scripture1 = $pelements->scripture1;
	$item->scripture2 = $pelements->scripture2;
	$item->media      = $pelements->media;

	if (isset($pelements->duration))
	{
		$item->duration = $pelements->duration;
	}
	else
	{
		$item->duration = null;
	}

	if (isset($pelements->studydate))
	{
		$item->studydate = $pelements->studydate;
	}
	else
	{
		$item->studydate = null;
	}
	$item->topics = $pelements->topics;

	if (isset($pelements->study_thumbnail))
	{
		$item->study_thumbnail = $pelements->study_thumbnail;
	}
	else
	{
		$item->study_thumbnail = null;
	}

	if (isset($pelements->series_thumbnail))
	{
		$item->series_thumbnail = $pelements->series_thumbnail;
	}
	else
	{
		$item->series_thumbnail = null;
	}
	$item->detailslink = $pelements->detailslink;
    }
}
$list      = $items;
$link_text = $params->get('pagetext');
$jinput    = new JInput;

if (!$templatemenuid)
{
	$templatemenuid = $jinput->get('templatemenuid', 1, 'get', 'int');
}
$linkurl  = JRoute::_('index.php?option=com_biblestudy&view=sermons&t=' . $templatemenuid);
$link     = '<a href="' . $linkurl . '"><button class="btn">' . $link_text . ' --></button></a>';
$document = JFactory::getDocument();
$css      = $params->get('css', 'biblestudy.css');

if (!$css || $css == '-1')
{
	$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
}
else
{
	$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
}

$language = JFactory::getLanguage();
$language->load('com_biblestudy', JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy');
$config = JComponentHelper::getParams('com_biblestudy');

// We need to load the path to the helper files
$path1 = JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy/helpers/';
$url   = $params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}
$pageclass_sfx = $params->get('pageclass_sfx');
/**
 * @todo fork the layout based on params to other custom template files, Tom can you see if this to do is still needed. TOM
 */
if ($params->get('useexpert_module') > 0)
{
    $template = 'default_custom';
}
elseif ($params->get('moduletemplate'))
{
    $template = 'default_'.$params->get('moduletemplate');
}
else
{
    $template = 'default_main';
}
require JModuleHelper::getLayoutPath('mod_biblestudy', $template);
