<?php

/**
 * Database Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Database Helper class for version 7.1.0
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class jbsDBhelper {

    /**
     * Discover the fields in a table
     * @param string table is the table you are checking
     * @param string field
     * @return boolean false equals field does not exist
     */
    static function checkTables($table, $field) {
        $db = JFactory::getDBO();
        $fields = $db->getTableColumns($table, 'false');
        if ($fields) {
            foreach ($fields as $key => $value) {
                if (substr_count($key, $field)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Alters a table
     * @desc command is only needed for MODIFY. Can be used to ADD, DROP, MODIFY tables.
     * @param array tables is an array of tables, fields, type of query and optional command line
     * @return boolean
     */
    public function alterDB($tables) {
        $msg = array();
        $result = null;
        foreach ($tables as $t) {
            $type = strtolower($t['type']);
            $command = $t['command'];
            $table = $t['table'];
            $field = $t['field'];
            switch ($type) {
                case 'drop':
                    if (!$table || !$field) {
                        break;
                    }
                    //check the field to see if it exists first
                    if (jbsDBhelper::checkTables($table, $field) === TRUE) {
                        $query = 'ALTER TABLE `' . $table . '` DROP `' . $field . '`';
                        $result = jbsDBhelper::performDB($query);
                        if ($result) {
                            $msg[] = $result;
                        }
                    }
                    break;

                case 'add':
                    if (!$table || !$field) {
                        break;
                    }
                    if (jbsDBhelper::checkTables($table, $field) !== TRUE) {
                        $query = 'ALTER TABLE `' . $table . '` ADD `' . $field . '` ' . $command;
                        $result = jbsDBhelper::performDB($query);
                        if ($result) {
                            $msg[] = $result;
                        }
                    }
                    break;

                case 'modify':
                    if (!$table || !$field) {
                        break;
                    }
                    if (jbsDBhelper::checkTables($table, $field) !== TRUE) {
                        $query = 'ALTER TABLE `' . $table . '` MODIFY `' . $field . '` ' . $command;
                        $result = jbsDBhelper::performDB($query);
                        if ($result) {
                            $msg[] = $result;
                        }
                    }
                    break;
            }
        }
        if (!empty($msg)) {
            return $msg;
        } elseif ($msg === FALSE) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * performs a database query
     * @param $query is a Joomla ready query
     * @return boolean true if success, or error string if failed
     */
    public static function performDB($query) {
        if (!$query) {
            return false;
        }
        $db = JFactory::getDbo();
        $db->setQuery($query);
        if (!$db->execute()) {
            return $db->stderr(true);
        } else {
            return true;
        }
    }

    /**
     * Checks a table for the existance of a field, if it does not find it, runs the Admin model fix()
     * @param string table is the table you are checking
     * @param string field you are checking
     * @param boolean $description
     * @return boolean
     */
    function checkDB($table, $field, $description = null) {
        $done = $this->checkTables($table, $field);
        if (!$done) {
            $admin = JModel::getInstance('Admin', 'biblestudyModel');
            $fixtables = $admin->fix();
            return true;
        } else {
            return true;
        }
    }

    /**
     * Get Opjects for tables
     * @return array
     */
    public static function getObjects() {
        $db = JFactory::getDBO();
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
        $prelength = strlen($prefix);
        $prefix . $bsms = 'bsms_';
        $objects = array();
        foreach ($tables as $table) {
            if (substr_count($table, $bsms)) {
                $table = substr_replace($table, '#__', 0, $prelength);
                $objects[] = array('name' => $table);
            }
        }
        return $objects;
    }

    /**
     * Get State of install for Main Admin Controller
     * @return \JRegistry
     * @since 7.1.0
     */
    public static function getinstallstate() {
        $result = FALSE;
        $db = JFactory::getDBO();
        $table = '#__bsms_admin';
        $field = 'id';
        if (jbsDBhelper::checkTables($table, $field) === TRUE):
            $db->setQuery('SELECT installstate FROM #__bsms_admin WHERE id = 1');
            $results = $db->loadObject();
            // Convert parameter fields to objects.
            $registry = new JRegistry;
            $registry->loadJSON($results->installstate);
            if ($registry->get('jbsname')):
                $result = $registry;
            endif;
        endif;
        return $result;
    }

    /**
     * Get State of install for Main Admin Controller
     * @return \JRegistry
     * @since 7.1.0
     */
    public static function setinstallstate() {
        $query = 'UPDATE #__bsms_admin SET installstate = NULL WHERE id = 1';
        $result = jbsDBhelper::performDB($query);
        if ($result) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

}
