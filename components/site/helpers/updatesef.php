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
			
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=studieslist', 'Biblestudy/studieslist.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'studieslist redirection table update error: '.$error.' | ';
			}
			
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=studydetails', 'Biblestudy/studydetails.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'studydetails redirection table update error: '.$error.' | ';
			}
		
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=teacherlist', 'Biblestudy/teacherlist.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'teacherlist redirection table update error: '.$error.' | ';
			}
			
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=serieslist', 'Biblestudy/serieslist.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'serieslist redirection table update error: '.$error.' | ';
			}
		
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=seriesdetail', 'Biblestudy/seriesdetail.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'seriesdetail redirection table update error: '.$error.' | ';
			}
		
		$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=teacheredit', 'Biblestudy/teacheredit.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'teacheredit redirection table update error: '.$error.' | ';
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=teacherdisplay', 'Biblestudy/teacherdisplay.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'teacherdisplay redirection table update error: '.$error.' | ';
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=commentsedit', 'Biblestudy/commentsedit.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'commentsedit redirection table update error: '.$error.' | ';
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=commentslist', 'Biblestudy/commentslist.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'commentslist redirection table update error: '.$error.' | ';
			}
	
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=landingpage', 'Biblestudy/landingpage.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'landingpage redirection table update error: '.$error.' | ';
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=mediafilesedit', 'Biblestudy/mediafilesedit.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'mediafilesedit redirection table update error: '.$error.' | ';
			}
	
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=podcastedit', 'Biblestudy/podcastedit.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'podcastedit redirection table update error: '.$error.' | ';
			}
		
			$query = "INSERT INTO `#__redirection` (`id`, `cpt`, `rank`, `oldurl`, `newurl`, `dateadd`) VALUES (NULL, '0', '0', 'index.php?option=com_biblestudy&Itemid=".$itemid."&lang=".$language."&view=studiesedit', 'Biblestudy/studiesedit.html ', '".$today."');";
			$db->setQuery($query);
			$db->query();
			if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$msg[] = 'studiesedit redirection table update error: '.$error.' | ';
			}
		
		}
	$msg = implode('|',$msg);
	return $msg;
	
}

?>