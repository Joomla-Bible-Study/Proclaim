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
    $imageh = $this->params->get('imageh', 24);
    $imagew = $this->params->get('imagew', 24);
    $color1 = $this->params->get('color1');
    $color2 = $this->params->get('color2');
    $page_width = $this->params->get('page_width', '100%');
    $widpos1 = $this->params->get('widthcol1');
    $widpos2 = $this->params->get('widthcol2');
    $widpos3 = $this->params->get('widthcol3');
    $widpos4 = $this->params->get('widthcol4');
    $show_description = $this->params->get('show_description', 1);
    $downloadCompatibility = $this->params->get('compatibilityMode');
	$params = $mainframe->getPageParameters();
//to do: write an external function to create the css
$document =& JFactory::getDocument();
$type = 'text/css';
$css_call = JView::loadHelper('css');
$styles = getCss($params);
$document->addStyleDeclaration($styles, $type);
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$suffix = $params->get('suffix');

    ?>
<div class="bspagecontainer<?php echo $suffix;?>" > <!-- This div is the container for the whole page -->
<div class="bspageheader<?php echo $suffix;?>">
	
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
    if ( $this->params->get( 'show_page_title_list' ) >0 ) {
    ?>
      <span class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="line-height:<?php echo $height; ?>px;"><?php echo $this->params->get('page_title');?></span>
    <?php
    }?>

</div><!--end of div for pageheader-->

<div class="bsteacher<?php echo $suffix;?>">
    <img
    src="<?php echo $this->params->get('teacherimage');?>" border="1"
    width="<?php echo $this->params->get('teacherw');?>"
    height="<?php echo $this->params->get('teacherh');?>" /><br />
	<?php echo $this->params->get('teachername');?>
</div><!--end of bsteacher div-->

<div class="bsdropdownmenu<?php echo $suffix;?>" >

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
<div class="headercontainer<?php echo $suffix;?>"><!--this is the container for all the headers, so that we can add a line underneath and have it go the while width-->
<div class="bslistheader<?php echo $suffix;?>" >
	<?php 
    $header_call = JView::loadHelper('header');
    $header = getHeader($this->params);
    echo $header;
   ?>
</div><!--end of bslistheader div-->
</div><!--end of headercontainer div-->
<div class="bslistings<?php echo $suffix;?>" >

	<?php // This is the count for the listing table items
     $k = 1;
     $row_count = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
  	{ // This is the beginning of a loop that will cycle through all the records according to the query
		$bgcolor = ($row_count % 2) ? $color1 : $color2; //This code cycles through the two color choices made in the parameters
		$row = &$this->items[$i];
		$id4 = $row->id;
		$filesizefield = '#__bsms_mediafiles.study_id';
		$filesize_call = JView::loadHelper('filesize');
		$file_size = getFilesize($id4, $filesizefield);
	 
		  $show_media = $this->params->get('show_media',1);
		  $filesize_showm = $this->params->get('filesize_showm');
		  $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails&id=' . $row->id);
		  $duration = $row->media_hours.$row->media_minutes.$row->media_seconds;
		  if (!$duration) { $duration = '';}
		  else {
			  $duration_type = $this->params->get('duration_type');
			  $hours = $row->media_hours;
			  $minutes = $row->media_minutes;
			  $seconds = $row->media_seconds;
			  $duration_call = JView::loadHelper('duration');
			  $duration = getDuration($duration_type, $hours, $minutes, $seconds);
		  }
		  $booknumber = $row->booknumber;
		  $ch_b = $row->chapter_begin;
		  $ch_e = $row->chapter_end;
		  $v_b = $row->verse_begin;
		  $v_e = $row->verse_end;
		  $id2 = $row->id;
		  $show_verses = $this->params->get('show_verses');
		  $scripture1 = format_scripture2($id2, $esv, $booknumber, $ch_b, $ch_e, $v_b, $v_e, $show_verses);
		  if ($row->booknumber2){
		   $booknumber = $row->booknumber2;
		   $ch_b = $row->chapter_begin2;
		   $ch_e = $row->chapter_end2;
		   $v_b = $row->verse_begin2;
		   $v_e = $row->verse_end2;
		   $id2 = $row->id;
		  $scripture2 = format_scripture2($id2, $esv, $booknumber, $ch_b, $ch_e, $v_b, $v_e, $show_verses);
		  }
		  $df =  ($this->params->get('date_format'));
		  $date_call = JView::loadHelper('date');
		  $date = getstudyDate($df, $row->studydate);	
		
		  $textwidth=$this->params->get('imagew');
		  $textwidth = ($textwidth + 1);
		  $storewidth = $this->params->get('storewidth');
		  $teacher = $row->teachername;
		  $study = $row->studytitle;
		  $sname = $row->series_text;
		  $intro = str_replace('"','',$row->studyintro);
		  $mtype = $row->message_type;
		  $snumber = $row->studynumber;
		  $details_text = $this->params->get('details_text');
		  $filesize_show = $this->params->get('filesize_show');
		  $secondary = $row->secondary_reference;
		  if (!$row->booknumber2){$scripture2 = '';}
		  
		  $listarraycall = JView::loadHelper('listarray');
		  $a = getListarray($params, $row, $scripture1, $scripture2, $date, $file_size, $duration);
		
		  //This calls the helper once that will process each column's array, coming from the $a variable. We will then call a function in each column from this helper file
		  $array_call = JView::loadHelper('columnarray');
		  if ($this->params->get('use_color') > 0) {
			  echo '<div class="bslistingcontainer'.$suffix.'" style="background-color: '.$bgcolor.';">';}
			  else { echo '<div class="bslistingcontainer'.$suffix.'">';}
        
		$columnnumber = 1;
		$column1 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column1) { echo '<div class="column'.$columnnumber.$suffix.'">';}
		echo $column1; 
		
		if ($column1) {echo '</div>';}
		$columnnumber = 2;
		$column2 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column2) { echo '<div class="column'.$columnnumber.$suffix.'">';}
		echo $column2; 
		
		if ($column2) {echo '</div>';}
        $columnnumber = 3;
		$column3 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column3) { echo '<div class="column'.$columnnumber.$suffix.'">';}
       	echo $column3; 
		
		if ($column3) {echo '</div>';}
		$columnnumber = 4;
		$column4 = getColumnarray($a, $row, $columnnumber, $this->params);
		if ($column4) { echo '<div class="column'.$columnnumber.$suffix.'">';}
		echo $column4;
		
		if ($column4) {echo '</div>';}
		
		//Collects the details links
		if ($params->get('show_full_text') > 0) {
				$textorpdf = 'text';
				$textlink_call = JView::loadHelper('textlink');
				$textlink = getTextlink($params, $row, $scripture1, $textorpdf);
				echo '<div class="bstext'.$suffix.' zoomtip">'.$textlink.'</div>';
				
		}
		
		if 	($params->get('show_pdf_text') > 0) {
				$textorpdf = 'pdf';
				$textlink_call = JView::loadHelper('textlink');
				$textlink = getTextlink($params, $row, $scripture1, $textorpdf);
				echo '<div class="bstext'.$suffix.' zoomtip">'.$textlink.'</div>';
				
		}	//end details links
		
		//Store section
		
		if ($params->get('show_store') > 0) {
			$store_call = JView::loadHelper('store');
			$store = getStore($params, $row->id);
			echo '<div class="bsstore'.$suffix.'">'.$store.'</div>';
			
		} //end store
		//show media section
		
		if ($params->get('show_media') > 0) {
				echo '<div class="bsmediatable'.$suffix.'">';
        		$ismodule = 0;
				$params = $this->params;
				$filesize_call = JView::loadHelper('filesize');
				$call_filepath = JView::loadHelper('filepath');
				$call_mediatable = JView::loadHelper('mediatable');
				$mediatable = getMediatable($params, $row->id, $ismodule, $duration);
				echo $mediatable.'</div>';
				
		}//End of bsmediatable div
		
		//column for description
		
		if ($params->get('show_description') > 0) {
	        echo '<div class="bsbottomlisting'.$suffix.'">'.$row->studyintro.'</div>';
			}//End of bsbottomlisting
        if ($params->get('line_break') > 0) { echo '<br />';}  ?>      
        
        </div><!--end of bslistingcontainer-->
		
<?php			
 //end of list - row count increment goes next
	$row_count++; // This increments the row count and adjusts the variable for the color background
  	$k = 3 - $k;
	
  } //This is the end of the for statement for each result from the database that will create its own 6 column table

 ?>
	
		
	
<div class="bsfooter<?php echo $suffix;?>" style="clear: both; position: relative; padding: 10px 10px; width: 100%; ">
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