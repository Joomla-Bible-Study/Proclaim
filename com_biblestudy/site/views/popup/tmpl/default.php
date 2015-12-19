<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
$style = 'body { background-color:' . $this->params->get('popupbackground', 'black') . ' !important;}
	#all{background-color:' . $this->params->get('popupbackground', 'black') . ' !important;}';
$doc = JFactory::getDocument();
$doc->addStyleDeclaration($style);
?>
<div id="popupwindow" class="popupwindow">
<<<<<<< HEAD
	<div class="popuptitle"><p class="popuptitle"><?php echo $this->headertext ?>
		</p>
	</div>
=======
    <body style="background-color:<?php echo $this->params->get('popupbackground', 'black') ?>">
        <div class="popuptitle"><p class="popuptitle"><?php echo $this->headertext ?>
        </p>
        </div>
<?php
// Here is where we choose whether to use the Internal Viewer or All Videos
if ($this->params->get('player') == 3 || $this->player == 3 || $this->params->get('player') == 2 || $this->player == 2)
{
	$mediacode = $jbsMedia->getAVmediacode($this->media->mediacode, $this->media);
	echo JHTML::_('content.prepare', $mediacode);
}

if ($this->params->get('player') == 1 || $this->player == 1)
{
	?>
<?php echo JHtml::script(JURI::base() . 'media/com_biblestudy/player/key.js'); ?>
<div class='playeralign' style="margin-left: auto; margin-right: auto; width:<?php echo $this->playerwidth + 1; ?>px;">
    <div id='placeholder'>
        <a href='//www.adobe.com/go/getflashplayer'><?php echo JText::_('Get flash') ?></a> <?php echo JText::_('to see this player') ?>
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

>>>>>>> Joomla-Bible-Study/master
	<?php
	// Here is where we choose whether to use the Internal Viewer or All Videos
	if ($this->params->get('player') == 3 || $this->player == 3 || $this->params->get('player') == 2 || $this->player == 2)
	{
		$mediacode = $this->getMedia->getAVmediacode($this->media->mediacode, $this->media);
		echo JHtml::_('content.prepare', $mediacode);
	}
	// Legacy Player (since JBS 6.2.2) is now deprecated and will be rendered with JWPlayer.
	if ($this->params->get('player') == 1 || $this->player == 1 || $this->player == 7)
	{
		$player = ($this->player == '7' ? true : false);
		JHtml::_('jwplayer.framework');
		echo JHtml::_('jwplayer.render', $this, $this->media->id, $this->params, true, $player);
	}

	if ($this->player == 8)
	{
		echo stripslashes($this->params->get('mediacode'));
	}
	if ($this->player == 0)
	{
		echo '<a href="' . JRoute::_($this->path1) . '"> Link to: ' .
			$this->media->studytitle . '</a>';
	}
	?>
	<div class="popupfooter">
		<p class="popupfooter">
			<?php echo $this->footertext; ?>
		</p>
	</div>
</div>

