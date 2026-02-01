<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmupload\HtmlView $this */

?>
<div id="mediamu_wrapper">
    <div id="uploader_content">
        <?php
        echo $this->loadTemplate('uploader'); ?>
    </div>

    <div id="filebroswer_content">
        <?php
        echo $this->loadTemplate('navigator'); ?>
    </div>
</div>

