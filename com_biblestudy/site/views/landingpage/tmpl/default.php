<?php
//No Direct Access
defined('_JEXEC') or die;

require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');

$mainframe = JFactory::getApplication();
$option = JRequest::getCmd('option');
JHTML::_('behavior.tooltip');
$database = JFactory::getDBO();
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'helper.php');
$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
$showhide = getShowhide();
$document->addScriptDeclaration($showhide);
$stylesheet = JURI::base() . 'media/com_biblestudy/css/biblestudy.css';
$document->addStyleSheet($stylesheet);
$params = $this->params;
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'image.php');
$d_path1 = 'media/com_biblestudy/images';


$listingcall = JView::loadHelper('listing');
?>
<form action="<?php echo str_replace("&", "&amp;", $this->request_url); ?>" method="post" name="adminForm">
    <div id="biblestudy_landing" class="noRefTagger"> <!-- This div is the container for the whole page -->

        <div id="bsms_header">
            <h1 class="componentheading">
                <?php
                if ($this->params->get('show_page_image') > 0) {
                    if ($this->main->path == NULL) {

                    } else {
                        ?>
                        <img src="<?php echo JURI::base() . $this->main->path; ?>" alt="<?php echo $this->main->path; ?>" width="<?php echo $this->main->width; ?>" height="<?php echo $this->main->height; ?>" />
                        <?php
                        //End of column for logo
                    }
                }
                if ($this->params->get('show_page_title') > 0) {
                    echo $this->params->get('page_title');
                }
                echo "\n";
                ?>
            </h1>
        </div> <!-- End div id="bsms_header" -->

        <?php
        $i = 1;

        for ($i = 1; $i <= 7; $i++) {

            $showIt = $params->get('headingorder_' . $i);

            if ($params->get('show' . $showIt) == 1) {
                //Wrap each in a DIV...
                ?>

                <div id="landing_item">
                    <div id="landing_title">
                        <?php
                        echo $params->get($showIt . 'label');
                        echo "\n";
                        ?>
                    </div> <!-- end div id="landing_title" -->
                    <?php
                    $heading_call = null;
                    $heading = null;
                    $showIt_phrase = null;
                    switch ($showIt) {

                        case 'teachers':
                            $heading_call = JView::loadHelper('teacher');
                            $heading = getTeacherLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_TEACHERS');
                            //echo "</div>";
                            break;

                        case 'series':
                            $heading_call = JView::loadHelper('serieslist');
                            $heading = getSeriesLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_SERIES');
                            //echo "</div>";
                            break;

                        case 'locations':
                            $heading_call = JView::loadHelper('location');
                            $heading = getLocationsLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_LOCATIONS');
                            //echo "</div>";
                            break;

                        case 'messagetypes':
                            $heading_call = JView::loadHelper('messagetype');
                            $heading = getMessageTypesLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_MESSAGE_TYPES');
                            //echo "</div>";
                            break;

                        case 'topics':
                            $heading_call = JView::loadHelper('topics');
                            $heading = getTopicsLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_TOPICS');
                            //	echo "</div>";
                            break;

                        case 'books':
                            $heading_call = JView::loadHelper('book');
                            $heading = getBooksLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_BOOKS');
                            //echo "</div>";
                            break;

                        case 'years':
                            $heading_call = JView::loadHelper('year');
                            $heading = getYearsLandingPage($params, $id = null, $this->admin_params);
                            $showIt_phrase = JText::_('JBS_CMN_YEARS');
                            //echo "</div>";
                            break;
                    }// End Switch


                    if ($params->get('landing' . $showIt . 'limit')) {
                        $images = new jbsImages();
                        $showhide_tmp = $images->getShowHide();

                        $showhideall = "      <div id='showhide'>";

                        $buttonlink = "\n\t" . '<a class="showhideheadingbutton" href="javascript:ReverseDisplay(' . "'showhide" . $showIt . "'" . ')">';
                        $labellink = "\n\t" . '<a class="showhideheadinglabel" href="javascript:ReverseDisplay(' . "'showhide" . $showIt . "'" . ')">';

                        switch ($params->get('landing_hide', 0)) {
                            case 0:         // image only
                                $showhideall .= $buttonlink;
                                $showhideall .= "\n\t\t" . '<img src="' . JURI::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" border="0" width="' . $showhide_tmp->width . '" height="' . $showhide_tmp->height . '">';
                                $showhideall .= ' '; // spacer
                                $showhideall .= "\n\t" . '</a>';
                                break;

                            case 1:         // image and label
                                $showhideall .= $buttonlink;
                                $showhideall .= "\n\t\t" . '<img src="' . JURI::base() . $showhide_tmp->path . '" alt="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" title="' . JText::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' . $showIt_phrase . '" border="0" width="' . $showhide_tmp->width . '" height="' . $showhide_tmp->height . '">';
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
                        echo $showhideall;
                    }
                    ?>
                    <div id="landinglist">

                        <?php
                        if ($heading) {
                            echo $heading;
                        }
                        echo "\n" . '      </div> <!-- end div id="landinglist" ' . $i . " -->";
                        echo "\n";
                        ?>
                    </div> <!-- end div id="landing_item" <?php echo $i; ?> -->
                    <?php
                }
            } // End Loop for the landing items
            ?>
        </div>
    </div> <!-- end div id="biblestudy_landing" -->
    <input name="option" value="com_biblestudy" type="hidden">

    <input name="task" value="" type="hidden">
    <input name="boxchecked" value="0" type="hidden">
    <input name="controller" value="studieslist" type="hidden">
</form>