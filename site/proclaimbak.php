<?php
namespace CWM\Component\Proclaim\Site\Controller;
use Joomla\CMS\Factory;
use JLoader;
use Joomla\CMS\Language\Text;
/**
 * Core BibleStudy Site File
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
// Include dependencies
//JLoader::registerNamespace(CWM\Component\Proclaim);
JLoader::registerPrefix('CWMHelperRoute', JPATH_COMPONENT );

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new Exception(Text::_('JERROR_ERROR') . Text::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 404);
}

$controller = BaseController::getInstance('Proclaim');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
