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
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$row = $this->studydetails;
$listingcall = JView::loadHelper('listing');
?>
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
 <div id="header">
 
<div class="buttonheading">

	<?php 
	if ($this->params->get('show_print_view') > 0) 
	{
		$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ) );
        echo '<a href="#&tmpl=component" onclick="window.print();return false;">'.$text.'</a>';
	}
	if ($this->params->get('show_pdf_view') > 0 ) 
    { 
        $url = 'index.php?option=com_biblestudy&view=studydetails&id='.$this->studydetails->id.'&format=pdf';
        $status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
        $text = JHTML::_('image.site', 'pdf24.png', '/components/com_biblestudy/images/', NULL, NULL, JText::_('PDF'), JText::_('PDF'));
        $attribs['title']	= JText::_( 'PDF' );
        $attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
        $attribs['rel']     = 'nofollow';
        $link = JHTML::_('link', JRoute::_($url), $text, $attribs);
        echo $link; 
    } ?>

</div>

 <?php if ($params->get('title_line_1') + $params->get('title_line_2') > 0) 
	{
		$title_call = JView::loadHelper('title');
		$title = getTitle($params, $row);
		echo $title;
	}?> 

<?php if ($params->get('show_teacher_view') > 0)
	{	?>        
    
    <?php	
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($params, $row->teacher_id, &$this->admin_params);
	echo $teacher;
	}?>
   </div><!-- header -->
 
 <table id="bsmsdetailstable" cellspacing="0">
     <?php //dump ($params->get('use_headers_view'), 'headers: ');
if ($params->get('use_headers_view') > 0)
	{	
     $headerCall = JView::loadHelper('header');
     $header = getHeader($row, $params);
     echo $header;
	}	?>
    <tbody>

        <?php 
if ($params->get('list_items_view') == 1)
		{
			$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
			include_once($path1.'mediatable.php');
			$listing = getMediatable($params, $row, &$this->admin_params);	
			echo $listing;
		}
if ($params->get('list_items_view') == 0)
		{
			$oddeven = 'bsodd';
 			$listing = getListing($row, $params, $oddeven, &$this->admin_params);
 			echo $listing;
		}?>
 </tbody></table>
 <table id="bsmsdetailstable" cellspacing="0">
 <tr><td id="studydetailstext">
 <?php echo $this->article->studytext;?>
 </td></tr></table>
 
<?php if ($params->get('show_comments') > 0)
		{?>
        <table id="commentstable" cellspacing="0">
		<tr><td>
<?php $Itemid = JRequest::getVar('Itemid');
		$comments_call = JView::loadHelper('comments');
        $comments = getComments($params, $row, $Itemid);
		echo $comments;
		?>
	</td></tr>
		</table>
<?php } //end of if comments param?>

<?php if ($this->params->get('show_passage_view') > 0) { ?>
		
          <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>><?php echo JText::_('Show/Hide Scipture Passage');?><<</a>

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
					$templatemenuid = $params->get('studielisttemplateid');
					if (!$templatemenuid) {$templatemenuid = JRequest::getVar('templatemenuid',1,'get','int');}
					//$returnmenu = JRequest::getVar('templatemenuid', 'get', 'int');
					if (!returnmenu) {$returnmenu = 1;}
					//dump ($returnmenu, 'returnmenu: ');
					$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&templatemenuid='.$templatemenuid);?>
			<a href="<?php echo $link;?>"> <?php echo $link_text; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->

</div><!--End of page container div-->