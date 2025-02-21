<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No direct access
defined('_JEXEC') or die();

?>

<div id="filebroswer_container">
    <div id="upload_in_progress"></div>
    <iframe name="dirbroswer" id="dirbroswer" height="344px" width="100%"
            src="index.php?option=com_proclaim&view=cwmdir&tmpl=component">
    </iframe>
</div>
