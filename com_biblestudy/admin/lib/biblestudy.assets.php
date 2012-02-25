<?php

/**
 * @version $Id: biblestudy.assets.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;

class fixJBSAssets
{

    public function fixAssets()
    {
        
       	$db = JFactory::getDBO();
        @set_time_limit(300);
        //Get all of the table names
        $objects = $this->getObjects();
        $msg = array();
        foreach ($objects as $object)
        {
            @set_time_limit(300);
            $query = 'SELECT id FROM '.$object['name'];
            $db->setQuery($query);
            $db->query();
            $datarows = $db->loadObjectList();
            if ($datarows)
            {
                foreach ($datarows as $data)
                {
                    JTable::addIncludePath(JPATH_COMPONENT.'/tables');
                    $table = JTable::getInstance($object['assetname'], 'Table', array('dbo' => $db));
                    if ($data->id)
                    {
                        
                       try {$table->load($data->id);}
                        catch (Exception $e) {echo 'Caught exception: ',  $e->getMessage(), "\n";}
                        if (!$table->store()) 
                        {
                            $this->setError($db->getErrorMsg());
                            return false;
                        }
                    }
                }
            }
        }

    }

    function checkAssets()
    {
        $return = array();
        $db = JFactory::getDBO();
        //First get the new parent_id
		$query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
		$db->setQuery($query);
        $db->query();
		$parent_id = $db->loadResult();

        //get the names of the JBS tables
        $objects = $this->getObjects();

        //Run through each table
        foreach ($objects as $object)
        {
            //Put the table into the return array

            //Get the total number of rows and collect the table into a query
            $query = 'SELECT j.id as jid, j.asset_id as jasset_id, a.id as aid, a.parent_id FROM '.$object['name'].' as j LEFT JOIN #__assets as a ON (a.id = j.asset_id)';
            $db->setQuery($query);
            $db->query();
            $results = $db->loadObjectList();
            $nullrows = 0;
            $matchrows = 0;
            $nomatchrows = 0;
            $numrows = count($results);
            //Now go through each record to test it for asset id
            foreach ($results as $result)
            {
                //if there is no jasset_id it means that this has not been set and should be
                if (!$result->jasset_id) {$nullrows ++;}
                //if there is a jasset_id but no match to the parent_id then a mismatch has occured
                if ($parent_id != $result->parent_id && $result->jasset_id){$nomatchrows ++;}
                // if $parent_id and $result->parent_id match then everything is okay
                if ($parent_id == $result->parent_id){$matchrows ++;}
            }
            $return[] = array('realname'=>$object['realname'],
            'numrows'=>$numrows,
            'nullrows'=>$nullrows,
            'matchrows'=>$matchrows,
            'nomatchrows'=>$nomatchrows);
        }

        return $return;
    }

    function getObjects()
    {
        $objects = array(array('name'=>'#__bsms_servers','titlefield'=>'server_name','assetname'=>'serversedit','realname'=>'JBS_CMN_SERVERS'),
		array('name'=>'#__bsms_folders','titlefield'=>'foldername','assetname'=>'foldersedit','realname'=>'JBS_CMN_FOLDERS'),
		array('name'=>'#__bsms_studies','titlefield'=>'studytitle','assetname'=>'studiesedit','realname'=>'JBS_CMN_STUDIES'),
		array('name'=>'#__bsms_comments','titlefield'=>'comment_date','assetname'=>'commentsedit','realname'=>'JBS_CMN_COMMENTS'),
		array('name'=>'#__bsms_locations','titlefield'=>'location_text','assetname'=>'locationsedit','realname'=>'JBS_CMN_LOCATIONS'),
		array('name'=>'#__bsms_media','titlefield'=>'media_text','assetname'=>'mediaedit','realname'=>'JBS_CMN_MEDIAIMAGES'),
		array('name'=>'#__bsms_mediafiles','titlefield'=>'filename','assetname'=>'mediafilesedit','realname'=>'JBS_CMN_MEDIA_FILES'),
		array('name'=>'#__bsms_message_type','titlefield'=>'message_type','assetname'=>'messagetypeedit','realname'=>'JBS_CMN_MESSAGE_TYPES'),
		array('name'=>'#__bsms_mimetype','titlefield'=>'mimetext','assetname'=>'mimetypeedit','realname'=>'JBS_CMN_MIME_TYPES'),
		array('name'=>'#__bsms_podcast','titlefield'=>'title','assetname'=>'podcastedit','realname'=>'JBS_CMN_PODCASTS'),
		array('name'=>'#__bsms_series','titlefield'=>'series_text','assetname'=>'seriesedit','realname'=>'JBS_CMN_SERIES'),
		array('name'=>'#__bsms_share','titlefield'=>'name','assetname'=>'shareedit','realname'=>'JBS_CMN_SOCIAL_NETWORKING_LINKS'),
		array('name'=>'#__bsms_teachers','titlefield'=>'teachername','assetname'=>'teacheredit','realname'=>'JBS_CMN_TEACHERS'),
		array('name'=>'#__bsms_templates','titlefield'=>'title','assetname'=>'templateedit','realname'=>'JBS_CMN_TEMPLATES'),
		array('name'=>'#__bsms_topics','titlefield'=>'topic_text','assetname'=>'topicsedit','realname'=>'JBS_CMN_TOPICS'),
                array('name'=>'#__bsms_styles','titlefield'=>'filename','assetname'=>'style','realname'=>'JBS_CMN_CSS'),
                array('name'=>'#__bsms_admin','titlefield'=>'id','assetname'=>'admin','realname'=>'JBS_CMN_ADMINISTRATION')
		);
        return $objects;
    }
}