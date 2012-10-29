<?php

/**
 * Core Admin BibleStudy file
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_biblestudy')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once(JPATH_COMPONENT_ADMINISTRATOR . '/liveupdate/liveupdate.php');
if (JRequest::getCmd('view', '') == 'liveupdate') {
    LiveUpdate::handleRequest();
    return;
}

require_once(JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php');


jimport('joomla.version');
$version = new JVersion();

if ($version->RELEASE == '3.0') {
    $versionName = TRUE;
} else {
    $versionName = FALSE;
}
define('BIBLESTUDY_CHECKREL', $versionName);


// Register helper class
JLoader::register('BibleStudyHelper', dirname(__FILE__) . '/helpers/biblestudy.php');

addCSS();
addJS();

$controller = JControllerLegacy::getInstance('Biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

/**
 * Global css
 *
 * @since   1.7.0
 */
function addCSS() {
    if (BibleStudyHelper::debug() === '1'):
        JHTML::stylesheet('media/com_biblestudy/css/biblestudy-debug.css');
    endif;
    if (!BIBLESTUDY_CHECKREL):
        JHTML::stylesheet('media/com_biblestudy/css/z2.5.css');
        JHTML::stylesheet('/media/com_biblestudy/jui/css/bootstrap.css');
        JHTML::stylesheet('/media/com_biblestudy/jui/css/chosen.css');
        JHTML::stylesheet('media/com_biblestudy/css/biblestudy-j2.5.css');
    endif;
    JHTML::stylesheet('media/com_biblestudy/css/general.css');
    JHTML::stylesheet('media/com_biblestudy/css/icons.css');
}

/**
 * Global JS
 *
 * @since   7.0
 */
function addJS() {
    if (!BIBLESTUDY_CHECKREL):
        JHTML::script('/media/com_biblestudy/jui/js/jquery.js');
        JHTML::script('/media/com_biblestudy/jui/js/jquery-ui.core.min.js');
        JHTML::script('/media/com_biblestudy/jui/js/bootstrap.js');
        JHTML::script('/media/com_biblestudy/jui/js/jquery-noconflict.js');
        JHTML::script('/media/com_biblestudy/jui/js/chosen.jquery.js');
    endif;
    //JHTML::script('media/com_biblestudy/js/ui/jquery-ui.js');
}