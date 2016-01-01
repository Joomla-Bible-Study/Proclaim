<?php
/**
 * Core Admin BibleStudy file
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}

include_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';

if (version_compare(PHP_VERSION, BIBLESTUDY_MIN_PHP, '<'))
{
	throw new Exception(JText::_('JERROR_ERROR') . JText::sprintf('JBS_CMN_PHP_ERROR', BIBLESTUDY_MIN_PHP), 404);
}

// Component debugging
define("COM_BIBLESTUDY_DEBUG", false);

JLoader::discover('JBSM', BIBLESTUDY_PATH_LIB);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_LIB);
JLoader::discover('JBSM', BIBLESTUDY_PATH_HELPERS);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_HELPERS);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_ADDON);
JHtml::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html/');

// If phrase is not found in specific language file, load english language file:
$language = JFactory::getLanguage();
$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, null, true);

addCSS();

$app = JFactory::getApplication();

$jbsstate = JBSMDbHelper::getInstallState();

$type = $app->input->getWord('view');

$controller = JControllerLegacy::getInstance('Biblestudy');

$controller->execute($app->input->getCmd('task'));
if ($jbsstate && $type == 'install')
{
	$cache = new JCache(array('defaultgroup' => 'default'));
	$cache->clean();
	$app->input->set('view', 'install');
	$controller->setRedirect('index.php?option=com_biblestudy&view=install');
}
else
{
	$controller->redirect();
}

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
	JHTML::stylesheet('media/com_biblestudy/css/general.css');
	JHTML::stylesheet('media/com_biblestudy/css/icons.css');
}
