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
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.backup.php');
require_once ( JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'parameter.php' );

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
        $data = JRequest::getVar('template_export', '', 'post', '');
        $exporttemplate = $data;
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

    /**
     * Get Exported Template Settings
     *
     * @param object $result
     * @param object $data
     * @return string
     */
    private function getExportSetting($result, $data) {
        $registry = new JRegistry;
        $registry->loadJSON($result->params);
        $params = $registry;
        $db = JFactory::getDBO();
        $objects = '';
        $objects = "--\n-- Template Table\n--\n";
        //Create the main template insert
        $objects .= "\nINSERT INTO #__bsms_templates SET `type` = '" . $db->getEscaped($result->type) . "',";
        $objects .= "\n`params` = '" . $db->getEscaped($result->params) . "',";
        $objects .= "\n`title` = '" . $db->getEscaped($result->title) . "',";
        $objects .= "\n`text` = '" . $db->getEscaped($result->text) . "';";

        //Get the individual template files
        $sermons = $params->get('sermonstemplate');
        if ($sermons) {
            $objects .= "\n--\n-- Sermons\n--";
            $objects .= $this->getTemplate($sermons);
        }
        $sermon = $params->get('sermontemplate');
        if ($sermon) {
            $objects .= "\n--\n-- Sermon\n--";
            $objects .= $this->getTemplate($sermon);
        }
        $teachers = $params->get('teacherstemplate');
        if ($teachers) {
            $objects .= "\n--\n-- Teachers\n--";
            $objects .= $this->getTemplate($teachers);
        }
        $teacher = $params->get('teachertemplate');
        if ($teacher) {
            $objects .= "\n--\n-- Teacher\n--";
            $objects .= $this->getTemplate($teacher);
        }
        $seriesdisplays = $params->get('seriesdisplaystemplate');
        if ($seriesdisplays) {
            $objects .= "\n--\n-- Seriesdisplays\n--";
            $objects .= $this->getTemplate($seriesdisplays);
        }
        $seriesdisplay = $params->get('seriesdisplaytemplate');
        if ($seriesdisplay) {
            $objects .= "\n--\n-- SeriesDisplay\n--";
            $objects .= $this->getTemplate($seriesdisplay);
        }
        $css = $params->get('css');
        $length = strlen($css);
        $css = substr($css, 0, -4);
        if ($css) {
            $objects .= "\n\n--\n-- CSS Style Code\n--\n";
            $query2 = $db->getQuery(true);
            $query2->select('style.*');
            $query2->from('#__bsms_styles AS style');
            $query2->where('style.filename = "' . $css . '"');
            $db->setQuery($query2);
            $db->query();
            $cssresult = $db->loadObject();
            $objects .= "\nINSERT INTO #__bsms_styles SET `published` = '1',\n`filename` = '" . $db->getEscaped($cssresult->filename) . "',\n`stylecode` = '" . $db->getEscaped($cssresult->stylecode) . "';\n";
        }
        $objects .= "\n-- --------------------------------------------------------\n\n";
        return $objects;
    }
    
    /**
     * Get Template Settings
     *
     * @param array $template
     * @return boolean|string
     */
    function getTemplate($template) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('tc.id, tc.templatecode,tc.type,tc.filename');
        $query->from('#__bsms_templatecode as tc');
        $query->where('tc.filename ="' . $template . '"');
        $db->setQuery($query);
        if (!$object = $db->loadObject()) {
            return false;
        }
        $templatereturn = '
                        INSERT INTO #__bsms_templatecode SET `type` = "' . $db->getEscaped($object->type) . '",
                        `templatecode` = "' . $db->getEscaped($object->templatecode) . '",
                        `filename`="' . $db->getEscaped($template) . '",
                        `published` = "1";
                        ';
        return $templatereturn;
    }

}