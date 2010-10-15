<?php
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.images.class.php');
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); ?>

<?php
$mainframe =& JFactory::getApplication();, $option;
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
	echo "\n";?>
      </h1>
    </div> <!-- End div id="bsms_header" -->

<?php

$i = 1;

for ($i=1;$i<=7;$i++) {

  $showIt = $params->get('headingorder_'.$i);

  if ($params->get('show'.$showIt) == 1 )
    {
    	//Wrap each in a DIV...

?>

    <div id="landing_item">
      <div id="landing_title">
      <?php echo $params->get($showIt.'label'); echo "\n"; ?>
      </div> <!-- end div id="landing_title" -->
<?php
  if ($params->get('landing'.$showIt.'limit'))
  {
	$images = new jbsImages();
	$showhide_tmp = $images->getShowHide($this->admin[0]->showhide); //dump ($this->admin[0]->showhide, 'showhideimage: ');
//	$d_image = ($this->admin[0]->showhide ? DS.$this->admin[0]->showhide : '/showhide.gif');
//	$d_path = $d_path1.$d_image;
//	$showhide_tmp = $images->getImagePath($d_path);
    $showhide_image = $showhide_tmp->path; //dump ($showhide_tmp, 'showhide_tmp');

	$showhideall = "      <div id='showhide'>";

	$buttonlink = "\n\t".'<a class="showhideheadingbutton" href="javascript:ReverseDisplay('."'showhide".$showIt."'".')">';
	$labellink = "\n\t".'<a class="showhideheadinglabel" href="javascript:ReverseDisplay('."'showhide".$showIt."'".')">';

	switch ($params->get('landing_hide', 0))
	{
		case 0:         // image only
		$showhideall .= $buttonlink;
                $showhideall .= "\n\t\t".'<img src="'.JURI::base().$showhide_image.'" alt="'.JText::_('Show/Hide All').' '.JText::_($showIt).'" title="'.JText::_('Show/Hide All').' '.JText::_($showIt).'" border="0" width="'.$showhide_tmp->width.'" height="'.$showhide_tmp->height.'">';
		$showhideall .= ' '; // spacer
		$showhideall .= "\n\t".'</a>';
		break;

		case 1:         // image and label
		$showhideall .= $buttonlink;
                $showhideall .= "\n\t\t".'<img src="'.JURI::base().$showhide_image.'" alt="'.JText::_('Show/Hide All').' '.JText::_($showIt).'" title="'.JText::_('Show/Hide All').' '.JText::_($showIt).'" border="0" width="'.$showhide_tmp->width.'" height="'.$showhide_tmp->height.'">';
		$showhideall .= ' '; // spacer
		$showhideall .= "\n\t".'</a>';
		$showhideall .= $labellink;
		$showhideall .= "\n\t\t".'<span id="landing_label">'.$params->get('landing_hidelabel').'</span>';
		$showhideall .= "\n\t".'</a>';
		break;

		case 2:         // label only
		$showhideall .= $labellink;
		$showhideall .= "\n\t\t".'<span id="landing_label">'.$params->get('landing_hidelabel').'</span>';
		$showhideall .= "\n\t".'</a>';
		break;
	}

	$showhideall .= "\n".'      </div> <!-- end div id="showhide" for '.$i.' -->'."\n";
	echo $showhideall;
}
?>
      <div id="landinglist">
<?php

    $heading_call = null;
    $heading = null;
    switch ($showIt) {

      case 'teachers':
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

      case 'messagetypes':
        $heading_call = JView::loadHelper('messagetype');
        $heading = getMessageTypesLandingPage($params, $id=null, $this->admin_params);
        //echo "</div>";
        break;

      case 'topics':
        $heading_call = JView::loadHelper('topics');
        $heading = getTopicsLandingPage($params, $id=null, $this->admin_params);
        //	echo "</div>";
        break;

      case 'books':
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
    if ($heading) {
      echo $heading;
    }
    echo "\n".'      </div> <!-- end div id="landinglist" '.$i." -->";
    echo "\n";
?>
    </div> <!-- end div id="landing_item" <?php echo $i; ?> -->
<?php
  }
} // End Loop for the landing items

?>
</div> <!-- end div id="biblestudy_landing" -->
  <input name="option" value="com_biblestudy" type="hidden">

  <input name="task" value="" type="hidden">
  <input name="boxchecked" value="0" type="hidden">
  <input name="controller" value="studieslist" type="hidden">
</form>
