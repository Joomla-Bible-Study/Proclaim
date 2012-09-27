<?php

/**
 * Controller for the css styles
 * @package BibleStudy.Admin
 * @since 7.1.0
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Style Class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerStyle extends JControllerForm {

    /**
     * Tries to fix css renaming.
     *
     * @since	7.1.0
     */
    public function fixcss() {
        $model = $this->getModel('styles');
        $model->fixcss();
        $this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=styles', false));
    }

}