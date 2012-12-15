<?php
/**
 * Default Main
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers');

JHTML::_('behavior.tooltip');
$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');

$row = $this->item;
// @todo need to clean up old code.
JViewLegacy::loadHelper('listing');
JViewLegacy::loadHelper('share');
?>
<div id="bsmHeader">
    <?php
    if ($this->item->params->get('showpodcastsubscribedetails') == 1) {
        echo $this->subscribe;
    }
    if ($this->item->params->get('showrelated') == 1) {
        echo $this->related;
    }
    ?>
    <div class="buttonheading">

        <?php
        if ($this->item->params->get('show_print_view') > 0) {
            echo $this->page->print;
        }
        ?>
    </div>

    <?php
    //Social Networking begins here
    if ($this->item->admin_params->get('socialnetworking') > 0) {
        ?>
        <div id="bsms_share">
            <?php
            $social = getShare($this->detailslink, $row, $this->item->params, $this->item->admin_params);
            echo $this->page->social;
            ?>
        </div>
    <?php } //End Social Networking     ?>
    <?php
    if ($this->item->params->get('show_teacher_view') > 0) {
        JViewLegacy::loadHelper('teacher');
        $teacher = getTeacher($this->item->params, $row->teacher_id, $this->item->admin_params);
        echo $teacher;
        ?>
    </td>
    <td>
        <?php
    }
    if ($this->item->params->get('title_line_1') + $this->item->params->get('title_line_2') > 0) {
        JViewLegacy::loadHelper('title');
        $title = getTitle($this->item->params, $row, $this->item->admin_params, $this->template);
        echo $title;
    }
    ?>
</div><!-- header -->
<div>
    <table id="bsmsdetailstable" cellspacing="0">
        <thead>
            <?php
            if ($this->item->params->get('use_headers_view') > 0 || $this->item->params->get('list_items_view') < 1) {
                JViewLegacy::loadHelper('header');
                $header = getHeader($row, $this->item->params, $this->item->admin_params, $this->template, $showheader = $this->item->params->get('use_headers_view'), $ismodule = 0);
                echo $header;
            }
            ?>
        </thead>
        <tbody>
            <?php if ($this->item->params->get('list_items_view') == 1) { ?> <!-- Media table listing view -->
                        <?php
                        require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
                        $media = new jbsMedia();
                        $listing = $media->getMediaTable($row, $this->item->params, $this->item->admin_params);
                        echo $listing;
                        ?>
                <?php
            }
            if ($this->item->params->get('list_items_view') == 0) {
                ?><!-- List items view -->
                        <?php
                        $oddeven = 'bsodd';
                        $listing = getListing($row, $this->item->params, $oddeven, $this->item->admin_params, $this->template, $ismodule = 0);
                        echo $listing;
                        ?>
    <?php } ?>
        </tbody>
    </table>
    <?php
    echo $this->passage;
    if ($this->item->params->get('show_scripture_link') > 0) {
        ?>
        <div style="width:80%">
        <?php echo $this->article->studytext; ?>
        </div>
        <?php
    } else {
        echo $this->item->studytext;
    }
    ?>
    <?php
    if ($this->item->params->get('showrelated') == 2) {
        echo $this->related;
    }
    ?>
    <?php
    if ($this->item->params->get('showpodcastsubscribedetails') == 2) {
        echo $this->subscribe;
    }
    ?>
</div>