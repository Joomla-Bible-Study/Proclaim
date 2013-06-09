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

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$go             = modBibleStudyPodcast::checkforcombiblestudy($params);
$templateparams = null;

if (!$go)
{
	echo "Extension Bible Study not present or enabled";
}
else
{
	$templateparams = modBibleStudyPodcast::getTemplateParams($params);
}
require_once(JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php');
JLoader::register('PodcastSubscribe', JPATH_ROOT . '/components/com_biblestudy/helpers/podcastsubscribe.php');
JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
// Load the css
$document = JFactory::getDocument();
$css      = $templateparams->get('css');

if (!$css || $css == "-1")
{
	$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
}
else
{
	$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
}
$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);

// Run the podcast subscription
$podcast   = new podcastSubscribe;
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

// Display the module
require JModuleHelper::getLayoutPath('mod_biblestudy_podcast', 'default');