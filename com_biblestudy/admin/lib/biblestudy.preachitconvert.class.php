<?php

/**
 * @author Tom Fuller
 * @copyright 2012
 * @since 7.1.0
 * @desc Method to convert from PreachIt to JBS
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class JBSPIconvert
{

    function convertPI()
    {
        $commentsids = array();
        $serversids = array();
        $foldersids = array();
        $mediafilesids = array();
        $studiesids = array();
        $teachersids = array();
        $seriesids = array();
        $podcastids = array();
        
        $piconversion = '<table>';
        //first convert comments
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__picomments';
        $db->setQuery($query);
        $db->query();
        $picomments = $db->loadObjectList();
        if (!$picomments){$piconversion .= '<tr><td>'.JText::_('JBS_ADM_NO_COMMENTS').'</td></tr>'; }
        else
        {
            foreach ($picomments AS $pi)
            {
                $query = 'INSERT INTO #__bsms_comments SET `published` = "'.$pi->published.'", `study_id` = "'.$pi->study_id.
                '", `user_id` = "'.$pi->user_id.'", `full_name` = "'.$pi->full_name.'", `comment_date` = "'.$pi->comment_date.'", `comment_text` = "'.$db->getEscaped($pi->comment_text).'"';
                	$db->setQuery($query);
    			$db->query();
    			if ($db->getErrorNum() > 0)
    			{
    				$error = $db->getErrorMsg();
    				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_COMMENTS').' id: '.$pi->id.' - '.$error.'</td></tr>';
    			}
    			else
    			{
    				$cupdated = 0;
    				$cupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
    				$cadd = $cadd + $cupdated;
                    //get the new commentid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_comments ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $commentsids[] = array('newid'=>$newid,'oldid'=>$oldid);
    			}
            }
        }
        //Create servers and folders
        $query = 'SELECT * FROM #__pifilepath';
        $db->setQuery($query);
        $db->query();
        $piservers = $db->loadObjectList();
        if (!$piservers)
        {
            $piconversion .= '<tr><td>'.JText::_('JBS_ADM_NO_SERVERS').'</td></tr>';
        }
        else
        {
            foreach ($piservers AS $pi)
            {
                $query = 'INSERT INTO #__bsms_servers SET `server_path` = "'.$pi->server.'", `server_name` = "'.$pi->server.'"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0)
        			{
        				$error = $db->getErrorMsg();
        				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_SERVERS').' id: '.$pi->id.' - '.$error.'</td></tr>';
        			}
        			else
        			{
        				$supdated = 0;
        				$supdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$sadd = $sadd + $supdated;
                         //get the new serverid so we can later connect it to a study
                        $query = 'SELECT id FROM #__bsms_servers ORDER BY `id` DESC LIMIT 1';
                        $db->setQuery($query);
                        $db->query();
                        $newid = $db->loadResult();
                        $oldid = $pi->id;
                        $serversids[] = array('newid'=>$newid,'oldid'=>$oldid);
        			}
                $query = 'INSERT INTO #__bsms_folders SET `folder_name` = "'.$pi->folder.'", `folder_path` = "'.$pi->folder.'"';
                $db->query();
                if ($db->getErrorNum() > 0)
        			{
        				$error = $db->getErrorMsg();
        				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_FOLDER').' id: '.$pi->id.' - '.$error.'</td></tr>';
        			}
        			else
        			{
        				$fupdated = 0;
        				$fupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$fadd = $fadd + $fupdated;
                         //get the new folderid so we can later connect it to a study
                        $query = 'SELECT id FROM #__bsms_folders ORDER BY `id` DESC LIMIT 1';
                        $db->setQuery($query);
                        $db->query();
                        $newid = $db->loadResult();
                        $oldid = $pi->id;
                        $foldersids[] = array('newid'=>$newid,'oldid'=>$oldid);
        			}
            }
            //Teachers
            $query = 'SELECT * FROM #__piteachers';
            $db->setQuery($query);
            $db->query();
            $piteachers = $db->loadObjectList();
            foreach ($piteachers AS $pi)
            {
                //Map new folder for images to old one
                $foldersmall = $pi->image_folder;
                $folderlarge = $pi->image_folderlrg;
                foreach ($foldersids as $folder)
                {
                    if ($folder['oldid'] == $foldersmall){$foldersmall = $folder['newid'];}
                    if ($folder['oldid'] == $folderlarge){$folderlarge = $folder['newid'];}
                }
                //look up folders to use in teacher images
                $query = 'SELECT id, folder FROM #__pifolders WHERE id = '.$foldersmall;
                $db->setQuery();
                $object = $db->loadObject();
                $newfoldersmall = $bject->folder;
                $query = 'SELECT id, folder FROM #__pifolders WHERE id = '.$folderlarge;
                $db->setQuery();
                $object = $db->loadObject();
                $newfolderlarge = $object->folder;
                $query = 'INSERT INTO #__bsms_teachers SET `teachername` = "'.$pi->teacher_name.'", `alias` = "'.$pi->alias
                .'", `title` = "'.$pi->teacher_role.'", `image` = "'.$newfolderlarge.'/'.$pi->teacher_image_lrg.'", `thumb` = "'
                .$newfoldersmall.'/'.$pi->teacher_image_sm.'", `email` = "'.$pi->teacher_email.'", `website` = "'.$pi->teacher_website.'", 
                `short` = "'.$db->getEscaped($pi->teacher_description).'", `list_show` = "'.$pi->teacher_view.'"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0)
        			{
        				$error = $db->getErrorMsg();
        				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_TEACHER').' id: '.$pi->id.' - '.$error.'</td></tr>';
        			}
        			else
        			{
        				$tupdated = 0;
        				$tupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$tadd = $tadd + $tupdated;
                         //get the new teacherid so we can later connect it to a study
                        $query = 'SELECT id FROM #__bsms_teachers ORDER BY `id` DESC LIMIT 1';
                        $db->setQuery($query);
                        $db->query();
                        $newid = $db->loadResult();
                        $oldid = $pi->id;
                        $teachersids[] = array('newid'=>$newid,'oldid'=>$oldid);
        			}
            } 
            //Convert Series
            $query = 'SELECT * FROM #__piseries';
            $db->setQuery($query);
            $db->query();
            $series = $db->loadObjectList();
            foreach ($series AS $pi)
            {
                //Map new folder for images to old one
                $foldersmall = $pi->image_folder;
                $folderlarge = $pi->image_folderlrg;
                foreach ($foldersids as $folder)
                {
                    if ($folder['oldid'] == $foldersmall){$foldersmall = $folder['newid'];}
                    if ($folder['oldid'] == $folderlarge){$folderlarge = $folder['newid'];}
                }
                //look up folders to use in series images
                $query = 'SELECT id, folder FROM #__pifolders WHERE id = '.$foldersmall;
                $db->setQuery();
                $object = $db->loadObject();
                $newfoldersmall = $bject->folder;
                $query = 'SELECT id, folder FROM #__pifolders WHERE id = '.$folderlarge;
                $db->setQuery();
                $object = $db->loadObject();
                $newfolderlarge = $object->folder;
                $query = 'INSERT INTO #__bsms_series SET `series_text` = "'.$pi->series_name.'", `alias` = "'.$pi->series_alias.'", 
                `description` = "'.$pi->series_description.'", `series_thumbnail` = "'.$newfoldersmall.'/'.$pi->series_image_sm.'"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0)
        			{
        				$error = $db->getErrorMsg();
        				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_SERIES').' id: '.$pi->id.' - '.$error.'</td></tr>';
        			}
        			else
        			{
        				$tupdated = 0;
        				$tupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$tadd = $tadd + $tupdated;
                         //get the new teacherid so we can later connect it to a study
                        $query = 'SELECT id FROM #__bsms_series ORDER BY `id` DESC LIMIT 1';
                        $db->setQuery($query);
                        $db->query();
                        $newid = $db->loadResult();
                        $oldid = $pi->id;
                        $seriesids[] = array('newid'=>$newid,'oldid'=>$oldid);
        			}
            }
            //Convert the podcacst
            $query = 'SELECT * FROM #__pipodcast';
            $db->setQuery($query);
            $db->query();
            $podcasts = $db->loadObjectList();
            foreach ($podcasts AS $pi)
            {
                $query = 'INSERT INTO #__bsms_podcasts SET `title` = "'.$pi->name.'", `website` = "'.$pi->website.'", 
                `description` = "'.$pi->description.'", `image` = "'.$pi->image.'", `imageh` = "'.$pi->imagehgt.'", `imagew` = "'
                .$pi->imagewth.'", `author` = "'.$pi->author.'", `filename` = "'.$pi->filename.'", `language` = "'.$pi->language.'", 
                `editor_name` = "'.$pi->editor.'", `editor_email` = "'.$pi->email.'", `podcastlimit` = "'.$pi->records.'", 
                `eposidetitle` = "'.$pi->itunestitle.'", `detailstemplateid` = "1"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0)
        			{
        				$error = $db->getErrorMsg();
        				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_PODCAST').' id: '.$pi->id.' - '.$error.'</td></tr>';
        			}
        			else
        			{
        				$pupdated = 0;
        				$pupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
        				$padd = $padd + $pupdated;
                         //get the new teacherid so we can later connect it to a study
                        $query = 'SELECT id FROM #__bsms_podcasts ORDER BY `id` DESC LIMIT 1';
                        $db->setQuery($query);
                        $db->query();
                        $newid = $db->loadResult();
                        $oldid = $pi->id;
                        $podcasts[] = array('newid'=>$newid,'oldid'=>$oldid);
        			}
            }
            
        }
        $piconversion .= '</table>';
        return $piconversion;
    }
 
 
    function performdb($query) 
    {
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
}


?>