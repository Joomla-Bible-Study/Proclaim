<?php
/**
 * Mod_Biblestudy core file
 *
 * @package     Proclaim
 * @subpackage  Model.BibleStudy
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries
 * */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}
else
{
	return;
}

// Need for inline player
$document = Factory::getDocument();

/** @var $params Registry */
$templatemenuid = $params->get('t');
$template       = JBSMParams::getTemplateparams($templatemenuid);
$pagebuilder    = new JBSMPageBuilder;

$admin        = JBSMParams::getAdmin();
/** @var Registry $admin_params */
$admin_params = $admin->params;
$admin_params->merge($template->params);
$admin_params->merge($params);
$params = $admin_params;

require_once __DIR__ . '/helper.php';

$items = ModJBSMHelper::getLatest($params);

// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
$user   = Factory::getUser();
$groups = $user->getAuthorisedViewLevels();
$count  = count($items);

if ($params->get('useexpert_module') > 0 || is_string($params->get('moduletemplate')) === true)
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

$linkurl  = JRoute::_('index.php?option=com_proclaim&view=sermons&t=' . $templatemenuid);
$link     = '<a href="' . $linkurl . '"><button class="btn">' . $link_text . '</button></a>';
$document = Factory::getDocument();

JHtml::_('biblestudy.framework');
JHtml::_('biblestudy.loadcss', $params);
$config = JComponentHelper::getParams('com_proclaim');

// We need to load the path to the helper files
$path1 = JPATH_BASE . '/components/com_proclaim/helpers/';
$url   = $params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}

JHtml::_('biblestudy.loadCss', $params, null, 'font-awesome');
$pageclass_sfx = $params->get('pageclass_sfx');

if ($params->get('simple_mode') === '1')
{
	$template = 'default_simple';
}
elseif ($params->get('moduletemplate') && !$params->get('simple_mode'))
{
	$template = 'default_' . $params->get('moduletemplate');
}
else
{
	$template = 'default_main';
}

require JModuleHelper::getLayoutPath('mod_biblestudy', $template);
