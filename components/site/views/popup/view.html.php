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
			$document =& JFactory::getDocument();
            $document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');

			//Use only component template not the full site template
			JRequest::setVar('tmpl', 'component');
            jimport ('joomla.application.component.helper');
			$db	= & JFactory::getDBO();
			$mediaid  = JRequest::getInt('mediaid','','get');
			$Itemid = JRequest::getInt('Itemid','1','get');
            $templateid = JRequest::getInt('template','1','get');
            $query = 'SELECT * FROM #__bsms_templates WHERE id = '.$templateid;
            $db->setQuery($query);
            $db->query();
            $template = $db->loadObject();
            $params = new JParameter($template->params); 
			
			
			// The popup window call the counter function
			$play = $getMedia->hitPlay($mediaid);
			
			// All Videos plugin running youtube video in popup
			//echo JHTML::_('content.prepare', '{youtube}cyheJ480LYA{/youtube}');

             //This is the inline player
             $media = $getMedia->getMediaRows($mediaid);
             $itemparams = new JParameter($media->params); 
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
  //Here is where we start the display
  
echo '<div class="popupwindow">';
$headertext = '';
$footertext = '';
$headertext = $this->titles($params->get('popuptitle'), $media);
$footertext = $this->titles($params->get('popupfooter'), $media);
echo '<p class="popuptitle">'.$headertext.'</p>';
//echo "<BR>";          
echo         "<p id='preview' style='text-align:center; vertical-align:middle';>There is a problem with the player. We apologize for the inconvenience</p>
			<script type='text/javascript' src='".JURI::base()."components/com_biblestudy/assets/player/swfobject.js'></script>
			<script type='text/javascript'>
			var s1 = new SWFObject('".JURI::base()."components/com_biblestudy/assets/player/player.swf','player','".$playerwidth."','".$playerheight."','9');
            s1.addVariable('file','".$path1."');
            s1.addVariable('title','".$media->studytitle."');
            s1.addVariable('lightcolor','".$lightcolor."');
            s1.addVariable('frontcolor','".$frontcolor."');
            s1.addVariable('backcolor','".$backcolor."');
            s1.addVariable('author','".$media->teachername."');
            s1.addVariable('date','".$media->studydate."');
            s1.addVariable('description','".$media->studyintro."');
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

<?PHP

// Footer
echo '</div><div class="popupfooter"><p class="popupfooter">';
echo $footertext;
echo '</p>';





							
							
							
        }
function titles($text, $media)
{
    $text = str_replace('{{teacher}}', $media->teachername, $text);
    $text = str_replace('{{studydate}}', $media->studydate, $text);
    $text = str_replace('{{filename}}', $media->filename, $text);
    $text = str_replace('{{description}}', $media->description, $text);
    $text = str_replace('{{length}}', $media->length, $text);
    $text = str_replace('{{title}}', $media->studytitle, $text);
   
    return $text;
}

    }
    ?>
</div>

<!-- Close popup window

<BODY onLoad="setTimeout(window.close, 1)">

 -->