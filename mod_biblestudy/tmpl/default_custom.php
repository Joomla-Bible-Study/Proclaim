<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die(); 

$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once($path1.'listing.php'); 
?>
<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
<?php 
switch ($params->get('module_wrapcode')) {
      case '0':
        //Do Nothing
        break;
      case 'T':
        //Table
        echo '<table id="bsmsmoduletable" width="100%">';
        break;
      case 'D':
        //DIV
        echo '<div class="bsmsmoduletable">';
        break;
      }
      
  if ($params->get('module_headercode')){echo $params->get('module_headercode');}
  else
  {
    include_once($path1.'header.php');
    include_once($path1.'helper.php');
    $header = getHeader($list[0], $params, $admin_params, $templatemenuid, $params->get('use_headers'), $ismodule);
    echo $header;
  }
  
foreach ($list as $row)
{
    $listing = getListingExp($row, $params, $admin_params, $templatemenuid);
   	echo $listing; 
}

switch ($params->get('module_wrapcode')) {
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
</div>

<div class="modulelistingfooter"><br />
    <?php $link_text = $params->get('pagetext', 'More Bible Studies');
			
			if ($params->get('show_link') > 0){
					$t = $params->get('t');
					if (!$t) {$t = JRequest::getVar('t',1,'get','int');}
					//$addItemid = getItemidLink($isplugin = 0, $admin_params);
					$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t='.$t);?>
			<a href="<?php echo $link;?>"> <?php echo $link_text.'<br />'; ?> </a> <?php } //End of if view_link not 0?>
</div><!--end of footer div-->