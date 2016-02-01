<?php
/**
 * Podcast Model
 *
 * @package     BibleStudy
 * @subpackage  Model.Podcast
 * @copyright   (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 */
// No direct access
defined('_JEXEC') or die;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

$templateparams = null;

if (!JComponentHelper::isEnabled('com_biblestudy'))
{
	throw new Exception("Extension Bible Study not present or enabled");
}
else
{
	$templateparams = JBSMParams::getTemplateParams($params);
}

JHtml::_('biblestudy.framework');
JHtml::_('biblestudy.loadcss', $params);
$podcast   = new JBSMPodcastSubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require JModuleHelper::getLayoutPath('mod_biblestudy_podcast', 'default');
