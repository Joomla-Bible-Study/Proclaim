<?php

/**
 * Default Main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$t = $this->template;
?>
<!-- Begin Fluid layout -->
<div class="container-fluid">
    <div class="row-fluid">
        <div class="col-lg-12">
            <?php
            $listing = new Cwmlisting();
            $list    = $listing->getFluidListing($this->items, $this->params, $t, $type = 'seriesdisplay');
            echo $list;
            ?>
        </div>
    </div>
    <div class="row-fluid">
        <div class="col-lg-12">
            <?php
            $seriesstudies = $listing->getFluidListing($this->seriesstudies, $this->params, $t, $type = 'sermons');
            echo $seriesstudies; ?>
        </div>
    </div>
    <hr/>
    <div class="row-fluid">
        <div class="col-lg-12">
            <?php
            if ($this->params->get('series_list_return') > 0) {
                echo '<a href="'
                    . Route::_(
                        'index.php?option=com_proclaim&view=cwmseriesdisplays&t=' . $t->id
                    ) . '"><button class="btn"><< '
                    . Text::_('JBS_SER_RETURN_SERIES_LIST') . '</button></a>'; ?>
                <?php
                echo '<a href="'
                    . Route::_(
                        'index.php?option=com_proclaim&view=cwmsermons&filter_series=' . $this->items->id . '&t=' . $t->id
                    )
                    . '"><button class="btn">' . Text::_('JBS_CMN_SHOW_ALL') . ' ' . Text::_(
                        'JBS_SER_STUDIES_FROM_THIS_SERIES'
                    )
                    . ' >></button></a>'; ?>
                <?php
            }
            ?>
        </div>
    </div>
    <!-- End Fluid Layout -->
</div>
