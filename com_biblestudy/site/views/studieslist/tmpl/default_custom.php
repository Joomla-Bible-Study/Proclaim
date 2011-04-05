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
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$params = $this->params;
    
 	if ($this->allow)
			{?>
			<table><tr><td align="center"><?php echo '<h2>'.$message.'</h2>';?></td></tr></table>
			<?php
			$studiesedit_call = JView::loadHelper('studiesedit');
			$studiesedit = getStudiesedit($row, $params);
			echo $studiesedit;
			}

$listingcall = JView::loadHelper('listing');

$menuitemid = JRequest::getInt( 'Itemid' );
  if ($menuitemid)
  {
    $menu = JSite::getMenu();
    $menuparams = $menu->getParams( $menuitemid );
  }


?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->

    <div id="bsms_header">
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
<?php if ($params->get('listteachers') )
	{
	$teacher_call = JView::loadHelper('teacher');
	$teacher = getTeacher($params, $id=null, $this->admin_params);
	}

	?>
    </div><!--header-->

    <div id="listintro">
    <?php if ($params->get('intro_show') > 0) { echo $params->get('list_intro');}?>
    </div>
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
<?php

  switch ($params->get('wrapcode')) {
      case '0':
        //Do Nothing
        break;
      case 'T':
        //Table
        echo '<table id="bsms_studytable" width="100%">';
        break;
      case 'D':
        //DIV
        echo '<div>';
        break;
      }
  echo $params->get('headercode');


  foreach ($this->items as $row) { //Run through each row of the data result from the model


  $listing = '';
  if (($allow_entry > 0) && ($entry_access <= $entry_user)) {

    $listing .= "<tr><td style='background-color:#FAF1EB;' align=center>";
    $listing .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]='.$row->id.'"> ['.JText::_('JBS_CMN_EDIT').'] </a>';
    $listing .= "</td>";
    $listing .= "<td><table>";
  }
  $listing .= getListingExp($row, $params, $this->admin_params, $this->template);

  if (($allow_entry > 0) && ($entry_access <= $entry_user)) {
    $listing .= "</table></td></tr>";
  }

	echo $listing;
 }

    switch ($params->get('wrapcode')) {
      case '0':
        //Do Nothing
        break;
      case 'T':
        //Table
        echo '</table>';
        break;
      case 'D':
        //DIV
        echo '</div>';
        break;
      }
?>


      <div class="listingfooter" >
	<?php

      echo $this->pagination->getPagesLinks();
      echo $this->pagination->getPagesCounter();

	  ?>
</div> <!--end of bsfooter div-->
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>