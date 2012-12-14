<?php

/**
 * Message JViewLegacy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');


/**
 * View class for Messages
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewmessages extends JViewLegacy {

    /**
     * Items
     * @var array
     */
    protected $items;

    /**
     * Pagination
     * @var array
     */
    protected $pagination;

    /**
     * State
     * @var array
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @see     fetch()
     * @since   11.1
     */
    public function display($tpl = null) {

	    $items = $this->get('Items');
	    $this->pagination = $this->get('Pagination');
	    $this->state = $this->get('State');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . 'administrator/templates/system/css/system.css');
        $document->addStyleSheet(JURI::base() . 'administrator/templates/bluestork/css/template.css');
        $this->canDo = JBSMHelper::getActions('', 'message');
		var_dump($this->canDo);
        $this->books = $this->get('Books');
        $this->teachers = $this->get('Teachers');
        $this->series = $this->get('Series');
        $this->messageTypes = $this->get('MessageTypes');
        $this->years = $this->get('Years');
	    $modelView = $this->getModel();
	    $this->items = $modelView->getTranslated($items);

        $user = JFactory::getUser();

        //if (!$this->canDo->get('core.edit')) {
        //    JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
        //    return false;
        //}
        //Puts a new record link at the top of the form
        //if ($this->canDo->get('core.create')) {
            $this->newlink = '<a href="' . JRoute::_('index.php?option=com_biblestudy&view=message&task=message.edit') . '">' . JText::_('JBS_CMN_NEW') . '</a>';
        //}

        parent::display($tpl);
    }

}