<?php
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');

function getMediatable($params, $row, $admin_params)
{
//dump ($row, 'row: ');
	$getMedia = new jbsMedia();
jimport ('joomla.application.component.helper');
//dump ($admin_params, 'admin_params: ');
if (!$row->id) {return FALSE;}
    global $mainframe, $option;
	$database = & JFactory::getDBO();
		$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		include_once($path1.'filesize.php');
		include_once($path1.'filepath.php');
		include_once($path1.'duration.php');
		include_once($path1.'image.php');
		include_once ($path1.'helper.php');
	$database->setQuery('SELECT * FROM #__bsms_admin WHERE id = 1');
	$database->query();
	$admin = $database->loadObjectList();
 

	$d_image = ($admin[0]->download ? '/'.$admin[0]->download : '/download.png');
	
	$images = new jbsImages();
 	$download_tmp = $images->getMediaImage($admin[0]->download, $media=NULL);

    $download_image = $download_tmp->path;
	$query_media1 = 'SELECT #__bsms_mediafiles.*,'
    . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
    . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
    . ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2,'
    . ' #__bsms_media.media_alttext AS malttext,'
    . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
    . ' FROM #__bsms_mediafiles'
    . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
    . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
    . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
    . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
    . ' WHERE #__bsms_mediafiles.study_id = '.$row->id.' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC, #__bsms_mediafiles.mime_type ASC';
    $database->setQuery( $query_media1 );
    $media1 = $database->loadObjectList('id');
	$rows2 = count($media1);
	
	
	$compat_mode = $admin_params->get('compat_mode');
	if ($rows2 < 1) { $mediatable = null; return $mediatable; }
	
	
	$mediatable = '<div><table class="mediatable"><tbody><tr>';
	$row_count = 0;
	
	foreach ($media1 as $media) {
		
	$row_count = $row_count + 1;
	//Load the parameters
	$itemparams = new JParameter ($media->params);
	$Itemid = $params->get('detailstemplateid', 1);
	$images = new jbsImages();
 	$image = $images->getMediaImage($media->path2, $media->impath);

	
	$mediatable .= '<td>';
	
	
	//todo - not sure how much of this is needed
	 $idfield = '#__bsms_mediafiles.id';
	  $filesize = getFilesize($media->size);
	  $duration = getDuration($params, $row); //This one IS needed
	  //dump ($duration, 'duration: ');
	  //dump ($params);
      $mimetype = $media->mimetext;
      $src = JURI::base().$image->path;
	  $height = $image->height;
	  $width = $image->width;
      $ispath = 0;
	  $mime = '';
	  $path1 = getFilepath($media->id, $idfield, $mime);
	  
      //dump ($playertype, 'playertype: ');
      $playertype = 0;
      if ($params->get('media_player') == 1 || $itemparams->get('player') == 1)
      {
      	$playertype = 1;
      }
      if ($params->get('useavr') == 1 || $itemparams->get('player') == 2)
	  {
	  	$playertype = 2;
	  } 
	 
      	$view = JRequest::getWord('view', 'studieslist','get'); 
        $t = JRequest::getInt('templatemenuid',1,'get');
		$start = JRequest::getInt('start',0,'get'); //dump ($start, 'start: ');
		$player = JRequest::getInt('player','','get');
	    $mediaid = JRequest::getInt('mediaid','','get');
        
          	switch ($view)
    		{
    			case 'studieslist':
    				$returnid = $row->study_id;
    				break;
    			
    			case 'seriesdetail':
                //    $returnid = $row->id;
    				$returnid = JRequest::getInt('returnid','get');
    			//	JRequest::setVar('mediaid',$media->id,'get',true);
    			//	$mediaid = JRequest::getInt('mediaid','','get');
    				break;
    			
    			case 'teacherdisplay':
    			//	$cid = JRequest::getInt('id','','get');
    				$returnid = $row->teacher_id;
                //    dump ($returnid, 'returnid: ');
    			//	$mediaid = JRequest::getInt('mediaid','','get');
    		//    	dump ($row->teacher_id, 'row: ');
                    break;
    			case 'studydetails':
                    $studyid = '&id='.$row->study_id;
                  $returnid = $row->study_id.$studyid;
                  $mediaid = JRequest::getInt('mediaid','','get');
                  
                  break;
                    	
    			default:
    		//		$cid = $row->id;
    				break;	
    			
    		}
      
		
       $playerwidth = $params->get('player_width');
       $playerheight = $params->get('player_height');
       if ($itemparams->get('playerheight')) {$playerheight = $itemparams->get('playerheight');}
       if ($itemparams->get('playerwidth')) {$playerwidth = $itemparams->get('playerwidth');}
    //   dump ($playerwidth, 'width: ');
    //   dump ($playerheight, 'height: ');
	//	dump ($player, 'player: ');
	//	dump ($start, 'start: ');
	//	dump ($mediaid, 'mediaid: ');
	//	dump ($_GET, 'get: ');
   // dump ($returnid, 'returnid: '); dump ($view, 'view: ');
		
		//	dump ($playertype, 'playertype: ');
      switch ($playertype)
      {
      	case 0:
      	
          //	$media1_link = '<form action="index.php" method="post"><input type="hidden" id="view" name="view" value="'.$view.'"><input type="hidden" name="controller" id="controller" value="'.$view.'"><input type="hidden" name="task" id="task" value="play"><input type="hidden" name="start" id="start" value="1"><input type="hidden" id="player" name="player" value="0"><input type="hidden" name="templatemenuid" id="templatemenuid" value="'.$t.'"><input type="hidden" id="mediaid" name="mediaid" value="'.$media->id.'"><input type="image" src="'.$src.'" height="'.$height.'" width="'.$width.'" title=" Click to submit. '.$mimetype.' '.$duration.' '.$filesize.'" alt="'.$src.'" value="submit"></form>';
    		  $media1_link = '<a href="'.JRoute::_(JURI::base().'index.php?option=com_biblestudy&view='.$view.'&mediaid='.$media->id.'&templatemenuid='.$t.'&task=play&player=0&start=1&returnid='.$returnid).'"><img src="'.$src.'" height="'.$height.'" width="'.$width.'" title="'.$mimetype.' '.$duration.' '.$filesize.'" alt="'.$src.'"></a>'; 
    		  
          	if ($mediaid == $media->id && $start == 1 && $player==0)
          	{
          		$play = $getMedia->hitPlay($media->id);
                echo "<script>";
    			echo "window.open('".$path1."','newwindow', config='height=".$playerheight.",width=".$playerwidth.",toolbar=no, menubar=no, scrollbars=yes, resizable=yes,location=no, directories=no, status=no')";
    			echo "</script>";
          	}
          
    		break;
		
        case 1:
//	dump ($mediaid, 'mediaid: ');
//    dump ($media->id, 'media->id: ');
    		if ($player == 1 && $start == 1 && $mediaid == $media->id)
    		{
    			$play = $getMedia->hitPlay($media->id);
                $media1_link = $getMedia->getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1);
    		}
    		else 
    		{
    			$media1_link = '<a href="'.JRoute::_(JURI::base().'index.php?option=com_biblestudy&view='.$view.'&mediaid='.$media->id.'&templatemenuid='.$t.'&task=play&player=1&start=1&returnid='.$returnid).'"><img src="'.$src.'" height="'.$height.'" width="'.$width.'" title="'.$mimetype.' '.$duration.' '.$filesize.'" alt="'.$src.'"></a>'; 
    		}
    		break;

		case 2:
       // if ($player == 2 && $start == 1 && $mediaid == $media->id)
    	//	{$play = $getMedia->hitPlay($media->id);}
			$media1_link = $getMedia->getAVRLink($media, $width, $height, $src, $params, $image, $Itemid);
		break;
		
		
      }

	  if ($media->docMan_id > 0)
	 	{
			$media1_link = getDocman($media, $width, $height, $src, $duration, $filesize);
		}
	if ($media->article_id > 0)
		{
			$media1_link = getArticle($media, $width, $height, $src);
		}
	if ($media->virtueMart_id > 0)
		{
			$media1_link = getVirtuemart($media, $width, $height, $src, $params);
		}
		
      
       /**
        * @desc: I hope to in the future load media files using this method
        */
       /*  echo ('<div class="inlinePlayer" id="media-'.$media->id.'"></div>');
        echo ('<a href="'.$path1.'" class="btnPlay" alt="'.$media->id.'">Play</a>');*/


       /*$abspath    = JPATH_SITE;
        require_once($abspath.'/components/com_biblestudy/classes/class.biblestudymediadisplay.php');
        $inputtype = 0;
        $media_display = new biblestudymediadisplay($row->id, $inputtype);
        $media_display->id = $row->id;
        $media_display->inputtype = 0;*/

       // Here is where we begin to build the mediatable variable
	
	 //Here we test to see if docMan or article is used
	 
	$link_type = $media->link_type;
	
		
		if ($link_type > 0)
		{ 
	   		$width=$download_tmp->width;
	   		$height=$download_tmp->height;
	   		  
	      if($compat_mode == 0) 
		  {
	      		$downloadlink ='<a href="index.php?option=com_biblestudy&id='.$media->id.'&view=studieslist&controller=studieslist&task=download">';
		  }
		  else
		  {
	      		$downloadlink ='<a href="http://joomlabiblestudy.org/router.php?file='.$media->spath.$media->fpath.$media->filename.'&size='.$media->size.'">';
		  }
	     $downloadlink .= '<img src="'.$download_image.'" alt="'.JText::_('Download').'" height="'.$height.'" width="'.$width.'" title="'.JText::_('Download').'" />'.JText::_('</a>'); 
  
	  	}
	  	switch ($link_type)
	  	{
 			case 0:
 			$mediatable .= $media1_link;
 			break;
 			
			case 1:
	  		$mediatable .= $media1_link.$downloadlink;
	  		break;
	  		
	  		case 2:
	  		$mediatable = '<div><table class="mediatable"><tbody><tr><td>'.$downloadlink;
	  		break;
	  	}
	$mediatable .= '</td>';
	
	} //end of foreach of media results
	
	$mediatable .= '</tr>';

if ($params->get('show_filesize') > 0 ) 
	{
		$mediatable .= '<tr>';
		foreach ($media1 as $media) {
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
			
				$mediatable .= '<td><span class="bsfilesize">'.$filesize.'</span></td>';
				 
		} //end second foreach
		$mediatable .= '</tr>';
	} // end of if show_filesize

	$mediatable .='</table>';
    return $mediatable;
}

function getDocman($media, $width, $height, $src, $duration, $filesize)
	{
		$docman = '<a href="index.php?option=com_docman&task=doc_download&gid='.$media->docMan_id.'"
		 title="'.$media->malttext.' - '.$media->comment.'" target="'.$media->special.'"><img src="'.$src
       .'" alt="'.$media->malttext.' '.$duration.' '.$filesize.'" width="'.$width
       .'" height="'.$height.'" border="0" /></a>';
		
		
	return $docman;
	}
	
function getArticle($media, $width, $height, $src)
	{
		$article = '<a href="index.php?option=com_content&view=article&id='.$media->article_id.'"
		 alt="'.$media->malttext.' - '.$media->comment.'" target="'.$media->special.'"><img src="'.$src.'" width="'.$width
       	.'" height="'.$height.'" border="0" /></a>';
		
	return $article;
	}
	
function getVirtuemart($media, $width, $height, $src, $params)
	{
		
		$vm = '<a href="index.php?option=com_virtuemart&page=shop.product_details&flypage='.$params->get('store_page', 'flypage.tpl').'&product_id='.$media->virtueMart_id.'"
		alt="'.$media->malttext.' - '.$media->comment.'" target="'.$media->special.'"><img src="'.$src.'" width="'.$width
       	.'" height="'.$height.'" border="0" /></a>';
		
	return $vm;
	}
	

	function getMediaRows($study_id) {
    $query = 'SELECT #_bsms_mediafiles.*,'
       . ' #_bsms_servers.id AS ssid, #_bsms_servers.server_path AS spath,'
       . ' #_bsms_folders.id AS fid, #_bsms_folders.folderpath AS fpath,'
       . ' #_bsms_media.id AS mid, #_bsms_media.media_image_path AS impath, #_bsms_media.media_image_name AS imname, #_bsms_media.path2 AS path2,'
       . ' #_bsms_media.media_alttext AS malttext,'
       . ' #_bsms_mimetype.id AS mtid, #_bsms_mimetype.mimetext'
       . ' FROM #_bsms_mediafiles'
       . ' LEFT JOIN #_bsms_media ON (#_bsms_media.id = #_bsms_mediafiles.media_image)'
       . ' LEFT JOIN #_bsms_servers ON (#_bsms_servers.id = #_bsms_mediafiles.server)'
       . ' LEFT JOIN #_bsms_folders ON (#_bsms_folders.id = #_bsms_mediafiles.path)'
       . ' LEFT JOIN #_bsms_mimetype ON (#_bsms_mimetype.id = #_bsms_mediafiles.mime_type)'
       . ' WHERE #_bsms_mediafiles.study_id = '.$study_id.' AND #_bsms_mediafiles.published = 1 ORDER BY ordering ASC, #_bsms_mediafiles.mime_type ASC;';
        
    $database = & JFactory::getDBO();
    $database->setQuery( $query );
    $database->query();
    $mediaRows = $database->loadObjectList();
    return $mediaRows;
    }