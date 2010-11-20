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
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
JHTML::_('behavior.tooltip');
//$params = $mainframe->getPageParameters();
$params = $this->params;
//dump ($params);
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$row = $this->studydetails;
$listingcall = JView::loadHelper('listing');
$sharecall = JView::loadHelper('share');
//dump ($row, 'row: ');
?>
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
 <div id="bsmHeader">
 
<div class="buttonheading">

	<?php 
	
	
	if ($this->params->get('show_print_view') > 0) 
	{
		$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'JBS_CMN_PRINT' ) );
        echo '<a href="#&tmpl=component" onclick="window.print();return false;">'.$text.'</a>';
	}
	if ($this->params->get('show_pdf_view') > 0 ) 
    { 
        $url = 'index.php?option=com_biblestudy&view=studydetails&id='.$this->studydetails->id.'&format=pdf';
        $status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
        $text = JHTML::_('image.site', 'pdf24.png', '/components/com_biblestudy/images/', NULL, NULL, JText::_('JBS_MED_PDF'), JText::_('JBS_MED_PDF'));
        $attribs['title']	= JText::_( 'JBS_MED_PDF' );
        $attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
        $attribs['rel']     = 'nofollow';
        $link = JHTML::_('link', JRoute::_($url), $text, $attribs);
        echo $link; 
    } 
	
	
	?>

</div>
<?php //Social Networking begins here
if ($this->admin_params->get('socialnetworking')> 0)
	{ ?>
<div id="bsms_share">
<?php
		$social = getShare($this->detailslink, $row, $params, $this->admin_params);
		echo $social;
?>
</div>
<?php } //End Social Networking ?>
<table><tr><td>
  
 
 <?php if ($params->get('show_teacher_view') > 0)
	{ ?>        
    
    <?php	
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($params, $row->teacher_id, $this->admin_params);
	echo $teacher;
	echo '</td><td>';
	}?>
	
	
	<?php
	if ($params->get('title_line_1') + $params->get('title_line_2') > 0) 
	{
		$title_call = JView::loadHelper('title');
		$title = getTitle($params, $row, $this->admin_params, $this->template);
		echo $title;
	}?> 


</td></tr></table>
   </div><!-- header -->
 
 <table id="bsmsdetailstable" cellspacing="0">
     <?php //dump ($params->get('use_headers_view'), 'headers: ');
if ($params->get('use_headers_view') > 0 || $params->get('list_items_view')< 1)
	{	
     $headerCall = JView::loadHelper('header');
     $header = getHeader($row, $params, $this->admin_params, $this->template, $showheader=$params->get('use_headers_view'), $ismodule=0);
     echo $header;
	}	?>
    <tbody>

        <?php 
if ($params->get('list_items_view') == 1)
		{
			echo '<tr class="bseven"><td class="media">';
		//	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		//	include_once($path1.'mediatable.php');
            require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
            $media = new jbsMedia();
            $listing = $media->getMediaTable($row, $params, $this->admin_params);
		//	$listing = getMediatable($params, $row, $this->admin_params);
			echo $listing;
			echo '</td></tr>';
			
		}
if ($params->get('list_items_view') == 0)
		{
			$oddeven = 'bsodd';
 			$listing = getListing($row, $params, $oddeven, $this->admin_params, $this->template, $ismodule=0);
 			echo $listing;
		}?>
 </tbody></table>
 <table id="bsmsdetailstable" cellspacing="0">
 <tr><td id="studydetailstext">
 <?php 
 if ($this->params->get('show_scripture_link') > 0)
 	{ echo $this->article->studytext;}
	else {echo $this->studydetails->studytext;}
	
	?>
	
 </td></tr></table>
<?php 

?>
<?php if ($params->get('show_comments') < 2)
		{
		  
         echo '<div id="commentstable" >';
	   
        $Itemid = JRequest::getVar('Itemid',1);
		$comments_call = JView::loadHelper('comments');
        $comments = getComments($params, $row, $Itemid);
		echo $comments;
		?>
	
		</div>
<?php } //end of if comments param?>

<?php 
        switch ($this->params->get('show_passage_view', '0'))
        {
            case 0:
                break;
            
            case 1:
                ?>
                <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>>
                <?php echo JText::_('Show/Hide Scripture Passage');?><<</a>
                <div id="scripture" style="display:none;"></strong>
                <?php 
                
                $passage_call = JView::loadHelper('passage');
                $response = getPassage($params, $row);
                echo $response;
                echo '</div>';
                break;
        
            case 2:
                echo '<div id="scripture">';
                $passage_call = JView::loadHelper('passage');
                $response = getPassage($params, $row);
                echo $response;
                echo '</div>';
                break;
        }
?>
        
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
					//$itemid_call = JView::loadHelper('helper');
					//$addItemid = getItemidLink($isplugin=0, $admin_params);
		 	$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&templatemenuid='.$templatemenuid);}
			 else
			 {
			 $link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&templatemenuid='.$templatemenuid);	
			 }?>
			<a href="<?php echo $link;?>"> <?php echo $link_text; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->

</div><!--End of page container div-->