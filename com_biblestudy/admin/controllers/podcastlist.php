<?php

/**
 * @version     $Id: podcastlist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controlleradmin');

    abstract class controllerClass extends JControllerAdmin {

    }

class biblestudyControllerpodcastlist extends controllerClass {

    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {
        parent::__construct();

        // Register Extra tasks
        $this->registerTask('add', 'edit');
    }

    function edit() {
        JRequest::setVar('view', 'podcastedit');
        JRequest::setVar('layout', 'form');
        JRequest::setVar('hidemainmenu', 1);

        parent::display();
    }

    /**
     * remove record(s)
     * @return void
     */
    function remove() {
        $model = $this->getModel('podcastedit');
        if (!$model->delete()) {
            $msg = JText::_('JBS_PDC_ERROR_DELETING_PODCAST');
        } else {
            $msg = JText::_('JBS_PDC_PODCAST_DELETED');
        }

        $this->setRedirect('index.php?option=com_biblestudy&view=podcastlist', $msg);
    }

  /*  function writeXMLFile() {

        $mainframe = & JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $path1 = JPATH_SITE . '/components/com_biblestudy/helpers/';
        include_once($path1 . 'writexml.php');


        $result = writeXML();
        if ($result) {

           
            $application = JFactory::getApplication();
            $application->enqueueMessage( ''. JText::_('JBS_PDC_PODCAST_SAVED') .'' ) ;
            
        } else {
            $application->enqueueMessage( ''. JText::_('JBS_PDC_ERROR_SAVING_PODCAST') .'' ) ;
        }
    }
*/
    /**
     * Proxy for getModel
     *
     * @param <String> $name    The name of the model
     * @param <String> $prefix  The prefix for the PHP class name
     * @return JModel
     *
     * @since 7.0
     */
    public function &getModel($name = 'podcastedit', $prefix = 'biblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

}

?>