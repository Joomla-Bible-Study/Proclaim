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
$params = $mainframe->getPageParameters();
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$row = $this->studydetails;
$listingcall = JView::loadHelper('listing');
?>
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
 <div id="header">
 <h1 class="componentheading">
 <?php echo $row->studytitle;?>
  </h1>
   </div><!-- header -->
 
 <table id="bslisttable" cellspacing="0">
     <?php 
	 if ($params->get('use_headers') > 0){
     $headerCall = JView::loadHelper('header');
     $header = getHeader($row, $params);
     echo $header;}
     ?>
      <tbody>

        <?php 
 //This sets the alternativing colors for the background of the table cells
$oddeven = 'bsodd';
 $listing = getListing($row, $params, $oddeven);
 echo $listing;?>
 </tbody></table>
 <table id="studydetailstable" cellspacing="0">
 <tr><td id="studydetailstext">
 <?php echo $this->article->studytext;?>
 </td></tr></table>
    <div class="listingfooter">
    <?php $link_text = $this->params->get('link_text');
			if (!$link_text) {
				$link_text = JText::_('Return to Studies List');
			}
			if ($this->params->get('view_link') == 0){}else{
				if ($this->params->get('view_link') == 1){
					$item = JRequest::getVar('Itemid');
					$returnmenu = $this->params->get('studieslistitemid');
					//dump ($returnmenu, 'returnmenu: ');
					if ($returnmenu) {$item = $returnmenu;}
					$link = JRoute::_('index.php?option='.$option.'&view=studieslist');}
					if ($item){
						$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&Itemid='.$item);}?>
			<a href="<?php echo $link;?>"> <?php echo $link_text; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->

</div><!--End of page container div-->