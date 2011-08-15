<?php

/**
 * @version $Id: biblestudy.listing.class.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();

class JBSListing
{
    
    function getOtherlinks($id3, $islink, $params)
    {
        $link = '';
        $db	= JFactory::getDBO();
        $query = 'SELECT #__bsms_mediafiles.* FROM #__bsms_mediafiles WHERE study_id = '.$id3.' AND #__bsms_mediafiles.published = 1';
        $db->setQuery( $query ); 
        $db->query();
        $num_rows = $db->getNumRows();
        if ($num_rows > 0)
        {
        $mediafiles = $db->loadObjectList(); 
            foreach ($mediafiles AS $media)
            {
            switch ($islink)
            {
                case 6:
                if ($media->article_id > 0)
                {$link = 'index.php?option=com_content&view=article&id='.$media->article_id;}
                break;
                
                case 7:
                if ($media->virtueMart_id > 0)
                {$link = 'index.php?option=com_virtuemart&page=shop.product_details&flypage='
                .$params->get('store_page', 'flypage.tpl').'&product_id='.$media->virtueMart_id;}
                break;
                
                case 8:
                if ($media->docMan_id > 0)
                {$link = 'index.php?option=com_docman&task=doc_download&gid='.$media->docMan_id;} 
                break;
            }
                
            }
        }
        return $link;
    }
       
}