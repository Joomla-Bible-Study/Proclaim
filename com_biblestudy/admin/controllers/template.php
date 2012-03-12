<?php

/**
 * @version     $Id: template.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.backup.php');
require_once ( JPATH_ROOT .DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'parameter.php' );
jimport('joomla.application.component.controllerform');

abstract class controllerClass extends JControllerForm {

}

class BiblestudyControllerTemplate extends controllerClass {

	protected $view_list = 'templates';

	function __construct() {
		parent::__construct();

		//register extra tasks
	}


	function copy() {
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$model = & $this->getModel('template');

		if ($model->copy($cid)) {
			$msg = JText::_('JBS_TPL_TEMPLATE_COPIED');
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=templates', $msg);
	}


	function makeDefault() {
		$mainframe = & JFactory::getApplication();
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseError(500, JText::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'));
		}

		$model = $this->getModel('template');
		if (!$model->makeDefault($cid, 0)) {
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_biblestudy&view=templates');
	}

        function export_import()
        {
               if ( $userfile = JRequest::getVar('template_import', null, 'files', 'array'))
               {
                   if ($import = $this->template_import($userfile))
                   {
                       $message = JText::_('JBS_TPL_IMPORT_SUCCESS');
                       $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
                   }
                   else
                   {
                       $message = JText::_('JBS_TPL_IMPORT_FAILED');
                       $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
                   }
               }
               //assuming there is not $userfile, then this is an export
               if ($export = $this->template_export())
                    {
                        $message = JText::_('JBS_TPL_EXPORT_SUCCESS');
                        $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
                   }
               else
                   {
                       $message = JText::_('JBS_TPL_EXPORT_FAILED');
                       $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
                   }
               
        }
           
            
        
        function template_import($userfile)
        {
             jimport('joomla.filesystem.file');
            // Make sure that file uploads are enabled in php
            if (!(bool) ini_get('file_uploads')) {
                JError::raiseWarning('SOME_ERROR_CODE', JText::_('JBS_TPL_NO_UPLOADS'));
                return false;
            }
            
        }
        
        function template_export()
        {
            if (!$exporttemplate = JRequest::get('template_export','','post'))
            {
                $message = JText::_('JBS_TPL_NO_FILE_SELECTED');
                $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
            }
            $objects = array();
            jimport('joomla.filesystem.file');
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('t.id, t.type, t.params, t.title, t.text');
            $query->from('#__bsms_templates as t');
            $query->where('t.id = '.$exporttemplate);
            $db->setQuery($query);
            $result = $db->loadObject();
            //Create the main template insert
            $objects[] = 'INSERT INTO #__bsms_templates SET `type` = "'.$db->getEscaped($result->type).'",
                `params` = "'.$db->getEscaped($result->params).'", `title` = "'.$db->getEscaped($result->text).'";\n';
            $registry = new JRegistry;
            $registry->loadJSON($result->params);
            $params = $registry;
            //Get the individual template files
            if($sermons = $params->get('sermonstemplate')){$objects[]=$this->getTemplate($sermons);}
            if($sermon = $params->get('sermontemplate')){$objects[]=$this->getTemplate($sermon);}
            if($teachers = $params->get('teacherstemplate')){$objects[]=$this->getTemplate($teachers);}
            if($teacher = $params->get('teachertemplate')){$objects[]=$this->getTemplate($teacher);}
            if($seriesdisplays = $params->get('seriesdisplays')){$objects[]=$this->getTemplate($seriesdisplays);}
            if($seriesdisplay = $params->get('seriesdisplay')){$objects[]=$this->getTemplate($seriesdisplay);}
            $filecontents = implode('\n',$objects);
            $filename = $result->title.'sql';
            $filepath = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename;
            if (!$filewrite = JFile::write($filepath,$filecontents)){return false;}
            $xport = new JBSExport();
            $savefile = $xport->output_file($filepath, $filename, 'text/x-sql');
        }
        
        function getTemplate($template)
        {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('tc.id, tc.templatecode,tc.type,tc.filename');
            $query->from('#__bsms_templatecode as tc');
            $query->where('tc.id ='.$template);
            $db->setQuery($query);
            if (!$object = $db->loadObject()) {return false;}
            $templatereturn = 'INSERT INTO #__bsms_templatecode SET `type` = "'.$db->getEscaped($object->type).'",
                `templatecode` = "'.$db->getEscaped($object->templatecode).'", `filename`="'.$db->getEscaped($object->filename).'",
                `published` = "1";\n';
            return $templatereturn;
        }
}