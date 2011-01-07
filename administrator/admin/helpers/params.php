<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.helper');

/**
 * //Eugen
 * This class may not be required
 */
class BsmHelper extends JComponentHelper {

    /**
     * Gets the settings from Admin
     *
     * @since   7.0
     */
    public function getDefaults() {
        jimport('joomla.form.form');
        JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms'); 
        $form = &JForm::getInstance('com_biblestudy.admin', 'admin');

    }
}
?>