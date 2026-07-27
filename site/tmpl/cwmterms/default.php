<?php

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Site\View\Cwmterms\HtmlView $this */
?>
<div class="com-proclaim">
        <div class="termstext">
            <?php
            echo $this->termstext;
            ?>
        </div>
        <?php if (!empty($this->media)) : ?>
        <div class="termslink">
            <?php
                // Without a media id there is nothing to continue to — reaching
                // the view directly used to warn "Attempt to read property on
                // null" straight into the page output.
                echo '<a href="index.php?option=com_proclaim&task=cwmsermons.download&id=' . (int) $this->media->study_id
                . '&mid=' . (int) $this->media->id . '">'
                . Text::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
            ?></div>
        <?php endif; ?>
</div>
