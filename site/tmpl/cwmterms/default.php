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

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Site\View\Cwmterms\HtmlView $this */
?>
<div class="com-proclaim">
        <div class="termstext">
            <?php
            echo $this->termstext;
            ?>
        </div>
        <div class="termslink">
            <?php
            if ((int)$this->compat_mode === 1) {
                echo '<a href="https://www.christianwebministries.org/router.php?file=' .
                Cwmhelper::mediaBuildUrl($this->media->spath, $this->media->filename, $this->params->get('media_path'))
                . '&size=' . $this->media->size . '">' . Text::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
            } else {
                echo '<a href="index.php?option=com_proclaim&task=cwmsermons.download&id=' . $this->media->study_id
                . '&mid=' . $this->media->id . '">'
                . Text::_('JBS_CMN_CONTINUE_TO_DOWNLOAD') . '</a>';
            } ?></div>
</div>
</div>