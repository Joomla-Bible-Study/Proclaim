<?php
/**
 * Core Admin BibleStudy file
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new Exception(JText::_('JERROR_ERROR') . JText::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 403);
}

addCSS();

$app = JFactory::getApplication();

$jbsstate = JBSMDbHelper::getInstallState();

$type = $app->input->get('view');

$controller = JControllerLegacy::getInstance('Biblestudy');

if ($jbsstate && $type == 'install')
{
	JBSMHelper::clearcache('admin');
	JBSMHelper::clearcache('site');
	$app->input->set('task', 'browse');
}

$controller->execute($app->input->get('task'));
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
	if (JBSMDEBUG)
	{
		JHtml::stylesheet('media/com_biblestudy/css/biblestudy-debug.css');
	}

	JHtml::stylesheet('media/com_biblestudy/css/general.css');
	JHtml::stylesheet('media/com_biblestudy/css/icons.css');
}
