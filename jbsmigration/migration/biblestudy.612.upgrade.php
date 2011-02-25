<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined('_JEXEC') or die();
class JBS612Install{
    
function upgrade612()
{
$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';

$query ="UPDATE #__bsms_mediafiles SET params = 'player=2', internal_viewer = '0' WHERE internal_viewer = '1' AND params IS NULL";
$msg = $this->performdb($query);
	
//Let's check to see if there is a css file - if not, we'll copy one over
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		if (!JFile::copy($src, $dest))
		{
			$msg = false;
		}
		else
		{
			$msg = true;
		}
		
	}
$application->enqueueMessage( ''. JText::_('Upgrading from build 612') .'' ) ;
return $msg;
}

function performdb($query)
    {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->query();
        
		if ($db->getErrorNum() != 0)
			{
				$results = false; return $results;
			}
			else
			{
				$results = true; return $results;
            }
    }
}
?>