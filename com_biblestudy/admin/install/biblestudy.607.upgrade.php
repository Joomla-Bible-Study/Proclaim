<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined( '_JEXEC' ) or die('Restricted access');

class jbs607Install{
function upgrade607()
{
            $query = "ALTER TABLE #__bsms_studies ADD COLUMN media_hours VARCHAR(2) NULL AFTER studyintro";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN media_minutes VARCHAR(2) NULL AFTER media_hours";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN media_seconds VARCHAR(2) NULL AFTER media_minutes";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN secondary_reference TEXT NULL AFTER chapter_end";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN title VARCHAR(50) NULL AFTER teachername";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN phone VARCHAR(50) NULL AFTER title";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN email VARCHAR(100) NULL AFTER phone";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;
			
            $query = "ALTER TABLE #__bsms_teachers ADD COLUMN website VARCHAR(250) NULL AFTER email";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN information TEXT NULL AFTER website";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN image TEXT NULL AFTER information";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN imageh TEXT NULL AFTER image";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;
			
            $query = "ALTER TABLE #__bsms_teachers ADD COLUMN imagew TEXT NULL AFTER imageh";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN thumb TEXT NULL AFTER imagew";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN thumbw TEXT NULL AFTER thumb";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN thumbh TEXT NULL AFTER thumbw";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN short TEXT NULL AFTER thumbh";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN ordering INT(3) NULL AFTER short";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN catid INT(3) NULL default '1' AFTER ordering";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN list_show TINYINT(1) NOT NULL default '1' AFTER catid";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

$res = '<table><tr><td>Upgrade Joomla Bible Study to version 7.0.0</td></tr>';  
        
        $result_table = $res.$msg2.'</table>';

        return $result_table;
}
function performdb($query)
    {
        $db = JFactory::getDBO();
        $results = '';
        if (!$query){$results = "Error. No query found"; return $results;}
        $db->setQuery($query);
        $db->query();
        
        		if ($db->getErrorNum() != 0)
					{
						$error = "DB function failed with error number ".$db->getErrorNum()."<br /><font color=\"red\">";
						$error .= $db->stderr(true);
						$error .= "</font>";
						
					
					}
					else
					{
						$error = "";
						
					}
                    $results .= '<tr><td><div >'.$error.'<pre>';
                    $results .= $query.'</pre></div></td>';
        
        
       return $results;
    }
}
?>