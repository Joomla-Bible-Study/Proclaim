<?php
/**
 * @version		$Id: default_custom.php 8591 2007-08-27 21:09:32Z Tom Fuller $
 * @package		mod_biblestudy
 * @copyright            2010-2011
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$document = & JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'listing.php');
?>
<div id="biblestudy" class="noRefTagger">
    <!-- This div is the container for the whole page -->


    <?php
    switch ($params->get('module_wrapcode')) {
        case '0':
            //Do Nothing
            break;
        case 'T':
            //Table
            ?><table id="bsmsmoduletable" width="100%"><?php
            break;
        case 'D':
            //DIV
            ?><div class="bsmsmoduletable"><?php
            break;
    }

    if ($params->get('module_headercode')) {
        echo $params->get('module_headercode');
    } else {
        include_once($path1 . 'header.php');
        include_once($path1 . 'helper.php');
        $header = getHeader($list[0], $params, $admin_params, $templatemenuid, $params->get('use_headers'), $ismodule);
        echo $header;
    }

    foreach ($list as $row) {
        $listing = getListingExp($row, $params, $admin_params, $templatemenuid);
        echo $listing;
    }

    switch ($params->get('module_wrapcode')) {
        case '0':
            //Do Nothing
            break;
        case 'T':
            //Table
            ?></table><?php
            break;
        case 'D':
            //DIV
            ?></div><?php
            break;
    }
    ?>
</div>

<div class="modulelistingfooter">
    <br />

    <?php
    $link_text = $params->get('pagetext', 'More Bible Studies');

    if ($params->get('show_link') > 0) {
        $t = $params->get('t');
        if (!$t) {
            $t = JRequest::getVar('t', 1, 'get', 'int');
        }
        $link = JRoute::_('index.php?option=com_biblestudy&view=studieslist&t=' . $t);
        ?>
        <a href="<?php echo $link; ?>"> <?php echo $link_text . '<br />'; ?> </a> <?php } //End of if view_link not 0  ?>
</div>
<!--end of footer div-->
