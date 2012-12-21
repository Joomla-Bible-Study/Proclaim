<?php
/**
 * Default Custom
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

$user = JFactory::getUser();
$mainframe = JFactory::getApplication();
 $input = new JInput;
    $option = $input->get('option','','cmd');
$params = $this->params;

$t = $params->get('teachertemplateid');
if (!$t) {
    $t = $input->get('t', 1, 'int');
}
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
$admin_params = $this->admin_params;
include_once($path1 . 'image.php');

$listingcall = JViewLegacy::loadHelper('teacher');
?>
<div id="biblestudy" class="noRefTagger">
    <table id="bsm_teachertable" cellspacing="0">
        <tbody>
            <tr class="titlerow">
                <td align="center" colspan="3" class="title" >
                    <?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS')); ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php
    switch ($params->get('teacher_wrapcode')) {
        case '0':
            //Do Nothing
            break;
        case 'T':
            //Table
            echo '<table id="bsms_teachertable" width="100%">';
            break;
        case 'D':
            //DIV
            echo '<div>';
            break;
    }
    echo $params->get('teacher_headercode');


    foreach ($this->items as $row) { //Run through each row of the data result from the model
        $listing = getTeacherListExp($row, $params, $oddeven = 0, $this->admin_params, $t);
        echo $listing;
    }

    switch ($params->get('teacher_wrapcode')) {
        case '0':
            //Do Nothing
            break;
        case 'T':
            //Table
            echo '</table>';
            break;
        case 'D':
            //DIV
            echo '</div>';
            break;
    }
    ?>
    <div class="listingfooter" >
        <?php
        echo $this->pagination->getPagesLinks();
        echo $this->pagination->getPagesCounter();
        ?>
    </div> <!--end of bsfooter div-->
</div>