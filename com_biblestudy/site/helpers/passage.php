<?php

/**
 * Passage Helper
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Get Passage
 * @param object $params
 * @param object $row
 * @return string
 */
function getPassage($params, $row) {
    $esv = 1;
    $scripturerow = 1;
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'scripture.php');
    $scripture = getScripture($params, $row, $esv, $scripturerow);
    if ($scripture) {
        $key = "IP";
        $response = "" . $scripture . " (ESV)";
        $passage = urlencode($scripture);
        $options = "include-passage-references=false";
        $url = "http://www.esvapi.org/v2/rest/passageQuery?key=$key&passage=$passage&$options";
        $p = (get_extension_funcs("curl")); // This tests to see if the curl functions are there. It will return false if curl not installed
        if ($p) { // If curl is installed then we go on
            $ch = curl_init($url); // This will return false if curl is not enabled
            if ($ch) { //This will return false if curl is not enabled
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response .= curl_exec($ch);
                curl_close($ch);
            } // End of if ($ch)
        } // End if ($p)
    } else {
        $response = JText::_('JBS_STY_NO_PASSAGE_INCLUDED');
    }

    return $response;
}