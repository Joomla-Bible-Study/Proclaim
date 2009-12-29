<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
global $mainframe, $option;
JHTML::_('behavior.tooltip');
$message = JRequest::getVar('msg');
$database = & JFactory::getDBO();
$teacher_menu = $this->params->get('teacher_id', 1);
$topic_menu = $this->params->get('topic_id', 1);
$book_menu = $this->params->get('booknumber', 101);
$location_menu = $this->params->get('locations', 1);
$series_menu = $this->params->get('series_id', 1);
$messagetype_menu = $this->params->get('messagetype', 1);
//$params = $mainframe->getPageParameters();
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
$params = $this->params;
//dump( $params, 'Variable Name' );
//dump ($this->admin_params);
	$user =& JFactory::getUser();
	$entry_user = $user->get('gid');
	if (!$entry_user) { $entry_user = 0;}
	$entry_access = $this->admin_params->get('entry_access');
	if (!$entry_access) {$entry_access = 23;}
	$allow_entry = $this->admin_params->get('allow_entry_study');
	//dump ($entry_access, 'entry_access: ');
	if (($allow_entry > 0) && ($entry_access <= $entry_user)) 
			{?>
			<table><tr><td align="center"><?php echo '<h2>'.$message.'</h2>';?></td></tr></table>
			<?php 
			$studiesedit_call = JView::loadHelper('studiesedit');
			$studiesedit = getStudiesedit($row, $params);
			echo $studiesedit;
			}

$listingcall = JView::loadHelper('listing');

?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
    <div id="bsms_header">
      <h1 class="componentheading">
<?php
     if ($this->params->get( 'show_page_image' ) >0) {
     
     ?>
      <img src="<?php echo JURI::base().$this->main->path;?>" alt="<?php echo $this->main->path; ?>" width="<?php echo $this->main->width;?>" height="<?php echo $this->main->height;?>" />
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

$i = 1;

for ($i=1;$i<=7;$i++) {
      
  $showIt = $params->get('headingorder_'.$i);

  //Wrap each in a DIV...
  echo "<div id='bsms_landingpage_" . $showIt ."'>";
  
  if ($params->get('show'.$showIt) == 1 )
    {
    ?>
<h2 class="bsms_landingpage_title">
  <?php echo $params->get($showIt.'label'); ?>
</h2>
<?php
    $heading_call = null;
    $heading = null;
	  switch ($showIt) {
      case 'teacher':
      
      $heading_call = JView::loadHelper('teacher');  
      $heading = getTeacherLandingPage($params, $id=null, $this->admin_params);
      break;
      
      case 'series':
        $heading_call = JView::loadHelper('serieslist');
        $heading = getSeriesLandingPage($params, $id=null, $this->admin_params);
        break;
      
      case 'locations':
       	$heading_call = JView::loadHelper('location');
      	$heading = getLocations($params, $id=null, $this->admin_params);
        break;
      
      case 'messagetype':
       	$heading_call = JView::loadHelper('messagetype');
      	$heading = getMessageTypes($params, $id=null, $this->admin_params);
        break;
      
      case 'topics':
         	$heading_call = JView::loadHelper('topics');
        	$heading = getTopics($params, $id=null, $this->admin_params);
      break;
      
      case 'book':
       	$heading_call = JView::loadHelper('book');
	      $heading = getBooks($params, $id=null, $this->admin_params);
        break;
         
      case 'years':
       	$heading_call = JView::loadHelper('year');
	      $heading = getYears($params, $id=null, $this->admin_params);
        break;
     
    }
	  if ($heading) {echo $heading;}
  } // End Switch
  echo "</div>";
} // End Loop

?>    

  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>

