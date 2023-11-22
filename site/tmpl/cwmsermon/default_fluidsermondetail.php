<?php

/**
 * Helper for Template Code
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

?>
<div class="container-fluid">


    <div class="row-fluid">
        <div class="col-lg-12">
            <h3 style="text-align:right;">
                Bible Study from Calvary Chapel Newberg
            </h3>
        </div>
        <div class="col-lg-12">
            <h4 style="text-align:right;">
                with <?php
                echo $this->item->teachername; ?>
            </h4>
        </div>
    </div>
    <?php
    echo $this->print;
    echo $this->page->social;
    //echo $this->related;
    ?>
    <br/>
    <h2 style="text-align:center;">
        <?php
        echo $this->item->studytitle; ?>
    </h2>
    <h4 style="text-align:center;">
        <strong><?php
            echo $this->item->scripture1; ?></strong></h4>
    <?php
    echo $this->item->media; ?>
    <p>
        <?php
        echo $this->item->studytext; ?>
    </p>
</div>
