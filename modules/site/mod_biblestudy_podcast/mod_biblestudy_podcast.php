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
// No direct access
defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

$templateparams = null;

if (!JComponentHelper::isEnabled('com_proclaim'))
{
	throw new Exception("Extension Bible Study not present or enabled");
}

$templateparams = JBSMParams::getTemplateparams($params);

JHtml::_('biblestudy.framework');
JHtml::stylesheet('media/css/podcast.css');
$podcast   = new JBSMPodcastSubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require JModuleHelper::getLayoutPath('mod_biblestudy_podcast', 'default');
