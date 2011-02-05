<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */
defined( '_JEXEC' ) or die('Restricted access');

class jbs608Install{
    
function upgrade608()
{
    $db = JFactory::getDBO();
    $query = "CREATE TABLE IF NOT EXISTS `#__bsms_comments` (
				`id` INT(3) NOT NULL AUTO_INCREMENT,
				`published` TINYINT(1) NOT NULL default '0',
				`study_id` INT(11) NOT NULL,
				`user_id` INT(11) NOT NULL,
				`full_name` VARCHAR(50) NOT NULL,
				`user_email` VARCHAR(100) NOT NULL,
				`comment_date` DATETIME NOT NULL,
				`comment_text` TEXT NOT NULL,
				PRIMARY KEY (`id`) ) TYPE=MyISAM CHARACTER SET `utf8`";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;   
             
			$query = "ALTER TABLE #__bsms_studies ADD COLUMN booknumber2 VARCHAR(4) NULL AFTER secondary_reference";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN chapter_begin2 VARCHAR(4) NULL AFTER booknumber2";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN verse_begin2 VARCHAR(4) NULL AFTER chapter_begin2";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN chapter_end2 VARCHAR(4) NULL AFTER verse_begin2";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN verse_end2 VARCHAR(4) NULL AFTER chapter_end2";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN prod_dvd VARCHAR(100) NULL AFTER verse_end2";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN prod_cd VARCHAR(100) NULL AFTER prod_dvd";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN server_cd VARCHAR(10) NULL AFTER prod_cd";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN server_dvd VARCHAR(10) NULL AFTER server_cd";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN image_cd VARCHAR(10) NULL AFTER server_dvd";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN image_dvd VARCHAR(10) NULL default '0' AFTER image_cd";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN studytext2 TEXT NULL AFTER image_dvd";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN comments TINYINT(1) NULL default '1' AFTER studytext2";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN hits INT(10) NOT NULL default '0' AFTER comments";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN user_id INT(10) NULL AFTER hits";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN user_name VARCHAR(50) NULL AFTER user_id";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN link_type VARCHAR(1) NULL AFTER createdate";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN hits INT(10) NULL AFTER link_type";
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