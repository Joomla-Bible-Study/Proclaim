<?php
/**
 * Podcast Model
 *
 * @package     Proclaim
 * @subpackage  Model.Podcast
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 */
use CWM\Component\Proclaim\Site\Helper\CWMPodcastsubscribe;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// No direct access
defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

$templateparams = null;

if (!ComponentHelper::isEnabled('com_proclaim'))
{
	throw new Exception("Extension Proclaim not present or enabled");
}

//$templateparams = CWMParams::getTemplateparams($params);

//$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
//$wa->useStyle('com_proclaim.cwmcore');
//$wa->useStyle('com_proclaim.podcast');

$podcast   = new CWMPodcastSubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require ModuleHelper::getLayoutPath('mod_proclaim_podcast', 'default');
