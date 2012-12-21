
<?php
/**
 * Main view
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @todo need to revamp this system this default main.
 * */
defined('_JEXEC') or die;

$show_link = $params->get('show_link', 1);
$pagetext = $params->get('pagetext');
$ismodule = 1;
?>
<div id="biblestudy" class="noRefTagger">
    <div id="jbsmoduleheader"><?php echo $params->get('pageheader'); ?></div>
    <!-- This div is the container for the whole page -->
    <table id="bsmsmoduletable" cellspacing="0">
        <?php
        $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy/helpers/';
        $path2 = JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/';
        include_once($path1 . 'header.php');
        include_once($path2 . 'helper.php');
        include_once($path1 . 'listing.php');
        $header = getHeader($list[0], $params, $admin_params, $template, $params->get('use_headers'), $ismodule);
        echo $header;
        ?>
        <tbody>
            <?php
            $class1 = 'bsodd';
            $class2 = 'bseven';
            $oddeven = $class1;
            foreach ($list as $study) {
                if ($oddeven == $class1) {
                    //Alternate the color background
                    $oddeven = $class2;
                } else {
                    $oddeven = $class1;
                }
                $listing = getListing($study, $params, $oddeven, $admin_params, $template, $ismodule);
                echo $listing;
            }
            ?>
        </tbody>
    </table>
</div>
<div style="clear: both;"></div>
<div class="modulelistingfooter">
    <br />
    <?php
    if ($params->get('show_link') > 0) {
        echo $link;
    }
    ?>
</div>
<!--end of footer div-->
