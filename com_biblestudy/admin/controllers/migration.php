<?php

/**
 * Controller for Migration
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
jimport('joomla.html.parameter');
include_once(BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.restore.php');
include_once(BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.backup.php');
include_once(BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.migrate.php');
JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * JBS Export Migration Controller
 *
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class BiblestudyControllerMigration extends JControllerLegacy {

    /**
     * Method to display the view
     * @param boolon $cachable
     * @param boolon $urlparams
     *
     * @access	public
     */
    public function display($cachable = false, $urlparams = false) {

        $input = new JInput;
        $input->set('view', 'admin');
        //JRequest::setVar('view', JRequest::getCmd('view', 'admin'));
        $application = JFactory::getApplication();
        $input->set('migrationdone', '0');
        //JRequest::setVar('migrationdone', '0', 'get');
        $task = $input->get('task');
        //$task = JRequest::getWord('task', '', '');
        $oldprefix = $input->get('oldprefix','');
        //$oldprefix = JRequest::getInt('oldprefix', '', 'post');
        $run = 0;
        $run = $input->get('run','','int');
        //$run = JRequest::getInt('run', '', 'get');

        if ($task == 'export' && ($run == 1 || $run == 2)) {
            $export = new JBSExport();
            if (!$result = $export->exportdb($run)) {
                $msg = JText::_('JBS_CMN_OPERATION_FAILED');
                $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
            } elseif ($run == 2) {
                if (!$result) {
                    $msg = $result;
                } else {
                    $msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
                }
                $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
            }
        }

        if ($task == 'migrate' && $run == 1 && !$oldprefix) {

            $migrate = new JBSMigrate();
            $migration = $migrate->migrate();
            if ($migration) {
                $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
                $input->set('migrationdone','1');
                //JRequest::setVar('migrationdone', '1', 'get');
                $input->set('jbsmessages', $jbsmessages);
                //JRequest::getVar('jbsmessages', $jbsmessages, 'get', 'array');
            } else {
                JError::raiseWarning('403', JText::_('JBS_CMN_OPERATION_FAILED'));
            }
        }

        if ($task == 'import') {
            $this->import();
        }
        parent::display($tpl);

        return $this;
    }

    /**
     * Import function from the backup page
     * @since 7.1.0
     */
    public function import() {
        $application = JFactory::getApplication();
        $import = new JBSImport();
        $parent = FALSE;
        $result = $import->importdb($parent);
        if ($result === true) {
            $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
        }
        $this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
    }

    /**
     * Do the import
     *
     * @param boolean $parent Sorece of info
     * @param boolean $cachable
     * @param boolean $urlparams Description
     */
    public function doimport($parent = true, $cachable = false, $urlparams = false) {
        $copysuccess = false;
        //This should be where the form admin/form_migrate comes to with either the file select box or the tmp folder input field
        $application = JFactory::getApplication();
        $input = new JInput;
        $input->set('view', $input->get('view','admin','cmd'));
        //JRequest::setVar('view', JRequest::getCmd('view', 'admin'));

        //Add commands to move tables from old prefix to new
        $oldprefix = '';
        $oldprefix = $input->get('oldprefix','','string');
        //$oldprefix = JRequest::getWord('oldprefix', '', 'post');

        if ($oldprefix) {
            if ($this->copyTables($oldprefix)) {
                $copysuccess = 1;
            } else {
                JError::raiseWarning('403', JText::_('JBS_CMN_DATABASE_NOT_COPIED'));
                $copysuccess = false;
            }
        } else {
            $import = new JBSImport();
            $result = $import->importdb($parent);
        }
        if ($result || $copysuccess) {
            $migrate = new JBSMigrate();
            $migration = $migrate->migrate();
            if ($migration) {
                $application->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE'), 'message');
                //Final step is to fix assets
                $this->fixAssets();
                $installer = new Com_BiblestudyInstallerScript();
                $installer->deleteUnexistingFiles();
                $installer->fixMenus();
                $installer->fixImagePaths();
                $installer->fixemptyaccess();
                $installer->fixemptylanguage();
                $input->set('migrationdone','1');
                //JRequest::setVar('migrationdone', '1', 'get');
            } elseif (!$copysuccess) {
                JBSMDbHelper::resetdb();
            } else {
                JBSMDbHelper::resetdb();
                JError::raiseWarning('403', JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'));
            }
        }
        $this->setRedirect('index.php?option=com_biblestudy&task=admin.edit&id=1');
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
        $db = JFactory::getDBO();
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        foreach ($tables as $table) {
            $isjbs = substr_count($table, $oldprefix . 'bsms');
            if ($isjbs) {
                $oldlength = strlen($oldprefix);
                $newsubtablename = substr($table, $oldlength);
                $newtablename = $prefix . $newsubtablename;
                $query = 'DROP TABLE IF EXISTS ' . $newtablename;
                if (!JBSMDbHelper::performdb($query)) {
                    return FALSE;
                }
                $query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;
                if (!JBSMDbHelper::performdb($query)) {
                    return FALSE;
                }
                $query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;
                if (!JBSMDbHelper::performdb($query)) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * Fix Assets Table
     *
     * @return boolean
     */
    public function fixAssets() {
        //require_once(BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.assets.php');
        JLoader::register('fixJBSAssets', dirname(__FILE__) . '/lib/biblestudy.assets.php');
        $asset = new fixJBSAssets();
        $asset->fixAssets();
        return true;
    }

}

// end of class
