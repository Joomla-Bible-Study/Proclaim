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

$teacher_menu1 = $this->params->get('teacher_id');
$teacher_menu = $teacher_menu1[0];
$topic_menu1 = $this->params->get('topic_id');
$topic_menu = $topic_menu1[0];
$book_menu1 = $this->params->get('booknumber');
$book_menu = $book_menu1[0];
$location_menu1 = $this->params->get('locations');
$location_menu = $location_menu1[0];
$series_menu1 = $this->params->get('series_id');
$series_menu = $series_menu1[0];
$messagetype_menu1 = $this->params->get('messagetype');
$messagetype_menu = $messagetype_menu1[0];
$params = $this->params;
// @todo are we using these $teachers & $listingcall
$teachers = $params->get('teacher_id');
$listingcall = JView::loadHelper('listing');
?>
<div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page -->
    <div id="bsheader">
        <?php
        if ($this->params->get('showpodcastsubscribelist') == 1) {
            echo $this->subscribe;
        }
        ?>
        <h1 class="componentheading">
            <?php
            if ($this->params->get('show_page_image') > 0) {
                ?>
                <img src="<?php echo JURI::base() . $this->main->path; ?>" alt="<?php echo $this->params->get('page_title'); ?>" width="<?php echo $this->main->width; ?>" height="<?php echo $this->main->height; ?>" />
                <?php
                //End of column for logo
            }
            ?>
            <?php
            if ($this->params->get('show_page_title') > 0) {
                echo $this->params->get('page_title');
            }
            ?>
        </h1>
        <?php
        if ($params->get('listteachers') && $params->get('list_teacher_show') > 0) {
            JView::loadHelper('teacher');
            $teacher = getTeacher($params, $id = null, $this->admin_params);
            if ($teacher) {
                echo $teacher;
            }
        }
        ?>
    </div><!--header-->

    <div id="listintro">
        <p>
            <?php
            if ($params->get('intro_show') == 1) {
                echo $params->get('list_intro');
            }
            ?>
        </p>
    </div>
    <fieldset id="filter-bar">
        <div class="filter-select fltrt">
            <?php
            if ($this->params->get('use_go_button') > 0) {
                echo $this->page->gobutton;
            }

            if ($this->params->get('show_pagination') == 1) {
                echo '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
            }
            if (($this->params->get('show_locations_search') > 0 && ($location_menu == -1)) || $this->params->get('show_locations_search') > 1) {
                echo $this->page->locations;
            }
            if (($this->params->get('show_book_search') > 0 && $book_menu == -1) || $this->params->get('show_book_search') > 1) {
                echo $this->page->books;
            }
            if (($this->params->get('show_teacher_search') > 0 && ($teacher_menu == -1)) || $this->params->get('show_teacher_search') > 1) {
                //echo $this->lists['teacher_id'];
                echo $this->page->teachers;
            }
            if (($this->params->get('show_series_search') > 0 && ($series_menu == -1)) || $this->params->get('show_series_search') > 1) {
                //echo $this->lists['seriesid'];
                echo $this->page->series;
            }
            if (($this->params->get('show_type_search') > 0 && ($messagetype_menu == -1)) || $this->params->get('show_type_search') > 1) {
                //echo $this->lists['messagetypeid'];
                echo $this->page->messagetypes;
            }
            if ($this->params->get('show_year_search') > 0) {
                //echo $this->lists['studyyear'];
                echo $this->page->years;
            }

            if ($this->params->get('listlanguage') == 1) {
                echo $this->page->languages;
            }

            if ($this->params->get('show_order_search') > 0) {
                //echo $this->lists['orders'];
                echo $this->page->order;
            }
            if (($this->params->get('show_topic_search') > 0 && ($topic_menu == -1)) || $this->params->get('show_topic_search') > 1) {
                // echo $this->lists['topics'];
                echo $this->page->topics;
            }
            if ($this->params->get('show_popular') > 0) {
                //echo $this->popular;
                echo $this->page->popular;
            }
            ?>
        </div><!--dropdownmenu-->
    </fieldset>
    <div class="clr"></div>
    <table class="bslisttable" cellspacing="0">
        <thead>
        <?php
        if (isset($this->items['0'])):
            JView::loadHelper('header');
            $header = getHeader($this->items['0'], $params, $this->admin_params, $this->template, $showheader = $params->get('use_headers_list'), $ismodule = 0);
            echo $header;
        endif;
        ?>
        </thead>
        <tbody>

            <?php
            //This sets the alternativing colors for the background of the table cells
            $class1 = 'bsodd';
            $class2 = 'bseven';
            $oddeven = $class1;
            foreach ($this->items as $row) { //Run through each row of the data result from the model
                if ($oddeven == $class1) { //Alternate the color background
                    $oddeven = $class2;
                } else {
                    $oddeven = $class1;
                }

                $listing = getListing($row, $params, $oddeven, $this->admin_params, $this->template, $ismodule = 0);
                echo $listing;
            }
            ?>
        </tbody></table>
    <div class="listingfooter pagination" >
        <?php
        if ($this->params->get('show_pagination') == 2) {
                echo '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
            }
        echo $this->pagination->getPageslinks();
        ?>
    </div> <!--end of bsfooter div-->
    <?php
    if ($this->params->get('showpodcastsubscribelist') == 2) {
        echo $this->subscribe;
    }
    ?>
</div><!--end of bspagecontainer div-->

