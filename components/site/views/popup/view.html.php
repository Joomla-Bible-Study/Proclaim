    <?php
    // no direct access
    defined( '_JEXEC' ) or die( 'Restricted access' );    jimport( 'joomla.application.component.view');

// This is the popup window for the teachings.  We could put anything in this window.

    class biblestudyViewpopup extends JView
    {
                                  
        function display()
        {
			require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
            $path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
            include_once($path1.'scripture.php');
            include_once($path1.'date.php');
			$getMedia = new jbsMedia();
            JRequest::setVar('tmpl', 'component');
            $mediaid  = JRequest::getInt('mediaid','','get');
			$Itemid = JRequest::getInt('Itemid','1','get');
            $templateid = JRequest::getInt('template','1','get'); 
            $close = JRequest::getInt('close','0','get');
            $player = JRequest::getInt('player','1','get');
            
			$document =& JFactory::getDocument();
            $document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
            $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        //Errors when using local swfobject.js file.  IE 6 doesn't work
        //$document->addScript(JURI::base().'components/com_biblestudy/assets/player/swfobject.js');
        
        // If this is a direct new window then all we need to do is perform hitPlay and close this window
		if ($close == 1)
            {
              //  $play = $getMedia->hitPlay($mediaid); //dump ($mediaid, 'play: ');
                echo JHTML::_('content.prepare','<script language=javascript>window.close();</script>');
                
            }
        	
			
            jimport ('joomla.application.component.helper');
			
            $getMedia = new jbsMedia();
            $media = $getMedia->getMediaRows2($mediaid); //dump ($media, 'media: ');
            $db	= & JFactory::getDBO();
			$query = 'SELECT * FROM #__bsms_templates WHERE id = '.$templateid; 
            $db->setQuery($query);
            $db->query();
            $template = $db->loadObject();
            $params = new JParameter($template->params); // dump ($params, 'params: ');
            $itemparams = new JParameter($media->params); // dump ($media, 'params; ');
            $saveid = $media->id;
            $media->id = $media->study_id;
			$scripture = getScripture($params, $media, $esv='0', $scripturerow='1'); //dump ($media->study_id, 'scripture: ');
            $media->id = $saveid;
            $date = getstudyDate($params, $media->studydate);
			// The popup window call the counter function
			$play = $getMedia->hitPlay($mediaid);
			
            $studyintro = str_replace('"', '\"', $media->studyintro);
            $studyintro = str_replace("'", "\'", $media->studyintro);
            $studytitle = str_replace("'", "\'", $media->studytitle);
            $studytitle = str_replace('"', '\"', $media->studytitle);
            
            
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
            $extraparams = '';
        	if ($itemparams->get('playervars')){$extraparams = $itemparams->get('playervars');}
			if ($itemparams->get('altflashvars'))
			{
				$flashvars = $itemparams->get('altflashvars');
			}
            $backcolor = $params->get('backcolor','0x287585');
            $frontcolor = $params->get('frontcolor','0xFFFFFF');
            $lightcolor = $params->get('lightcolor','0x000000');
            $screencolor = $params->get('screencolor','0xFFFFFF');
  //Here is where we start the display
   
 ?> 
<div class="popupwindow">
<?php
$headertext = '';
$footertext = ''; 

// Need to add in template
echo "<body bgcolor='".$params->get('popupbackground', 'white').">";

$headertext = $this->titles($params->get('popuptitle'), $media);
if ($itemparams->get('itempopuptitle')) {$headertext = $this->titles($itemparams->get('itempopuptitle'), $media);}
$footertext = $this->titles($params->get('popupfooter'), $media);
if ($itemparams->get('itempopupfooter')) {$footertext = $this->titles($itemparams->get('itempopupfooter'), $media);}
echo '<p class="popuptitle">'.$headertext.'</p>';

//Here is where we choose whether to use the Internal Viewer or All Videos
if ($itemparams->get('player') == 3 || $player == 3) {
    $mediacode = $getMedia->getAVmediacode($media->mediacode);
    echo JHTML::_('content.prepare', $mediacode);}  
      
            
if ($itemparams->get('player')== 1 || $player == 1)
{  
  
	echo    "<script type='text/javascript'>
swfobject.embedSWF('".JURI::base()."components/com_biblestudy/assets/player/player.swf', 'placeholder', '".$playerwidth."', '".$playerheight."', '9.0.0', false,{file:'".$path1."',title:'".$studytitle."',author:'".$media->teachername."',date:'".$media->studydate."',description:'".$studyintro."',autostart:'true',lightcolor:'".$lightcolor."',frontcolor:'".$frontcolor."',backcolor:'".$backcolor."',screencolor:'".$screencolor."',displayheight:'300'},{allowfullscreen:'true',allowscriptaccess:'always'},{id:'".$media->id."', name:'".$media->id."'});
</script>
<div id='placeholder'><a href='http://www.adobe.com/go/getflashplayer'>Get flash</a> to see this player</div>";
//  Flashvar - Colors, Autostart, Title, Author, Date, Description, Link, Image
//    Params - Allowfullscreen, Allowscriptaccess
//    Attributes - ID, Name

// Did not include ,link:'http://www.newhorizoncf.org',image:'/images/mp3player.jpg' in the Flashvar until adding options
// use this: JURI::base()."index.php?option=com_biblestudy&view=studieslist&templatemenuid=".$templateid
}

echo "<BR>Title: ". $studytitle;
echo "<BR>Teacher: ". $media->teachername;
echo "<BR>Date: ". $date;
echo "<BR>Scripture: " . $scripture; //Need to get Scripture 

//TODO:Need to get difference between direct popup and not so can have popup use this script
if ($itemparams->get('player')== 0 || JRequest::getInt('player','','get') == 0)
{
  //  echo '<div id=\'direct\'><script type=text/javascript> window.location.href=\''.$path1.'\'</script></div>';
  
  
    echo '<div class=\'direct\'><iframe src ="'.$path1.'" width="100%" height="100%" scrolling="no" frameborder="1" marginheight="0" marginwidth="0"><p>Your browser does not support iframes.</p>
</iframe></div>';
}
?>

<?PHP

// Footer
?>
</div><div class="popupfooter"><p class="popupfooter">
<?php
echo $footertext;
?>
</p></div>
<?php

        } //end of display function

function titles($text, $media)
{
   // dump ($text, 'text1: ');
    $text = str_replace('{{teacher}}', $media->teachername, $text);
    $text = str_replace('{{studydate}}', $media->studydate, $text);
    $text = str_replace('{{filename}}', $media->filename, $text);
    $text = str_replace('{{description}}', $media->description, $text);
    $text = str_replace('{{length}}', $media->length, $text);
    $text = str_replace('{{title}}', $media->studytitle, $text);
 //  dump ($text, 'text2: ');
    return $text;
}

    
    } //end of class 
    
    
            
    ?>


