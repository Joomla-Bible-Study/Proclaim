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
foreach ($this->items as $study)
{
    echo $study->studytitle.'<br />';
}
