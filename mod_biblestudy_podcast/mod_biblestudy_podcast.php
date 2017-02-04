<?php
/**
 * Podcast Model
 *
 * @package     BibleStudy
 * @subpackage  Model.Podcast
 * @copyright   2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.joomlabiblestudy.org
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
	$templateparams = JBSMParams::getTemplateparams($params);
}

JHtml::_('biblestudy.framework');
JHtml::stylesheet('media/css/podcast.css');
$podcast   = new JBSMPodcastSubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require JModuleHelper::getLayoutPath('mod_biblestudy_podcast', 'default');
