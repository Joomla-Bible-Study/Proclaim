<?php

/**
 * Controller for Styles list
 * @package BibleStudy.Admin
 * @since 7.1.0
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Styles list controller class.
 *
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyControllerStyles extends JControllerAdmin {

    /**
     * Proxy for getModel
     *
     * @param string $name    The name of the model
     * @param string $prefix  The prefix for the PHP class name
     * @param array $config
     *
     * @return JModel
     * @since 7.1.0
     */
    public function getModel($name = 'Style', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Tries to fix css renaming.
     *
     * @since	7.1.0
     */
    function fixcss() {

        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        // Initialise variables.
        $user = JFactory::getUser();
        $input = new JInput;
        $ids = $input->get('cid','','array');
        //$ids = JRequest::getVar('cid', array(), '', 'array');

        // Access checks.
        foreach ($ids as $i => $id) {
            if (!$user->authorise('core.edit.state', 'com_biblestudy.styles.' . (int) $id)) {
                // Prune items that you can't change.
                unset($ids[$i]);
                JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
            }
        }

        if (empty($ids)) {
            JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
        } else {
            // Get the model.
            $model = $this->getModel();

            // Publish the items.
            if (!$model->fixcss($ids)) {
                JError::raiseWarning(500, $model->getError());
            }
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=styles');
    }

}