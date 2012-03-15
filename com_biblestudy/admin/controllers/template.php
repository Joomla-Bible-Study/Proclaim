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

        
        
        function template_import()
        {
             jimport('joomla.filesystem.file');
            // Make sure that file uploads are enabled in php
            if (!(bool) ini_get('file_uploads')) {
                JError::raiseWarning('SOME_ERROR_CODE', JText::_('JBS_TPL_NO_UPLOADS'));
                return false;
            }
             jimport('joomla.filesystem.file');
        @set_time_limit(300);

        $result = false;
        $userfile = JRequest::getVar('template_import', null, 'files', 'array'); 
         // Make sure that file uploads are enabled in php
        if (!(bool) ini_get('file_uploads')) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLFILE'));
            return false;
        }


        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile)) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
            return false;
        }

        // Check if there was a problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLUPLOADERROR'));
            return false;
        }

        // Build the appropriate paths
        $config = JFactory::getConfig();
        $tmp_dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name'];

        $tmp_src = $userfile['tmp_name'];

        // Move uploaded file
        jimport('joomla.filesystem.file');
        $uploaded = @move_uploaded_file($tmp_src, $tmp_dest);

        $db = JFactory::getDBO();

        $query = @file_get_contents(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name']);
        $queries = $db->splitSql($query);
        foreach ($queries as $querie) {
            if (substr_count($querie,'INSERT'))
            { 
                $db->setQuery($querie);
                $db->query();
                if ($db->getErrorNum() != 0) {
                    $error = "DB function failed with error number " . $db->getErrorNum() . "<br /><font color=\"red\">";
                    $error .= $db->stderr(true);
                    $error .= "</font>";
                    print_r($error);
                    //return false;
                }
                $typecss = substr_count($querie,'#__bsms_styles');
                $typefile = substr_count($querie,'#__bsms_templatecode');
                $typetemplate = substr_count($querie,'#__bsms_templates');
                if ($typecss)
                {
                    $query = 'SELECT id from #__bsms_styles ORDER BY id DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $data = $db->loadObject();
                    JTable::addIncludePath(JPATH_COMPONENT.'/tables');
                    $table = JTable::getInstance('Style', 'BiblestudyTable', array('dbo' => $db));
                    if ($data->id)
                    {
                       $cssid = $data->filename; 
                       try {$table->load($data->id);}
                        catch (Exception $e) {echo 'Caught exception: ',  $e->getMessage(), "\n";}
                        if (!$table->store()) 
                        {
                            $this->setError($db->getErrorMsg());
                            return false;
                        }
                    }
                }
                if ($typefile)
                {
                    $query = 'SELECT id, filename from #__bsms_templatecode ORDER BY id DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $data = $db->loadObject();
                    JTable::addIncludePath(JPATH_COMPONENT.'/tables');
                    $table = JTable::getInstance('Templatecode', 'BiblestudyTable', array('dbo' => $db));
                    if ($data->id)
                    {
                       switch ($data->type)
                       {
                           case 1:
                               //sermonlist
                               $sermonstemplate = $data->filename;
                               break;
                           
                           case 2:
                               //sermon
                               $sermontemplate = $data->filename;
                               break;
                           
                           case 3:
                               //teachers
                               $teacherstemplate = $data->filename;
                               break;
                           
                           case 4:
                               //teacher
                               $teachertemplate = $data->filename;
                               break;
                           
                           case 5:
                               //serieslist
                               $seriesdisplaystemplate = $data->filename;
                               break;
                           
                           case 6:
                               //series
                               $seriesdisplaytemplate = $data->filename;
                               break;
                       }
                       try {$table->load($data->id);}
                        catch (Exception $e) {echo 'Caught exception: ',  $e->getMessage(), "\n";}
                        if (!$table->store()) 
                        {
                            $this->setError($db->getErrorMsg());
                            return false;
                        }
                    }
                }
                if ($typetemplate)
                {
                    $query = 'SELECT id from #__bsms_templates ORDER BY id DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $data = $db->loadObject(); 
                    JTable::addIncludePath(JPATH_COMPONENT.'/tables');
                    $table = JTable::getInstance('Template', 'Table', array('dbo' => $db));
                    if ($data->id)
                    {
                        
                       try {
                            $table->load($data->id); //dump($table->params);
                            $registry = new JRegistry();
                            $registry->loadArray($table->params); 
                            $registry->set('sermonstemplate',$sermonstemplate);
                            $registry->set('sermontemplate',$sermontemplate);
                            $registry->set('teachertemplate',$teachertemplate);
                            $registry->set('teacherstemplate',$sermonstemplate);
                            $registry->set('seriesdisplaystemplate',$seriesdisplaystemplate);
                            $registry->set('seriesdisplaytemplate',$seriesdisplaytemplate);
                            $registry->set('css',$cssid);
                            $data->params = $registry->toString(); 
                            $table->bind($data->id);
                           }
                        catch (Exception $e) {echo 'Caught exception: ',  $e->getMessage(), "\n";}
                        if (!$table->store()) 
                        {
                            $this->setError($db->getErrorMsg());
                            return false;
                        }
                    }
                }
            }
        }
        $message = JText::_('JBS_TPL_IMPORT_SUCCESS');
        $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
        }
        
        function template_export()
        {
            $data = JRequest::getVar('jform', array(), 'post', 'array');
            $exporttemplate = $data['params']['template_export'];
            if (!$exporttemplate )
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
                `params` = "'.$db->getEscaped($result->params).'", `title` = "'.$db->getEscaped($result->title).'",
                `text` = "'.$db->getEscaped($result->text).'";
                    ';
          
            //Get the individual template files
            $sermons = $data['params']['sermonstemplate'];
            if($sermons){$objects[]=$this->getTemplate($sermons);}
            $sermon = $data['params']['sermontemplate'];
            if($sermon){$objects[]=$this->getTemplate($sermon);}
            $teachers = $data['params']['teacherstemplate'];
            if($teachers){$objects[]=$this->getTemplate($teachers);}
            $teacher = $data['params']['teachertemplate'];
            if($teacher ){$objects[]=$this->getTemplate($teacher);}
            $seriesdisplays = $data['params']['seriesdisplaystemplate'];
            if($seriesdisplays){$objects[]=$this->getTemplate($seriesdisplays);}
            $seriesdisplay = $data['params']['seriesdisplaytemplate'];
            if($seriesdisplay){$objects[]=$this->getTemplate($seriesdisplay);}
            $css = $data['params']['css']; 
            $length = strlen($css);
            $css = substr($css,0,-4); 
            if($css){
                $query2 = $db->getQuery(true);
                $query2->select('style.*');
                $query2->from('#__bsms_styles AS style');
                $query2->where('style.filename = "'.$css.'"');
                $db->setQuery($query2);
                $db->query();
                $cssresult = $db->loadObject();
                $objects[] = 'INSERT INTO #__bsms_styles SET `published` = "1",
                    `filename` = "'.$db->getEscaped($cssresult->filename).'",
                    `stylecode` = "'.$db->getEscaped($cssresult->stylecode).'";';
                }
            $filecontents = implode('',$objects);
            $filename = $result->title.'.sql';
            $filepath = JPATH_ROOT.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename;
            if (!$filewrite = JFile::write($filepath,$filecontents)){return false;}
            $xport = new JBSExport();
            $savefile = $xport->output_file($filepath, $filename, 'text/x-sql');
            $filedelete = JFile::delete($filepath);
            $message = JText::_('JBS_TPL_EXPORT_SUCCESS');
            $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
        }
        
        function getTemplate($template)
        {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('tc.id, tc.templatecode,tc.type,tc.filename');
            $query->from('#__bsms_templatecode as tc');
            $query->where('tc.filename ="'.$template.'"');
            $db->setQuery($query);
            if (!$object = $db->loadObject()) {return false;}
            $templatereturn = '
INSERT INTO #__bsms_templatecode SET `type` = "'.$db->getEscaped($object->type).'",
`templatecode` = "'.$db->getEscaped($object->templatecode).'",
`filename`="'.$db->getEscaped($template).'",
`published` = "1";
';
            return $templatereturn;
        }
}