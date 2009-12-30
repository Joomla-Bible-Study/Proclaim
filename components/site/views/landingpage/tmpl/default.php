<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
global $mainframe, $option;
JHTML::_('behavior.tooltip');
$database = & JFactory::getDBO();
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
//$document->addStyleSheet(JURI::base().'components/com_biblestudy'.DS.'tooltip.css');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
$params = $this->params;
//dump( $params, 'Variable Name' );
//dump ($this->admin_params);
	

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
 </div>
<?php 

$i = 1;

for ($i=1;$i<=7;$i++) {
      
  $showIt = $params->get('headingorder_'.$i);

  
  
  if ($params->get('show'.$showIt) == 1 )
    {
    	//Wrap each in a DIV...
  echo "<div id='bsms_landingpage_" . $showIt ."'>";
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
     
    }// End Switch
	  if ($heading) {echo $heading;}
	  echo "</div>";
  } 
  
} // End Loop

?>    
</div>
  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>

