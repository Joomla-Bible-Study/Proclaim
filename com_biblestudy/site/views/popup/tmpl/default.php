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

