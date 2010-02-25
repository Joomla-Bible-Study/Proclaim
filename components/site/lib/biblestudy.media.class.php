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
//jimport ('joomla.application.component.helper');	
	
function hitPlay($id)
	{
		$db =& JFactory::getDBO();
		$query = 'UPDATE #__bsms_mediafiles SET plays = plays + 1 WHERE id = '.$id; //dump ($query, 'query: ');
	//	$db->setQuery('UPDATE #__bsms_mediafiles SET plays = plays + 1 WHERE id = '.$id);
		$db->setQuery('UPDATE '.$db->nameQuote('#__bsms_mediafiles').'SET '.$db->nameQuote('plays').' = '.$db->nameQuote('plays').' + 1 '.' 	WHERE id = '.$id);
		$db->query();
		return true;
	}

function getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1)
		{
		//	$play = $this->hitPlay($media->id);
   			$player_width = $params->get('player_width', 290);
			$media1_link =
			 '<script language="JavaScript" src="'.JURI::base().'components/com_biblestudy/audio-player.js"></script>
		<object type="application/x-shockwave-flash" data="'.JURI::base().'components/com_biblestudy/player.swf" id="audioplayer'.$row_count.'" height="24" width="'.$params->get('player_width', 290).'">
		<param name="movie" value="'.JURI::base().'components/com_biblestudy/player.swf">
		<param name="FlashVars" value="playerID='.$row_count.'&amp;soundFile='.$path1.'>
		<param name="quality" value="high">
		<param name="menu" value="false">
		<param name="wmode" value="transparent">
		</object> ';
			
		return $media1_link;
		}

function getDirectLink($media, $width, $height, $duration, $src, $path1, $filesize)
	{
      // $play = $this->hitPlay($media->id); //dump ($play, 'play: ');
	   $media1_link = '<a href="'.$path1.'" title="'.$media->malttext.' - '.$media->comment.' '.$duration.' '
       .$filesize.'" target="'.$media->special.'" onclick="playHit('.$media->id.')"><img src="'.$src
       .'" alt="'.$media->malttext.' - '.$media->comment.' - '.$duration.' '.$filesize.'" width="'.$width
       .'" height="'.$height.'" border="0" /></a>';
	   
	   return $media1_link;
	}
	
function getAVRLink($media, $width, $height, $src, $params, $image, $Itemid)
	{
	//	$play = $this->hitPlay($media->id);
       JPluginHelper::importPlugin('system', 'avreloaded');
	   
       $studyfile = $media->spath.$media->fpath.$media->filename;
       $mediacode = $media->mediacode;
       
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
       .'" }<img src="'.JURI::base().$image->path.'" alt="'.$media->malttext. ' - '.$media->comment
       .' '.$duration.' '.$filesize.'" width="'.$image->width
       .'" height="'.$image->height.'" border="0" title="'
       .$media->malttext.' - '.$media->comment.' '.$duration.' '.$filesize.'" />{/avrpopup}';	
     return $media1_link;	
	}
		
}


?>