<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<table><tr><td>This is the header</td></tr></table>
<?php 
foreach ($this->study as $study)
{
    echo $study->studydate.' - <a href="'.$study->detailslink.'">'.$study->studytitle.'</a>'.$study->media.'<br />';
}
