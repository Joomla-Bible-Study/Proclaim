<?php defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript" language="JavaScript">
function HideContent(d) {
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
document.getElementById(d).style.display = "block";
}
function ReverseDisplay(d) {
if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
else { document.getElementById(d).style.display = "none"; }
}
</script>


<?php
global $mainframe, $option;
JHTML::_('behavior.tooltip');
//$params = $mainframe->getPageParameters();
$params = $this->params;
$admin_params = $this->admin_params;

//dump ($params);
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$row = $this->studydetails;
$listingcall = JView::loadHelper('listing');
//dump ($row, 'row: ');
?>
 <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
    <?php
   
    $details = getStudyExp($row, $params, $admin_params, $this->template);
		echo $details;
    ?>

<!--
<?php if ($params->get('show_comments') > 0)
		{?>
        <table id="commentstable" cellspacing="0">
		<tr><td>
<?php $Itemid = JRequest::getVar('Itemid',1);
		$comments_call = JView::loadHelper('comments');
        $comments = getComments($params, $row, $Itemid);
		echo $comments;
		?>
	</td></tr>
		</table>
<?php } //end of if comments param?>
-->
	<div class="listingfooter"><br />
    <?php $link_text = $this->params->get('link_text');
			if (!$link_text) {
				$link_text = JText::_('Return to Studies List');
			}
			if ($this->params->get('view_link') > 0){
					//$returnmenu = $params->get('templatemenuid');
					$templatemenuid = $params->get('studieslisttemplateid');
					if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
					//$returnmenu = JRequest::getVar('templatemenuid', 'get', 'int');
					if (!isset($returnmenu)) {$returnmenu = 1;}
					//dump ($returnmenu, 'returnmenu: ');
			$Itemid = JRequest::getVar('Itemid','','get');
			if (!$Itemid)
				{
					$itemid_call = JView::loadHelper('helper');
					$addItemid = getItemidLink($isplugin=0, $admin_params);
		 	$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&templatemenuid='.$templatemenuid).'&Itemid='.$addItemid;}
			 else
			 {
			 $link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&templatemenuid='.$templatemenuid);	
			 }?>
			<a href="<?php echo $link;?>"> <?php echo $link_text; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->

</div><!--End of page container div-->