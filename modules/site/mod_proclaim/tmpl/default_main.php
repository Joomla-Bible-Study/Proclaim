<?php

/**
 * Main view
 *
 * @package         Proclaim
 * @subpackage      mod_proclaim
 * @copyright   (C) 2007 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

/** @var Registry $params */
$show_link = $params->get('show_link', 1);

$Listing = new Cwmlisting();
?>
<div class="container-fluid">
    <?php
    if ($params->get('pageheader')) {
        ?>
        <div class="row-fluid">
            <div class="col-12">
                <?php
                echo HtmlHelper::_('content.prepare', $params->get('pageheader'), '', 'com_proclaim.module'); ?>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="row-fluid">
        <div class="col-12">
            <?php
            /** @var \stdClass $list */

            /** @var \stdClass $cwmtemplate */
            try {
                echo $Listing->getFluidListing($list, $params, $cwmtemplate, $type = "sermons");
            } catch (Exception $e) {
            }
            ?>
        </div>
    </div>

    <div class="row-fluid">
        <div class="col-12">
            <?php
            if ($params->get('show_link') > 0) {
                /** @var string $link */
                echo $link;
            }
            ?>
        </div>
    </div>
    <!--end of footer div-->
</div> <!--end container -->
<div style="clear: both;"></div>
