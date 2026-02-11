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

$this->loadHelper('title');
$this->loadHelper('teacher');
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
                    echo $this->item->scripture1;
                }
                // Add archive badge if item is archived and badge is enabled
                $showBadge = $this->item->params->get('show_archive_badge', '');
                if ($showBadge === '' || $showBadge === null) {
                    $showBadge = $this->item->params->get('default_show_archive_badge', '1');
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
if (!$isPrint && $this->item->params->get('socialnetworking') > 0) {
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
    // Print mode: show scripture references as text instead of iframe
    $refs = [];

    // Use junction table scriptures if available
    if (!empty($row->scriptures) && \is_array($row->scriptures)) {
        foreach ($row->scriptures as $ref) {
            if ($ref->booknumber <= 0) {
                if ($ref->referenceText !== '') {
                    $refs[] = $ref->referenceText;
                }
                continue;
            }
            $refs[] = \CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper::formatReference(
                $ref->booknumber,
                $ref->chapterBegin,
                $ref->verseBegin,
                $ref->chapterEnd,
                $ref->verseEnd
            );
        }
    } elseif (!empty($row->bookname)) {
        // Legacy fallback: build from flat columns
        $bookRef = Text::_($row->bookname);
        if (!empty($row->chapter_begin)) {
            $bookRef .= ' ' . $row->chapter_begin;
            if (!empty($row->verse_begin)) {
                $bookRef .= ':' . $row->verse_begin;
            }
            if (!empty($row->chapter_end) && !empty($row->verse_end)) {
                if ($row->chapter_end !== $row->chapter_begin) {
                    $bookRef .= '-' . $row->chapter_end . ':' . $row->verse_end;
                } else {
                    $bookRef .= '-' . $row->verse_end;
                }
            } elseif (!empty($row->verse_end)) {
                $bookRef .= '-' . $row->verse_end;
            }
        }
        $refs[] = $bookRef;
    }

    if (!empty($refs)) {
        echo '<div class="passage-print">';
        echo '<h3>' . Text::_('JBS_CMN_BIBLE_PASSAGE') . '</h3>';
        echo '<p><strong>' . htmlspecialchars(implode('; ', $refs)) . '</strong></p>';
        echo '</div>';
    }
} else {
    echo $this->passage;
}
?>
    <hr/> <?php

    echo $this->item->studytext;

?>
<?php
if ($this->item->params->get('showrelated') === '2') {
    echo $this->related;
}
