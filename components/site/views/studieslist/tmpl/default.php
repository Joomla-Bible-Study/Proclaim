<?php defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript" src="components/com_biblestudy/tooltip.js"></script>
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
?>

<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm"><?php
 //Set some initalization parameters
 $user =& JFactory::getUser();
 $entry_user = $user->get('gid');
 if (!$entry_user) {
  $entry_user = 0;
 }
 $entry_access = $this->params->get('entry_access');
 if (!$entry_access) {
  $entry_access = 23;
 }
 $allow_entry = $this->params->get('allow_entry_study');
?>

<?php
//Start test to see if message needs to be displayed
if ($message) {?>
 <!-- Begin Message -->
 <div style="width:100%">
  <h2><?php echo $message;?></h2>
 </div>
 <!-- End Message --><?php
  }?>

<?php
//Start test to see if frontend study entry is allowed
if ($allow_entry > 0) {
 if ($entry_access <= $entry_user) {?>
 <!-- Begin Front End Study Submission -->
 <div style="width:100%;">
  <strong>Studies</strong>
  <br><a href="<?php echo JURI::base()?>index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form">Add a New Study</a>
  <br><a href="<?php echo JURI::base()?>index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form">Add a New Media File Record</a>
<?php
  if ($this->params->get('show_comments') > 0){?>
  <br><a href="<?php echo JURI::base()?>index.php?option=com_biblestudy&view=commentslist">Manage Comments</a>
<?php
  }?>
 </div>
 <!-- End Front End Study Submission --><?php
 }
}
//End test to see if frontend study entry is allowed?>

<?php
 //Start test to see if frontend podcast entry is allowed
if ($this->params->get('allow_podcast') > 0){
 $podcast_access = $this->params->get('podcast_access');
 if (!$podcast_access) {
  $podcast_access = 23;
 }
 if ($podcast_access <= $entry_user){
  $query = ('SELECT id, title, published FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC');
  $database->setQuery( $query );
  $podcasts = $database->loadAssocList();
  ?>
 <!-- Begin Front End Podcast Submission -->
 <div style="width:100%;">
  <strong>Podcasts</strong>
  <br><a href="<?php echo JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form';?>">Add A Podcast</a>
<?php 
  foreach ($podcasts as $podcast){
  $pod = $podcast['id']; $podtitle = $podcast['title'];?>
  <br><a href="<?php echo JURI::base().'index.php?option=com_biblestudy&controller=podcastedit&view=podcastedit&layout=form&task=edit&cid[]='.$pod;?>"><?php echo $podtitle;?></a>
<?php
  }?>
 </div>
 <!-- End Front End Podcast Submission --><?php
 }
}
 //End test to see if frontend podcast entry is allowed?>


<?php 
 //Begin display page logo and title
$wtd = $this->params->get('pimagew');?>
 <!-- Begin Display Page image and Title -->
 <div style="width:100%;">
  <!-- <h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="border: 1px solid;"> -->
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
  <!-- </h1> -->
 </div>
 <!-- End Display Page image and Title --><?php
 //End display page logo and title?>


 <table width="<?php echo $page_width; ?>">
 <?php if ($this->params->get('show_teacher_list') >0) { ?>
 <tr>
  <td width="<?php echo $this->params->get('teacherw');?>"><img
   src="<?php echo $this->params->get('teacherimage');?>" border="1"
   width="<?php echo $this->params->get('teacherw');?>"
   height="<?php echo $this->params->get('teacherh');?>" /><br />
   <?php echo $this->params->get('teachername');?></td>
 </tr>
 <?php }?>
 <tr>
  <td><?php //Row for drop down boxes?> <?php //This is the column that holds the search drop downs?>

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

  </td>
 </tr>
 <?php //End of row for drop down boxes?>

 <?php // The table to hold header rows ?>
 <tr><td>
 <table width="<?php echo $this->params->get('header_width', '100%');?>" cellpadding="0">
<?php //mirrors 6 colum table below?>
  <tr>
   <td></td>
<?php // begin array for positions to see if we need a column for the header
  $headercheck = array( array(  'position' => $this->params->get('position1')),
  array( 'position' => $this->params->get('position2')),
  array( 'position' => $this->params->get('position3')),
  array( 'position' => $this->params->get('position4')),
  array( 'position' => $this->params->get('position5')),
  array( 'position' => $this->params->get('position6')),
  array( 'position' => $this->params->get('position7')),
  array( 'position' => $this->params->get('position8')),
  array( 'position' => $this->params->get('position9')),
  array( 'position' => $this->params->get('position10')),
  array( 'position' => $this->params->get('position11')),
  array( 'position' => $this->params->get('position12')),
  array( 'position' => $this->params->get('position13')),
  array( 'position' => $this->params->get('position14')),
  array( 'position' => $this->params->get('position15')),
  array( 'position' => $this->params->get('position16')),
  array( 'position' => $this->params->get('position17')),
  array( 'position' => $this->params->get('position18'))
  ); //print_r($headercheck);

  //Beginning of header rows
  $isheader1 = 0;
  $isheader2 = 0;
  $isheader3 = 0;
  $isheader4 = 0;
  if ($this->params->get('use_headers') >0) {
   //$header_count = count($headercheck);
   //dump ($header_count, 'Header_count');
   $rows1=count($headercheck);
   for($j=0;$j<$rows1;$j++)
   {
    if ($headercheck[$j]['position']==1){ $isheader1 = 1;}
    if ($headercheck[$j]['position']==2){ $isheader2 = 1;}
    if ($headercheck[$j]['position']==3){ $isheader3 = 1;}
    if ($headercheck[$j]['position']==4){ $isheader4 = 1;}
   }
   if ($isheader1 == 1)
   {echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header1_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header1').'</span></th>';}
   if ($isheader2 == 1)
   {echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header2_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header2').'</span></th>';}
   if ($isheader3 == 1)
   {echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header3_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header3').'</span></th>';}
   if ($isheader4 == 1)
   {echo '<th align="'.$this->params->get('header_align').'" bgcolor="'.$this->params->get('header_color').'" width="'.$this->params->get('header4_width').'"><span '.$this->params->get('header_span').'>'.$this->params->get('header4').'</span></th>';}
   
  } // end of if use headers?>
  </tr>
 </table>
 </td></tr><?php //End of table for header rows?>


  <?php //This is where each result from the database of studies is diplayed with options for each 6 column table?>

  <?php

  $k = 1;
  $row_count = 0;
  ?>
 <tr>
  <td><?php 
  for ($i=0, $n=count( $this->items ); $i < $n; $i++)
  { // This is the beginning of a loop that will cycle through all the records according to the query?>
  <?php $bgcolor = ($row_count % 2) ? $color1 : $color2; //This code cycles through the two color choices made in the parameters?>
  <?php $row = &$this->items[$i]; ?>
  <?php
 

  /* Now we do this small line which is basically going to tell
   PHP to alternate the colors between the two colors we defined above. */
  $bgcolor = ($row_count % 2) ? $color1 : $color2;
  ?>
  <?php
$id4 = $row->id;
$filesizefield = '#__bsms_mediafiles.study_id';
//$filepath_call = JView::loadHelper('filepath'); 
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
  //if ($number_rows < 1) {$file_size = '0';}
  $params		=& $mainframe->getParams('com_biblestudy');
$listarraycall = JView::loadHelper('listarray');
$a = getListarray($params, $row, $scripture1, $scripture2, $date, $file_size, $duration);
//dump ($a, 'a: ');
  // Obtain a list of columns
  foreach ($a as $key => $arow) {
   $position[$key]  = $arow['position'];
   $order[$key] = $arow['order'];
  }
  //Remove all rows in the array that show the element should not be displayed


  // Sort the data with position and order ascending
  // Add $a as the last parameter, to sort by the common key
  array_multisort($position, SORT_ASC, $order, SORT_ASC, $a);

  //Copy the array into four so we can deal with them individually in each column
  $column1 = $a;
  $column2 = $a;
  $column3 = $a;
  $column4 = $a;
  //This calls the helper once that will process each column's array, coming from the $a variable. We will then call a function in each column from this helper file
  $array_call = JView::loadHelper('columnarray');
  $color = $this->params->get('use_color');
  ?>

<?php //Beginning of row for 6 column table?> <?php if ($this->params->get('line_break') > 0) {echo '<br />'; } ?>
  <table <?php if ($color > 0){echo 'bgcolor="'.$bgcolor.'"';}?>
   width="<?php echo $page_width; ?>" cellpadding="0" cellspacing="0">
   <?php //6 Column table?>
   <tr valign="<?php echo $this->params->get('colalign');?>">
   <?php //Row for 6 column table?>

   <?php //Remove all rows in the array that are not Column1
/*   $rows1=count($column1);
   for($j=0;$j<$rows1;$j++)
   {
    if ($column1[$j]['position']!=1)
    {
     unset($column1[$j]);
    }
   }
*/
   //$count1 = count($column1['element']);
   //$column1 = array_values($column1);
   //print_r ($column1);
   //echo 'Rows1: '.$rows1; ?>
   <?php $columnnumber = 1;
	$column = getColumnarray($a, $row, $columnnumber, $params);
	//if ($column) { //This tests to see if there is anything in column?>
   <?php //if (isset($column1[0]['position'])) { //This tests to see if there is anything in column1?>
   <?php if ($entry_user >= $entry_access){//This adds a <td> for user frontend editing of the record?>
    <td width="10" valign="<?php echo $this->params->get('colalign');?>"><a
     href="<?php echo JURI::base();?>index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]=<?php echo $row->id;?>"><?php echo JText::_('[Edit]');?></a></td>
     <?php } //End of front end user authorized to edit records?>
  <!--  <td width="<?php //echo $widpos1;?>"><?php //Column 1 of 5?>
    <table border="<?php //echo $this->params->get('border');?>"
     cellpadding="<?php //echo $this->params->get('padding');?>"
     cellspacing="<?php //echo $this->params->get('spacing');?>">
     <?php //This is the table for the list. It's outside the foreach?>
     <?php


     //Now let's assign some elements and go through each of them.
    /* foreach ($column1 as $c1) {
      $element1 = $c1['element'];
      $position1 = $c1['position'];
      $isbullet1=$c1['isbullet'];
      $span1=$c1['span'];
      $islink1=$c1['islink'];*/
      ?>

     <tr valign="<?php //echo $this->params->get('colalign');?>">
     <?php //We make a new row and td for each record in this column ?>
      <td valign="<?php //echo $this->params->get('colalign');?>"><?php 
      //Now we produce each element in turn with its parameters
	  	
		echo $column;
/*	  echo '<span '.$span1.'>';
      if ($isbullet1 == 1) {
       echo '<ul><li>'; }
       switch ($islink1) {
        case 1 :
         $link1 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
         echo '<a href="'.$link1.'">';
         break;
        case 2 :
         $link1 = JRoute::_($filepath);
         echo '<a href="'.$link1.'">';
         break;
        case 3 :
         $link1 = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $row->tid );
         echo '<a href="'.$link1.'">';
         break;
       }
       echo $element1;
       if ($islink1 > 0) { echo '</a>'; }
       if ($isbullet1 == 1) { echo '</li></ul>';}
       echo '</span>';
      */ ?></td>
     </tr>
     <?php //This is tne end of each td and row for the list ?>
     <?php
     //} //End of foreach Column1
     ?>
    </table>
    <?php //This ends the table inside of column 1 that holds the actual listings. ?>



    </td> -->
    <?php //}//End of column 1 of 5 Also end of if ($column1->position)?>

    <?php //Remove all rows in the array that are not Column 2
  /* 
   $rows2=count($column2);
    for($j=0;$j<$rows2;$j++)
    {
     if ($column2[$j]['position']!=2)
     {
      unset($column2[$j]);
     }
    }
    $column2 = array_values($column2);
	*/
    //print_r($column2);
    //$testit = strlen($column2[0]['element']); echo 'Testit: '.$testit;
    ?>
    <?php 
		$columnnumber = 2;
		$column = getColumnarray($a, $row, $columnnumber, $params);
//	if ($column) { //This tests to see if there is anything in column?>
    <?php //if (isset($column2[0]['position'])) { //This tests to see if there is anything in column2?>
   <!-- <td width="<?php //echo $widpos2;?>"><?php //Beginning of Column 2 of 5?>

    <table border="<?php //echo $this->params->get('border');?>"
     cellpadding="<?php //echo $this->params->get('padding');?>"
     cellspacing="<?php //echo $this->params->get('spacing');?>">
     <?php //This is the table for the list. It's outside the foreach?>
     <?php

     //Now let's assign some elements and go through each of them.
   /* 
	foreach ($column2 as $c2) {
      $element2 = $c2['element'];
      $position2 = $c2['position'];
      $isbullet2=$c2['isbullet'];
      $span2=$c2['span'];
      $islink2=$c2['islink'];
    */
	?>

     <tr valign="<?php //echo $this->params->get('colalign');?>">
     <?php //We make a new row and td for each record in this column ?>
      <td valign="<?php //echo $this->params->get('colalign');?>"><?php 
      //Now we produce each element in turn with its parameters
	  	
		echo $column;
      /*
	  echo '<span '.$span2.'>';
      if ($isbullet2 == 1) {
       echo '<ul><li>'; }
       switch ($islink2) {
        case 1 :
         $link2 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
         echo '<a href="'.$link2.'">';
         break;
        case 2 :
         $link2 = JRoute::_($filepath);
         echo '<a href="'.$link2.'">';
         break;
        case 3 :
         $link2 = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $row->tid );
         echo '<a href="'.$link2.'">';
         break;
       }
       echo $element2;
       if ($islink2 > 0) { echo '</a>'; }
       if ($isbullet2 == 1) { echo '</li></ul>';}
       echo '</span>';
	   */
       ?></td>
     </tr>
     <?php //This is tne end of each td and row for the list ?>
     <?php
     //} //End of foreach $column2
     ?>
    </table>
    <?php //This ends the table inside of column 2 that holds the actual listings. It is outside the foreach loop?>


    </td> -->
    <?php //}//End of Column 2 of 5 And if($column2->position?>

    <?php //Remove all rows in the array that are not Column1
	/*
    $rows3=count($column3);
    for($j=0;$j<$rows3;$j++)
    {
     if ($column3[$j]['position']!=3)
     {
      unset($column3[$j]);
     }
    }
    $column3 = array_values($column3);
	*/
    //$testit3 = count($column3[0]['position']); echo 'Testit: '.$testit3;
    //print_r($column3);?>
    <?php 
		$columnnumber = 3;
		$column = getColumnarray($a, $row, $columnnumber, $params);
	//if ($column) { //This tests to see if there is anything in column?>
    <?php //if (isset($column3[0]['position'])) { //This tests to see if there is anything in column3?>
   <!-- <td width="<?php //echo $widpos3;?>"><?php //Begin Column 3 of 5?>

    <table border="<?php //echo $this->params->get('border');?>"
     cellpadding="<?php //echo $this->params->get('padding');?>"
     cellspacing="<?php //echo $this->params->get('spacing');?>">
     <?php //This is the table for the list. It's outside the foreach?>
     <?php

     //Now let's assign some elements and go through each of them.
	/* 
     foreach ($column3 as $c3) {
      $element3 = $c3['element'];
      $position3 = $c3['position'];
      $isbullet3=$c3['isbullet'];
      $span3=$c3['span'];
      $islink3=$c3['islink'];
     */
	 ?>

     <tr valign="<?php //echo $this->params->get('colalign');?>">
     <?php //We make a new row and td for each record in this column ?>
      <td valign="<?php //echo $this->params->get('colalign');?>"><?php 
      //Now we produce each element in turn with its parameters
	  	
		echo $column;
     /* 
	  echo '<span '.$span3.'>';
      if ($isbullet3 == 1) {
       echo '<ul><li>'; }
       switch ($islink3) {
        case 1 :
         $link3 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
         echo '<a href="'.$link3.'">';
         break;
        case 2 :
         $link3 = JRoute::_($filepath);
         echo '<a href="'.$link3.'">';
         break;
        case 3 :
         $link3 = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $row->tid );
         echo '<a href="'.$link3.'">';
         break;
       }
       echo $element3;
       if ($islink3 > 0) { echo '</a>'; }
       if ($isbullet3 == 1) { echo '</li></ul>';}
       echo '</span>';
	   */
       ?></td>
     </tr>
     <?php //This is tne end of each td and row for the list ?>
     <?php
     //} //End of foreach $column3
     ?>
    </table>
    <?php //This ends the table inside of column 3 that holds the actual listings. It is outside the foreach loop?>

    </td> -->
    <?php //}//End of Column 3 of 5 and $jf($column3->position)?>

    <?php //Remove all rows in the array that are not Column1
	/*
    $rows4=count($column4);
    for($j=0;$j<$rows4;$j++)
    {
     if ($column4[$j]['position']!=4)
     {
      unset($column4[$j]);
     }
    }
    $column4 = array_values($column4);
	*/
    //print_r($column4);?>
    <?php 
	
	 	$columnnumber = 4;
		$column = getColumnarray($a, $row, $columnnumber, $params);
	//if ($column) { //This tests to see if there is anything in column?>
    <?php //if (isset($column4[0]['position'])) { //This tests to see if there is anything in column4?>
  <!--  <td width="<?php //echo $widpos4;?>"><?php //Begin Column 4 of 5?>

    <table border="<?php// echo $this->params->get('border');?>"
     cellpadding="<?php //echo $this->params->get('padding');?>"
     cellspacing="<?php //echo $this->params->get('spacing');?>">
     <?php //This is the table for the list. It's outside the foreach?>
     <?php

     //Now let's assign some elements and go through each of them.
	/*
     foreach ($column4 as $c4) {
      $element4 = $c4['element'];
      $position4 = $c4['position'];
      $isbullet4=$c4['isbullet'];
      $span4=$c4['span'];
      $islink4=$c4['islink'];
      */
	  ?>


     <tr valign="<?php //echo $this->params->get('colalign');?>">
     <?php //We make a new row and td for each record in this column ?>
      <td valign="<?php //echo $this->params->get('colalign');?>"><?php 
      //Now we produce each element in turn with its parameters
	 
		echo $column;
	/*	
	  $columnnumber = 4;
		$column = getColumnarray($a, $row, $columnnumber);
		echo $column;
      echo '<span '.$span4.'>';
      if ($isbullet4 == 1) {
       echo '<ul><li>'; }
       switch ($islink4) {
        case 1 :
         $link4 = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
         echo '<a href="'.$link4.'">';
         break;
        case 2 :
         $link4 = JRoute::_($filepath);
         echo '<a href="'.$link4.'">';
         break;
        case 3 :
         $link4 = JRoute::_('index.php?option=com_biblestudy&view=teacherdisplay' . '&id=' . $row->tid );
         echo '<a href="'.$link4.'">';
         break;
       }
       echo $element4;
       if ($islink4 > 0) { echo '</a>'; }
       if ($isbullet4 == 1) { echo '</li></ul>';}
       echo '</span>';
	   */
       ?></td>
     </tr>
     <?php //This is tne end of each td and row for the list ?>
     <?php
    // } //End of forach $column4
     ?>
    </table>
    <?php //This ends the table inside of column 4 that holds the actual listings. It is outside the foreach loop?>

    </td> -->
    <?php //}//End of Column 4 of 5 and if($column4->position)?>

    <?php if (($this->params->get('show_full_text') + $this->params->get('show_pdf_text')) > 0) { //Tests to see if show text and/or pdf is set to "show"?>

    <td width="<?php echo $textwidth;?>"><?php //Column 5 of 6 column table - this is to hold the text and pdf images/links?>
    <table align="left">
     <tr valign="<?php echo $this->params->get('colalign');?>">

     <?php
     if ($this->params->get('show_full_text') > 0) {
      $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id );
      JHTML::_('behavior.tooltip');?>
      <td><?php 
       if ($this->params->get('tooltip') >0) {?>
        <span class="zoomTip"
         title="<strong>Sermon Info:</strong>::
       	 <?php if ($study) {?><strong><?php echo JText::_('Title:');?></strong> <?php echo $study;}?><br><br>
       	 <?php if ($intro) {?><strong><?php echo JText::_('Details:');?></strong> <?php echo $intro;}?><br><br>
       	 <?php if ($snumber) {?><strong><?php echo JText::_('Sermon Number:');?></strong> <?php echo $snumber;}?><br>
       	 <strong><?php echo JText::_('Teacher:');?>:</strong> <?php echo $teacher;?><br><br>
       	 <hr /><br>
       	 <?php if ($scripture1) {?><strong><?php echo JText::_('Scripture:');?> </strong><?php echo $scripture1;}?>"><?php
       } //end of is show tooltip?> <?php
       $src = JURI::base().$this->params->get('text_image');
       if ($imagew) {$width = $imagew;} else {$width = 24;}
       if ($imageh) {$height = $imageh;} else {$height= 24;}
       ?> <a href="<?php echo $link; ?>"><img
       src="<?php echo JURI::base().$this->params->get('text_image');?>"
       alt="<?php echo $details_text;?>" width="<?php echo $width;?>"
       height="<?php echo $height;?>" border="0" /></a>
       <?php if ($this->params->get('tooltip') >0) { ?></span><?php } ?>
      </td>
      <?php //End of text column ?>

      <?php } // end of show_full_text if ?>

      <?php if ($this->params->get('show_pdf_text') > 0) { ?>
      <?php $link = JRoute::_('index.php?option=com_biblestudy&view=studydetails' . '&id=' . $row->id . '&format=pdf' ); ?>

      <td><?php $src = JURI::base().$this->params->get('pdf_image');
      if ($imagew) {$width = $imagew;} else {$width = 24;}
      if ($imageh) {$height = $imageh;} else {$height= 24;}
      ?> <a href="<?php echo $link; ?>" target="_blank"
       title="<?php echo $details_text;?>"><img
       src="<?php echo JURI::base().$this->params->get('pdf_image');?>"
       alt="<?php echo $details_text.JText::_('- PDF Version');?>"
       width="<?php echo $width;?>" height="<?php echo $height;?>"
       border="0" /></a></td>
       <?php //This is the end of the column for the pdf image?>

       <?php } // End of show pdf text ?>
     </tr>
    </table>
    </td>
    <?php //End column 5 of 6?>

    <?php } //This is the end of the if statement to see if text and/or pdf images set to "show"?>
    <?php if ($this->params->get('show_store') > 0){?>
<?php /*
    <td width="<?php echo $storewidth;?>"><?php //This td is for the store column?>
    <?php $query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
    .' s.image_cd, s.prod_cd, s.server_cd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_cd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_cd )
                        WHERE s.id ='.$row->id;
    $database->setQuery($query);
    $cd = $database->loadObject(); ?> <?php $query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
    .' s.image_dvd, s.prod_dvd, s.server_dvd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_dvd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_dvd )
                        WHERE s.id ='.$row->id;
    $database->setQuery($query);
    $dvd = $database->loadObject();
    if (($cd->mid + $dvd->mid) > 0) {?>

    <table>
     <tr>
     <?php

     if ($cd->mid > 0){
      $src = JURI::base().$cd->media_image_path;
      if ($imagew) {$width = $imagew;} else {$width = 24;}
      if ($imageh) {$height = $imageh;} else {$height= 24;}
      ?>
      <td><?php echo '<a href="'.$cd->server_path.$cd->prod_cd.'" title="'.$cd->media_alttext.'"><img src="'.JURI::base().$cd->media_image_path.'" width="'.$width.'" height="'.$height.'" alt="'.$cd->media_alttext.' "border="0" /></a>';?></td>
      <?php } ?>
      <?php if ($dvd->mid > 0){
       $src = JURI::base().$dvd->media_image_path;
       if ($imagew) {$width = $imagew;} else {$width = 24;}
       if ($imageh) {$height = $imageh;} else {$height= 24;}
       ?>
      <td><?php echo '<a href="'.$dvd->server_path.$dvd->prod_dvd.'" title="'.$dvd->media_alttext.'"><img src="'.JURI::base().$dvd->media_image_path.'" width="'.$width.'" height="'.$height.'" alt="'.$dvd->media_alttext.' "border="0" /></a>';?></td>
      <?php } ?>
     </tr>
     <tr>
      <td colspan="2" align="center"><span
      <?php echo $this->params->get('store_span');?>><?php echo $this->params->get('store_name');?></span></td>
     </tr>
    </table>
    <?php }?></td>
    */
	$rowid = $row->id;
	$callstore = JView::loadHelper('store');
	$store = getStore($this->params, $rowid);
	echo $store; ?>
    <?php  }//End of store column?>

    <?php if ($this->params->get('show_media') > 0) { ?>

    <td width="<?php echo $this->params->get('media_width');?>"><?php //Column 6 of 6 column table. This column holds the media?>

    <?php $query_media1 = 'SELECT #__bsms_mediafiles.*,'
    . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
    . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
    . ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname,'
    . ' #__bsms_media.media_alttext AS malttext,'
    . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
    . ' FROM #__bsms_mediafiles'
    . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
    . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
    . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
    . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
    . ' WHERE #__bsms_mediafiles.study_id = '.$row->id.' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC';
    $database->setQuery( $query_media1 );
    $media1 = $database->loadObjectList('id');
     
    ?>

    <table align="left">
     <tr valign="<?php echo $this->params->get('colalign');?>">

     <?php

     foreach ($media1 as $media) {
      $download_image = $this->params->get('download_image');
      if (!$download_image) { $download_image = 'components/com_biblestudy/images/download.png';}
      $link_type = $media->link_type;
	  
      $useplayer = 0;
	  
      if ($this->params->get('media_player') > 0) {
       //Look to see if it is an mp3
       $ismp3 = substr($media->filename,-3,3);
       if ($ismp3 == 'mp3'){$useplayer = 1;}else {$useplayer = 0;}
	   } //End if media_player param test
      $idfield = '#__bsms_mediafiles.id';
	  $id4 = $media->id;
	  $id3 = $id4;
	  $filesizefield = '#__bsms_mediafiles.id';
	  //dump ($media->id, 'id4: ');
	  $filesize_call = JView::loadHelper('filesize');
	  $filesize = getFilesize($id4, $filesizefield);
	  
	  $media_size = $filesize;
	 // dump ($media_size, 'filesize: ');
      $mimetype = $media->mimetext;
      $src = JURI::base().$media->impath;
      if ($imagew) {$width = $imagew;} else {$width = 24;}
      if ($imageh) {$height = $imageh;} else {$height= 24;}
      $ispath = 0;
	  $call_filepath = JView::loadHelper('filepath');
	  $path1 = getFilepath($id3, $idfield);
  
       $pathname = $media->fpath;
       $filename = $media->filename;
       $ispath = 1;
       $direct_link = '<a href="'.$path1.'" title="'.$media->malttext.' '.$duration.' '
       .$media_size.'" target="'.$media->special.'"><img src="'.JURI::base().$media->impath
       .'" alt="'.$media->imname.' '.$duration.' '.$media_size.'" width="'.$width
       .'" height="'.$height.'" border="0" /></a>';
      $isavr = 0;
      if (JPluginHelper::importPlugin('system', 'avreloaded'))
      {
       $isavr = 1;
       $studyfile = $media->spath.$media->fpath.$media->filename;
       $mediacode = $media->mediacode;
       //dump ($mediacode, 'mediacode');
       $isrealfile = substr($media->filename, -4, 1);
       $fileextension = substr($media->filename,-3,3);
       if ($mediacode == ''){
        $mediacode = '{'.$fileextension.'remote}-{/'.$fileextension.'remote}';
       }
       $mediacode = str_replace("'",'"',$mediacode);
       $ispop = substr_count($mediacode, 'popup');
       if ($ispop < 1) {
        $bracketpos = strpos($mediacode,'}');
        $mediacode = substr_replace($mediacode,' popup="true" ',$bracketpos,0);
       }
       $isdivid = substr_count($mediacode, 'divid');
       if ($isdivid < 1) {
        $dividid = ' divid="'.$media->id.'"';
        $bracketpos = strpos($mediacode, '}');
        $mediacode = substr_replace($mediacode, $dividid,$bracketpos,0);
       }
       $isonlydash = substr_count($mediacode, '}-{');
       if ($isonlydash == 1){
        $ishttp = substr_count($studyfile, 'http://');
        if ($ishttp < 1) {
         //We want to see if there is a file here or if it is streaming by testing to see if there is an extension
         $isrealfile = substr($media->filename, -4, 1);
         if ($isrealfile == '.') {
          $isslash = substr_count($studyfile,'//');
          if (!$isslash) {
           $studyfile = substr_replace($studyfile,'http://',0,0);
          }
         }
        }

        if ($isrealfile != '.')
        {
         $studyfile = $media->filename;
        }
        $mediacode = str_replace('-',$studyfile,$mediacode);
       }
       $popuptype = 'window';
       if($this->params->get('popuptype') != 'window') {
        $popuptype = 'lightbox';
       }
       $avr_link = $mediacode.'{avrpopup type="'.$popuptype.'" id="'.$media->id
       .'"}<img src="'.JURI::base().$media->impath.'" alt="'.$media->imname
       .' '.$duration.' '.$media_size.'" width="'.$width
       .'" height="'.$height.'" border="0" title="'
       .$media->malttext.' '.$duration.' '.$media_size.'" />{/avrpopup}';
       //dump ($avr_link, 'AVR Lnk');

      }
      $useavr = 0;
      $useavr = $useavr + $this->params->get('useavr') + $media->internal_viewer;
      $isfilesize = 0;
     // if ($media_size > 0)
     // {
      // $isfilesize = 1;
       $media1_sizetext = '<span style="font-size:0.60em;">'.$filesize.'</span>';
     // }
      //else {$media1_sizetext = '';}
      $media1_link = $direct_link;

      if ($useavr > 0)
      { $media1_link = $avr_link;
      //dump ($avr_link, 'AVR Link');
       
      }
      if ($useplayer == 1){
       $player_width = $this->params->get('player_width');
       if (!$player_width) { $player_width = '290'; }
       $media1_link =
     '<script language="JavaScript" src="'.JURI::base().'components/com_biblestudy/audio-player.js"></script>
<object type="application/x-shockwave-flash" data="'.JURI::base().'components/com_biblestudy/player.swf" id="audioplayer'.$row_count.'" height="24" width="290">
<param name="movie" value="'.JURI::base().'components/com_biblestudy/player.swf">
<param name="FlashVars" value="playerID='.$row_count.'&amp;soundFile='.$path1.'">
<param name="quality" value="high">
<param name="menu" value="false">
<param name="wmode" value="transparent">
</object> ';}
       ?>
       <?php
       /**
        * @desc: I hope to in the future load media files using this method
        */
       /*  echo ('<div class="inlinePlayer" id="media-'.$media->id.'"></div>');
        echo ('<a href="'.$path1.'" class="btnPlay" alt="'.$media->id.'">Play</a>');*/


       /*$abspath    = JPATH_SITE;
        require_once($abspath.DS.'components/com_biblestudy/classes/class.biblestudymediadisplay.php');
        $inputtype = 0;
        $media_display = new biblestudymediadisplay($row->id, $inputtype);
        $media_display->id = $row->id;
        $media_display->inputtype = 0;*/

       ?>
      <td align="left"><?php //dump ($media1_link, 'Media1_link'); ?> <?php  echo $media1_link; ?>
      <?php if ($this->params->get('show_filesize') > 0)
      { ?> <?php echo $media1_sizetext;

      }?> <?php if ($link_type > 0){ $src = JURI::base().$download_image;
      if ($this->params->get('download_side') > 0) { echo '<td>';}
      if ($imagew) {$width = $imagew;} else {$width = 24;}
      if ($imageh) {$height = $imageh;} else {$height= 24;}
      if($downloadCompatibility == 0) {
       echo '<a href="index.php?option=com_biblestudy&id='.$media->id.'&view=studieslist&controller=studieslist&task=download">';
      }else{
       echo('<a href="http://joomlaoregon.com/router.php?file='.$media->spath.$media->fpath.$media->filename.'&size='.$media->size.'">');
      }
      ?> <img src="<?php echo JURI::base().$download_image;?>"
       alt="<?php echo JText::_('Download');?>"
       height="<?php echo $height;?>" width="<?php echo $width;?>"
       title="<?php echo JText::_('Download');?>" /><?php echo JText::_('</a>'); if ($this->params->get('download_side') > 0) { echo '</td>';}}?>

      </td>


      <?php } //end of foreach of media results?>
     </tr>
    </table>
    <?php //This ends the table that holds the media images and is outside the foreach?>
    </td>
    <?php //End column 6 of 6?>

    <?php } //This is the end of the if show media statement ?>

   </tr>
   <?php //End row for 6 column table?>
  </table>
  <?php //End 6 Column table?>
  <table width="<?php echo $page_width; ?>"
  <?php if ($color > 0){echo 'bgcolor="'.$bgcolor.'"';}?>>
   <?php if ($show_description > 0) { ?>
   <tr>
    <td><?php  echo '<span '.$this->params->get('descriptionspan').'> '.$row->studyintro.'</span>'; ?>
    </td>
   </tr>
   <?php if ($this->params->get('line') > 0) { ?>
   <tr>
    <td width="<?php echo $this->params->get('mod_table_width');?>"><?php //This row is to hold the line and should run along the bottom of the 5 column table?>
    <?php echo '<img src="'.JURI::base().'components/com_biblestudy/images/square.gif" height="2" width="100%" alt="" />'; ?>
    <?php } //End of if show lines?></td>
   </tr>
   <?php } // End show description?>

  </table>

  <?php    $row_count++; // This increments the row count and adjusts the variable for the color background
  $k = 3 - $k;
  } //This is the end of the for statement for each result from the database that will create its own 6 column table?>

  </td>
 </tr>
 <?php //End of row for 6 column table?>


 <tfoot>
 <tr>
  <td align="center"><?php 
  echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
  echo $this->pagination->getLimitBox();
  echo $this->pagination->getPagesLinks();
  echo $this->pagination->getPagesCounter();
  //echo $this->pagination->getListFooter(); ?>
  </td>
 </tr>
 </tfoot>
</table> <?php //This is the end of the table for the overall listing page?>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="studieslist" />
</form>
