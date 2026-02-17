<?php

/**
 * Helper for Template Code
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

// Add template accent color for pagination
$accentColor = $this->params->get('backcolor', '#287585');
$wa = $this->getDocument()->getWebAssetManager();
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");

?>


<div class="row">
    <h2>
        <?php echo Text::_('JBS_CMN_MESSAGES'); ?>
    </h2>
</div>

<div class="row"><p>

        <?php
        echo $this->params->get('list_intro'); ?> </p></div>


<?php
foreach ($this->items as $study) {
    ?>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3">

            <strong> <?php
                echo $study->scripture1; ?></strong>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <a href="<?php
            echo $study->detailslink; ?>"><strong><?php
                    echo $study->studytitle; ?></strong></a>
        </div>
        <div class="col-12 col-lg-4">
            <?php
            echo $study->media; ?>
        </div>
        <div class="col-12">
            <p><?php
                echo $study->studyintro; ?></p>
        </div>
        <hr class="col-12 sermon-list-separator"/>
    </div>
    <?php
} ?>
<div class="pagination-container pagelinks">
    <?php echo $this->pagination->getPageslinks(); ?>
</div>
