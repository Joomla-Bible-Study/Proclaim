<?php

/**
 * @package BibleStudy
 * @subpackage Model.Podcast
 * @author Joomla Bible Study Team
 * @copyright 2012
 * @desc a module to display the podcast subscription table
 */
defined('JPATH_BASE') or die;

//Check to see if the component is enabled and the version
$db = JFactory::getDBO();
$query = $db->getQuery('true');
$query->select('element, enabled');
$query->from('#__extensions');
$query->where('element = "com_biblestudy"');
$db->setQuery($query);
$db->query();
$results = $db->loadObjectList();
if (!$results) {
    echo 'Extension Bible Study not found';
    $go = false;
} else {
    foreach ($results as $result) {
        if ($result->enabled == '1') {
            $go = true;
        } else {
            $go = false;
        }
    }
}
if (!$go) {
    echo "Extension Bible Study not present or enabled";
} else {
    //get the params
    $introtext = $params->get('subscribeintro', "Follow Us!");
    $t = $params->get('t', 1);
    $query = $db->getQuery('true');
    $query->select('*');
    $query->from('#__bsms_templates');
    $query->where('id = ' . $t);
    $db->setQuery($query);
    $db->query();
    $template = $db->loadObject();
    $registry = new JRegistry;
    $registry->loadJSON($template->params);
    $templateparams = $registry;

    //load the helper file
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
    echo $subscribe;
}