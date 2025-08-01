<?php

/**
 * Helper for Template Code
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

?>


<div class="row-fluid col-lg-12">
    <h2>
        Bible Studies
    </h2>
</div>

<div class="row-fluid col-lg-12"><p>

        <?php
        echo $this->params->get('list_intro'); ?> </p></div>


<?php
foreach ($this->items as $study) {
    ?>
    <div class="row-fluid col-lg-12">
        <div class="col-lg-3">

            <strong> <?php
                echo $study->scripture1; ?></strong>
        </div>
        <div class="col-lg-4">
            <a href="<?php
            echo $study->detailslink; ?>"><strong><?php
                    echo $study->studytitle; ?></strong></a>
        </div>
        <div class="col-lg-4">
            <?php
            echo $study->media; ?>
        </div>
        <div class="col-lg-12" style="margin-left: -2px;">
            <p><?php
                echo $study->studyintro; ?></p>
        </div>
        <hr class="col-lg-12 sermon-list-separator"/>
    </div>
    <?php
} ?>
<div class="row-fluid col-lg-12 pagination">
    <?php
    echo $this->pagination->getPageslinks(); ?>
</div>
