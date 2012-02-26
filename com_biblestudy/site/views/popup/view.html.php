<?php
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

// This is the popup window for the teachings.  We could put anything in this window.
//TODO Need to Clean this up and rework to be proper Joomla calles bcc

class biblestudyViewpopup extends JView {

    function display() {
        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
        $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once($path1 . 'scripture.php');
        include_once($path1 . 'date.php');
        include_once($path1 . 'duration.php');
        //	$getMedia = new jbsMedia();
        JRequest::setVar('tmpl', 'component');
        $mediaid = JRequest::getInt('mediaid', '', 'get');
        $templateid = JRequest::getInt('t', '1', 'get');
        $close = JRequest::getInt('close', '0', 'get');
        $player = JRequest::getInt('player', '1', 'get');

        $document = JFactory::getDocument();
        $css = $params->get('css','biblestudy.css');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/'.$css);
        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
        //Errors when using local swfobject.js file.  IE 6 doesn't work
        // If this is a direct new window then all we need to do is perform hitPlay and close this window
        if ($close == 1) {
            echo JHTML::_('content.prepare', '<script language=javascript>window.close();</script>');
        }


        jimport('joomla.application.component.helper');

        $getMedia = new jbsMedia();
        $media = $getMedia->getMediaRows2($mediaid);
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__bsms_templates WHERE id = ' . $templateid;
        $db->setQuery($query);
        $db->query();
        $template = $db->loadObject();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template->params);
        $params = $registry;


        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($media->params);
        $itemparams = $registry;
        $saveid = $media->id;
        $media->id = $media->study_id;
        $scripture = getScripture($params, $media, $esv = '0', $scripturerow = '1');
        $media->id = $saveid;
        $date = getstudyDate($params, $media->studydate);
        // The popup window call the counter function
        $play = $getMedia->hitPlay($mediaid);
        $length = getDuration($params, $media);
        $badchars = array("'", '"');
        $studytitle = str_replace($badchars, ' ', $media->studytitle);
        $studyintro = str_replace($badchars, ' ', $media->studyintro);

        $path1 = $media->spath . $media->fpath . $media->filename;
        if (preg_match('@^(?:http://)?([^/]+)@i', $path1)) {
            $path1 = 'http://' . $path1;
        }
        $playerwidth = $params->get('player_width');
        $playerheight = $params->get('player_height');
        if ($itemparams->get('playerheight')) {
            $playerheight = $itemparams->get('playerheight');
        }
        if ($itemparams->get('playerwidth')) {
            $playerwidth = $itemparams->get('playerwidth');
        }
        $extraparams = '';
        if ($itemparams->get('playervars')) {
            $extraparams = $itemparams->get('playervars');
        }
        if ($itemparams->get('altflashvars')) {
            $flashvars = $itemparams->get('altflashvars');
        }
        $backcolor = $params->get('backcolor', '0x287585');
        $frontcolor = $params->get('frontcolor', '0xFFFFFF');
        $lightcolor = $params->get('lightcolor', '0x000000');
        $screencolor = $params->get('screencolor', '0xFFFFFF');
        //Here is where we start the display
        ?>
        <div class="popupwindow">
            <?php
            $headertext = '';
            $footertext = '';

            // Need to add in template
            echo "<body bgcolor='" . $params->get('popupbackground', 'black') . "'>";

            $headertext = $this->titles($params->get('popuptitle'), $media, $scripture, $date, $length);
            if ($itemparams->get('itempopuptitle')) {
                $headertext = $this->titles($itemparams->get('itempopuptitle'), $media, $scripture, $date, $length);
            }
            $footertext = $this->titles($params->get('popupfooter'), $media, $scripture, $date, $length);
            if ($itemparams->get('itempopupfooter')) {
                $footertext = $this->titles($itemparams->get('itempopupfooter'), $media, $scripture, $date, $length);
            }
            echo '<div class="popuptitle"><p class="popuptitle">' . $headertext . '</p></div>';
            //Here is where we choose whether to use the Internal Viewer or All Videos
            if ($itemparams->get('player') == 3 || $player == 3 || $itemparams->get('player') == 2 || $player == 2) {
                $mediacode = $getMedia->getAVmediacode($media->mediacode, $media);
                echo JHTML::_('content.prepare', $mediacode);
            }

            if ($itemparams->get('player') == 1 || $player == 1) {

                $embedshare = $params->get('embedshare', 'FALSE'); // Used for Embed Share replace with param
                echo "<div align='center'>";
                echo "<div id='placeholder'><a href='http://www.adobe.com/go/getflashplayer'>" . JText::_('Get flash') . "</a> " . JText::_('to see this player') . "</div>";
                echo "</div>";

                echo "<script type='text/javascript'>
							jwplayer('placeholder').setup({
								stretching: 'fill',
								flashplayer: '" . JURI::base() . "media/com_biblestudy/js/player/player.swf',
								width: " . $playerwidth . ",
								height:" . $playerheight . ",
								displayheight:'300',
								title:'" . $studytitle . "',
								author:'" . $media->teachername . "',
								date:'" . $media->studydate . "',
								description:'" . $studyintro . "',
								controlbar:'bottom',
                                                                file: '" . $path1 . "',
								link:'" . JURI::base() . "index.php?option=com_biblestudy&view=studieslist&templatemenuid=" . $templateid . "',
								image: '" . JURI::base() . $params->get('popupimage', 'media/com_biblestudy/images/speaker24.png') . "',
								autostart:'false',
								lightcolor: '" . $lightcolor . "',frontcolor:'" . $frontcolor . "',backcolor:'" . $backcolor . "',screencolor:'" . $screencolor . "',
								'plugins': {
                                                                    'viral-2': {'onpause':'" . $embedshare . "','oncomplete':'" . $embedshare . "','allowmenu':'" . $embedshare . "'},
								},
								'modes': [
                                                                        {type: 'html5'},
                                                                        {type: 'flash', src: '" . JURI::base() . "media/com_biblestudy/player/player.swf'},
                                                                        {type: 'download'}
								]

							})
						</script>";

                //  Flashvar - Colors, Autostart, Title, Author, Date, Description, Link, Image
                //    Params - Allowfullscreen, Allowscriptaccess
                //    Attributes - ID, Name
            }

            //TODO:Need to get difference between direct popup and not so can have popup use this script
            if (!$player) {
                //  echo '<div id=\'direct\'><script type=text/javascript> window.location.href=\''.$path1.'\'</script></div>';

                echo '<div class=\'direct\'><iframe src ="' . $path1 . '" width="100%" height="100%" scrolling="no" frameborder="1" marginheight="0" marginwidth="0"><p>' . JText::_('JBS_MED_BROWSER_DOESNOT_SUPPORT_IFRAMES') . '</p>
</iframe></div>';
            }



            //Legacy Player (since JBS 6.2.2)
            if ($player == 7) {
                echo '<script language="JavaScript" src="' . JURI::base() . 'media/com_biblestudy/legacyplayer/audio-player.js"></script>
		<object type="application/x-shockwave-flash" data="' . JURI::base() . 'media/com_biblestudy/legacyplayer/player.swf" id="audioplayer' . $media->id . '" height="24" width="' . $playerwidth . '">
		<param name="movie" value="' . JURI::base() . 'media/com_biblestudy/legacyplayer/player.swf">
		<param name="FlashVars" value="playerID=' . $media->id . '&amp;soundFile=' . $path1 . '">
		<param name="quality" value="high">
		<param name="menu" value="false">
		<param name="wmode" value="transparent">
		</object> ';
            }
            if ($player == 8) {
                echo $media->mediacode;
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
    }

//end of display function

    function titles($text, $media, $scripture, $date, $length) {
        if (isset($media->teachername)) {
            $text = str_replace('{{teacher}}', $media->teachername, $text);
        }
        if (isset($date)) {
            $text = str_replace('{{studydate}}', $date, $text);
        }
        if (isset($media->filename)) {
            $text = str_replace('{{filename}}', $media->filename, $text);
        }
        if (isset($media->studyintro)) {
            $text = str_replace('{{description}}', $media->studyintro, $text);
        }
        if (isset($length)) {
            $text = str_replace('{{length}}', $length, $text);
        }
        if (isset($media->studytitle)) {
            $text = str_replace('{{title}}', $media->studytitle, $text);
        }
        if (isset($scripture)) {
            $text = str_replace('{{scripture}}', $scripture, $text);
        }
        return $text;
    }

}

//end of class
