<?php

/**
 * Default Main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermon\HtmlView $this */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Create shortcuts to some parameters.
/** @type Joomla\Registry\Registry $params */
$params  = $this->item->params;
$user    = Factory::getApplication()->getIdentity();
$canEdit = $params->get('access-edit');

$row     = $this->item;
$isPrint = !empty($this->print);
?>

<?php
if (!$isPrint && $this->item->params->get('showpodcastsubscribedetails') === '1') {
    ?>
    <div class="row proclaim-podcast-subscribe">
        <div class="col-12">
            <?php
            echo $this->subscribe; ?>
        </div>
    </div>
    <?php
}
?>
    <div class="page-header">
        <h1 itemprop="headline">
            <?php
            if ($this->item->params->get('details_show_header') > 0) {
                if ($this->item->params->get('details_show_header') == 1) {
                    echo $this->item->studytitle;
                } else {
                    echo !empty($this->item->allScriptures) ? $this->item->allScriptures : $this->item->scripture1;
                }
                // Add archive badge if item is archived and badge is enabled
                $showBadge = $this->item->params->get('show_archive_badge', '');
                if ($showBadge === '' || $showBadge === null) {
                    $showBadge = $this->item->params->get('ddefault_show_archive_badge', '1');
                }
                if (isset($this->item->published) && (int)$this->item->published === 2
                    && (int)$showBadge === 1) {
                    echo ' <span class="badge bg-secondary proclaim-archive-badge">'
                        . Text::_('JBS_CMN_ARCHIVE_BADGE') . '</span>';
                }
            } ?>
        </h1>
    </div>
<?php
if ($this->item->params->get('showrelated') === '1') {
    ?>
    <div class="row">
        <div class="col-12">
            <?php
            echo $this->related; ?>
        </div>
    </div>
    <?php
}
?>


<?php
// Social Networking begins here
if (!$isPrint && !empty($this->page->social)) {
    echo $this->page->social;
}
// End Social Networking
?>
    <!-- Begin Fluid layout -->

<?php
echo $this->fluidListing;
?>

    <!-- End Fluid Layout -->

<?php
if ($isPrint) {
    // Print mode: show full passage text (always visible, no interactive elements)
    if (!empty($this->printPassage)) {
        echo '<div class="passage-print">';
        echo '<h3>' . Text::_('JBS_CMN_BIBLE_PASSAGE') . '</h3>';
        echo $this->printPassage;
        echo '</div>';
    }
} else {
    echo $this->passage;
}
?>
    <hr/> <?php

// Show studytext here only if NOT configured in the Layout Editor (backward compat)
if (
    !isset($this->item->params)
    || (int) $this->item->params->get('dstudytextrow', 0) === 0
) {
    if ($isPrint && !empty($this->item->studytext)) {
        echo '<h3 class="studytext-print-heading">' . Text::_('JBS_CMN_MESSAGE_TEXT') . '</h3>';
    }
    echo $this->item->studytext;
}

?>
<?php
if ($this->item->params->get('showrelated') === '2') {
    echo $this->related;
}
