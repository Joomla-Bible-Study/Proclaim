<?php

/**
 * @version     $Id: view.html.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();
require_once (JPATH_SITE  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'biblestudy.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');
jimport('joomla.html.toolbar');

/**
 * @package     BibleStudy.Administrator
 * @since       7.0
 */
class biblestudyViewmediafiles extends JView {

    protected $items;
    protected $pagination;
    protected $state;

    function display($tpl = null) {
         $this->canDo	= BibleStudyHelper::getActions($this->item->id, 'mediafilesedit');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->mediatypes = $this->get('Mediatypes');
        $document = & JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . 'components' .DS. 'com_biblestudy' .DS. 'assets' .DS. 'css' .DS.  'icons.css');
        //Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }


 if (!$this->canDo->get('core.edit')) 
        {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
 // Load the toolbar helper
 require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'toolbar.php' );


 // render the toolbar on the page. rendering it here means that it is displayed on every view of your component.

      //Puts a new record link at the top of the form
      if ($this->canDo->get('core.create')) 
        { 
            echo '<a href="index.php?option=com_biblestudy&view=mediafile&layout=form">'.JText::_('JBS_CMN_NEW').'</a>';
        }
        parent::display($tpl);
    }

   

}

?>
