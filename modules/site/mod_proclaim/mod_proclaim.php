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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Site\Helper\CWMPagebuilder;
use CWM\Module\Proclaim\Site\Helper\ModProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
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
try
{
	$document = Factory::getApplication()->getDocument();
}
catch (Exception $e)
{
	Factory::getApplication()->enqueueMessage($e, 'error');
}

/** @var $params Registry */
$templatemenuid = $params->get('t');

try
{
	$template = CWMParams::getTemplateparams($templatemenuid);
}
catch (\Exception $e)
{
	Factory::getApplication()->enqueueMessage($e, 'error');
}

$pagebuilder = new CWMPageBuilder;

$admin = CWMParams::getAdmin();
/** @var Registry $admin_params */
$admin_params = $admin->params;
$admin_params->merge($template->params);
$admin_params->merge($params);
$params = $admin_params;

$items = ModProclaimHelper::getLatest($params);

// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
$user     = Factory::getApplication()->getIdentity();
$groups = $user->getAuthorisedViewLevels();
$count  = count($items);

if ($params->get('useexpert_module') > 0 || is_string($params->get('moduletemplate')) === true)
{
	foreach ($items AS $item)
	{
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
			. str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));

		try
		{
			$pelements = $pagebuilder->buildPage($item, $params, $template);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e, 'error');
		}

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

$input = Factory::getApplication()->input;

if (!$templatemenuid)
{
	$templatemenuid = $input->getInt('templatemenuid', 1);
}

$linkurl = Route::_('index.php?option=com_proclaim&view=cwmsermons&t=' . $templatemenuid);
$link    = '<a href="' . $linkurl . '"><button class="btn">' . $link_text . '</button></a>';

HtmlHelper::_('proclaim.framework');
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('com_proclaim.cwmcore');
$wa->useStyle('com_proclaim.general');
$config = JComponentHelper::getParams('com_proclaim');

// We need to load the path to the helper files
// $path1 = JPATH_BASE . '/components/com_proclaim/helpers/';
$url = $params->get('stylesheet');

if ($url)
{
	$document->addStyleSheet($url);
}

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

require ModuleHelper::getLayoutPath('mod_proclaim', $template);
