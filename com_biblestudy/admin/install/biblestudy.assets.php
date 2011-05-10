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
		print '<p>'.JText::_('JBS_INS_16_ASSET_IN_PROCESS').'</p>';
        
        //Check to see if there is already an asset_id and if so, does it match the parent_id in jos_assets. If not, reset it and in jos_asssets
        $db = JFactory::getDBO();
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $parent_id = $db->loadResult();
        
        $query = 'SELECT t.asset_id, a.id, a.parent_id FROM #__bsms_templates as r LEFT JOIN #__assets AS a ON (r.asset_id = t.id) WHERE t.id = 1';
        $db->setQuery($query);
        $asset = $db->loadObject();
        
        
        $objects = array(array('name'=>'#__bsms_servers','titlefield'=>'server_name','assetname'=>'serversedit'),
                        array('name'=>'#__bsms_folders','titlefield'=>'foldername','assetname'=>'foldersedit'),
                        array('name'=>'#__bsms_studies','titlefield'=>'studytitle','assetname'=>'studiesedit'),
                        array('name'=>'#__bsms_comments','titlefield'=>'comment_date','assetname'=>'commentsedit'),
                        array('name'=>'#__bsms_locations','titlefield'=>'location_text','assetname'=>'locationsedit'),
                        array('name'=>'#__bsms_media','titlefield'=>'media_text','assetname'=>'mediaedit'),
                        array('name'=>'#__bsms_mediafiles','titlefield'=>'filename','assetname'=>'mediafilesedit'),
                        array('name'=>'#__bsms_message_type','titlefield'=>'message_type','assetname'=>'messagetypeedit'),
                        array('name'=>'#__bsms_mimetype','titlefield'=>'mimetext','assetname'=>'mimetypeedit'),
                        array('name'=>'#__bsms_podcast','titlefield'=>'title','assetname'=>'podcastedit'),
                        array('name'=>'#__bsms_series','titlefield'=>'series_text','assetname'=>'seriesedit'),
                        array('name'=>'#__bsms_share','titlefield'=>'name','assetname'=>'shareedit'),
                        array('name'=>'#__bsms_teachers','titlefield'=>'teachername','assetname'=>'teacheredit'),
                        array('name'=>'#__bsms_templates','titlefield'=>'title','assetname'=>'templateedit'),
                        array('name'=>'#__bsms_topics','titlefield'=>'topic_text','assetname'=>'topicsedit'),
                        );

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
        $parent_id = $db->loadResult();
        
        
        $query = 'SELECT id, '.$titlefield.' AS ta FROM '.$name;
        $db->setQuery($query);
        $oldtables = $db->loadObjectList(); 
        if ($oldtables)
        {
            foreach ($oldtables AS $oldtable)
            {
                
                
                @set_time_limit(300);
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
            
            //Since parent_id and level don't seem to set properly, let's add them back to the assets table
            $query = 'UPDATE #__assets SET parent_id = '.$parent_id.', level = 2 WHERE id = '.$table->id;
            $this->db_new->setQuery($query);
    		$this->db_new->query();
        
            }
        }
        else {return false;} 
        return true;
        
    
    }
}

?>