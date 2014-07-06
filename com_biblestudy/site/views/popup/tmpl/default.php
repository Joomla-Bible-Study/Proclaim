<?php
/**
 * Default
 *
 * @package        BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
$style = 'body { background-color:' . $this->params->get('popupbackground', 'black') . ' !important;}
	#all{background-color:' . $this->params->get('popupbackground', 'black') . ' !important;}';
$doc = JFactory::getDocument();
$doc->addStyleDeclaration($style);
?>
<div id="popupwindow" class="popupwindow">
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
		JHTML::_('jwplayer.framework');
		echo JHtml::_('jwplayer.render', $this, $this->media->id, $this->params->toObject(), true);
	}

	// Legacy Player (since JBS 6.2.2)
	if ($this->player == 7)
	{
		$doc->addScriptDeclaration(JURI::base() . "media/com_biblestudy/legacyplayer/audio-player.js");
		?>
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
		echo stripslashes($this->media->mediacode);
	}
	?>
	<div class="popupfooter">
		<p class="popupfooter">
			<?php echo $this->footertext; ?>
		</p>
	</div>
</div>

