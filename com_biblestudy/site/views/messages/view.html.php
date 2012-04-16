<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'biblestudy.php');
require_once (JPATH_ROOT  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.admin.class.php');
jimport('joomla.application.component.view');

class biblestudyViewmessages extends JView {
    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
        $this->canDo = BibleStudyHelper::getActions('', 'studiesedit');
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->books = $this->get('Books');
        $this->teachers = $this->get('Teachers');
        $this->series = $this->get('Series');
        $this->messageTypes = $this->get('MessageTypes');
        $this->years = $this->get('Years');

        $user = JFactory::getUser();

        if (!$this->canDo->get('core.edit'))
        {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
     //Puts a new record link at the top of the form
     if ($this->canDo->get('core.create'))
     {
      $this->newlink = '<a href="'.JRoute::_('index.php?option=com_biblestudy&view=message&task=message.edit').'">'.JText::_('JBS_CMN_NEW').'</a>';
     }
        parent::display($tpl);

    }

}