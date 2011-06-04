<?php
/**
* @version $Id:biblestudy.stats.class.php  $
* Joomla Bible Study Component
* @package biblestudy
*
* @Copyright (C) 2010 Joomla Bible Study Team All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.JoomlaBibleStudy.org
*
* Based on Kunena & FireBoard Component
* @Copyright (C) 2006 - 2007 Best Of Joomla All rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.bestofjoomla.com
*
* Based on joomlaboard Component
* @copyright (C) 2000 - 2004 TSMF / Jan de Graaff / All Rights Reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author TSMF & Jan de Graaff
**/

// Dont allow direct linking
defined( '_JEXEC' ) or die('Restricted access');

/**
* Bible Study stats support class
* @package com_biblestudy
*/
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
include_once ($path1.'helper.php');
class jbStats {



function top_score_site($item)
	{
		$t = JRequest::getInt('t',1,'get');
   
		$admin_params = getAdminsettings();
		$limit = $admin_params->get('popular_limit','25');
		$top = '<select onchange="goTo()" id="urlList"><option value="">'.JText::_('JBS_CMN_SELECT_POPULAR_STUDY').'</option>';
		$final = array();
		$final2 = array();
  
		$db = &JFactory::getDBO();
		$db->setQuery('SELECT m.study_id, s.access, s.published AS spub, sum(m.downloads + m.plays) as added FROM #__bsms_mediafiles AS m 
		LEFT JOIN #__bsms_studies AS s ON (m.study_id = s.id)
			where m.published = 1 GROUP BY m.study_id');
		$format = $admin_params->get('format_popular','0');
	
		$db->query();

		$items = $db->loadObjectList(); 
  
    //check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user = JFactory::getUser();
        $groups	= $user->getAuthorisedViewLevels(); 
        $count = count($items);
        
        for ($i = 0; $i < $count; $i++)
        {
            
            if ($items[$i]->access > 1)
            {
               if (!in_array($items[$i]->access,$groups))
               {
                    unset($items[$i]); 
               } 
	        }
        }
      
	foreach ($items as $result)
		{
			$db->setQuery('SELECT #__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits, #__bsms_studies.id, 
            #__bsms_mediafiles.study_id from #__bsms_studies LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id) 
            WHERE #__bsms_mediafiles.study_id = '.$result->study_id);
			$db->query();
			$hits = $db->loadObject();
			if (!$hits->studytitle){$name = $hits->id;}else{$name = $hits->studytitle;}
			if ($format < 1){$total = $result->added + $hits->hits;}
            else $total = $result->added;
			$selectvalue = JRoute::_(JURI::base().'index.php?option=com_biblestudy&view=studydetails&id='.$hits->id.'&t='.$t.'&Itemid='.$item);
			$selectdisplay = '<strong>'.$name.'</strong> - '.JText::_('JBS_CMN_SCORE').': '.$total;
			$final2 = array('score'=>$total,'select'=> $selectvalue, 'display'=> $selectdisplay);
			$final[] = $final2;
		}
	rsort($final);
	array_splice($final,$limit);

	foreach ($final as $topscore)
		{

			$top .= '<option value="'.$topscore['select'].'">'.$topscore['display'].'</option>';

		}
		$top .= '</select>';
	return $top;

	}
}
