<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
global $mainframe, $option;
JHTML::_('behavior.tooltip');
$series_menu = $this->params->get('series_id', 1);
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.js');
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
$params = $this->params;
$url = $params->get('stylesheet');
if ($url) {$document->addStyleSheet($url);}	
$listingcall = JView::loadHelper('serieslist');

?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
    <div id="bsmHeader">
      <h1 class="componentheading">
<?php
     if ($this->params->get( 'show_page_image_series' ) >0) {
     
     ?>
      <img src="<?php echo JURI::base().$this->main->path;?>" alt="<?php echo $this->main->path; ?>" width="<?php echo $this->main->width;?>" height="<?php echo $this->main->height;?>" />
    <?php //End of column for logo
    }
    ?>
    <?php
if ( $this->params->get( 'show_series_title' ) >0 ) {
    echo $this->params->get('series_title');
    }
	?>
      </h1>
<!--header-->
    
    
    <div id="bsdropdownmenu">

<?php 

	
if ($this->params->get('search_series') > 0 ){ echo $this->lists['seriesid']; }   
	 
//if ($this->params->get('show_order_search') > 0) { echo $this->lists['orders'];}
  
?>


    </div><!--dropdownmenu-->
     <table id="seriestable" cellspacing="0">
      <tbody>

        <?php 
 //This sets the alternativing colors for the background of the table cells
 $class1 = 'bsodd';
 $class2 = 'bseven';
 $oddeven = $class1;

 foreach ($this->items as $row) { //Run through each row of the data result from the model
 	//echo '<table id="bslisttable" cellspacing="0">';
	if($oddeven == $class1){ //Alternate the color background
	$oddeven = $class2;
	} else {
	$oddeven = $class1;
	}

	$listing = getSerieslist($row, $params, $oddeven, $this->admin_params, $this->template, $view = 0);
	//dump ($listing, 'listing: ');
 	echo $listing;
 	
 	//echo '</table>';
 }
 ?>
 </tbody></table>
<div class="listingfooter" >
	<?php 
      
      echo $this->pagination->getPagesLinks();
      echo $this->pagination->getPagesCounter();
      //echo $this->pagination->getListFooter(); ?>
</div> <!--end of bsfooter div-->
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">
  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="serieslist" type="hidden">
  </div>
</form>

