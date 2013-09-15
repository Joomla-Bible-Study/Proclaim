<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

?>
<div id="popupwindow" class="popupwindow">
	<body style="background-color:<?php echo $this->params->get('popupbackground', 'black') ?>">
	<div class="popuptitle"><p class="popuptitle"><?php echo $this->headertext ?>
		</p>
	</div>
	<?php
	// Here is where we choose whether to use the Internal Viewer or All Videos
	if ($this->params->get('player') == 3 || $this->player == 3 || $this->params->get('player') == 2 || $this->player == 2)
	{
		$mediacode = $this->getMedia->getAVmediacode($this->media->mediacode, $this->media);
		echo JHTML::_('content.prepare', $mediacode);
	}

if ($this->params->get('player') == 1 || $this->player == 1)
{
	?>

<div class='playeralign' style="margin-left: auto; margin-right: auto; width:<?php echo $this->playerwidth + 1; ?>px;">
    <div id='placeholder'>
        <a href='http://www.adobe.com/go/getflashplayer'><?php echo JText::_('Get flash') ?></a> <?php echo JText::_('to see this player') ?>
    </div>
</div>
<script language="javascript" type="text/javascript">
    jwplayer('placeholder').setup({
        'file':'<?php echo $this->path1; ?>',
        'height':'<?php echo $this->playerheight; ?>',
        'width':'<?php echo $this->playerwidth; ?>',
        'image':'<?php echo $this->params->get('popupimage', 'media/com_biblestudy/images/speaker24.png') ?>',
        'flashplayer':'<?php echo JURI::base() ?>media/com_biblestudy/player/jwplayer.flash.swf',
        'autostart':'<?php echo $this->autostart; ?>',
        'backcolor':'<?php echo $this->backcolor; ?>',
        'frontcolor':'<?php echo $this->frontcolor; ?>',
        'lightcolor':'<?php echo $this->lightcolor; ?>',
        'screencolor':'<?php echo $this->screencolor; ?>',
        'controlbar.position':'<?php echo $this->params->get('playerposition'); ?>',
        'controlbar.idlehide':'<?php echo $this->playeridlehide; ?>'
    });
</script>

	<?php
	/*  Flashvar - Colors, Autostart, Title, Author, Date, Description, Link, Image
	//    Params - Allowfullscreen, Allowscriptaccess
	//    Attributes - ID, Name */
}

	// Legacy Player (since JBS 6.2.2)
	if ($this->player == 7)
	{
		?>
		<script language="javascript" type="text/javascript"
		        src="<?php echo JURI::base() ?>media/com_biblestudy/legacyplayer/audio-player.js"></script>
		<object type="application/x-shockwave-flash"
		        data="<?php echo JURI::base() ?>media/com_biblestudy/legacyplayer/player.swf"
		        id="audioplayer<?php echo $this->media->id ?>" height="24" width="<?php echo $this->playerwidth ?>">
			<param name="movie" value="<?php echo JURI::base() ?>media/com_biblestudy/legacyplayer/player.swf"/>
			<param name="FlashVars"
			       value="playerID=<?php echo $this->media->id ?>&amp;soundFile=<?php echo $this->path1 ?>"/>
			<param name="quality" value="high"/>
			<param name="menu" value="false"/>
			<param name="wmode" value="transparent"/>
		</object>
	<?php
	}
	if ($this->player == 8)
	{
		echo $this->media->mediacode;
	}
	?>
	</body>

	<div class="popupfooter">
		<p class="popupfooter">
			<?php echo $this->footertext; ?>
		</p>
	</div>
</div>

