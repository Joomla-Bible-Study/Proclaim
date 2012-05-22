<?php
//No Direct Access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers');

//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
JHTML::_('behavior.tooltip');
$params = $this->params;
$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');

$row = $this->study;
// @todo need to clean up old code.
$listingcall = JView::loadHelper('listing');
$sharecall = JView::loadHelper('share');

?> 
    <div id="bsmHeader">

        <div class="buttonheading">

            <?php
            if ($this->params->get('show_print_view') > 0) {
                echo $this->page->print;
            }
            ?>

        </div>
        <?php
        //Social Networking begins here
        if ($this->admin_params->get('socialnetworking') > 0) {
            ?>
            <div id="bsms_share">
                <?php
                //  $social = getShare($this->detailslink, $row, $params, $this->admin_params);
                echo $this->page->social;
                ?>
            </div>
        <?php } //End Social Networking  ?>
        <table><tr><td>


                    <?php if ($this->params->get('show_teacher_view') > 0) {
                        ?>

                        <?php
                        $teacher_call = JView::loadHelper('teacher');
                        $teacher = getTeacher($this->params, $row->teacher_id, $this->admin_params);
                        echo $teacher;
                        echo '</td><td>';
                    }
                    ?>


                    <?php
                    if ($this->params->get('title_line_1') + $params->get('title_line_2') > 0) {
                        $title_call = JView::loadHelper('title');
                        $title = getTitle($this->params, $row, $this->admin_params, $this->template);
                        echo $title;
                    }
                    ?>


                </td></tr></table>
    </div><!-- header -->
?>

    <?php if ($this->params->get('showpodcastsubscribedetails') == 1 || $this->params->get('showpodcastsubscribedetails') == 2)
{
    if ($this->params->get('showpodcastsubscribedetails')== 1){$float = 'style="float: right;"';}else{$float = 'style="float: left;"';}
    ?><div id="subscribelinks" <?php echo $float; ?>><?php echo $this->subscribe; ?></div><?php
} ?>
    <table id="bsmsdetailstable" cellspacing="0">
        <?php
        if ($this->params->get('use_headers_view') > 0 || $this->params->get('list_items_view') < 1) {
            $headerCall = JView::loadHelper('header');
            $header = getHeader($row, $this->params, $this->admin_params, $this->template, $showheader = $params->get('use_headers_view'), $ismodule = 0);
            echo $header;
        }
        ?>
        <tbody>

            <?php
            if ($this->params->get('list_items_view') == 1) {
                echo '<tr class="bseven"><td class="media">';
                require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
                $media = new jbsMedia();
                $listing = $media->getMediaTable($row, $this->params, $this->admin_params);
                echo $listing;
                echo '</td></tr>';
            }
            if ($params->get('list_items_view') == 0) {
                $oddeven = 'bsodd';
                $listing = getListing($row, $this->params, $oddeven, $this->admin_params, $this->template, $ismodule = 0);
                echo $listing;
            }
            ?>
        </tbody></table>
    <table id="bsmsdetailstable" cellspacing="0">
        <tr><td id="studydetailstext">
                <?php
                if ($this->params->get('show_scripture_link') > 0) {
                    echo $this->article->studytext;
                } else {
                    echo $this->study->studytext;
                }
                ?>

            </td></tr></table>
   
<?php if ($this->params->get('showpodcastsubscribedetails') == 3 || $this->params->get('showpodcastsubscribedetails') == 4)
{
    if ($this->params->get('showpodcastsubscribedetails')== 4){$float = 'style="float: left;"';}else{$float = 'style="float: right;"';}
    ?><div id="subscribelinks" <?php echo $float; ?>><?php echo $this->subscribe; ?></div><?php
}
 
