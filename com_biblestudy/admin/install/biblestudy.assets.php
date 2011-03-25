<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 * @desc a class to insert assets for each table in JBS
 * @since 7.0
 */

defined('_JEXEC') or die();

class fixJBSAssets
{
    public function JBSAsset()
    {
        $jconfig = new JConfig();

		$this->config['driver']   = 'mysql';
		$this->config['host']     = $jconfig->host;
		$this->config['user']     = $jconfig->user;
		$this->config['password'] = $jconfig->password;
		$this->config['database'] = $jconfig->db;
		$this->config['prefix']   = $jconfig->dbprefix;
        $this->db_new = JDatabase::getInstance($this->config);
        $db = JFactory::getDBO();
        
        // Getting the asset table
	//	$table = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $db->query();
        $parent_id = $db->loadResult();
        
        
        $query = "SELECT id, server_name FROM #__bsms_servers";
        $db->setQuery($query);
        $db->query();
        $servers = $db->loadObjectList();
        
        foreach ($servers AS $server)
        {
            $table = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));
            $table->name = 'com_biblestudy.serversedit.'.$server->id;
            $table->parent_id = $parent_id;
            $table->level = 2;
            $table->rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';
            $table->title = mysql_real_escape_string($server->server_name);
            
            
          //  echo $parent_id; echo $table->level;
           	$table->store();
         //print_r($table);
           	$query = "UPDATE #__bsms_servers SET asset_id = {$table->id}"
		." WHERE id = {$server->id}";
		$this->db_new->setQuery($query);
		$this->db_new->query();
    
        } 
    }
}

?>