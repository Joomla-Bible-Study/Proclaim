<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Provides a media player and hits to the plays field
 */

defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
class jbsMedia 
{ 
	
	
function hitPlay($id)
	{
	//	$test = "hit it";
    //    dump ($test, 'test: ');
        $db =& JFactory::getDBO();
		$query = 'UPDATE #__bsms_mediafiles SET plays = plays + 1 WHERE id = '.$id; //dump ($query, 'query: ');
	//	$db->setQuery('UPDATE #__bsms_mediafiles SET plays = plays + 1 WHERE id = '.$id);
		$db->setQuery('UPDATE '.$db->nameQuote('#__bsms_mediafiles').'SET '.$db->nameQuote('plays').' = '.$db->nameQuote('plays').' + 1 '.' 	WHERE id = '.$id);
		$db->query();
		return true;
	}

function getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1)
		{
		//	$hitPlay = $this->hitPlay($media->id);
         /*   $db = JFactory::getDBO();
            $query = 'SELECT s.studytitle, s.studydate, s.teacher_id, t.teachername, t.id as tid, s.id as sid, s.studyintro, 
            m.id as mid, m.study_id
             FROM #__bsms_studies AS s
            LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)
            LEFT JOIN #__bsms_mediafiles AS m ON (s.id = m.study_id)
            WHERE m.id = '.$media->id.' LIMIT 1';
            $db->setQuery($query);
            $db->query();
            $study = $db->loadObject();
         */
            $study = $this->getMediaRows($media->id);
            //We need to escape any " and ' in the text fields
            $study->studyintro = str_replace('"', '\"', $study->studyintro);
            $study->studyintro = str_replace("'", "\'", $study->studyintro);
            $study->studytitle = str_replace("'", "\'", $study->studytitle);
             $study->studytitle = str_replace('"', '\"', $study->studytitle);
            //dump ($study->teachername, 'study: ');
            jimport ('joomla.application.component.helper');
			$itemparams = new JParameter ($media->params);
		    $playerwidth = $params->get('player_width');
            $playerheight = $params->get('player_height');
            if ($itemparams->get('playerheight')) {$playerheight = $itemparams->get('playerheight');}
            if ($itemparams->get('playerwidth')) {$playerwidth = $itemparams->get('playerwidth');}
			$extraparams = $itemparams->get('playervars');
			if ($itemparams->get('altflashvars'))
			{
				$flashvars = $itemparams->get('altflashvars');
			}
   			//$player_width = $params->get('player_width', 290);
            $item = $itemparams->get('internal_popup');
            $internal_popup = $params->get('internal_popup',0);
            //dump ($internal_popup, 'params: '); dump ($item, 'item: ');
            $backcolor = $params->get('backcolor','0x287585');
            $frontcolor = $params->get('frontcolor','0xFFFFFF');
            $lightcolor = $params->get('lightcolor','0x000000');
            $template = JRequest::getInt('templatemenuid','1','get');
            $type = $internal_popup;
            if ($item == 0){$type = 0;}
      /*      if ($type == 1)
           {
                $media1_link = 
             //   "<script type='text/javascript'>
           //   "<a href=\"#\" onclick=\"window.open('components/com_biblestudy/assets/player/player.swf?file=".$path1."&amp;allowfullscreen=true&amp;height=".$playerheight."&amp;width=".$playerwidth."&amp;&amp;id=veneers&amp;searchbar=false&amp;showicons=false&amp;autostart=true&amp;overstretch=fit&amp;backcolor=".$backcolor."&amp;frontcolor=".$frontcolor."&amp;lightcolor=".$lightcolor."&amp;title=".$study->studytitle."&amp;author=".$study->teachername."&amp;date=".$study->studydate."&amp;description=".$study->studyintro."', 'newwindow', config='height=".$playerheight.",width=".$playerwidth.",toolbar=no, menubar=no, scrollbars=yes, resizable=yes,location=no, directories=no, status=no'); return false\"\"><img src='".$src."' height='".$height."' width='".$width."' title='".$mimetype." ".$duration." ".$filesize."' alt='".$src."'></a>";
              
              	// Nick's Code links to view/popup.php	
			$media1_link =  
            "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&view=popup&Itemid=7&template=".$template."&mediaid=".$media->id."&path=".$path1."', 'newwindow','width=500,height=500'); return false\"\"><img src='".$src."' height='".$height."' width='".$width."' title='".$mimetype." ".$duration." ".$filesize."' alt='".$src."'></a>";  
          }
          else
          {
            
New code by Nick:
            $media1_link =
        "<script type='text/javascript'>
swfobject.embedSWF('".JURI::base()."components/com_biblestudy/assets/player/player.swf', 'placeholder".$media->id."', '320', '196', '9.0.0', false,{file:'".$path1."',autostart:'false'}, {allowfullscreen:'true', allowscriptaccess:'always'}, {id:'".$media->id."', name:'".$media->id."'});
</script>
<div id='placeholder".$media->id."'><a href='http://www.adobe.com/go/getflashplayer'>Get flash</a> to see this player</div>";

            */
            $media1_link =
            //This is the inline player
           "<p id='preview".$media->id."'>There is a problem with the player.</p>
			<script type='text/javascript' src='".JURI::base()."components/com_biblestudy/assets/player/swfobject.js'></script>
			<script type='text/javascript'>
			var s1 = new SWFObject('".JURI::base()."components/com_biblestudy/assets/player/player.swf','player','".$playerwidth."','".$playerheight."','9');
            s1.addVariable('file','".$path1."');
            s1.addVariable('title','".$study->studytitle."');
            s1.addVariable('lightcolor','".$lightcolor."');
            s1.addVariable('frontcolor','".$frontcolor."');
            s1.addVariable('backcolor','".$backcolor."');
            s1.addVariable('author','".$study->teachername."');
            s1.addVariable('date','".$study->studydate."');
            s1.addVariable('description','".$study->studyintro."');
            s1.addVariable('overstretch','fit');
            s1.addVariable('searchbar','false');
            s1.addVariable('showicons','false');
			s1.addVariable('allowfullscreen','true');
			s1.addVariable('allowscriptaccess','always');
			s1.useExpressInstall('expressinstall.swf');
			s1.addVariable('play','true');
            s1.addVariable('autostart','false');
            ".$extraparams."
            s1.write('preview".$media->id."');
			</script> ";
			
        
      //  } 
		return $media1_link;
		}

function getMediaLink($id)
{
	$media = $this->getMediaRows($id);
	$medialink = $media->spath.$media->fpath.$media->filename;
	return $medialink;
}

function getMediaRows($id)
{
	$db = JFactory::getDBO();
	$query = 'SELECT #__bsms_mediafiles.*,'
    . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
    . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
    . ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2, s.studytitle, s.studydate, s.teacher_id, t.teachername, t.id as tid, s.id as sid, s.studyintro,'
    . ' #__bsms_media.media_alttext AS malttext,'
    . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
    . ' FROM #__bsms_mediafiles'
    . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
    . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
    . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
    . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
    . ' LEFT JOIN #__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)'
    . ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
    . ' WHERE #__bsms_mediafiles.id = '.$id.' AND #__bsms_mediafiles.published = 1';
    $db->setQuery($query);
    $db->query();
    $media = $db->loadObject(); 
	return $media;
}		

function getDirectLink($media, $width, $height, $duration, $src, $path1, $filesize)
	{
      // $play = $this->hitPlay($media->id); //dump ($play, 'play: ');
      //Added to open a small popup, register a hit, then close
      $media1_link = '<script type="text/javascript">function callhit('.$media->id.')
{
window.open(\'index.php?option=com_biblestudy&view=popup&close=1\',newwindow,\'width='.$width.', height='.$height.',menubar=no, status=no,location=no,toolbar=no,scrollbars=no\');
}</script>';

	   $media1_link .= '<a href="'.$path1.'"Onclick=\'callhit('.$media->id.')\' title="'.$media->malttext.' - '.$media->comment.' '.$duration.' '.$filesize.'" target="'.$media->special.'"><img src="'.$src.'" alt="'.$media->malttext.' - '.$media->comment.' - '.$duration.' '.$filesize.'" width="'.$width.'" height="'.$height.'" border="0" /></a>';
	   
	   return $media1_link;
	}
	
function getAVRLink($media, $width, $height, $src, $params, $image, $Itemid)
	{
	//	$play = $this->hitPlay($media->id);
		//dump ($media);
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
    
	function fileRedirect()
	{
		$mediaid = JRequest::getInt('mediaid',1,'get');
		$medialink = $this->getMediaLink($mediaid);
		$play = $this->hitPlay($mediaid); //dump ($medialink, 'media: ');
	//	echo "<script>";
	//	echo " self.location='http://".$medialink."';";
	//	echo "</script>";
	return $medialink;
//	dump ($medialink, 'medialink: '); 
	}


}


?>