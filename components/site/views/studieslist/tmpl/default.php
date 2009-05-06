<?php
defined('_JEXEC') or die();
		
?>
<script type="text/javascript" src="components/com_biblestudy/tooltip.js"></script>

<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">


	<?php
    global $mainframe, $option;
	$message = JRequest::getVar('msg');
    $database = & JFactory::getDBO();
    $teacher_menu = $this->params->get('teacher_id', 1);
    $topic_menu = $this->params->get('topic_id', 1);
    $book_menu = $this->params->get('booknumber', 101);
    $location_menu = $this->params->get('locations', 1);
    $series_menu = $this->params->get('series_id', 1);
    $messagetype_menu = $this->params->get('messagetype', 1);
    
    $color1 = $this->params->get('color1');
    $color2 = $this->params->get('color2');
	$params = $mainframe->getPageParameters();
	
//external function to create the css
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'biblestudyviews.css');

$type = 'text/css';
$css_call = JView::loadHelper('css');
$styles = getCss($params);
$document->addStyleDeclaration($styles, $type);
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$pageclass_sfx = $params->get('pageclass_sfx');

    ?>
    
<div class="listingpagecontainer<?php echo $pageclass_sfx;?>" > <!-- This div is the container for the whole page -->

<div class="editcontainer<?php echo $pageclass_sfx;?>">
<?php $edit_call = JView::loadHelper('editlisting');
$editlisting = getEditlisting($params);
if ($editlisting) {echo $editlisting;} ?>
</div>

<div class="listingpageheader<?php echo $pageclass_sfx;?>">
	
	<?php
     if ($this->params->get( 'show_page_image' ) >0) {
     $pimagew = $this->params->get('pimagew');
     $pimageh = $this->params->get('pimageh');
     if ($pimagew) {$width = $pimagew;} else {$width = 24;}
     if ($pimageh) {$height = $pimageh;} else {$height= 24;}
     ?>
      <img src="<?php echo JURI::base().$this->params->get('page_image');?>" alt="<?php echo $this->params->get('page_title'); ?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />
    <?php //End of column for logo
    }
    ?>

</div><!--end of div for pageheader-->
<?php
if ( $this->params->get( 'show_page_title' ) >0 ) {
    echo '<div class="pageheadertext'.$this->params->get('pageclass_sfx').'">'.$this->params->get('page_title').'</div>';
    }
if ($params->get('show_teacher_list') > 0)
	{	?>
<div class="listingteacher<?php echo $pageclass_sfx;?>">
    <?php	
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($params, $id);
	if ($teacher) {echo $teacher;}
	}
	?>
</div><!--end of bsteacher div-->

<div class="listingdropdownmenu<?php echo $pageclass_sfx;?>" >

  <?php if ($this->params->get('show_locations_search') > 0 && !($location_menu)) { echo $this->lists['locations'];}?>
  <?php if ($this->params->get('show_book_search') >0 && !($book_menu) ){ ?>

  <?php $query2 = 'SELECT id, booknumber AS value, bookname AS text, published'
  . ' FROM #__bsms_books'
  . ' WHERE published = 1'
  . ' ORDER BY booknumber';
  $database->setQuery( $query2 );
  $bookid = $database->loadAssocList();
  $filter_book  = $mainframe->getUserStateFromRequest( $option.'filter_book', 'filter_book',0,'int' );
  echo '<select name="filter_book" id="filter_book" class="inputbox" size="1" onchange="this.form.submit()"><option value="0"';
  if (!$filter_book ) {
   echo 'selected="selected"';}
   echo '>- '.JText::_('Select a Book').' -'.'</option>';
   foreach ($bookid as $bookid2) {
    $format = $bookid2['text'];
    $output = JText::_($format);
    $bookvalue = $bookid2['value'];
    if ($bookvalue == $filter_book){
     $selected = 'selected="selected"';
     echo '<option value="'.$bookvalue.'"'.$selected.' >'.$bookid2['text'].'</option>';
    } else {
     echo '<option value="'.$bookvalue.'">'.$output.'</option>';
    }
   };
   echo '</select>';?> <?php } ?> <?php if ($this->params->get('show_teacher_search') >0 && !($teacher_menu)) { ?>
   <?php echo $this->lists['teacher_id'];?> <?php } ?> <?php if ($this->params->get('show_series_search') >0 && !($series_menu)){ ?>
   <?php echo $this->lists['seriesid'];?> <?php } ?> <?php if ($this->params->get('show_type_search') >0 && !($messagetype_menu)) { ?>
   <?php echo $this->lists['messagetypeid'];?> <?php } ?> <?php if ($this->params->get('show_year_search') >0){ ?>
   <?php echo $this->lists['studyyear'];?> <?php } ?> <?php if ($this->params->get('show_order_search') >0) { ?>
   <?php
   $query6 = ' SELECT * FROM #__bsms_order '
   . ' ORDER BY id ';
   $database->setQuery( $query6 );
   $sortorder = $database->loadAssocList();
   $filter_orders  = $mainframe->getUserStateFromRequest( $option.'filter_orders','filter_orders','DESC','word' );
   echo '<select name="filter_orders" id="filter_orders" class="inputbox" size="1" onchange="this.form.submit()"><option value="0"';
   if (!$filter_orders ) {
    echo 'selected="selected"';}
    echo '>- '.JText::_('Select an Order').' -'.'</option>';
    foreach ($sortorder as $sortorder2) {
     $format = $sortorder2['text'];
     $output = JText::sprintf($format);
     $sortvalue = $sortorder2['value'];
     if ($sortvalue == $filter_orders){
      $selected = 'selected="selected"';
      echo '<option value="'.$sortvalue.'"'.$selected.' >'.$output.'</option>';
     } else {
      echo '<option value="'.$sortvalue.'">'.$output.'</option>';
     }
    };
    echo '</select>';?> <?php //echo $this->lists['sorting'];?> <?php } ?>
    <?php if ($this->params->get('show_topic_search') >0) { ?> <?php
    $query8 = 'SELECT DISTINCT #__bsms_studies.topics_id AS value, #__bsms_topics.topic_text AS text'
    . ' FROM #__bsms_studies'
    . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studies.topics_id)'
    . ' WHERE #__bsms_topics.published = 1'
    . ' ORDER BY #__bsms_topics.topic_text ASC';
    $database->setQuery( $query8 );
    $topicsid = $database->loadAssocList();
    $filter_topic  = $mainframe->getUserStateFromRequest( $option.'filter_topic', 'filter_topic',0,'int' );
    echo '<select name="filter_topic" id="filter_topic" class="inputbox" size="1" onchange="this.form.submit()"><option value="0"';
    if (!$filter_topic ) {
     echo 'selected="selected"';}
     echo '>- '.JText::_('Select a Topic').' -'.'</option>';
     foreach ($topicsid as $topicsid2) {
      $format = $topicsid2['text'];
      $output = JText::sprintf($format);
      $topicsvalue = $topicsid2['value'];
      if ($topicsvalue == $filter_topic){
       $selected = 'selected="selected"';
       echo '<option value="'.$topicsvalue.'"'.$selected.' >'.$output.'</option>';
      } else {
      echo '<option value="'.$topicsvalue.'">'.$output.'</option>';}

     };
     echo '</select>';?> <?php //echo $this->lists['topics'];?> <?php } ?>
	 <?php //End of row for drop down boxes?>
 
</div><!--end of bsdropdownmenu div-->
<div class="headercontainer<?php echo $pageclass_sfx;?>"><!--this is the container for all the headers, so that we can add a line underneath and have it go the while width-->
<?php if ($params->get('use_headers') >0) { ?>

	<?php 
    $header_call = JView::loadHelper('header');
    $header = getHeader($this->params);
    echo $header;
   ?>

<?php } ?>
</div><!--end of headercontainer div-->
<div class="listinglistings<?php echo $pageclass_sfx;?>" >

	<?php // This is the count for the listing table items
     $k = 1;
     $row_count = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
  	{ // This is the beginning of a loop that will cycle through all the records according to the query
		$bgcolor = ($row_count % 2) ? $color1 : $color2; //This code cycles through the two color choices made in the parameters
		$row = &$this->items[$i];
		$id4 = $row->id;
		
				  
		  $listarraycall = JView::loadHelper('listarray');
		  $a = getListarray($params, $row);
		
		  //This calls the helper once that will process each column's array, coming from the $a variable. We will then call a function in each column from this helper file
		  $array_call = JView::loadHelper('columnarray');
		  if ($this->params->get('use_color') > 0) {
			  echo '<div class="bslistingcontainer'.$pageclass_sfx.'" style="background-color: '.$bgcolor.';">';}
			  else { echo '<div class="bslistingcontainer'.$pageclass_sfx.'">';}
        
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
		
		//Collects the details links
		if ($params->get('show_full_text') > 0) {
				$textorpdf = 'text';
				$textlink_call = JView::loadHelper('textlink');
				$textlink = getTextlink($params, $row, $textorpdf);
				echo '<div class="listingtext'.$pageclass_sfx.'">'.$textlink.'</div>';
				
		}
		
		if 	($params->get('show_pdf_text') > 0) {
				$textorpdf = 'pdf';
				$textlink_call = JView::loadHelper('textlink');
				$textlink = getTextlink($params, $row, $textorpdf);
				echo '<div class="listingtext'.$pageclass_sfx.'">'.$textlink.'</div>';
				
		}	//end details links
		
		//Store section
		
		if ($params->get('show_store') > 0) {
			$store_call = JView::loadHelper('store');
			$store = getStore($params, $row->id);
			echo '<div class="listingstore'.$pageclass_sfx.'">'.$store.'</div>';
			
		} //end store
		//show media section
		
		if ($params->get('show_media') > 0) {
				echo '<div class="listingmediatable'.$pageclass_sfx.'">';
        		$ismodule = 0;
				$params = $this->params;
				$filesize_call = JView::loadHelper('filesize');
				$call_filepath = JView::loadHelper('filepath');
				$call_mediatable = JView::loadHelper('mediatable');
				$mediatable = getMediatable($params, $row);
				echo $mediatable.'</div>';
				
		}//End of bsmediatable div
		
		//column for description
		
		if ($params->get('show_description') > 0) {
	        echo '<div class="listingbottomlisting'.$pageclass_sfx.'">'.$row->studyintro.'</div>';
			}//End of bsbottomlisting
          ?>      
        
        </div><!--end of bslistingcontainer-->
		
<?php			
 //end of list - row count increment goes next
	$row_count++; // This increments the row count and adjusts the variable for the color background
  	$k = 3 - $k;
	
  } //This is the end of the for statement for each result from the database that will create its own 6 column table

 ?>
	
		
	
<div class="listingfooter<?php echo $pageclass_sfx;?>" >
	<?php 
      //echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
      //echo $this->pagination->getLimitBox();
      //echo $this->pagination->getPagesLinks();
      //echo $this->pagination->getPagesCounter();
      echo $this->pagination->getListFooter(); ?>
</div> <!--end of bsfooter div-->
</div><!--end of bslisting div-->
    


</div><!--end of page container div-->
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="studieslist" />
</form>