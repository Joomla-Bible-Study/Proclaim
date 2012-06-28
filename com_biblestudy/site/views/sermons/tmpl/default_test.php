<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<table><tr><td>This is the header</td></tr></table>
<?php

foreach ($this->study as $study) {
    echo $study->studydate . ' - <a href="' . $study->detailslink . '">' . $study->studytitle . '</a>' . $study->media . '<br />';
}
