<?php
/**
 * Mod_Biblestudy core file
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @copyright   2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

// Need for inline player
$document = JFactory::getDocument();

/** @var $params Registry */
$templatemenuid = $params->get('t');
$template = JBSMParams::getTemplateparams($templatemenuid);
$pagebuilder = new JBSMPageBuilder;

$admin = JBSMParams::getAdmin();
$admin_params = new Registry($admin->params);
$params->merge($admin_params);
$template->params->merge($params);
$params = $template->params;

require_once dirname(__FILE__) . '/helper.php';

$items = ModJBSMHelper::getLatest($params);

// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
$user   = JFactory::getUser();
$groups = $user->getAuthorisedViewLevels();
$count  = count($items);

if ($params->get('useexpert_module') > 0 || is_string($params->get('moduletemplate')) == true)
{

	foreach ($items AS $item)
	{
		$item->slug       = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
			. str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));
		$pelements        = $pagebuilder->buildPage($item, $params, $template);
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
	$templatemenuid = $jinput->getInt('templatemenuid', 1);
}
$linkurl  = JRoute::_('index.php?option=com_biblestudy&view=sermons&t=' . $templatemenuid);
$link     = '<a href="' . $linkurl . '"><button class="btn">' . $link_text . ' --></button></a>';
$document = JFactory::getDocument();

JHtml::_('biblestudy.framework');
JHtml::_('biblestudy.loadcss', $params);
$config = JComponentHelper::getParams('com_biblestudy');

// We need to load the path to the helper files
$path1 = JPATH_BASE . '/components/com_biblestudy/helpers/';
$url   = $params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}
$pageclass_sfx = $params->get('pageclass_sfx');

if ($params->get('useexpert_module') > 0)
{
	$template = 'default_custom';
}
elseif ($params->get('moduletemplate'))
{
	$template = 'default_' . $params->get('moduletemplate');
}
else
{
	$template = 'default_main';
}
require JModuleHelper::getLayoutPath('mod_biblestudy', $template);
