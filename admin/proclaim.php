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
use CWM\Component\Proclaim\Administrator\Helper\CWMDbHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

$app = Factory::getApplication();

// Access check.
if (!$app->getIdentity()->authorise('core.manage', 'com_proclaim'))
{
	throw new JAccessExceptionNotallowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new \Exception(Text::_('JERROR_ERROR') . Text::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 403);
}

addCSS();

$jbsstate = CWMDbHelper::getInstallState();

$type = $app->input->get('view');

$controller = BaseController::getInstance('CWMProclaim');

if ($jbsstate && $type === 'install')
{
	CWMHelper::clearcache('administrator');
	CWMHelper::clearcache('site');
	$app->input->set('task', 'browse');
}

$controller->execute($app->input->get('task'));
$controller->redirect();

/**
 * Global css
 *
 * @return void
 *
 * @throws \Exception
 * @since   7.0
 */
function addCSS()
{
	$document = Factory::getApplication()->getDocument();

	if (JBSMDEBUG)
	{
		$document->addStyleSheet('media/com_proclaim/css/biblestudy-debug.css');
	}

	$document->addStyleSheet('media/com_proclaim/css/general.css');
	$document->addStyleSheet('media/com_proclaim/css/icons.css');
}
