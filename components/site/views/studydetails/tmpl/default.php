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
$type = 'text/css';
$css_call = JView::loadHelper('css');
$styles = getCss($params);
$document->addStyleDeclaration($styles, $type);
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'biblestudyviews.css');
$pageclass_sfx = $params->get('pageclass_sfx');
$row = $this->studydetails;
?>
<div class="detailspagecontainer<?php echo $pageclass_sfx;?>"> <!-- This div is the container for the whole page -->

<div id="detailstitlecontainer<?php echo $pageclass_sfx;?>">		
<div class="buttonheading<?php echo $pageclass_sfx;?>">

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
<?php
	if ($params->get('show_teacher_view') > 0)
	{	?>        
<div class="detailsteacher<?php echo $pageclass_sfx;?>">
    
    <?php	
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($params, $row->teacher_id);
	if ($teacher) {echo $teacher;}
	?>
</div><!--end of detailsteacher div-->
<?php } ?>
<?php if ($params->get('title_line_1') + $params->get('title_line_2') > 0) 
	{
		$title_call = JView::loadHelper('title');
		$title = getTitle($params, $row);
		echo $title;
	}?> 
</div><!--end of titlecontainer div-->
<?php if ($params->get('use_headers') >0) { ?>
	
	<div class="detailsheadercontainer<?php echo $pageclass_sfx;?>" >
	<?php 
    $header_call = JView::loadHelper('header');
    $header = getHeader($this->params);
    echo $header;
	echo '</div>';
	}?>
    

    
   <?php  
    $listarraycall = JView::loadHelper('listarray');
	$a = getListarray($params, $row);
		
		  //This calls the helper once that will process each column's array, coming from the $a variable. We will then call a function in each column from this helper file
		  $array_call = JView::loadHelper('columnarray');
		echo '<div class="detailslistingcontainer'.$pageclass_sfx.'">';
        
		$columnnumber = 1;
		$column1 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column1) { echo '<div class="column'.$columnnumber.$pageclass_sfx.'">';}
		echo $column1; 
		
		if ($column1) {echo '</div>';}
		$columnnumber = 2;
		$column2 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column2) { echo '<div class="column'.$columnnumber.$pageclass_sfx.'">';}
		echo $column2; 
		
		if ($column2) {echo '</div>';}
        $columnnumber = 3;
		$column3 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column3) { echo '<div class="column'.$columnnumber.$pageclass_sfx.'">';}
       	echo $column3; 
		
		if ($column3) {echo '</div>';}
		$columnnumber = 4;
		$column4 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column4) { echo '<div class="column'.$columnnumber.$pageclass_sfx.'">';}
		echo $column4;
		
		if ($column4) {echo '</div>';}
		
	
		//Store section
		
		if ($params->get('show_store') > 0) {
			$store_call = JView::loadHelper('store');
			$store = getStore($params, $row->id);
			echo '<div class="detailsstore'.$pageclass_sfx.'">'.$store.'</div>';
			
		} //end store
		//show media section
		
		if ($params->get('show_media') > 0) {
				echo '<div class="detailsmediatable'.$pageclass_sfx.'">';
        		$ismodule = 0;
				$filesize_call = JView::loadHelper('filesize');
				$call_filepath = JView::loadHelper('filepath');
				$call_mediatable = JView::loadHelper('mediatable');
				$mediatable = getMediatable($params, $row);
				echo $mediatable.'</div>';
				
		}//End of bsmediatable div
		
		//column for description
		
		if ($params->get('show_description') > 0) {
	        echo '<div class="detailsbottomlisting'.$pageclass_sfx.'">'.$row->studyintro.'</div>';
			}//End of bsbottomlisting
          ?>      
        
        </div><!--end of detailslistingcontainer-->
        
		<div class="detailstext<?php echo $pageclass_sfx;?>">
        <p><?php echo $this->article->studytext;?></p>
        
         <?php if ($params->get('show_comments') > 0)
		{?>
        <div class="commentstable<?php echo $params->get('pageclass_sfx');?>">
		<?php $Itemid = JRequest::getVar('Itemid');
		$comments_call = JView::loadHelper('comments');
        $comments = getComments($params, $row, $Itemid);
		echo $comments;
		?>
               
        </div><!--end of div for comments-->
        
        <?php } //end of if comments param?>

        <?php if ($this->params->get('show_passage_view') > 0) { ?>
		<div id="passagecontainer<?php echo $pageclass_sfx;?>">
          <strong><a class="heading" href="javascript:ReverseDisplay('scripture')">>><?php echo JText::_('Show/Hide Scipture Passage');?><<</a>

        <div id="scripture" style="display:none;"></strong>
          <?php 
		  $passage_call = JView::loadHelper('passage');
          $response = getPassage($params, $row);
          echo $response;?>
        </div>

        
        </div>			
		<?php } //end of if passage?>

        </div><!--end of detailsstudytext-->
        
       

    <div class="detailsfooter<?php echo $pageclass_sfx;?>">
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