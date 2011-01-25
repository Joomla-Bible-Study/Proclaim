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
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
JHTML::_('behavior.tooltip');
//$params = $mainframe->getPageParameters();
$params = $this->params;
//print_r($params->params['show_comments']);
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
  
 
 <?php if ($this->params->get('show_teacher_view') > 0)
	{ ?>        
    
    <?php	
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($this->params, $row->teacher_id, $this->admin_params);
	echo $teacher;
	echo '</td><td>';
	}?>
	
	
	<?php
	if ($this->params->get('title_line_1') + $params->get('title_line_2') > 0) 
	{
		$title_call = JView::loadHelper('title');
		$title = getTitle($this->params, $row, $this->admin_params, $this->template);
		echo $title;
	}?> 


</td></tr></table>
   </div><!-- header -->
 
 <table id="bsmsdetailstable" cellspacing="0">
     <?php //dump ($params->get('use_headers_view'), 'headers: ');
if ($this->params->get('use_headers_view') > 0 || $this->params->get('list_items_view')< 1)
	{	
     $headerCall = JView::loadHelper('header');
     $header = getHeader($row, $this->params, $this->admin_params, $this->template, $showheader=$params->get('use_headers_view'), $ismodule=0);
     echo $header;
	}	?>
    <tbody>

        <?php 
if ($this->params->get('list_items_view') == 1)
		{
			echo '<tr class="bseven"><td class="media">';
		//	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		//	include_once($path1.'mediatable.php');
            require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.media.class.php');
            $media = new jbsMedia();
            $listing = $media->getMediaTable($row, $this->params, $this->admin_params);
		//	$listing = getMediatable($params, $row, $this->admin_params);
			echo $listing;
			echo '</td></tr>';
			
		}
if ($params->get('list_items_view') == 0)
		{
			$oddeven = 'bsodd';
 			$listing = getListing($row, $this->params, $oddeven, $this->admin_params, $this->template, $ismodule=0);
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

	


<?php 
        switch ($this->params->get('show_passage_view', '0'))
        {
            case 0:
                break;
            
            case 1:
                ?>
                <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>>
                <?php echo JText::_('JBS_CMN_SHOW_HIDE_SCRIPTURE');?><<</a>
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


</div><!--End of page container div-->