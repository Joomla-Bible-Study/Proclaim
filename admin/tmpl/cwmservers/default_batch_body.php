<?php

/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmservers\HtmlView $this */

use Joomla\CMS\Layout\LayoutHelper;

?>
<div class="p-3">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
        </div>
    </div>
</div>
