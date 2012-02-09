<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
//No Direct Access
defined('_JEXEC') or die;
?>
	<div class="listingfooter"><br />
    <?php $link_text = $this->params->get('link_text');
			if (!$link_text) {
				$link_text = JText::_('JBS_STY_RETURN_STUDIES_LIST');
			}
			if ($this->params->get('view_link') > 0){
					$t = $this->params->get('studieslisttemplateid');
					if (!$t) {$t = JRequest::getVar('t',1,'get','int');}
					if (!isset($returnmenu)) {$returnmenu = 1;}
			$Itemid = JRequest::getVar('Itemid','','get');
			if (!$Itemid)
				{
		 	$link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t='.$t);}
			 else
			 {
			 $link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t='.$t);
			 }?>
			<a href="<?php echo $link;?>"> <?php echo $link_text; ?> </a> <?php } //End of if view_link not 0?>
    </div><!--end of footer div-->