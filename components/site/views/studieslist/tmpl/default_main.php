
<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php

$mainframe =& JFactory::getApplication(); $option = JRequest::getCmd('option');
$message = JRequest::getVar('msg');
$database = & JFactory::getDBO();
$teacher_menu = $this->params->get('teacher_id');
$topic_menu = $this->params->get('topic_id');
$book_menu = $this->params->get('booknumber');
$location_menu = $this->params->get('locations');
$series_menu = $this->params->get('series_id');
$messagetype_menu = $this->params->get('messagetype');
//$params = $mainframe->getPageParameters();
$document =& JFactory::getDocument();
//$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$params = $this->params;
$teachers = $params->get('teacher_id');
if ($this->allow)
			{?>
			<table><tr><td align="center"><?php echo '<h2>'.$message.'</h2>';?></td></tr></table>
			<?php
			$studiesedit_call = JView::loadHelper('studiesedit');
			$studiesedit = getStudiesedit($row = null, $params);
			echo $studiesedit;
			}

$listingcall = JView::loadHelper('listing');

?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->

    <div id="bsheader">
      <h1 class="componentheading">
<?php
     if ($this->params->get( 'show_page_image' ) >0) {

     ?>
      <img src="<?php echo JURI::base().$this->main->path;?>" alt="<?php echo $this->main->path; ?>" width="<?php echo $this->main->width;?>" height="<?php echo $this->main->height;?>" alt="Bible Study" />
    <?php //End of column for logo
    }
    ?>
    <?php
if ( $this->params->get( 'show_page_title' ) >0 ) {
    echo $this->params->get('page_title');
    }
	?>
      </h1>
<?php

if ($params->get('listteachers') && $params->get('list_teacher_show') > 0)
	{
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($params, $id=null, $this->admin_params);
	if ($teacher) {echo $teacher;}
	}

	?>
    </div><!--header-->

    <div id="listintro"><table id="listintro"><tr><td><p>
    <?php if ($params->get('intro_show') == 1) { echo $params->get('list_intro');}?>
   </p></td></tr></table> </div>
    <div id="bsdropdownmenu">
    <?php if ($this->params->get('use_go_button') > 0)
        {
            ?><span id="gobutton"><input type="submit" value="<?php echo JText::_('JBS_STY_GO_BUTTON'); ?>" /></span>
    <?php }


if (($this->params->get('show_locations_search') > 0 && !($location_menu)) || $this->params->get('show_locations_search') > 1) { echo $this->lists['locations'];}
if (($this->params->get('show_book_search') > 0 && $book_menu == -1) || $this->params->get('show_book_search') > 1)
    {
        echo $this->lists['books'] .' ';
        echo JText::_('JBS_STY_FROM_CHAPTER').' <input type="text" id="minChapt" name="minChapt" size="3"';
        if (JRequest::getInt('minChapt','','post')) {
            echo 'value="'.JRequest::getInt('minChapt','','post').'"';
        }
        echo '> ';
        echo JText::_('JBS_STY_TO_CHAPTER').' <input type="text" id=maxChapt" name="maxChapt" size="3"';
        if (JRequest::getInt('maxChapt','','post')) {
            echo 'value="'.JRequest::getInt('maxChapt','','post').'"';
        }
        echo '> ';
    }
if (($this->params->get('show_teacher_search') > 0 && ($teacher_menu == -1)) || $this->params->get('show_teacher_search') > 1) { echo $this->lists['teacher_id'];  }
if (($this->params->get('show_series_search') > 0 && ($series_menu == -1)) || $this->params->get('show_series_search') > 1) { echo $this->lists['seriesid'];  }
if (($this->params->get('show_type_search') > 0 && ($messagetype_menu == -1)) || $this->params->get('show_type_search') > 1) { echo $this->lists['messagetypeid'];  }
if ($this->params->get('show_year_search') > 0) { echo $this->lists['studyyear'];  }
if ($this->params->get('show_order_search') > 0) { echo $this->lists['orders'];}
if (($this->params->get('show_topic_search') > 0 && ($topic_menu == -1)) || $this->params->get('show_topic_search') > 1) {  echo $this->lists['topics'];}
if ($this->params->get('show_popular') > 0 ) {  echo $this->popular;}

?>


    </div><!--dropdownmenu-->
     <table id="bslisttable" cellspacing="0">
     <?php

     $headerCall = JView::loadHelper('header');
     $header = getHeader($this->items[0], $params, $this->admin_params, $this->template, $showheader = $params->get('use_headers_list'), $ismodule=0);
	 echo $header;
     ?>
      <tbody>

        <?php
 //This sets the alternativing colors for the background of the table cells
 $class1 = 'bsodd';
 $class2 = 'bseven';
 $oddeven = $class1;
//dump ($this->items,'items: ');
 foreach ($this->items as $row) { //Run through each row of the data result from the model
	if($oddeven == $class1){ //Alternate the color background
	$oddeven = $class2;
	} else {
	$oddeven = $class1;
	}

	$listing = getListing($row, $params, $oddeven, $this->admin_params, $this->template, $ismodule=0);
 	echo $listing;
   // dump ($listing, 'listing: ');
 }
 ?>
 </tbody></table>
<div class="listingfooter" >
	<?php

    //  echo $this->pagination->getPagesLinks();
    //  echo $this->pagination->getPagesCounter();
      echo $this->pagination->getListFooter();
      echo $this->pagination->getPagesLinks();

	  ?>
</div> <!--end of bsfooter div-->
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>