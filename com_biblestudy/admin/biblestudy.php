<?php
/**
 * Core Admin BibleStudy file
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}


if (JFactory::getApplication()->input->getCmd('view', '') == 'liveupdate')
{
	LiveUpdate::handleRequest();

	return;
}

include_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new Exception(JText::_('JERROR_ERROR') . JText::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 404);
}

if (version_compare(JVERSION, '3.0', 'ge'))
{
	define('BIBLESTUDY_CHECKREL', true);
}
else
{
	define('BIBLESTUDY_CHECKREL', false);
}

// Component debugging
define("COM_BIBLESTUDY_DEBUG", false);

JLoader::discover('JBSM', BIBLESTUDY_PATH_LIB);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_LIB);
JLoader::discover('JBSM', BIBLESTUDY_PATH_HELPERS);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_HELPERS);
JLoader::discover('JBS', BIBLESTUDY_PATH_ADMIN_ADDONS_SERVERS);

addCSS();
addJS();

$controller = JControllerLegacy::getInstance('Biblestudy');
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();

/**
 * Global css
 *
 * @return void
 *
 * @since   7.0
 */
function addCSS()
{
	if (JBSMBibleStudyHelper::debug() === '1')
	{
		JHTML::stylesheet('media/com_biblestudy/css/biblestudy-debug.css');
	}
	if (!BIBLESTUDY_CHECKREL)
	{
		JHTML::stylesheet('media/com_biblestudy/jui/css/icomoon.css');
		JHTML::stylesheet('media/com_biblestudy/jui/css/bootstrap.css');
		JHTML::stylesheet('media/com_biblestudy/css/biblestudy-j2.5.css');
	}
	JHTML::stylesheet('media/com_biblestudy/css/general.css');
	JHTML::stylesheet('media/com_biblestudy/css/icons.css');
}

/**
 * Global JS
 *
 * @return void
 *
 * @since   7.0
 */
function addJS()
{
	if (!BIBLESTUDY_CHECKREL)
	{
		JHTML::script('media/com_biblestudy/jui/js/jquery.min.js');
		JHTML::script('media/com_biblestudy/jui/js/jquery-noconflict.js');
		JHTML::script('media/com_biblestudy/jui/js/plugins/bootstrap.min.js');
		JHTML::script('media/com_biblestudy/jui/js/plugins/chosen.jquery.min.js');
		JHTML::script('media/com_biblestudy/jui/js/plugins/jquery.ui.core.min.js');
		JHTML::script('media/com_biblestudy/jui/js/plugins/jquery.ui.sortable.min.js');
	}
}
