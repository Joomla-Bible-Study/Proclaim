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

require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';
JLoader::discover('JBSM', BIBLESTUDY_PATH_HELPERS);
JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_HELPERS);
JHtml::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html/');

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
JHtml::styleSheet('media/css/podcast.css');
$podcast   = new JBSMPodcastSubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require JModuleHelper::getLayoutPath('mod_biblestudy_podcast', 'default');
