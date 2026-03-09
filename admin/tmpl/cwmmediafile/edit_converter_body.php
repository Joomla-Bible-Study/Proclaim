<?php

/**
 * Converter Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmediafile\HtmlView $this */

?>
<div class="row g-3">
    <div class="col-auto">
        <label for="Text1" class="visually-hidden">Size</label>
        <input type="text" class="form-control" name="size_converter" id="Text1" size="5" placeholder="Size">
    </div>
    <div class="col-auto">
        <label for="Select1" class="visually-hidden">Unit</label>
        <select class="form-select" name="sel" id="Select1">
            <option value="KB">KB</option>
            <option value="MB" selected>MB</option>
            <option value="GB">GB</option>
        </select>
    </div>
</div>
