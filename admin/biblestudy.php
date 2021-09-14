<?php
/**
 * Core Admin BibleStudy file
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

//JLoader::registerNamespace('\\CWM\\Application\\Proclaim', JPATH_ADMINISTRATOR . '/components/com_biblestudy/src');

JLoader::registerNamespace('CWM\Component\BibleStudy\Administrator\Helper', JPATH_COMPONENT . '/src/Helper');

// No Direct Access
use CWM\Component\BibleStudy\Administrator\Helper\CWMDbHelper;
use CWM\Component\BibleStudy\Administrator\Helper\CWMHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Finder\Administrator\Indexer\Parser\Html;

defined('_JEXEC') or die;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_biblestudy'))
{
	throw new JAccessExceptionNotallowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new \Exception(Text::_('JERROR_ERROR') . Text::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 403);
}

addCSS();

$app = Factory::getApplication();


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
 * @since   7.0
 */
function addCSS()
{
	$document = Factory::getDocument();

	if (JBSMDEBUG)
	{
		$document->addStyleSheet('media/com_biblestudy/css/biblestudy-debug.css');
	}

	$document->addStyleSheet('media/com_biblestudy/css/general.css');
	$document->addStyleSheet('media/com_biblestudy/css/icons.css');
}
