<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Updates the CSS .media to .jbsmedia because some templates had media as a tag
 */
defined( '_JEXEC' ) or die('Restricted access');
$result_table = '<table><tr><td>This routine changes a CSS tag from .media to .jbsmedia to avoid collisions with some templates</td></tr>';
//This updates the mediafiles table to reflect the new way of associating files to podcasts

// This adds some css for the Landing Page

$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
$cssexists = JFile::exists($dest);
if ($cssexists)
{
	$cssread = JFile::read($dest);

	$cssread = str_replace('.media ','.jbsmedia ',$cssread);

	 
	$errcss = '';
	if (!JFile::write($dest, $cssread))
	{
		$result_table .= '<tr><td>There was a problem writing to the css file. Please contact customer support on JoomlaBibleStudy.org</td></tr>';
	}
	else
	{
		$result_table .= '<tr><td>CSS modified.</td></tr>';
	}

}


$result_table .= '</table>';
echo $result_table;
?>