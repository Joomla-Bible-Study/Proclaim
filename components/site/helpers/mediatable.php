<?php
defined('_JEXEC') or die();

function getMediatable($params, $row, $admin_params)
{
//dump ($admin_params, 'admin_params: ');
    global $mainframe, $option;
	$database = & JFactory::getDBO();
		$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		include_once($path1.'filesize.php');
		include_once($path1.'filepath.php');
		include_once($path1.'duration.php');
		include_once($path1.'image.php');
	  $database->setQuery('SELECT * FROM #__bsms_admin WHERE id = 1');
	  $database->query();
	  $admin = $database->loadObjectList();
	  //$admin_params =& new JParameter($admin[0]->params);
	$d_path1 = ($admin_params->get('media_imagefolder') ? 'images'.DS.$admin_params->get('media_imagefolder') : 'components/com_biblestudy/images');
	$d_image = ($admin[0]->download ? DS.$admin[0]->download : '/download.png');
	$d_path = $d_path1.$d_image;
	//dump ($d_image, 'd_image: ');
//if (!$media->path2) { $d_path = $media->impath; }
	//  if ($media->path2 && !$admin_params->get('media_imagefolder')) { $d_path = 'components/com_biblestudy/images/'.$media->path2; }
	  //if ($media->path2 && $admin_params->get('media_imagefolder')) { $d_path = 'images'.DS.$admin_params->get('media_imagefolder').DS.$media->path2;}
	  //$d_image = ($admin[0]->download ? $admin[0]->download : 'components/com_biblestudy/images/download.png');
	  //$d_path = ($admin_params->get('media_imagefolder') ? 'images/'.$admin_params->get('media_imagefolder') : 'components/com_biblestudy/images');
	  $download_tmp = getImage($d_path);
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
	//dump ($compat_mode, 'compat_mode: ');
	//dump ($rows2, 'Rows2: ');
	if ($rows2 < 1) { $mediatable = null; return $mediatable; }
	$mediatable = '<table class="mediatable"><tbody><tr>';
	foreach ($media1 as $media) {
	if (!$media->path2) { $i_path = $media->impath; }
	  if ($media->path2 && !$admin_params->get('media_imagefolder')) { $i_path = 'components/com_biblestudy/images/'.$media->path2; }
	  if ($media->path2 && $admin_params->get('media_imagefolder')) { $i_path = 'images'.DS.$admin_params->get('media_imagefolder').DS.$media->path2;}
	//$i_image = ($media->path2 ? $media->path2 : $media->impath);
	//$i_path = ($admin_params->get('media_imagefolder') ? 'images/'.$admin_params->get('media_imagefolder') : '');
	//dump ($admin_params, 'admin_params: ');
	//if ($media->path2 && !$admin_params->get('media_imagefolder')) { $i_path = 'components/com_biblestudy/images';}
	$image = getImage($i_path);
	
		$mediatable .= '<td>';
		
      
      $link_type = $media->link_type;
	  
      $useplayer = 0;
	  
      if ($params->get('media_player') > 0) {
       //Look to see if it is an mp3
       $ismp3 = substr($media->filename,-3,3);
       if ($ismp3 == 'mp3'){$useplayer = 1;}else {$useplayer = 0;}
	   } //End if media_player param test
      $idfield = '#__bsms_mediafiles.id';
	  $id4 = $media->id;
	  $id3 = $id4;
	  //dump ($media->id, 'id4: ');
	  $filesize = getFilesize($media->size);
	  //dump ($filesize, 'filesize');
	  $duration = getDuration($params, $row);
	  $media_size = $filesize;
	 // dump ($media_size, 'filesize: ');
      $mimetype = $media->mimetext;
      $src = JURI::base().$image->path;
	  $height = $image->height;
	  $width = $image->width;
      //if ($imagew) {$width = $imagew;} else {$width = 24;}
      //if ($imageh) {$height = $imageh;} else {$height= 24;}
      $ispath = 0;
	  $mime = '';
	  $path1 = getFilepath($id3, $idfield, $mime);
  
       $pathname = $media->fpath;
       $filename = $media->filename;
       $ispath = 1;
       $direct_link = '<a href="'.$path1.'" title="'.$media->malttext.' '.$duration.' '
       .$media_size.'" target="'.$media->special.'"><img src="'.$src
       .'" alt="'.$media->malttext.' '.$duration.' '.$media_size.'" width="'.$width
       .'" height="'.$height.'" border="0" /></a>';
      $isavr = 0;
	  //dump ($isavr, 'isavr: ');
      if (JPluginHelper::importPlugin('system', 'avreloaded'))
      {
		  JPluginHelper::importPlugin('system', 'avreloaded');
       $isavr = 1;
	   
       $studyfile = $media->spath.$media->fpath.$media->filename;
       $mediacode = $media->mediacode;
       
       $isrealfile = substr($media->filename, -4, 1);
       $fileextension = substr($media->filename,-3,3);
       if ($mediacode == ''){
        $mediacode = '{'.$fileextension.'remote}-{/'.$fileextension.'remote}';
       }
       $mediacode = str_replace("'",'"',$mediacode);
       $ispop = substr_count($mediacode, 'popup');
       if ($ispop < 1) {
        $bracketpos = strpos($mediacode,'}');
        $mediacode = substr_replace($mediacode,' popup="true" ',$bracketpos,0);
       }
       $isdivid = substr_count($mediacode, 'divid');
       if ($isdivid < 1) {
        $dividid = ' divid="'.$media->id.'"';
        $bracketpos = strpos($mediacode, '}');
        $mediacode = substr_replace($mediacode, $dividid,$bracketpos,0);
       }
       $isonlydash = substr_count($mediacode, '}-{');
       if ($isonlydash == 1){
        $ishttp = substr_count($studyfile, 'http://');
        if ($ishttp < 1) {
         //We want to see if there is a file here or if it is streaming by testing to see if there is an extension
         $isrealfile = substr($media->filename, -4, 1);
         if ($isrealfile == '.') {
          $isslash = substr_count($studyfile,'//');
          if (!$isslash) {
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
       if($params->get('popuptype') != 'window') {
        $popuptype = 'lightbox';
       }
       $avr_link = $mediacode.'{avrpopup type="'.$popuptype.'" id="'.$media->id
       .'"}<img src="'.JURI::base().$image->path.'" alt="'.$media->malttext
       .' '.$duration.' '.$media_size.'" width="'.$image->width
       .'" height="'.$image->height.'" border="0" title="'
       .$media->malttext.' '.$duration.' '.$media_size.'" />{/avrpopup}';
       //dump ($avr_link, 'AVR Lnk');

      }
      $useavr = 0;
      $useavr = $useavr + $params->get('useavr') + $media->internal_viewer;
      $isfilesize = 0;
     // if ($media_size > 0)
     // {
      // $isfilesize = 1;
       $media1_sizetext = $filesize;
     // }
      //else {$media1_sizetext = '';}
      $media1_link = $direct_link;

      if ($useavr > 0)
      { $media1_link = $avr_link;
      //dump ($avr_link, 'AVR Link');
       
      }
      if ($useplayer == 1){
       $player_width = $params->get('player_width');
       if (!$player_width) { $player_width = '290'; }
       $media1_link =
     '<script language="JavaScript" src="'.JURI::base().'components/com_biblestudy/audio-player.js"></script>
<object type="application/x-shockwave-flash" data="'.JURI::base().'components/com_biblestudy/player.swf" id="audioplayer'.$row_count.'" height="24" width="290">
<param name="movie" value="'.JURI::base().'components/com_biblestudy/player.swf">
<param name="FlashVars" value="playerID='.$row_count.'&amp;soundFile='.$path1.'">
<param name="quality" value="high">
<param name="menu" value="false">
<param name="wmode" value="transparent">
</object> ';}
       
       /**
        * @desc: I hope to in the future load media files using this method
        */
       /*  echo ('<div class="inlinePlayer" id="media-'.$media->id.'"></div>');
        echo ('<a href="'.$path1.'" class="btnPlay" alt="'.$media->id.'">Play</a>');*/


       /*$abspath    = JPATH_SITE;
        require_once($abspath.DS.'components/com_biblestudy/classes/class.biblestudymediadisplay.php');
        $inputtype = 0;
        $media_display = new biblestudymediadisplay($row->id, $inputtype);
        $media_display->id = $row->id;
        $media_display->inputtype = 0;*/

       // Here is where we begin to build the mediatable variable
		
	 $mediatable .= $media1_link;
	 //showing of filesize removed for now - it was causing problems.
		//if ($params->get('show_filesize') > 0 ) {
		//$mediatable .= '<div class="mediasize'.$params->get('pageclass_sfx').'">'.$filesize.'</div>';
		
		//}

		if ($link_type > 0){ //$src = JURI::base().$download_image;
	   $width=$download_tmp->width;
	   $height=$download_tmp->height;
      if($compat_mode == 0) {
       $mediatable .='<a href="index.php?option=com_biblestudy&id='.$media->id.'&view=studieslist&controller=studieslist&task=download">';
	   
      }else{
       $mediatable .='<a href="http://joomlaoregon.com/router.php?file='.$media->spath.$media->fpath.$media->filename.'&size='.$media->size.'">';
	   
      }
     
	$mediatable .= '<img src="'.$download_image.'" alt="'.JText::_('Download').'" height="'.$height.'" width="'.$width.'" title="'.JText::_('Download').'" />'.JText::_('</a>'); 
  
	  }
	
	
	$mediatable .= '</td>';
	
	} //end of foreach of media results
	
	$mediatable .= '</tr>';

if ($params->get('show_filesize') > 0 ) 
	{
		$mediatable .= '<tr>';
		foreach ($media1 as $media) {
			$filesize = getFilesize($media->size);
			
				$mediatable .= '<td><span class="bsfilesize">'.$filesize.'</span></td>';
				 
		} //end second foreach
		$mediatable .= '</tr>';
	} // end of if show_filesize

	$mediatable .='</table>';
    return $mediatable;
}

