<?php
/**
 * Default
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
$pathh = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
include_once($pathh . 'scripture.php');
include_once($pathh . 'date.php');
include_once($pathh . 'duration.php');
//	$getMedia = new jbsMedia();
JRequest::setVar('tmpl', 'component');
$mediaid = JRequest::getInt('mediaid', '', 'get');
$templateid = JRequest::getInt('t', '1', 'get');
$close = JRequest::getInt('close', '0', 'get');
$player = JRequest::getInt('player', '1', 'get');

$document = JFactory::getDocument();

$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
//Errors when using local swfobject.js file.  IE 6 doesn't work
// If this is a direct new window then all we need to do is perform hitPlay and close this window
if ($close == 1) {
    echo JHTML::_('content.prepare', '<script language="javascript" type="text/javascript">window.close();</script>');
}


jimport('joomla.application.component.helper');

$getMedia = new jbsMedia();
$media = $getMedia->getMediaRows2($mediaid);
$db = JFactory::getDBO();
$query = 'SELECT * FROM #__bsms_templates WHERE id = ' . $templateid;
$db->setQuery($query);
//$db->query();
$template = $db->loadObject();

// Convert parameter fields to objects.
$registry = new JRegistry;
$registry->loadJSON($template->params);
$params = $registry;

$css = $params->get('css', 'biblestudy.css');
if ($css != '-1'):
    $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
endif;
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
$getMedia->hitPlay($mediaid);
$length = getDuration($params, $media);
$badchars = array("'", '"');
$studytitle = str_replace($badchars, ' ', $media->studytitle);
$studyintro = str_replace($badchars, ' ', $media->studyintro);
$images = new jbsImages();
$seriesimage = $images->getSeriesThumbnail($media->series_thumbnail);
$this->series_thumbnail = '<img src="' . JURI::base() . $seriesimage->path . '" width="' . $seriesimage->width . '" height="' . $seriesimage->height . '" alt="' . $media->series_text . '" />';
$image = $images->getTeacherThumbnail($media->teacher_thumbnail, $media->thumb);
$this->teacherimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $media->teachername . '" />';
$path1 = $media->spath . $media->fpath . $media->filename;
if (preg_match('@^(?:http://)?([^/]+)@i', $path1)) {
    $path1 = 'http://' . $path1;
}
$playerwidth = $params->get('player_width');
$playerheight = $params->get('player_height');
if ($itemparams->get('playerheight') < 55 && $itemparams->get('playerheight')) {
    $playerheight = 55;
} elseif ($itemparams->get('playerheight')) {
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
if ($params->get('autostart', 1) == 1) {
    $autostart = 'true';
} else {
    $autostart = 'false';
}
if ($itemparams->get('autostart') == 1) {
    $autostart = 'true';
} elseif ($itemparams->get('autostart') == 2) {
    $autostart = 'false';
}
?>
<div class="popupwindow">
    <?php
    $headertext = '';
    $footertext = '';

    // Need to add in template
    ?><body style="background-color:<?php echo $params->get('popupbackground', 'black') ?>">
    <?php
    $headertext = $this->titles($params->get('popuptitle'), $media, $scripture, $date, $length);

    if ($itemparams->get('itempopuptitle')) {
        $headertext = $this->titles($itemparams->get('itempopuptitle'), $media, $scripture, $date, $length);
    }
    $footertext = $this->titles($params->get('popupfooter'), $media, $scripture, $date, $length);
    if ($itemparams->get('itempopupfooter')) {
        $footertext = $this->titles($itemparams->get('itempopupfooter'), $media, $scripture, $date, $length);
    }
    ?>
        <div class="popuptitle"><p class="popuptitle"><?php echo $headertext ?>
            </p>
        </div>
        <?php
        //Here is where we choose whether to use the Internal Viewer or All Videos
        if ($itemparams->get('player') == 3 || $player == 3 || $itemparams->get('player') == 2 || $player == 2) {
            $mediacode = $getMedia->getAVmediacode($media->mediacode, $media);
            echo JHTML::_('content.prepare', $mediacode);
        }

        if ($itemparams->get('player') == 1 || $player == 1) {
            ?>

            <div class='playeralign' style="margin-left: auto; margin-right: auto; width:<?php echo $playerwidth + 1; ?>px" >
                <video height="<?php echo $playerheight; ?>"
                       poster="<?php echo $params->get('popupimage', 'media/com_biblestudy/images/speaker24.png') ?>"
                       width="<?php echo $playerwidth; ?>" id='placeholder'><source src='<?php echo $path1; ?>' style="padding: 10px">
                    <a href='http://www.adobe.com/go/getflashplayer'><?php echo JText::_('Get flash') ?></a> <?php echo JText::_('to see this player') ?></video>
            </div>
            <script language="javascript" type="text/javascript">
                jwplayer('placeholder').setup({
                    flashplayer: '<?php echo JURI::base() ?>media/com_biblestudy/player/player.swf',
                    autostart:'<?php echo $autostart ?>'
                });
            </script>

            <?php
            //  Flashvar - Colors, Autostart, Title, Author, Date, Description, Link, Image
            //    Params - Allowfullscreen, Allowscriptaccess
            //    Attributes - ID, Name
        }

        //TODO:Need to get difference between direct popup and not so can have popup use this script
        if (!$player) {
            ?>
            <div class=\'direct\'>
                <iframe src ="<?php echo $path1; ?>" width="100%" height="100%" scrolling="no" frameborder="1" marginheight="0" marginwidth="0">
                <p>
                    <?php JText::_('JBS_MED_BROWSER_DOESNOT_SUPPORT_IFRAMES') ?>
                </p>

                </iframe>
            </div>
            <?php
        }

        //Legacy Player (since JBS 6.2.2)
        if ($player == 7) {
            ?>
            <script language="javascript" type="text/javascript" src="<?php echo JURI::base() ?>media/com_biblestudy/legacyplayer/audio-player.js"></script>
            <object type="application/x-shockwave-flash" data="<?php echo JURI::base() ?>media/com_biblestudy/legacyplayer/player.swf" id="audioplayer<?php echo $media->id ?>" height="24" width="<?php echo $playerwidth ?>">
                <param name="movie" value="<?php echo JURI::base() ?>media/com_biblestudy/legacyplayer/player.swf" />
                <param name="FlashVars" value="playerID=<?php echo $media->id ?>&amp;soundFile=<?php echo $path1 ?>" />
                <param name="quality" value="high" />
                <param name="menu" value="false" />
                <param name="wmode" value="transparent" />
            </object>
            <?php
        }
        if ($player == 8) {
            echo $media->mediacode;
        }
        ?>
        <?php // Footer     ?>
</div>
<div class="popupfooter">
    <p class="popupfooter">
        <?php echo $footertext; ?>
    </p>
</div>

