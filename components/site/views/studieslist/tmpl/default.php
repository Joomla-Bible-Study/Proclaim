<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>
<script type="text/javascript" src="components/com_biblestudy/tooltip.js"></script>
<style>
#bsdropdownmenu {
  margin-bottom: 10px;
}
#bslisttable {
  margin: 0;
  border-collapse:separate;
}
#bslisttable th, #bslisttable td {
  text-align:left;
  padding:0 5px 0 5px;
  border:none;
}
#bslisttable .row1col1,
#bslisttable .row2col1,
#bslisttable .row3col1,
#bslisttable .row4col1 {
  border-left: gray 2px solid;
}
#bslisttable .lastcol {
  border-right: gray 2px solid;
}
#bslisttable .lastrow td {
  border-bottom:2px solid gray;
  padding-bottom:7px;
}
#bslisttable th {
  background-color:#C02121;
  font-weight:bold;
  color:white;
}
#bslisttable th.row1col1,
#bslisttable th.row1col2,
#bslisttable th.row1col3,
#bslisttable th.row1col4 {
  border-top: gray 2px solid;
  padding-top:3px;
}
#bslisttable tr.lastrow th {
  border-bottom:2px solid gray;
  padding-bottom:3px;
}

#bslisttable tr.bsodd td {
  background-color:#FFFFFF;
}
#bslisttable tr.bseven td {
  background-color:#FFFFF0;
}

#bslisttable .date {
  white-space:nowrap;
  font-size:1.2em;
  color:darkcyan;
  font-weight:bold;
}
#bslisttable .scripture1 {
  white-space:nowrap;
  color:c02121;
  font-weight:bold;
}
#bslisttable .scripture2 {
  white-space:nowrap;
  color:c02121;
  font-weight:bold;
}
#bslisttable .title {
  font-size:1.2em;
  color:#c02121;
  font-weight:bold;
}
#bslisttable .series_text {
  white-space:nowrap;
  color:darkcyan;
}
#bslisttable .duration {
  white-space:nowrap;
  font-style:italic;
}
#bslisttable .studyintro {
	
}
#bslisttable .teacher {
	white-space:nowrap;
}
#bslisttable .location_text {
	white-space:nowrap;
}
#bslisttable .topic_text {
	white-space:nowrap;
}
#bslisttable .message_type {
	white-space:nowrap;
}
#bslisttable .media {
	white-space:nowrap;
}
#bslisttable .store {
	white-space:nowrap;
}
#bslisttable .details-text {
	
}
#bslisttable .details-pdf {
	
}
#bslisttable .details-text-pdf {
	
}
#bslisttable .detailstable td {
  border: none;
  padding: 0 2px 0 0;
}
#bslisttable .secondary_reference {
	white-space:nowrap;
}
#bslisttable .teacher-title-name {
	white-space:nowrap;
}
#bslisttable .submitted {
	white-space:nowrap;
}
#bslisttable .hits {
	white-space:nowrap;
}
#bslisttable .studynumber {
	white-space:nowrap;
}
#bslisttable .filesize {
	white-space:nowrap;
}
#bslisttable .custom {
	white-space:nowrap;
}
#bslisttable .mediatable td {
  border: none;
  padding: 0 6px 0 0;
}
#bslisttable .mediatable span.bsfilesize {
  font-size:0.6em;
}

</style>
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
//$document =& JFactory::getDocument();
$listingcall = JView::loadHelper('listing');
//$type = 'text/css';
//$css_call = JView::loadHelper('css');
//$styles = getCss($params);
//$document->addStyleDeclaration($styles, $type);
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'biblestudyviews.css');
//$url = $params->get('stylesheet');
//if ($url) {$document->addStyleSheet($url);}
//$pageclass_sfx = $params->get('pageclass_sfx');
?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
    <div id="header">
      <h1 class="componentheading">
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
    <?php
if ( $this->params->get( 'show_page_title' ) >0 ) {
    echo $this->params->get('page_title');
    }
	?>
      </h1>
    
    </div><!--header-->
    <div id="bsdropdownmenu">

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


    </div><!--dropdownmenu-->
     <table id="bslisttable" cellspacing="0">
      <thead>
        <tr>
          <th id="bsdatehead" class="row1col1">Date</th>
          <th id="bstitlehead" class="row1col2">Title</th>
          <th id="bsserieshead" class="row1col3">Series</th>

          <th id="bsmediahead" class="row1col4 lastcol" rowspan="2">Media</th>
        </tr>
        <tr>
          <th id="bsscripthead" class="row2col1">Scripture</th>
          <th id="bsteacherhead" class="row2col2">Teacher</th>
          <th id="bsdurhead" class="row2col3">Duration</th>
        </tr>

        <tr class="lastrow">
          <th id="bsdeschead" class="row3col1 lastcol" colspan="4">Description</th>
        </tr>
      </thead>
      <tbody>

        <?php 
 //This sets the alternativing colors for the background of the table cells
 $class1 = 'bsodd';
 $class2 = 'bseven';
 $oddeven = $class1;

 foreach ($this->items as $row) { //Run through each row of the data result from the model
	if($oddeven == $class1){ //Alternate the color background
	$oddeven = $class2;
	} else {
	$oddeven = $class1;
	}

	$listing = getListing($row, $params, $oddeven);
 	echo $listing;
 }
 ?>
 </tbody></table>
<div class="listingfooter" >
	<?php 
      if ($params->get('show_limitbox') > 0) {
		  echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
      echo $this->pagination->getLimitBox();
	  }
      echo $this->pagination->getPagesLinks();
      echo $this->pagination->getPagesCounter();
      //echo $this->pagination->getListFooter(); ?>
</div> <!--end of bsfooter div-->
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>

