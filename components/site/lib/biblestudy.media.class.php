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
			jimport ('joomla.application.component.helper');
			$itemparams = new JParameter ($media->params);
		//	$hitPlay = $this->hitPlay($media->id);
            $playerwidth = $params->get('player_width');
            $playerheight = $params->get('player_height');
            if ($itemparams->get('playerheight')) {$playerheight = $itemparams->get('playerheight');}
            if ($itemparams->get('playerwidth')) {$playerwidth = $itemparams->get('playerwidth');}
			$extraparams = $itemparams->get('playervars');
			$flashvars = "s1.addParam('flashvars','file=".$path1."&autostart=true');";
			if ($itemparams->get('altflashvars'))
			{
				$flashvars = $itemparams->get('altflashvars');
			}
   			//$player_width = $params->get('player_width', 290);
            $internal_popup = $params->get('internal_popup',0);
            $backcolor = $params->get('backcolor','0x287585');
            $frontcolor = $params->get('frontcolor','0xFFFFFF');
            $lightcolor = $params->get('lightcolor','0x000000');
            if ($internal_popup == 1 || $itemparams->get('internal_popup') == 1)
            {
                $media1_link = 
                "<script type='text/javascript'>
                window.open('components/com_biblestudy/assets/player/player.swf?file=".$path1."&amp;allowfullscreen=true&amp;height=".$playerheight."&amp;width=".$playerwidth."&amp;&amp;id=veneers&amp;searchbar=false&amp;showicons=false&amp;autostart=true&amp;overstretch=fit&amp;backcolor=".$backcolor."&amp;frontcolor=".$frontcolor."&amp;lightcolor=".$lightcolor."', 'newwindow', config='height=".$playerheight.",width=".$playerwidth.",toolbar=no, menubar=no, scrollbars=yes, resizable=yes,location=no, directories=no, status=no')
                </script>";
    		
            
            }
            else
            {
            $media1_link =
            //TF added for window
           "<p id='preview'>The player should show in this paragraph</p>
			<script type='text/javascript' src='".JURI::base()."components/com_biblestudy/assets/player/swfobject.js'></script>
			<script type='text/javascript'>
			var s1 = new SWFObject('".JURI::base()."components/com_biblestudy/assets/player/player.swf','player','".$playerwidth."','".$playerheight."','9');
			s1.addParam('allowfullscreen','true');
			s1.addParam('allowscriptaccess','always');
			s1.useExpressInstall('expressinstall.swf');
			".$flashvars."
			s1.addParam('play','true');
			".$extraparams."
			s1.write('preview');
			</script> ";
			}
		return $media1_link;
		}

function getDirectLink($media, $width, $height, $duration, $src, $path1, $filesize)
	{
      // $play = $this->hitPlay($media->id); //dump ($play, 'play: ');
	   $media1_link = '<a href="'.$path1.'" title="'.$media->malttext.' - '.$media->comment.' '.$duration.' '
       .$filesize.'" target="'.$media->special.'"><img src="'.$src
       .'" alt="'.$media->malttext.' - '.$media->comment.' - '.$duration.' '.$filesize.'" width="'.$width
       .'" height="'.$height.'" border="0" /></a>';
	   
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
    . ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2,'
    . ' #__bsms_media.media_alttext AS malttext,'
    . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
    . ' FROM #__bsms_mediafiles'
    . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
    . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
    . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
    . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
    . ' WHERE #__bsms_mediafiles.id = '.$id.' AND #__bsms_mediafiles.published = 1';
    $db->setQuery($query);
    $db->query();
    $media = $db->loadResult();
	return $media;
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