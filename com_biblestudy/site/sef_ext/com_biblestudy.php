<?php
/**
 * sh404SEF support for com_biblestudy component.
 *
 * @package     Proclaim.Site
 * @subpackage  sh404SEF.BibleStudy
 * @author      Nick Fossen <nfossen@gmail.com>
 * @copyright   2016 (C) Proclaim
 * @url         http://www.newhorizoncf.org
 * @license     GPL v2
 * {shSourceVersionTag: Version 6.2 - 2010-07-06}
 *
 */
// No Direct Access
defined('_JEXEC') or die;

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig      = & shRouter::shGetConfig();
$shLangName     = '';
$shLangIso      = '';
$title          = array();
$shItemidString = '';
$dosef          = shInitializePlugin($lang, $shLangName, $shLangIso, $option);

if ($dosef == false)
{
	return;
}
// ------------------  standard plugin initialize function - don't change ---------------------------
// remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');

if (!empty($Itemid))
{
	shRemoveFromGETVarsList('Itemid');
}

if (!empty($limit))
{
	shRemoveFromGETVarsList('limit');
}

if (isset($limitstart))
{
	shRemoveFromGETVarsList('limitstart');
}

// All urls will start with Biblestudy.  The "B" need to be uppercase
$title[] = "Biblestudy";

switch ($view)
{
	case 'sermons':
		$title[] = $view;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		break;
	case 'sermon': /* Need to keep the id because of the number of teachings */
		$title[] = $view;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		break;
	case 'seriesdisplays':
		$title[] = $view;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		break;
	case 'seriesdisplay':
		$title[] = $view;
		$query_name = $database->getQuery(true);
		$query_name->select('series_text')->from('#__bsms_series')->where('#__bsms_series.id = ' . $id);
		$database->setQuery($query_name);
		$series  = $database->loadResult();
		$title[] = $series;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		shRemoveFromGETVarsList('id');
		break;
	case 'teachers':
		$title[] = $view;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		break;
	case 'teacher':
		$title[] = $view;
		$query_name = $database->getQuery(true);
		$query_name->select('teachername')->from('#__bsms_teachers')->where('#__bsms_teachers.id = ' . $id);
		$database->setQuery($query_name);
		$teacher = $database->loadResult();
		$title[] = $teacher;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		shRemoveFromGETVarsList('id');
		break;
	case 'landingpage':
		$title[] = $view;
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		shRemoveFromGETVarsList('id');
		break;
	case 'popup':
		$title[] = $view;
		shRemoveFromGETVarsList('player');
		shRemoveFromGETVarsList('template');
		shRemoveFromGETVarsList('view');
		shRemoveFromGETVarsList('Itemid');
		shRemoveFromGETVarsList('id');
		break;
}

// Change the URL for downloading file
if (isset($task))
{
	if ($task == 'download')
	{
		$title[] = 'download';
		shRemoveFromGETVarsList('controller');
		shRemoveFromGETVarsList('task');
	}
}

// Remove biblestudy URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('t');


// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef)
{
	$string = shFinalizePlugin(
		$string, $title, $shAppendString, $shItemidString, (isset($limit) ? @$limit : null),
		(isset($limitstart) ? @$limitstart : null), (isset($shLangName) ? @$shLangName : null)
	);
}

// ------------------  standard plugin finalize function - don't change ---------------------------
