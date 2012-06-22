<?php

/**
 * @version     $Id: viewj16.html.php 1394 2011-01-17 21:39:05Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');

class biblestudyViewmediafile extends JView {

    protected $form;
    protected $item;
    protected $state;
    protected $admin;

    function display($tpl = null) {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        
        $this->state = $this->get("State");
        $this->canDo = BibleStudyHelper::getActions($this->item->id, 'mediafilesedit');
        //Load the Admin settings
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($issite = true);

        //Needed to load the article field type for the article selector
        jimport('joomla.form.helper');
        JFormHelper::addFieldPath(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_content' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR . 'modal');

        $user = JFactory::getUser();


        if (!$this->canDo->get('core.edit')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
         $document = JFactory::getDocument();
         $host = JURI::root();
                $document->addScript($host.'media/com_biblestudy/js/swfupload/swfupload.js');
                $document->addScript($host.'media/com_biblestudy/js/swfupload/swfupload.queue.js');
                $document->addScript($host.'media/com_biblestudy/js/swfupload/fileprogress.js');
                $document->addScript($host.'media/com_biblestudy/js/swfupload/handlers.js');
                $document->addScript(JURI::root() . 'administrator/components/com_biblestudy/views/mediafile/tmpl/submitbutton.js');
                $document->addStyleSheet($host.'media/com_biblestudy/js/swfupload/default.css');
                $document->addStyleSheet(JURI::base() . 'administrator/templates/system/css/system.css');
                $document->addStyleSheet(JURI::base() . 'administrator/templates/bluestork/css/template.css');
                $document->addStyleSheet($host . 'media/system/css/modal.css');
                $swfUploadHeadJs = JBSUpload::uploadjs($host);
                //add the javascript to the head of the html document
                $document->addScriptDeclaration($swfUploadHeadJs);
		//Needed to load the article field type for the article selector
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_content'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'modal');

                $db = JFactory::getDBO();
                //get server for upload dropdown
                $query = 'SELECT id as value, server_name as text FROM #__bsms_servers WHERE published=1 ORDER BY server_name ASC';
                $db->setQuery($query);
                $db->query();
               // $servers = $db->loadObjectList();
                $server = array(
                array('value' => '', 'text' => JText::_('JBS_MED_SELECT_SERVER')),
                );
                $serverlist = array_merge( $server, $db->loadObjectList() );
                $idsel = "'SWFUpload_0'";
                $this->assignRef('upload_server', JHTML::_('select.genericList', $serverlist, 'upload_server', 'class="inputbox" onchange="showupload('.$idsel.')"'. '', 'value', 'text', '' ));

                //Get folders for upload dropdown
                $query = 'SELECT id as value, foldername as text FROM #__bsms_folders WHERE published=1 ORDER BY foldername ASC';
                $db->setQuery($query);
                $db->query();
               // $folders = $db->loadObjectList();
                $folder = array(
                array('value' => '', 'text' => JText::_('JBS_MED_SELECT_FOLDER')),
                );
                $folderlist = array_merge( $folder, $db->loadObjectList() );
                $idsel = "'SWFUpload_0'";
                $this->assignRef('upload_folder', JHTML::_('select.genericList', $folderlist, 'upload_folder', 'class="inputbox" onchange="showupload('.$idsel.')"'. '', 'value', 'text', '' ));

        $this->setLayout('edit');

    //    require_once( JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'toolbar.php' );
    //    $toolbar = biblestudyHelperToolbar::getToolbar();
     //   $this->assignRef('toolbar', $toolbar);
        //	$isNew		= ($mediafilesedit->id < 1);

        parent::display($tpl);
    }

}