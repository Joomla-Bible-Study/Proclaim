<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined( '_JEXEC' ) or die('Restricted access');

class jbs611Install{
    
function upgrade611()
{
$query = "CREATE TABLE IF NOT EXISTS `#__bsms_locations` (
					`id` INT NOT NULL AUTO_INCREMENT,
					`location_text` VARCHAR(250) NULL,
					`published` TINYINT(1) NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;   
			
            $query = "ALTER TABLE #__bsms_studies ADD COLUMN show_level varchar(100) NOT NULL default '0' AFTER user_name";
			$msg = $this->performdb($query);
            $msg2 = $msg2.$msg;   
            
            $query = "ALTER TABLE #__bsms_studies ADD COLUMN location_id INT(3) NULL AFTER show_level";
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