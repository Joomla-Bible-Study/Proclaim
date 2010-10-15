<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined('_JEXEC') or die('Restricted access'); ?>
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
$mainframe =& JFactory::getApplication();, $option;
JHTML::_('behavior.tooltip');
//$params = $mainframe->getPageParameters();
$params = $this->params;
$admin_params = $this->admin_params;

//dump ($params);
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
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


<?php if ($params->get('show_comments') < 2)
		{?>
        <div id="commentstable" >
	
<?php $Itemid = JRequest::getVar('Itemid');
		$comments_call = JView::loadHelper('comments');
        $comments = getComments($params, $row, $Itemid);
		echo $comments;
		?>
	
		</div>
<?php } //end of if comments param?>
<?php if ($this->params->get('show_passage_view') > 0) { ?>
		
          <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>><?php echo JText::_('Show/Hide Scripture Passage');?><<</a>

        <div id="scripture" style="display:none;"></strong>
          <?php 
		  $passage_call = JView::loadHelper('passage');
          $response = getPassage($params, $row);
          echo $response;?>
        </div>
<?php } //end of if passage?>
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
					$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&templatemenuid='.$templatemenuid);?>
			<a href="<?php echo $link;?>"> <?php echo $link_text; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->

</div><!--End of page container div-->


