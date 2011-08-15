<?php

/**
 * @version $Id: store.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();

function getStore($params, $row)
{

 	$mainframe =& JFactory::getApplication();
	$database	= & JFactory::getDBO();
	$query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
    .' s.image_cd, s.prod_cd, s.server_cd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_cd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_cd )
                        WHERE s.id ='.$row->id;
    $database->setQuery($query);
    $cd = $database->loadObject();
	$query = 'SELECT m.media_image_name, m.media_alttext, m.media_image_path, m.id AS mid, s.id AS sid,'
    .' s.image_dvd, s.prod_dvd, s.server_dvd, sr.id AS srid, sr.server_path
                        FROM #__bsms_studies AS s
                        LEFT JOIN #__bsms_media AS m ON ( m.id = s.image_dvd )
                        LEFT JOIN #__bsms_servers AS sr ON ( sr.id = s.server_dvd )
                        WHERE s.id ='.$row->id;
    $database->setQuery($query);
    $dvd = $database->loadObject();
    $store = '<table id="detailstable"><tr><td>';
	if (($cd->mid + $dvd->mid) > 0) 
	{
		if ($cd->mid > 0)
		{
      		$src = JURI::base().$cd->media_image_path;
      		if ($imagew) {$width = $imagew;} else {$width = 24;}
      		if ($imageh) {$height = $imageh;} else {$height= 24;}
		$store .='<a href="'.$cd->server_path.$cd->prod_cd.'" title="'.$cd->media_alttext.'"><img src="'.JURI::base().$cd->media_image_path.'" width="'.$width.'" height="'.$height.'" alt="'.$cd->media_alttext.' "border="0" /></a></td>';
    	}
    
		if ($dvd->mid > 0)
		{
		   $src = JURI::base().$dvd->media_image_path;
		   if ($imagew) {$width = $imagew;} else {$width = 24;}
		   if ($imageh) {$height = $imageh;} else {$height= 24;}
		$store .='<td><a href="'.$dvd->server_path.$dvd->prod_dvd.'" title="'.$dvd->media_alttext.'"><img src="'.JURI::base().$dvd->media_image_path.'" width="'.$width.'" height="'.$height.'" alt="'.$dvd->media_alttext.' "border="0" /></a></td></tr><tr><td colspan="2" align="center"><span'.$params->get('store_span').$params->get('store_name').'</span></td>';
		}
	}
	$store .= '</tr></table>';
return $store;
}