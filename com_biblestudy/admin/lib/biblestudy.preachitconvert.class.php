<?php

/**
 * @author Tom Fuller
 * @copyright 2012
 * @since 7.1.0
 * @desc Method to convert from PreachIt to JBS
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class JBSPIconvert
{

    function convertPI()
    {
        $piconversion = '<table>';
        //first convert comments
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__picomments';
        $db->setQuery($query);
        $db->query();
        $picomments = $db->loadObjectList();
        if (!$picomments){$piconversion .= '<tr><td>'.JText::_('JBS_ADM_NO_COMMENTS').'</td></tr>'; }
        else
        {
            foreach ($picomments AS $picomment)
            {
                $query = 'INSERT INTO #__bsms_comments SET `published` = "'.$picomment->published.'", `study_id` = "'.$picomment->study_id.
                '", `user_id` = "'.$picomment->user_id.'", `full_name` = "'.$picomment->full_name.'", `comment_date` = "'.$picomment->comment_date.'", `comment_text` = "'.$db->getEscaped($picomment->comment_text).'"';
                	$db->setQuery($query);
    			$db->query();
    			if ($db->getErrorNum() > 0)
    			{
    				$error = $db->getErrorMsg();
    				$piconversion .= '<tr><td>'.JText::_('JBS_ADM_ERROR_OCCURED_MIGRATING_COMMENTS').': '.$error.'</td></tr>';
    			}
    			else
    			{
    				$updated = 0;
    				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
    				$add = $add + $updated;
    			}
            }
        }
        //Create servers and folders
        $query = 'SELECT * FROM #__pifilepath';
        $db->setQuery($query);
        $db->query();
        $piservers = $db->loadObjectList();
        if (!$piservers)
        {
            $piconversion .= '<tr><td>'.JText::_('JBS_ADM_NO_SERVERS').'</td></tr>';
        }
        else
        {
            foreach ($piservers AS $pi)
            {
                
            }
        }
        $piconversion .= '</table>';
        return $piconversion;
    }
 
 
    function performdb($query) 
    {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() != 0) {
            $results = JText::_('JBS_IBM_DB_ERROR') . ': ' . $db->getErrorNum() . "<br /><font color=\"red\">";
            $results .= $db->stderr(true);
            $results .= "</font>";
            return $results;
        } else {
            $results = false;
            return $results;
        }
    }   
}


?>