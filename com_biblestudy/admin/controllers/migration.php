<?php

/**
 * Controller for Migration
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
jimport('joomla.html.parameter');
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.restore.php');
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.backup.php');
include_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.migrate.php');

/**
 * JBS Export Migration Controller
 *
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyControllerMigration extends JController {

    /**
     * Method to display the view
     * @param boolon $cachable
     * @param boolon $urlparams
     *
     * @access	public
     */
    public function display($cachable = false, $urlparams = false) {

        JRequest::setVar('view', JRequest::getCmd('view', 'admin'));
        $application = JFactory::getApplication();
        JRequest::setVar('migrationdone', '0', 'get');
        $task = JRequest::getWord('task', '', '');
        $oldprefix = JRequest::getInt('oldprefix', '', 'post');
        $run = 0;
        $run = JRequest::getInt('run', '', 'get');
        $import = JRequest::getVar('file', '', 'post');

        if ($task == 'export' && ($run == 1 || $run == 2)) {
            $export = new JBSExport();
            if (!$result = $export->exportdb($run)) {
                $msg = JText::_('JBS_CMN_OPERATION_FAILED');
                $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
            } else {
                $msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
                $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
            }
        }

        if ($task == 'migrate' && $run == 1 && !$oldprefix) {

            $migrate = new JBSMigrate();
            $migration = $migrate->migrate();
            if ($migration) {
                $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
                JRequest::setVar('migrationdone', '1', 'get');
                $errors = JRequest::getVar('jbsmessages', $jbsmessages, 'get', 'array');
            } else {
                $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_FAILED') . '');
            }
        }

        if ($task == 'import') {
            $importjbs = $this->import();
        }
        parent::display();

        return $this;
    }

    /**
     * Import function
     * @since 7.1.0
     */
    public function import() {
        $application = JFactory::getApplication();
        $import = new JBSImport();
        $result = $import->importdb();
        if ($result === true) {
            $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
        } elseif ($result === false) {

        } else {
            $application->enqueueMessage('' . $result . '');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Do the import
     *
     * @param boolon $cachable
     * @param boolon $urlparams Description
     */
    public function doimport($cachable = false, $urlparams = false) {

        $copysuccess = false;
//        $import = new JBSImport();
        //      $result = $import->importdb();
        //This should be where the form admin/form_migrate comes to with either the file select box or the tmp folder input field
        $application = JFactory::getApplication();
        JRequest::setVar('view', JRequest::getCmd('view', 'admin'));
        //Add commands to move tables from old prefix to new
        $oldprefix = '';
        $oldprefix = JRequest::getWord('oldprefix', '', 'post');

        if ($oldprefix) {
            $tablescopied = $this->copyTables($oldprefix);
            //if error
            //check for empty array - if not, print results
            if (empty($tablescopied)) {
                $copysuccess = 1;
            } else {
                $copysuccess = false;
                print_r($tablescopied);
            }
        } else {

            $import = new JBSImport();
            $result = $import->importdb();
        }
        if ($result || $copysuccess) {
            //We need to drop the update table first as it will be added back later
            $db = JFactory::getDBO();
            $db->setQuery('DROP TABLE #__bsms_update');
            $db->query();
            $migrate = new JBSMigrate();
            $migration = $migrate->migrate();
            //Final step is to fix assets
            $this->fixAssets();
            if ($migration) {
                $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE') . '');
                JRequest::setVar('migrationdone', '1', 'get');
            } else {
                $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_FAILED') . $migration . '');
            }
            JRequest::setVar('migrationdone', '1', 'get');
        } else {
            $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_FAILED') . $migration . '');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Perform DB check
     *
     * @param array $query
     * @return string|boolean
     */
    public function performdb($query) {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() != 0) {
            $results = JText::_('JBS_IBM_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
            $results .= $db->stderr(true);
            $results .= "</font>";
            return $results;
        } else {
            $results = false;
            return $results;
        }
    }

    /**
     * Copy Old Tables to new Joomla! Tables
     *
     * @param string $oldprefix
     * @return array
     */
    public function copyTables($oldprefix) {
        //create table tablename_new like tablename; -> this will copy the structure...
        //insert into tablename_new select * from tablename; -> this would copy all the data
        $results = array();
        $db = JFactory::getDBO();
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        foreach ($tables as $table) {
            $isjbs = substr_count($table, $oldprefix . 'bsms');
            if ($isjbs) {
                $oldlength = strlen($oldprefix);
                $newsubtablename = substr($table, $oldlength);
                $newtablename = $prefix . $newsubtablename;
                $results = array();
                $query = 'DROP TABLE IF EXISTS ' . $newtablename;
                $result = $this->performdb($query);
                if ($result) {
                    $results[] = $result;
                }
                $query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;
                $result = $this->performdb($query);
                if ($result) {
                    $results[] = $result;
                }
                $query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;
                $result = $this->performdb($query);
                if ($result) {
                    $results[] = $result;
                }
            }
        }
        return $results;
    }

    /**
     * Fix Assets Table
     *
     * @return boolean
     */
    public function fixAssets() {
        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.assets.php');
        $asset = new fixJBSAssets();
        $asset->fixAssets();
        return true;
    }

}

// end of class
