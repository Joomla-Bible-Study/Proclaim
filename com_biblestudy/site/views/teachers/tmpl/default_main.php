<?php
/**
 * Teachers view subset main
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'route.php');
$params = $this->params;
$t = $params->get('teachertemplateid');
if (!$t) {
    $t = JRequest::getVar('t', 1, 'get', 'int');
}
$admin_params = $this->admin_params;
?>
<div id="biblestudy" class="noRefTagger">
    <table id="bsm_teachertable_list" cellspacing="0" >
        <tbody>
            <tr class="titlerow">
                <td align="center" colspan="3" class="title" ><?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS')); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                    $class1 = 'bsodd';
                    $class2 = 'bseven';
                    $oddeven = $class1;
                foreach ($this->items as $item) 
                    {
                        if ($item->title) {
                            $teacherdisplay = $item->teachername . ' - ' . $item->title;
                        } else {
                            $teacherdisplay = $item->teachername;
                        }
                        if ($oddeven == $class1) { //Alternate the color background
                            $oddeven = $class2;
                        } else {
                            $oddeven = $class1;
                        }
                        ?>
                    
                        <tr class="<?php echo $oddeven; ?> ">
                            <td class="bsm_teacherthumbnail_list" ><?php if ($item->thumb || $item->teacher_thumbnail) { ?>
                                <?php echo $item->image; } ?>
                            </td>
                            <td class="bsm_teachername">
                                <table id="bsm_teachertable_list" cellspacing="0">
                                    <tr>
                                        <td>
                                            <a href="<?php echo $item->teacherlink; ?>"><?php echo $teacherdisplay; ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" class="bsm_short">
                                            <?php echo $item->short; ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    
            <?php } //end of foreach ?>
            </td>
         </tr>
        </tbody>
    </table>
    <div class="listingfooter" >
        <?php
        echo $this->page->pagelinks;
        echo $this->page->counter;
        ?>
    </div> <!--end of bsfooter div-->
</div>