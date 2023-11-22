<?php

/**
 * Converter Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

?>
<div class="row-fluid">
    <div class="control-group">
        <div class="controls">
            <input name="size_converter" type=text ID="Text1" onChange="decOnly(this);" size=5>
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <select name="sel" ID="Select1">
                <option value="KB">KB</option>
                <option value="MB" selected>MB</option>
                <option value="GB">GB</option>
            </select>
        </div>
    </div>
</div>
