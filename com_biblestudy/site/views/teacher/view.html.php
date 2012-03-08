<?php

/**
 * @version     $Id: view.html.php 1330 2011-01-06 08:01:38Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.pagebuilder.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
require_once (JPATH_ROOT  .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_biblestudy' .DIRECTORY_SEPARATOR. 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.images.class.php');
$path1 = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR;
include_once($path1 . 'teacher.php');
include_once($path1 . 'listing.php');
include_once($path1.'image.php');
jimport('joomla.application.component.view');

class BiblestudyViewTeacher extends JView {

    function display($tpl = null) {

        $mainframe = JFactory::getApplication();
        $pagebuilder = new JBSPagebuilder();

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        $pathway = $mainframe->getPathWay();
        $images = new jbsImages();

        //Load the Admin settings and params from the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin(true);
        $this->admin_params = $this->admin;

        $itemparams = $mainframe->getPageParameters();

        //Prepare meta information (under development)
        if ($itemparams->get('metakey')) {
            $document->setMetadata('keywords', $itemparams->get('metakey'));
        } elseif (!$itemparams->get('metakey')) {
            $document->setMetadata('keywords', $this->admin_params->get('metakey'));
        }

        if ($itemparams->get('metadesc')) {
            $document->setDescription($itemparams->get('metadesc'));
        } elseif (!$itemparams->get('metadesc')) {
            $document->setDescription($this->admin_params->get('metadesc'));
        }
        $t = JRequest::getInt('t', 'get', 1);
        if (!$t) {
            $t = 1;
        }
        $template = $this->get('template');
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template[0]->params);
        $params = $registry;

        $css = $params->get('css','biblestudy.css');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/'.$css);

        $item = $this->get('Item');
        //add the slug
        $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : str_replace(' ', '-', htmlspecialchars_decode($item->teachername, ENT_QUOTES)) . ':' . $item->id;
        $id = JRequest::getInt('id', 'get');
        if ($id) {
            $item->id = $id;
        }
        $item->image = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
        $this->assignRef('item', $item);
      
        $whereitem = $item->id;
        $wherefield = 'study.teacher_id';
        $this->teacherstudies = $pagebuilder->studyBuilder($whereitem, $wherefield, $params, $this->admin_params);
      
        $this->item = $item;
        $print = JRequest::getBool('print');
        // build the html select list for ordering
        $this->assignRef('print', $print);
        $this->assignRef('params', $params);
        $this->assignRef('template', $template);
        parent::display($tpl);
    }

    

}