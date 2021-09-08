<?php
namespace CWM\Component\Biblstudy\Controller;
use Joomla\CMS\Factory;
use Joomla\CMS\Router;
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
//Router::register('JBSMHelperRoute', JPATH_COMPONENT . '/helpers/route.php');

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new Exception(JText::_('JERROR_ERROR') . JText::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 404);
}

$controller = BaseController::getInstance('Biblestudy');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
