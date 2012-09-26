<?php
/**
 * Default
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
JHTML::_('behavior.tooltip');
$params = $this->params;
JView::loadHelper('listing');
?>
<div id="biblestudy_landing" class="noRefTagger"> <!-- This div is the container for the whole page -->
    <div id="bsms_header">
        <h1 class="componentheading">
            <?php
            if ($this->params->get('show_page_image') > 0) {
                if (isset($this->main->path)) {
                    ?>
                    <img src="<?php echo JURI::base() . $this->main->path; ?>" alt="<?php echo $this->main->path; ?>" width="<?php echo $this->main->width; ?>" height="<?php echo $this->main->height; ?>" />
                    <?php
                    //End of column for logo
                }
            }
            if ($this->params->get('show_page_title') > 0) {
                //echo $this->params->get('page_title');
            }
            //echo "\n";
            ?>
        </h1>
    </div> <!-- End div id="bsms_header" -->

    <?php
    $i = 1;
    for ($i = 1; $i <= 7; $i++) {
        $showIt = $params->get('headingorder_' . $i);
        if ($params->get('show' . $showIt) == 1) {
            $heading_call = null;
            $heading = null;
            $showIt_phrase = null;
            switch ($showIt) {

                case 'teachers':
                    JView::loadHelper('teacher');
                    $heading = getTeacherLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_TEACHERS');
                    break;

                case 'series':
                    JView::loadHelper('serieslist');
                    $heading = getSeriesLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_SERIES');
                    break;

                case 'locations':
                    JView::loadHelper('location');
                    $heading = getLocationsLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_LOCATIONS');
                    break;

                case 'messagetypes':
                    JView::loadHelper('messagetype');
                    $heading = getMessageTypesLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_MESSAGE_TYPES');
                    break;

                case 'topics':
                    JView::loadHelper('topics');
                    $heading = getTopicsLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_TOPICS');
                    break;

                case 'books':
                    JView::loadHelper('book');
                    $heading = getBooksLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_BOOKS');
                    break;

                case 'years':
                    JView::loadHelper('year');
                    $heading = getYearsLandingPage($params, $id = null, $this->admin_params);
                    $showIt_phrase = JText::_('JBS_CMN_YEARS');
                    break;
            }// End Switch

            if ($params->get('landing' . $showIt . 'limit')) {
                $images = new jbsImages();
                $showhide_tmp = $images->getShowHide();

                $showhideall = "<div id='showhide" . $i . "'>";

                $buttonlink = "\n\t" . '<a class="showhideheadingbutton" href="javascript:ReverseDisplay(' . "'showhide" . $showIt . "'" . ')">';
                $labellink = "\n\t" . '<a class="showhideheadinglabel" href="javascript:ReverseDisplay(' . "'showhide" . $showIt . "'" . ')">';

                switch ($params->get('landing_hide', 0)) {
                    case 0:         // image only
                        $showhideall .= $buttonlink;
                        $showhideall .= "\n\t\t" . '<img src="' . JURI::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" border="0" width="' . $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';
                        $showhideall .= ' '; // spacer
                        $showhideall .= "\n\t" . '</a>';
                        break;

                    case 1:         // image and label
                        $showhideall .= $buttonlink;
                        $showhideall .= "\n\t\t" . '<img src="' . JURI::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" border="0" width="' . $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';
                        $showhideall .= ' '; // spacer
                        $showhideall .= "\n\t" . '</a>';
                        $showhideall .= $labellink;
                        $showhideall .= "\n\t\t" . '<span id="landing_label">' . $params->get('landing_hidelabel') . '</span>';
                        $showhideall .= "\n\t" . '</a>';
                        break;

                    case 2:         // label only
                        $showhideall .= $labellink;
                        $showhideall .= "\n\t\t" . '<span id="landing_label">' . $params->get('landing_hidelabel') . '</span>';
                        $showhideall .= "\n\t" . '</a>';
                        break;
                }

                $showhideall .= "\n" . '      </div> <!-- end div id="showhide" for ' . $i . ' -->' . "\n";
            }
            ?>
            <!-- Wrap each in a DIV... -->
            <div id="landing_item<?php echo $i; ?>">
                <div id="landing_title<?php echo $i; ?>">
                    <?php
                    echo $params->get($showIt . 'label');
                    echo "\n";
                    ?>
                </div> <!-- end div id="landing_title" -->
                <div id="landinglist<?php echo $i; ?>">
                    <?php
                    if (isset($heading)) {
                        echo $heading;
                    }
                    if (isset($showhideall)) {
                        echo $showhideall;
                    }
                    ?>
                </div> <!-- end div id="landinglist"<?php echo $i; ?> -->
            </div><!-- end div id="landing_item"<?php echo $i; ?> -->
            <?php
        }
    } // End Loop for the landing items
    ?>
</div><!-- end div id="biblestudy_landing" -->