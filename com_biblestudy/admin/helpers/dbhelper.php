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
    function checkTables($table, $field) {
        $db = JFactory::getDBO();
        $prefix = $db->getPrefix();
        $fields = $db->getTableColumns($table, 'false');
        if ($fields) {
            foreach ($fields as $key => $value) {
                if (substr_count($key, $field)) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Alters a table
     * @desc command is only needed for MODIFY. Can be used to ADD, DROP, MODIFY tables.
     * @param array tables is an array of tables, fields, type of query and optional command line
     * @return boolean
     */
    function alterDB($tables) {
        $msg = array();
        $result = null;
        foreach ($tables as $t) {
            $type = strtolower($t['type']); 
            $command = $t['command'];
            $table = $t['table'];
            $field = $t['field'];
            switch ($type)
            {
                case 'drop': 
                    if (!$table || !$field){break;}
                    //check the field to see if it exists first
                    if ($this->checkTables($table, $field))
                    { 
                        $query = 'ALTER TABLE '.$table.' DROP '.$field;
                        $result = $this->performDB($query);
                        if ($result){$msg[]= $result;}
                     }
                    break;
                
                case 'add':
                    if (!$table || !$field){break;}
                    if ($this->checkTables($table, $field))
                    {
                        $query = 'ALTER TABLE '.$table.' ADD '.$field.' '.$command;
                        $result = $this->performDB($query);
                        if ($result){$msg[]= $result;}
                     }
                    break;
                
                case 'modify':
                    if (!$table || !$field){break;}
                    if ($this->checkTables($table, $field))
                    {
                        $query = 'ALTER TABLE '.$table.' MODIFY '.$field.' '.$command;
                        $result = $this->performDB($query);
                        if ($result){$msg[]= $result;}
                     }
                    break;
            }
            //dump($type,'type: '); dump($command, 'command: '); dump($table, 'table: '); dump($field, 'field: '); 
        }
       if (!empty($msg)){return $msg;}
       else{return true;}
    }
    
    /**
     * performs a database query
     * @param $query is a Joomla ready query
     * @return boolean true if success, or error string if failed
     */
    function performDB($query)
    {
        if (!$query){return false;}
        $db = JFactory::getDbo();
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() != 0) { return $db->stderr(true);}
        else {return true;}
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

}
