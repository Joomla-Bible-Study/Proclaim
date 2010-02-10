<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined( '_JEXEC' ) or die('Restricted access');
// This adds some css for the Landing Page
$result_table = '<table><tr><td>This routine adds some items to the css file for the Landing Page view';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';
$landingread = JFile::read($dest);
$landingexists = 1;
	$landingexists = substr_count($landingread,'#landinglist');
	if ($landingexists < 1)
	{
		$landing = '
/* Landing Page Items */
#landinglist {
	
}
#landing_label {
	
}
#landing_item {
	
}
#landing_title {
	
}
#biblestudy_landing {
	
}';
$landingwrite = $landingread.$landing;
			$errcss = '';
			if (!JFile::write($dest, $landingwrite))
			{
				$result_table .= '<tr><td>There was a problem writing to the css file. Please contact customer support on JoomlaBibleStudy.org</td></tr>';
			}
			else
			{
				$result_table .= '<tr><td>Landing Page CSS written to file.</td></tr>';
			}
}
	$result_table .= '</table>';
	echo $result_table;
?>