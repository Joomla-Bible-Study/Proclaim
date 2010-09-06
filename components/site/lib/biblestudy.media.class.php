<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2010
 * @desc Class file to create the mediatable
 */
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');


class jbsMedia
{

    function getMediaTable($row, $params, $admin_params)
    {
        //First we get some items from GET and instantiate the images class
        //$table = 'the table';
        $admin = new JBSAdmin();
        $mediaPlayer = $admin->getMediaPlayer(); //dump ($mediaPlayer, 'mediaPlayer: ');

        $Itemid = JRequest::getInt('Itemid','1','get');
        $template = JRequest::getInt('templatemenuid','1','get');
	    $images = new jbsImages();
        $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
        include_once ($path1.'helper.php');

        //Here we get the administration row from the comnponent, and determine the download image to use
        $admin = $this->getAdmin();
        $d_image = ($admin[0]->download ? '/'.$admin[0]->download : '/download.png');
		$download_tmp = $images->getMediaImage($admin[0]->download, $media=NULL);
        $download_image = $download_tmp->path;
        $compat_mode = $admin_params->get('compat_mode');

        //Here we get a list of the media ids associated with the study we got from $row
        //$mediaids = $this->getMediaid($row->id);
        $mediaids = $this->getMediaRows($row->id);
        $rowcount = count($mediaids); // echo $rowcount; return true;
        if ($rowcount < 1) {$table = null; return $table;}
       // $table = $mediaids;
      //  dump ($table, 'table: ');
        //Here is where we begin to build the table
        $table = '<div><table class="mediatable"><tbody><tr>';

        //Now we look at each mediaid, and get the rest of the media information
        foreach ($mediaids AS $media)
        {
            //Step 1 is to get the media file

            $image = $images->getMediaImage($media->path2, $media->impath);

            $itemparams = new JParameter ($media->params);

             //Get the attributes for the player used in this item
             $player = $this->getPlayerAttributes($admin_params, $params, $itemparams, $mediaPlayer, $media); 
             $playercode = $this->getPlayerCode($params, $itemparams, $player, $image, $media);
//dump ($playercode, 'player: ');
            //Now we build the column for each media file
            $table .= '<td>';


             //Check to see if a download link is needed

            	$link_type = $media->link_type;

        		if ($link_type > 0)
        		{
        	   		$width=$download_tmp->width;
        	   		$height=$download_tmp->height;

        	      if($compat_mode == 0)
        		  {
        	      		$downloadlink ='<a href="index.php?option=com_biblestudy&id='.
                          $media->id.'&view=studieslist&controller=studieslist&task=download">';
        		  }
        		  else
        		  {
        	      		$downloadlink ='<a href="http://joomlabiblestudy.org/router.php?file='.
                          $media->spath.$media->fpath.$media->filename.'&size='.$media->size.'">';
        		  }
        	     $downloadlink .= '<img src="'.$download_image.'" alt="'.JText::_('Download').'" height="'.
                 $height.'" width="'.$width.'" title="'.JText::_('Download').'" /></a>';

        	  	}
        	  	switch ($link_type)
        	  	{
         			case 0:
         			$table .= $playercode;
         			break;

        			case 1:
        	  		$table .= $playercode.$downloadlink;
        	  		break;

        	  		case 2:
        	  		$table .= '<div><table class="mediatable"><tbody><tr><td>'.$downloadlink;
        	  		break;
        	  	}
            //End of the column holding the media image
            $table .= '</td>';


           } // end of foreach mediaids

         //End of row holding media image/link
         $table .= '</tr>';

            // This is the last part of the table where we see if we need to display the filesize
  if ($params->get('show_filesize') > 0 )
	{
		$table .= '<tr>';
		foreach ($mediaids as $media) {
			switch ($params->get('show_filesize'))
				{
					case 1:
						$filesize = getFilesize($media->size);
					break;
					case 2:
						$filesize = $media->comment;
					break;
					case 3:
						if ($media->comment ? $filesize = $media->comment : $filesize = getFilesize($media->size));
					break;
				}

				$table .= '<td><span class="bsfilesize">'.$filesize.'</span></td>';

		} //end second foreach
		$table .= '</tr>';
	} // end of if show_filesize


     	$table .='</table>';

        return $table;
    }

    function getMediaid($id)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT m.id as mid, m.study_id, s.id as sid FROM #__bsms_mediafiles AS m
         LEFT JOIN #__bsms_studies AS s ON (m.study_id = s.id) WHERE s.id = '.$id;
        $db->setQuery($query);
        $db->query();
        $mediaids = $db->loadObjectList();
        return $mediaids;
    }

    function getMediaRows($id)
{
	//$media = 'returned media'; return $media;
    $db = JFactory::getDBO();
	$query = 'SELECT #__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid,
     #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname,
      #__bsms_media.path2 AS path2, s.studytitle, s.studydate, s.studyintro, s.media_hours, s.media_minutes, s.media_seconds, s.teacher_id,
       s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro,
         #__bsms_media.media_alttext AS malttext, #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext FROM #__bsms_mediafiles 
         LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image) LEFT JOIN #__bsms_servers 
         ON (#__bsms_servers.id = #__bsms_mediafiles.server) LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path) 
         LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type) LEFT JOIN #__bsms_studies AS s 
         ON (s.id = #__bsms_mediafiles.study_id) LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id) 
         WHERE #__bsms_mediafiles.study_id = '.$id.' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC, #__bsms_mediafiles.mime_type ASC';
    $db->setQuery($query);
    $db->query();
    if ($media = $db->loadObjectList()){
			return $media;}

    else {$error = $db->getErrorMsg();
    return false;}

}

function getMediaRows2($id)
{
	//We use this for the popup view because it relies on the media file's id rather than the study_id field above
    $db = JFactory::getDBO();
	$query = 'SELECT #__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid, 
    #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, 
    #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2, s.studyintro, s.media_hours, s.media_minutes, 
    s.media_seconds, s.studytitle, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, 
    s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro,  #__bsms_media.media_alttext AS malttext, 
    #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext FROM #__bsms_mediafiles 
    LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image) 
    LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server) 
    LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path) 
    LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type) 
    LEFT JOIN #__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id) 
    LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id) 
    WHERE #__bsms_mediafiles.id = '.$id.' AND #__bsms_mediafiles.published = 1 
    ORDER BY ordering ASC, #__bsms_mediafiles.mime_type ASC';
    $db->setQuery($query);
    $db->query();
    if ($media = $db->loadObject()){
			return $media;}

    else {$error = $db->getErrorMsg();
    return false;}
}

function getAdmin()
{
    $db = JFactory::getDBO();
    $db->setQuery('SELECT * FROM #__bsms_admin WHERE id = 1');
	$db->query();
	$admin = $db->loadObjectList();
    return $admin;
}

function getPlayerAttributes($admin_params, $params, $itemparams, $mediaPlayer, $media)
{
    $player->playerwidth = $params->get('player_width');
    $player->playerheight = $params->get('player_height');
    
    if ($itemparams->get('playerheight')) {$player->playerheight = $itemparams->get('playerheight');}
    if ($itemparams->get('playerwidth')) {$player->playerwidth = $itemparams->get('playerwidth');}
    
/**
 * @desc Players - from Template:
 * First we check to see if in the template the user has set to use the internal player for all media. This can be overridden by itemparams
 * popuptype = whether AVR should be window or lightbox (handled in avr code)
 * internal_popup = whether direct or internal player should be popup/new window or inline
 * From media file:
 * player 0 = direct, 1 = internal, 2 = AVR, 3 = AVinternal_popup 0 = inline, 1 = popup, 2 = global settings
 *
 * Get the $player->player: 0 = direct, 1 = internal, 2 = AVR, 3 = AV, 4 = Docman, 5 = article, 6 = Virtuemart
 * $player->type 0 = inline, 1 = popup/new window
*/
     $player->player = 0;
    if ($params->get('media_player') > 0) {$player->player = $params->get('media_player');}
//     if ($params->get('media_player') == 1){$player->player = 1;}
    if ($itemparams->get('player') == 1)
        {
            $player->player = 1;
        }
    if ($itemparams->get('player') == 2)
        {
            $player->player = 2;
            if ($mediaPlayer == 'av')
            {
                $player->player = 3;
            }
        }
    if ($itemparams->get('player') == 3)
        {
            $player->player = 3;
        }
    if ($media->docMan_id > 0)
	 	{
			$player->player = 4;
		}
	if ($media->article_id > 0)
		{
			$player->player = 5;
		}
	if ($media->virtueMart_id > 0)
		{
			$player->player = 6;
		}

      $type = 0;
      if ($params->get('internal_popup') == 1) {$type = 1;}

    //Get the popup or inline
        $item = $itemparams->get('internal_popup','0');
        $internal_popup = $params->get('internal_popup',0);

        if ($item > 1){$player->type = $internal_popup;}
        else {$player->type = $item;}
      //  dump ($player, 'item: ');
    return $player;
}

function getDocman($media, $image)
	{
		$src = JURI::base().$image->path;
        $height = $image->height;
        $width = $image->width;
        $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
        include_once($path1.'filesize.php');
    	include_once($path1.'duration.php');
        $filesize = getFilesize($media->size);
        $duration = getDuration($params, $row);
        $docman = '<a href="index.php?option=com_docman&task=doc_download&gid='.$media->docMan_id.'"
		 title="'.$media->malttext.' - '.$media->comment.'" target="'.$media->special.'"><img src="'.$src
       .'" alt="'.$media->malttext.' '.$duration.' '.$filesize.'" width="'.$width
       .'" height="'.$height.'" border="0" /></a>';


	return $docman;
	}

function getArticle($media, $image)
	{
		
        $src = JURI::base().$image->path;
        $height = $image->height;
        $width = $image->width;
        $article = '<a href="index.php?option=com_content&view=article&id='.$media->article_id.'"
		 alt="'.$media->malttext.' - '.$media->comment.'" target="'.$media->special.'"><img src="'.$src.'" width="'.$width
       	.'" height="'.$height.'" border="0" /></a>';

	return $article;
	}

function getVirtuemart($media, $params, $image)
	{
		$src = JURI::base().$image->path;
        $height = $image->height;
        $width = $image->width;
		$vm = '<a href="index.php?option=com_virtuemart&page=shop.product_details&flypage='.$params->get('store_page', 'flypage.tpl').'&product_id='.$media->virtueMart_id.'"
		alt="'.$media->malttext.' - '.$media->comment.'" target="'.$media->special.'"><img src="'.$src.'" width="'.$width
       	.'" height="'.$height.'" border="0" /></a>';

	return $vm;
	}

function getPlayerCode($params, $itemparams, $player, $image, $media)
{
    $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
    include_once($path1.'filesize.php');
	include_once($path1.'duration.php');
    $src = JURI::base().$image->path;
    $height = $image->height;
    $width = $image->width;
    $backcolor = $params->get('backcolor','0x287585');
    $frontcolor = $params->get('frontcolor','0xFFFFFF');
    $lightcolor = $params->get('lightcolor','0x000000');
    $screencolor = $params->get('screencolor','0xFFFFFF');
    $template = JRequest::getInt('templatemenuid','1','get');
    //Here we get more information about the particular media file
    $filesize = getFilesize($media->size);
    $duration = getDuration($params, $row); //This one IS needed
    $mimetype = $media->mimetext;
    $path = $media->spath.$media->fpath.$media->filename;
     if(!eregi('http://', $path))
    				{
    					$path = 'http://'.$path;
    				}
    switch ($player->player)  //$player->type 0 = inline/new window, 1 = popup
    {
        case 0: //Direct
            switch ($player->type)
            {
                case 0: //new window
               // dump ($path, 'path: ');
                    $playercode =
                    '<a href="'.$path.'" onclick="window.open(\'index.php?option=com_biblestudy&view=popup&close=1&mediaid='.
                    $media->id.'\',\'newwindow\',\'width=100, height=100,menubar=no, status=no,location=no,toolbar=no,scrollbars=no\');
                     return true;" title="'.$media->malttext.' - '.$media->comment.' '.$duration.' '.$filesize.'" target="'.
                    $media->special.'"><img src="'.$src.'" alt="'.$media->malttext.' - '.$media->comment.' - '.$duration.
                    ' '.$filesize.'" width="'.$width.'" height="'.$height.'" border="0" /></a>';
                break;

                case 1: //Popup window

                    $playercode =
                    "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&player=0&view=popup&Itemid=".$Itemid.
                    "&template=".$template."&mediaid=".$media->id."', 'newwindow','width=".$player->playerwidth.",height=".
                    $player->playerheight."'); return false\"\"><img src='".$src."' height='".$height."' width='".$width.
                    "' title='".$mimetype." ".$duration." ".$filesize."' alt='".$src."'></a>";
                break;
            }
        break;

        case 1: //Internal
            switch ($player->type)
            {
                case 0: //Inline
                    $playercode =
                    "<script type='text/javascript'>
                    swfobject.embedSWF('".JURI::base()."components/com_biblestudy/assets/player/player.swf', 'placeholder".$media->id.
                    "', '".$player->playerwidth."', '".$player->playerheight."', '9.0.0', false,{file:'".$path.
                    "',autostart:'false'}, {allowfullscreen:'true', allowscriptaccess:'always', backcolor:'".$backcolor."', frontcolor:'".$frontcolor."', lightcolor:'".$lightcolor."',screencolor:'".$screencolor."'}, {id:'".$media->id."', name:'".$media->id."'});
                    </script>
                    <div id='placeholder".$media->id.
                    "'><a href=\"http://www.adobe.com/go/getflashplayer\" target=\"_blank\">".JTEXT::_('Get flash')."</a> ".JTEXT::_('to see this player')."</div>";
                
                break;

                case 1: //popup
                  //  dump ($template, 'template from mediaclass: ');
				  // Add space for popup window
				    $player->playerwidth = $player->playerwidth + 20;
					$player->playerheight = $player->playerheight + $params->get('popupmargin','50');

                    $playercode =
                    "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&player=1&view=popup&Itemid=".$Itemid.
                    "&template=".$template."&mediaid=".$media->id."', 'newwindow','width=".$player->playerwidth.",height=".
                    $player->playerheight."'); return false\"\"><img src='".$src."' height='".$height."' width='".$width.
                    "' title='".$mimetype." ".$duration." ".$filesize."' alt='".$src."'></a>";
                break;
            }
        break;

        case 2: //All Videos Reloaded
            $playercode = $this->getAVRLink($media, $params, $image); //echo $playercode;
        break;

        case 3: //All Videos

            switch ($player->type)
            {
                case 1: //This goes to the popup view
               	$playercode =
                "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&view=popup&player=3&template=".$template.
                "&mediaid=".$media->id."', 'newwindow','width=".$player->playerwidth.",height=".$player->playerheight."'); return false\"\">
                <img src='".$src."' height='".$height."' width='".$width."' title='".$mimetype." ".$duration." ".$filesize.
                "' alt='".$src."'></a>";
                break;

                case 0: // This plays the video inline
                $mediacode = $this->getAVmediacode($media->mediacode);
                $playercode = JHTML::_('content.prepare', $mediacode);
                break;
            }
        break;

        case 4: //Docman
            $playercode = $this->getDocman($media, $image);
        break;

        case 5: //article
            $playercode = $this->getArticle($media, $image); //dump ($playercode, 'playercode: ');
        break;

        case 6: //Virtuemart
            $playercode = $this->getVirtuemart($media, $params, $image);
        break;
    }
     //  dump ($playercode, 'playercode: ');
    return $playercode;
}

function hitPlay($id)
	{
	    $db =& JFactory::getDBO();
		$query = 'UPDATE #__bsms_mediafiles SET plays = plays + 1 WHERE id = '.$id; //dump ($query, 'query: ');
		$db->setQuery('UPDATE '.$db->nameQuote('#__bsms_mediafiles').'SET '.$db->nameQuote('plays').' = '.$db->nameQuote('plays').' + 1 '.' 	WHERE id = '.$id);
		$db->query();
		return true;
	}

function getAVRLink($media, $params, $image)
	{

	//	$play = $this->hitPlay($media->id);
		//dump ($media);
        $Itemid = JRequest::getInt('Itemid','1','get');
        $src = JURI::base().$image->path;
        $height = $image->height;
        $width = $image->width;
       JPluginHelper::importPlugin('system', 'avreloaded');

       $studyfile = $media->spath.$media->fpath.$media->filename;
       $mediacode = $media->mediacode;

       $bracketpos = strpos($mediacode,'}');
       $autostart = ' enablejs="true" autostart="true"';
    	$mediacode = substr_replace($mediacode, $autostart ,$bracketpos,0);

       $isrealfile = substr($media->filename, -4, 1);
       $fileextension = substr($media->filename,-3,3);
       if ($mediacode == '')
	   	{
			$mediacode = '{'.$fileextension.'remote}-{/'.$fileextension.'remote}';
       	}
       $mediacode = str_replace("'",'"',$mediacode);
       $ispop = substr_count($mediacode, 'popup');
       if ($ispop < 1)
	   	{
        	$bracketpos = strpos($mediacode,'}');
        	$mediacode = substr_replace($mediacode,' popup="true" ',$bracketpos,0);
		}

	   $isdivid = substr_count($mediacode, 'divid');
       if ($isdivid < 1)
	   	{
        	$dividid = ' divid="'.$media->id.'"';
        	$bracketpos = strpos($mediacode, '}');
        	$dividid = $dividid.' Itemid="2"';
        	$mediacode = substr_replace($mediacode, $dividid,$bracketpos,0);
       	}
       $isonlydash = substr_count($mediacode, '}-{');
       if ($isonlydash == 1)
	   	{
        	$ishttp = substr_count($studyfile, 'http://');
        	if ($ishttp < 1)
				{
         		$isrealfile = substr($media->filename, -4, 1);
         			if ($isrealfile == '.')
						{
          					$isslash = substr_count($studyfile,'//');
          						if (!$isslash)
									{
           								$studyfile = substr_replace($studyfile,'http://',0,0);
          							}
         				}
        		}


			if ($isrealfile != '.')
				{
				 $studyfile = $media->filename;
				}
			$mediacode = str_replace('-',$studyfile,$mediacode);
       }

	   $popuptype = 'window';
       if($params->get('popuptype') != 'window')
	   	{
        	$popuptype = 'lightbox';
       	}


		   $media1_link = $mediacode.'{avrpopup type="'.$popuptype.'" id="'.$media->id
       .'"}<img src="'.JURI::base().$image->path.'" alt="'.$media->malttext. ' - '.$media->comment
       .' '.$duration.' '.$filesize.'" width="'.$image->width
       .'" height="'.$image->height.'" border="0" title="'
       .$media->malttext.' - '.$media->comment.' '.$duration.' '.$filesize.'" />{/avrpopup}';
     return $media1_link;
	}

function getAVmediacode($mediacode)
    {
        $bracketpos = strpos($mediacode,'}');
        $dashposition = $bracketpos + 1;
        $isonlydash = substr_count($mediacode, '}-{');
        if ($isonlydash)
        {
            $mediacode = substr_replace($mediacode,$media->filename,$dashposition,0);
        }
        return $mediacode;
    }


} // End of class
?>
