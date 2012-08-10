<?php

/**
 * @version $Id: update701.php 2085 2011-11-11 21:10:18Z bcordis $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

/**
 * Database Helper class for version 7.1.0
 *
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class jbsDBhelper {
    
    /**
     * Discover the fields in a table
     * @ param string table is the table you are checking
     * @ param string field
     * @return boolean false equals field does not exist
     */
    function checkTables($table, $field)
    { 
        $db = JFactory::getDBO();
        $prefix = $db->getPrefix();
        $fields = $db->getTableColumns($table, 'false');
        if ($fields)
        {
            foreach ($fields as $key=>$value)
            { 
                if (substr_count($key, $field))
                {
                    return true;
                }
            }
            return false;
        }
            
    }
    
    /**
     * Alters a table
     * @desc command is only needed for MODIFY. Can be used to ADD, DROP, MODIFY tables.
     * @ param array tables is an array of tables, fields, type of query and optional command line
     * @ param string command is the mysql command you are using
     * @return boolean
     */
    function alterDB($tables)
    {
        $db = JFactory::getDbo();
        foreach ($tables as $t)
        {
            $type = $t['type'];
            $command = $t['command'];
            $table = $t['table'];
            $field = $t['field'];
            //dump($type,'type: '); dump($command, 'command: '); dump($table, 'table: '); dump($field, 'field: '); 
        }
    //    $query = 'ALTER TABLE '.$table.' '.$command;
    //    $db->setQuery($query);
    //    $db->query();
        if ($db->getErrorNum() != 0) { return $db->stderr(true);}
        else
        {
            return true;
        }
    }
    
    
    /**
     * Checks a table for the existance of a field, if it does not find it, runs the Admin model fix()
     * @ param string table is the table you are checking
     * @ param string field you are checking
     * @return boolean
     */
    function checkDB($table, $field, $description=null)
    {  
       $done = $this->checkTables($table, $field);                
        if (!$done)
        {
           $admin = JModel::getInstance('Admin', 'biblestudyModel');
            $fixtables = $admin->fix();
            return true;
        }
        else
        { 
            return true;
        }
    }
}
