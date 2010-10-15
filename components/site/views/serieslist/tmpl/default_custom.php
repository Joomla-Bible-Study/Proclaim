<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
$mainframe =& JFactory::getApplication();, $option;
JHTML::_('behavior.tooltip');
$series_menu = $this->params->get('series_id', 1);
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
$document->addStyleSheet(JURI::base().'components/com_biblestudy/assets/css/biblestudy.css');
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
      <?php

//dump ($params->get('series_wrapcode'), "Series Wrap");
  switch ($params->get('series_wrapcode')) {
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
  echo $params->get('series_headercode');
  
  foreach ($this->items as $row) { //Run through each row of the data result from the model
	 $listing = getSerieslistExp($row, $params, $this->admin_params, $this->template);
	 echo $listing;
 }
 
    switch ($params->get('series_wrapcode')) {
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
      //echo $this->pagination->getListFooter(); ?>
</div> <!--end of bsfooter div-->
  </div><!--end of bspagecontainer div-->
  <input name="option" value="com_biblestudy" type="hidden">
  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="serieslist" type="hidden">
  </div>
</form>
