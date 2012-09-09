<?php

/**
 * Podcast Model
 * @package BibleStudy
 * @subpackage Model.Podcast
 * @author Joomla Bible Study Team
 * @copyright 2012
 * @desc a module to display the podcast subscription table
 */
// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$go = modBibleStudyPodcast::checkforcombiblestudy($params);
if (!$go) {
    echo "Extension Bible Study not present or enabled";
} else {
    $templateparams = modBibleStudyPodcast::getTemplateParams($params);
}
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'podcastsubscribe.php');

//load the css
$document = JFactory::getDocument();
$url = $templateparams->get('css', 'biblestudy.css');
if ($url) {
    $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $url);
}
//run the podcast subscription
$podcast = new podcastSubscribe();
$subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

//display the module
require JModuleHelper::getLayoutPath('mod_biblestudy_podcast', 'default');