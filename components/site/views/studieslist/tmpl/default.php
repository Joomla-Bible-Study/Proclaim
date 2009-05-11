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

#bslisttable .bsdate {
  white-space:nowrap;
  font-size:1.2em;
  color:darkcyan;
  font-weight:bold;
}
#bslisttable .bsscripture {
  white-space:nowrap;
  color:c02121;
  font-weight:bold;
}
#bslisttable .bstitle {
  font-size:1.2em;
  color:#c02121;
  font-weight:bold;
}
#bslisttable .bsseries {
  white-space:nowrap;
  color:darkcyan;
}
#bslisttable .bsduration {
  white-space:nowrap;
  font-style:italic;
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
$document =& JFactory::getDocument();
$type = 'text/css';
$css_call = JView::loadHelper('css');
$styles = getCss($params);
$document->addStyleDeclaration($styles, $type);
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'biblestudyviews.css');
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}
$pageclass_sfx = $params->get('pageclass_sfx');
?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<tbody><tr>
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
    <div id="header<?php echo $pageclass_sfx;?>">
      <h1 class="componentheading<?php echo $pageclass_sfx;?>">
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

        <tr class="bsodd">
          <td class="row1col1 bsdate" headers="bsdatehead">Mar 29, 2009</td>

          <td class="row1col2 bstitle" headers="bstitlehead"><a href="/if linked..."><span class="bstitle">A Great Party</span></a></td>
          <td class="row1col3 bsseries" headers="bsserieshead">Luke</td>
          <td class="row1col4 lastcol" rowspan="2" headers="bsmediahead">
            <table class="mediatable"><tbody>
              <tr>
                <td>
                  <a href="http://oicjapan.org/messages/luke/2009-03-29.mp3"
                  title="mp3 audio file 34:31 7.9 MB" target="_blank"><img
                  src="http://oicjapan.org/components/com_biblestudy/images/speaker24.png"
                  alt="mp3 34:31 7.9 MB" width="24" border="0" height="24"></a><br />
                  <span class="bsfilesize">7.9 MB</span>

                </td>
                <td>
                  <a href="http:///messages/luke/2009-03-29.pdf" 
                  title="PDF Document 34:31 237 KB" target="_blank"><img 
                  src="http://oicjapan.org/components/com_biblestudy/images/pdf24.png" 
                  alt="PDF 34:31 237 KB" width="24" border="0" height="24"></a><br />
                  <span class="bsfilesize">237 KB</span>
                </td>
              </tr>
            </tbody></table>
          </td>

        </tr>
        <tr class="bsodd">
          <td class="row2col1 bsscripture" headers="bsscripthead">Luke 5:27-39</td>
          <td class="row2col2 bsteacher" headers="bsteacherhead">Dan Ellrick</td>
          <td class="row2col3 bsduration" headers="bsdurhead">34:31</td>
        </tr>
        <tr class="bsodd lastrow">

          <td class="row3col1 bsdesc lastcol" colspan="4" headers="bsdeschead">
            <span class=>Jesus still speaks today saying, 'Follow me.' To those who say, 'Yes,' He brings a new miracle of freedom to live as we are meant to live, in fellowship with God.</span>
          </td>
        </tr>

        <tr class="bseven">
          <td class="row1col1 bsdate" headers="bsdatehead">Mar 22, 2009</span>
          </td>

          <td class="row1col2" headers="bstitlehead"><a href="/if linked..."><span class="bstitle">The Son of Man</span></a></td>
          <td class="row1col3 bsseries" headers="bsserieshead">Luke</td>
          <td class="row1col4 lastcol" rowspan="2" headers="bsmediahead">
            <table class="mediatable"><tbody>
              <tr>
                <td>
                  <a href="http://oicjapan.org/messages/luke/2009-03-22.mp3"
                  title="mp3 audio file 46:02 10.5 MB" target="_blank"><img
                  src="http://oicjapan.org/components/com_biblestudy/images/speaker24.png"
                  alt="mp3 46:02 10.5 MB" width="24" border="0" height="24"></a><br />
                  <span class="bsfilesize">10.5 MB</span>

                </td>
                <td>
                  <a href="http:///messages/luke/2009-03-22.pdf" 
                  title="PDF Document 46:02 213 KB" target="_blank"><img 
                  src="http://oicjapan.org/components/com_biblestudy/images/pdf24.png" 
                  alt="PDF 46:02 213 KB" width="24" border="0" height="24"></a><br />
                  <span class="bsfilesize">213 KB</span>
                </td>
              </tr>
            </tbody></table>
          </td>

        </tr>
        <tr class="bseven">
          <td class="row2col1 bsscripture" headers="bsscripthead">Luke 5:17-26</td>
          <td class="row2col2 bsteacher" headers="bsteacherhead">Dan Ellrick</td>
          <td class="row2col3 bsduration" headers="bsdyrhead">46:02</td>
        <tr class="bseven lastrow">
          <td class="row3col1 bsdesc lastcol" colspan="4" headers="bsdeschead">

            <span class=>Friends bring friends to Jesus, just as the paralyzed man's friends brought him. Jesus did a great miracle when He healed the paralyzed man, but the even greater miracle was that He forgave the man of his sins. The healing gave him life for a few years, the forgiving gave him live forever.</span>
          </td>
        </tr>
      </tbody>
    </table> <!--end of bslisttable-->

<div class="listingfooter<?php echo $pageclass_sfx;?>" >
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

