    <?php
    // no direct access
    defined( '_JEXEC' ) or die( 'Restricted access' );    jimport( 'joomla.application.component.view');

// This is the popup window for the teachings.  We could put anything in this window.

    class biblestudyViewpopup extends JView
    {
                                  
        function display()
        {
			require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
			$getMedia = new jbsMedia();
			
			echo "Test Bible Study Popup<BR><BR><BR>";

			//Use only component template not the full site template
			JRequest::setVar('tmpl', 'component');

			$db	= & JFactory::getDBO();

			$mediaid  = JRequest::getInt('mediaid','','get');
			$Itemid = JRequest::getInt('Itemid','1','get');
            $template = JRequest::getInt('template','1','get');
            
			// I am sure there is a better way to get this information
/*			$path = JRequest::getVar('path');
			
			
			$query = 'SELECT filename'
					. ' FROM #__bsms_mediafiles'
					. ' WHERE id like "'. $mediaid .'"';
					$db->setQuery( $query );
					$mediafilename  = $db->loadResult();
*/
		//echo $mediaid. "<BR><BR>";
		//	echo $mediafilename. "<BR><BR>";	
			
			
			
	//dump($path,'path');
			
			// The popup window call the counter function
			$play = $getMedia->hitPlay($mediaid);
			
			// All Videos plugin running youtube video in popup
			//echo JHTML::_('content.prepare', '{youtube}cyheJ480LYA{/youtube}');

 //This is the inline player
 $media = $getMedia->getMediaRows($mediaid);
// dump ($media, 'media: ');
 $path1 = $media->spath.$media->fpath.$media->filename;
 if(!eregi('http://', $path1)) 
				{
					$path1 = 'http://'.$path1;
				}
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
            $backcolor = $params->get('backcolor','0x287585');
            $frontcolor = $params->get('frontcolor','0xFFFFFF');
            $lightcolor = $params->get('lightcolor','0x000000');
            
  echo         "<p id='preview'>There is a problem with the player. We apologize for the inconvenience</p>
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
            s1.write('preview');
			</script> ";
?>
<!--<object width="289" height="280" data="components/com_biblestudy/assets/player/player.swf?file=<?php //echo $path;?>" type="application/x-shockwave-flash">
 <param name="bgcolor" value="#ffffff" />
<param name="flashvars" value="playerMode=embedded" />
<param name="src" value="components/com_biblestudy/assets/player/player.swf?file=<?php //echo $path;?>" />
<param name="wmode" value="window" />
<param name="quality" value="best" />
</object>

<!--
This doesn't work with IE8 but the other works with IE and FF
<embed
   src="components/com_biblestudy/assets/player/player.swf?file=<?php// echo $path;?>" 
   width="300"
   height="300"
   allowscriptaccess="always"
   allowfullscreen="true"
   id="player1"
   name="player1"

/>
-->
<?PHP







							
							
							
        }


    }
    ?>


<!-- Close popup window

<BODY onLoad="setTimeout(window.close, 1)">

 -->