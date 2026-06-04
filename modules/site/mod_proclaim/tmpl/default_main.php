<?php

/**
 * Main view
 *
 * @package         Proclaim
 * @subpackage      mod_proclaim
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

/** @var Registry $params */
$show_link = $params->get('show_link', 1);

$Listing = new Cwmlisting();
?>
<div class="com-proclaim container-fluid">
    <?php
    if ($params->get('pageheader')) {
        ?>
        <div class="row">
            <div class="col-12">
                <?php
                echo HTMLHelper::_('content.prepare', $params->get('pageheader'), '', 'com_proclaim.module'); ?>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="col-12">
            <?php
    /** @var stdClass $list */

    /** @var stdClass $cwmtemplate */
            try {
                echo $Listing->getFluidListing($list, $params, $cwmtemplate, $type = "sermons");
            } catch (Exception $e) {
                // Never let a listing render error blank the module silently. Always
                // log it; surface the detail inline only when component debug is on
                // (JBSMDEBUG, set by api.php — 1 on admin pages or with ?jbsmdbg=1).
                Log::add(
                    'mod_proclaim: failed to render sermon listing — ' . $e->getMessage(),
                    Log::ERROR,
                    'mod_proclaim'
                );

                if (\defined('JBSMDEBUG') && JBSMDEBUG) {
                    echo '<div class="alert alert-danger" role="alert">'
                        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                        . '</div>';
                }
            }
            ?>
        </div>
    </div>

    <div class="row">
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
