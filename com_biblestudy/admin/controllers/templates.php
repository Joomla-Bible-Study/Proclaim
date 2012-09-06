<?php

/**
 * Controller for Templates
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Controller for Templates
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyControllerTemplates extends JControllerAdmin {

    /**
     * Proxy for getModel
     *
     * @param <String> $name    The name of the model
     * @param <String> $prefix  The prefix for the PHP class name
     * @return JModel
     *
     * @since 7.0.0
     */
    public function &getModel($name = 'Template', $prefix = 'BiblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

     /**
     * Import Template
     *
     * @return boolean
     */
    function template_import() {
        /**
         * Attempt to increase the maximum execution time for php scripts with check for safe_mode.
         */
        if (!ini_get('safe_mode')) {
            set_time_limit(300);
        }

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
        $uploaded = move_uploaded_file($tmp_src, $tmp_dest);

        $db = JFactory::getDBO();

        $query = file_get_contents(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $userfile['name']);
        $queries = $db->splitSql($query);
        if (count($queries) == 0) {
            // No queries to process
            return 0;
        }
        foreach ($queries as $querie) {
            $querie = trim($querie);
            if (substr_count($querie, 'INSERT')) {
                if ($querie != '' && $querie{0} != '#') :
                    $db->setQuery($querie);
                    if (!$db->execute()) {
                        JError::raiseWarning(1, "DB function failed with error number " . $db->getErrorNum() . " " . $db->stderr(true));
                        return false;
                    }
                endif;
                $typecss = substr_count($querie, '#__bsms_styles');
                $typefile = substr_count($querie, '#__bsms_templatecode');
                $typetemplate = substr_count($querie, '#__bsms_templates');
                if ($typecss) {
                    $query = 'SELECT id from #__bsms_styles ORDER BY id DESC LIMIT 1';
                    $db->setQuery($query);
                    $data = $db->loadObject();
                    JTable::addIncludePath(JPATH_COMPONENT . '/tables');
                    $table = JTable::getInstance('Style', 'Table', array('dbo' => $db));
                    if ($data->id) {
                        $cssid = $data->filename;
                        try {
                            $table->load($data->id);
                        } catch (Exception $e) {
                            echo 'Caught exception: ', $e->getMessage(), "\n";
                        }
                        if (!$table->store()) {
                            $this->setError($db->getErrorMsg());
                            return false;
                        }
                    }
                }
                if ($typefile) {
                    $query = 'SELECT id, type, filename from #__bsms_templatecode ORDER BY id DESC LIMIT 1';
                    $db->setQuery($query);
                    $data = $db->loadObject();
                    JTable::addIncludePath(JPATH_COMPONENT . '/tables');
                    $table = JTable::getInstance('Templatecode', 'Table', array('dbo' => $db));
                    if ($data->id) {
                        switch ($data->type) {
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
                            case 7:
                                //module
                                $moduletemplate = $data->filename;
                                break;
                        }
                        try {
                            $table->load($data->id);
                        } catch (Exception $e) {
                            echo 'Caught exception: ', $e->getMessage(), "\n";
                        }
                        if (!$table->store()) {
                            $this->setError($db->getErrorMsg());
                            return false;
                        }
                    }
                }
                if ($typetemplate) {
                    $query = 'SELECT id from #__bsms_templates ORDER BY id DESC LIMIT 1';
                    $db->setQuery($query);
                    $data = $db->loadObject();
                    JTable::addIncludePath(JPATH_COMPONENT . '/tables');
                    $table = JTable::getInstance('Template', 'Table', array('dbo' => $db));
                    if ($data->id) {
                        try {
                            $table->load($data->id);
                            $registry = new JRegistry();
                            $registry->loadArray($table->params);
                            if (!empty($sermonstemplate)):
                                $registry->set('sermonstemplate', $sermonstemplate);
                            endif;
                            if (!empty($sermontemplate)):
                                $registry->set('sermontemplate', $sermontemplate);
                            endif;
                            if (!empty($teachertemplate)):
                                $registry->set('teachertemplate', $teachertemplate);
                            endif;
                            if (!empty($teacherstemplate)):
                                $registry->set('teacherstemplate', $teacherstemplate);
                            endif;
                            if (!empty($seriesdisplaystemplate)):
                                $registry->set('seriesdisplaystemplate', $seriesdisplaystemplate);
                            endif;
                            if (!empty($seriesdisplaytemplate)):
                                $registry->set('seriesdisplaytemplate', $seriesdisplaytemplate);
                            endif;
                            $registry->set('css', $cssid);
                            $data->params = $registry->toString();
                            $table->bind($data->id);
                        } catch (Exception $e) {
                            echo 'Caught exception: ', $e->getMessage(), "\n";
                        }
                        if (!$table->store()) {
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

    /**
     * Export the Template
     *
     * @return boolean
     */
    function template_export() {
        $data = JRequest::getVar('jform', array(), 'post', 'array');
        $exporttemplate = $data['params']['template_export'];
        if (!$exporttemplate) {
            $message = JText::_('JBS_TPL_NO_FILE_SELECTED');
            $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
        }
        jimport('joomla.filesystem.file');
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('t.id, t.type, t.params, t.title, t.text');
        $query->from('#__bsms_templates as t');
        $query->where('t.id = ' . $exporttemplate);
        $db->setQuery($query);
        $result = $db->loadObject();
        $objects[] = $this->getExportSetting($result, $data);
        $filecontents = implode(' ', $objects);
        $filename = $result->title . '.sql';
        $filepath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $filename;
        if (!JFile::write($filepath, $filecontents)) {
            return false;
        }
        $xport = new JBSExport();
        $xport->output_file($filepath, $filename, 'text/x-sql');
        JFile::delete($filepath);
        $message = JText::_('JBS_TPL_EXPORT_SUCCESS');
        $this->setRedirect('index.php?option=com_biblestudy&view=templates', $message);
    }

    
}