<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No direct access
defined('_JEXEC') or die();

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

