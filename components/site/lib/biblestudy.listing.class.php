<?php

/**
 * @desc This is a class to retrieve a listing for the studylist table
 * @author Tom Fuller
 * @copyright 2010
 */

?>
<?php defined('_JEXEC') or die();

class JBSListing
{
    
    function getOtherlinks($id3, $islink, $params)
    {
        $link = '';
        $db	= JFactory::getDBO();
        $query = 'SELECT #__bsms_mediafiles.* FROM #__bsms_mediafiles WHERE study_id = '.$id3.' AND #__bsms_mediafiles.published = 1';
        $db->setQuery( $query ); 
        $db->query();
        $numrows = $db->getNumRows(); 
        if ($numrows > 0)
        {
            $media = $db->loadObject(); 
            
            switch ($islink)
            {
                case 6:
                $link = 'index.php?option=com_content&view=article&id='.$media->article_id;
                break;
                
                case 7:
                $link = 'index.php?option=com_virtuemart&page=shop.product_details&flypage='.
                $params->get('store_page', 'flypage.tpl').'&product_id='.$media->virtueMart_id;
                break;
                
                case 8:
                $link = 'index.php?option=com_docman&task=doc_download&gid='.$media->docMan_id;
                break;
            }
        }        
        
        return $link;
    }
    
    
    
}
?>