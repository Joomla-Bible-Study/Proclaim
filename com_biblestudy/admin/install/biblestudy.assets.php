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
    public function AssetEntry()
    {
        $objects = array(array(name=>'#__bsms_servers',titlefield=>'server_name',assetname=>'serversedit'),
                        array(name=>'#__bsms_folders',titlefield=>'foldername',assetname=>'foldersedit'));

        foreach ($objects AS $object)
        {
            $name = $object['name'];
            $titlefield = $object['titlefield'];
            $assetname =  $object['assetname'];
            $doAsset = $this->JBSAsset($name, $titlefield, $assetname);
        }
        if ($doAsset){return true;}else{return false;}
    }
    public function JBSAsset($name, $titlefield, $assetname)
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
	
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $db->query();
        $parent_id = $db->loadResult();
        
        
        $query = 'SELECT id, '.$titlefield.' AS ta FROM '.$name;
        $db->setQuery($query);
        $db->query();
        $oldtables = $db->loadObjectList(); 
        if ($oldtables)
        {
            foreach ($oldtables AS $oldtable)
            {
                $table = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->db_new));
                $table->name = 'com_biblestudy.'.$assetname.'.'.$oldtable->id;
                $table->parent_id = $parent_id;
                $table->level = '2';
                $table->rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';
                $table->title = mysql_real_escape_string($oldtable->ta);
        
               	$table->store();
             
            $query = 'UPDATE '.$name.' SET asset_id = '.$table->id.' WHERE id = '.$oldtable->id;
    		$this->db_new->setQuery($query);
    		$this->db_new->query();
        
            }
        }
        else {return false;} 
        return true;
    }
}

?>