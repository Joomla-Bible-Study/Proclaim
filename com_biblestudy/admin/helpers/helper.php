<?php defined('_JEXEC') or die('Restriced Access');
require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
/**
 * @author Joomla Bible Study
 * @copyright 2009
 * @desc This is a general helper file. In the future we should bring lots of small functions into this file
 */


function getAdminsettings()
	{
			$db =& JFactory::getDBO();
		//	$query = 'SELECT *'
		//	. ' FROM #__bsms_admin'
		//	. ' WHERE id = 1';
		//	$adminsettings = $db->loadAssoc();
		//	$admin_params = null;
		//	$admin_params = new JParameter($adminsettings['params']); 
		
	//	ToDo: A better way to access parameters. maybe use the model/table from admin?
	//	jimport( 'joomla.application.component.view' );
	//	jimport( 'joomla.application.component.model' );
	
		$db->setQuery ("SELECT params FROM #__bsms_admin WHERE id = 1");
		$db->query();
		$compat = $db->loadObject();
		$admin_params = new JParameter($compat->params);
					
		return $admin_params;
	}

function getTooltip($rowid, $row, $params, $admin_params, $template)
	{
		$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
		include_once($path1.'elements.php');
		
		//Tom added the below because tooltip wasn't working as of 6.1.1
		$toolTipArray = array('className' => 'custom', 'showDelay'=>'500', 
		'hideDelay'=>'500', 'fixed'=>true,
		'onShow'=>"function(tip) {tip.effect('opacity', 
 		{duration: 500, wait: false}).start(0,1)}", 
		'onHide'=>"function(tip) {tip.effect('opacity', 
		{duration: 500, wait: false}).start(1,0)}");
		JHTML::_('behavior.tooltip', '.hasTip', $toolTipArray); 

		//$toolTipArray = array('className'=>'custom');
		//JHTML::_('behavior.mootools');
		//JHTML::_('behavior.tooltip', '.zoomTip', $toolTipArray);

        $linktext = '<span class="zoomTip" title="<strong>'.$params->get('tip_title').'  :: ';
       	$tip1 = getElementid($params->get('tip_item1'), $row, $params, $admin_params, $template);  
		$tip2 = getElementid($params->get('tip_item2'), $row, $params, $admin_params, $template);
		$tip3 = getElementid($params->get('tip_item3'), $row, $params, $admin_params, $template);
		$tip4 = getElementid($params->get('tip_item4'), $row, $params, $admin_params, $template);
		$tip5 = getElementid($params->get('tip_item5'), $row, $params, $admin_params, $template);
		$test = $params->get('tip_item1');
		//dump ($test, 'tip1: ');
		$linktext .= '<strong>'.$params->get('tip_item1_title').'</strong>: '.$tip1->element.'<br />';
		$linktext .= '<strong>'.$params->get('tip_item2_title').'</strong>: '.$tip2->element.'<br /><br />';
		$linktext .= '<strong>'.$params->get('tip_item3_title').'</strong>: '.$tip3->element.'<br />';
		$linktext .= '<strong>'.$params->get('tip_item4_title').'</strong>: '.$tip4->element.'<br />';
		$linktext .= '<strong>'.$params->get('tip_item5_title').'</strong>: '.$tip5->element;
 		$linktext .= '">';
	return $linktext;	
	}
	
	function getShowhide ()
	{
		$showhide = '
function HideContent(d) {
document.getElementById(d).style.display = "none";
}
function ShowContent(d) {
document.getElementById(d).style.display = "block";
}
function ReverseDisplay(d) {
if(document.getElementById(d).style.display == "none") { document.getElementById(d).style.display = "block"; }
else { document.getElementById(d).style.display = "none"; }
}
';

		return $showhide;
	}

?>