<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');

jimport('joomla.application.component.view');

/**
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewcommentsedit extends JView {

    protected $form;
    protected $item;
    protected $state;

    /**
     *
     * @param boolean $tpl
     * @return boolean
     */
    function display($tpl = null) {
        $this->canDo = @BibleStudyHelper::getActions($this->item->id, 'commentsedit');
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");

        //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = @BsmHelper::getAdmin($issite = true);
        //check permissions to enter studies
        //check permissions to enter studies
        if (!$this->canDo->get('core.edit')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
        $this->setLayout('form');


        parent::display($tpl);
    }

}