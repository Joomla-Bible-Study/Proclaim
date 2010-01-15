<?php
defined('_JEXEC') or die('Restriced Access');
/**
 * @author Joomla Bible Study
 * @copyright 2010
 */

function updateSEF()
{
	$path1 = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'helpers'.DS;
	include_once($path1.'helper.php');
	$db = & JFactory::getDBO();
	$tn = '#__redirection';
	$fields = $db->getTableFields( array( $tn ) );
	$sef = false;
	$sef = isset( $fields[$tn]['id'] );
	if ($sef) 
		{
			$document =& JFactory::getDocument();
			$language = $document->getLanguage();
			$msg = array();
			$today = date("Y-m-d");
			$itemid = getItemidLink();
			if (!$itemid) {$itemid = 2;}
			//oldurl is sh404SEF url and newurl is the joomla url
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0',  'Biblestudy/studieslist.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=studieslist','".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'studieslist redirection table update error: '.$error;
			}
			
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/studydetails.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=studydetails', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'studydetails redirection table update error: '.$error;
			}
		
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/teacherlist.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=teacherlist', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'teacherlist redirection table update error: '.$error;
			}
			
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/serieslist.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=serieslist','".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'serieslist redirection table update error: '.$error;
			}
		
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/seriesdetail.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=seriesdetail', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'seriesdetail redirection table update error: '.$error;
			}
		
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/teacheredit.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=teacheredit', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'teacheredit redirection table update error: '.$error;
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/teacherdisplay.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=teacherdisplay', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'teacherdisplay redirection table update error: '.$error;
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/commentsedit.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=commentsedit', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'commentsedit redirection table update error: '.$error;
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/commentslist.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=commentslist', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'commentslist redirection table update error: '.$error;
			}
	
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/landingpage.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=landingpage',  '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'landingpage redirection table update error: '.$error;
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/mediafilesedit.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=mediafilesedit',  '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'mediafilesedit redirection table update error: '.$error;
			}
	
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/podcastedit.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=podcastedit', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'podcastedit redirection table update error: '.$error;
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'Biblestudy/studiesedit.html','index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=studiesedit', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'studiesedit redirection table update error: '.$error;
			}
		
		}
		if ($msg)
{
	$messagetable = '<table>';
	foreach ($msg as $messages)
	{
		$messagetable .= '<tr><td>'.$messages.'</td></tr>';
	}
	$messagetable .= '</table>';
}

	//$msg = implode('|',$msg);
	return $messagetable;
	
}

?>