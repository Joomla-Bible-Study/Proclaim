<?php
/**
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since		7.1.0
 * */

// No direct access.
defined('_JEXEC') or die;

/**
 * @package		BibleStudy.Administrator
 * @since	7.1.0
 */
class BiblestudyControllerDatabase extends JController {

    /**
     * Tries to fix missing database updates
     *
     * @since	7.1.0
     */
    function fix() {
        $model = $this->getModel('database');
        $model->fix();
        $this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=admin', false));
    }

}
