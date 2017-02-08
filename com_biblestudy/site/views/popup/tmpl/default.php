<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
$style = 'body { background-color:' . $this->params->get('popupbackground', 'black') . ' !important; padding:0 !important;}
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
		echo JHtml::_('content.prepare', $mediacode);
	}
	// Legacy Player (since JBS 6.2.2) is now deprecated and will be rendered with JWPlayer.
	if ($this->params->get('player') == 1 || $this->player == 1 || $this->player == 7)
	{
		$player = new stdClass;
		$player->mp3 = ($this->player == '7' ? true : false);
		JHtml::_('jwplayer.framework');
		echo JHtml::_('jwplayer.render', $this->media, $this->params, true, $player);
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

