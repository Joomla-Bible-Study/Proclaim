<?php
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php 
global $mainframe, $option;
JHTML::_('behavior.tooltip');
$database = & JFactory::getDBO();
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'helper.php');
$document =& JFactory::getDocument();
$document->addScript(JURI::base().'components/com_biblestudy/tooltip.js');
$showhide = getShowhide(); //dump ($showhide, 'showhide: ');
$document->addScriptDeclaration($showhide);
//$document->addStyleSheet(JURI::base().'components/com_biblestudy'.DS.'tooltip.css');
$stylesheet = JURI::base().'components/com_biblestudy/assets/css/biblestudy.css';
$document->addStyleSheet($stylesheet);
$params = $this->params;
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'image.php');
$d_path1 = ($this->admin_params->get('media_imagefolder') ? 'images/'.$this->admin_params->get('media_imagefolder') : 'components/com_biblestudy/images');
//dump( $params, 'Variable Name' );
//dump ($this->admin_params);
	

$listingcall = JView::loadHelper('listing');

?>
<form action="<?php echo str_replace("&","&amp;",$this->request_url); ?>" method="post" name="adminForm">

<!--<tbody><tr>-->
  <div id="biblestudy_landing" class="noRefTagger"> <!-- This div is the container for the whole page -->
  
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
    	
  
    ?>
    <tr><td>
    <div id="landing_item">
    <div id="landing_title">
<!--<h2>-->
  <?php echo $params->get($showIt.'label'); ?>
	
<?php
if ($params->get('landing'.$showIt.'limit')) 
{
	$images = new jbsImages();
	$showhide_tmp = $images->getShowHide($this->admin[0]->showhide); //dump ($this->admin[0]->showhide, 'showhideimage: ');
//	$d_image = ($this->admin[0]->showhide ? DS.$this->admin[0]->showhide : '/showhide.gif');
//	$d_path = $d_path1.$d_image;
//	$showhide_tmp = $images->getImagePath($d_path);
    $showhide_image = $showhide_tmp->path; //dump ($showhide_tmp, 'showhide_tmp');
	
	$showhideall = "<div id='showhide'><a class='showhideheading' ";
	$showhideall .=  'href="';
	$showhideall .= "javascript:ReverseDisplay(";
	$showhideall .= "'showhide".$showIt."'";
	$showhideall .= ')">';
	
	switch ($params->get('landing_hide', 0))
	{
		case 0:
		$showhideall .= ' <img src="'.JURI::base().$showhide_image.'" alt="'.JText::_('Show/Hide All '.$showIt).'" title="'.JText::_('Show/Hide All '.$showIt).'" border="0" width="'.$showhide_tmp->width.'" height="'.$showhide_tmp->height.'">';
		break;
		
		case 1:
		$showhideall .= ' <img src="'.JURI::base().$showhide_image.'" alt="'.JText::_('Show/Hide All '.$showIt).'" title="'.JText::_('Show/Hide All '.$showIt).'" border="0" width="'.$showhide_tmp->width.'" height="'.$showhide_tmp->height.'"> '.'<span id="landing_label">'.$params->get('landing_hidelabel').'</span>';
		break;
		
		case 2:
		$showhideall .= '<span id="landing_label" >'.$params->get('landing_hidelabel').'</span>';
		break;
	} 
	
	$showhideall .= '</a></div>';
	echo $showhideall;
}
?><!--</h2>--></div><div id="landinglist"><?php
			
    $heading_call = null;
    $heading = null;
	  switch ($showIt) {
      case 'teacher':
      
      $heading_call = JView::loadHelper('teacher');  
      $heading = getTeacherLandingPage($params, $id=null, $this->admin_params);
        //echo "</div>";
      break;
      
      case 'series':
        $heading_call = JView::loadHelper('serieslist');
        $heading = getSeriesLandingPage($params, $id=null, $this->admin_params);
        //echo "</div>";
        break;
      
      case 'locations':
       	$heading_call = JView::loadHelper('location');
      	$heading = getLocationsLandingPage($params, $id=null, $this->admin_params);
      	//echo "</div>";
        break;
      
      case 'messagetype':
       	$heading_call = JView::loadHelper('messagetype');
      	$heading = getMessageTypesLandingPage($params, $id=null, $this->admin_params);
      	//echo "</div>";
        break;
      
      case 'topics':
         	$heading_call = JView::loadHelper('topics');
        	$heading = getTopicsLandingPage($params, $id=null, $this->admin_params);
        //	echo "</div>";
      break;
      
      case 'book':
       	$heading_call = JView::loadHelper('book');
	      $heading = getBooksLandingPage($params, $id=null, $this->admin_params);
	      //echo "</div>";
        break;
         
      case 'years':
       	$heading_call = JView::loadHelper('year');
	      $heading = getYearsLandingPage($params, $id=null, $this->admin_params);
	      //echo "</div>";
        break;
     
    }// End Switch
	  if ($heading) {echo $heading;}
	  ?></div><?php
  } 
  ?></div><?php
} // End Loop

?>   
</div>
  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>

