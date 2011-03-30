
<?php defined('_JEXEC') or die('Restriced Access'); ?>
<?php
$show_link = $params->get('show_link',1);
$pagetext = $params->get('pagetext');
$document =& JFactory::getDocument();
$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css');
//$document->addStyleSheet(JURI::base().'components'.DS.'com_biblestudy'.DS.'tooltip.css');
$url = $params->get('stylesheet');
$ismodule = 1;
if ($url) {$document->addStyleSheet($url);}
?>
<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
<table id="bsmsmoduletable" cellspacing="0">
	<?php 
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy/helpers/';
    include_once($path1.'header.php');
    include_once($path1.'helper.php');
    include_once($path1.'listing.php');
    $header = getHeader($list[0], $params, $admin_params, $template, $params->get('use_headers'), $ismodule);
    //dump ($list);
    //header = getHeader($this->items[0], $params, $this->admin_params, $this->template, $showheader = $params->get('use_headers_list'));
    echo $header;
   ?>

<tbody>
<?php	
 $class1 = 'bsodd';
 $class2 = 'bseven';
 $oddeven = $class1;
foreach ($list as $study) {
	if($oddeven == $class1){ //Alternate the color background
	$oddeven = $class2;
	} else {
	$oddeven = $class1;
	}
	
//	print_r ($study);
	$listing = getListing($study, $params, $oddeven, $admin_params, $template, $ismodule);
 	echo $listing; 
	//modBiblestudyHelper::renderStudy($study, $params, $oddeven);
	}
	?>
	</tbody></table>
</div>	
<div class="modulelistingfooter"><br />
    <?php $link_text = $params->get('pagetext', 'More Bible Studies');
			
			if ($params->get('show_link') > 0){
					$t = $params->get('studielisttemplateid');
					if (!$t) {$t = JRequest::getVar('t',1,'get','int');}
					
					$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t='.$t);?>
			<a href="<?php echo $link;?>"> <?php echo $link_text.'<br />'; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->