<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

define('JSTART', '$j(document).ready( function() {');
define('JSTOP', '});');
addLoadingDiv();
addCSS();
addJS();

jimport('joomla.application.component.controller');
$controller = JController::getInstance('biblestudy');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

/**
 * Adds a loading div for any ajax requests via JQuery
 * This will become obsolete if we move away from JQuery
 * 
 * @since   7.0
 */
function addLoadingDiv() {
    echo '
                <div id="loading">
                    <img src="' . JURI::base() . 'components/com_biblestudy/images/loading.gif."/>
                    <span id="loadingMsg">Loading...</span>
                </div>
                ';
}

/**
 * Global css
 *
 * @since   7.0
 */
function addCSS() {
    $doc = & JFactory::getDocument();
    $doc->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/general.css');
    $doc->addStyleSheet(JURI::base() . 'components/com_biblestudy/css/icons.css');
}

/**
 * Global JS
 * 
 * @since   7.0
 */
function addJS() {
    $doc = & JFactory::getDocument();
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/jquery.js');
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/noconflict.js');
    $doc->addScript(JURI::base() . 'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
}

?>
