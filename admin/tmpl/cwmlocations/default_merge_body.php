<?php

/**
 * Merge Modal Body
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhtml;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmlocations\HtmlView $this */

$locations = Cwmhtml::locationList();
?>
<div class="p-3">
    <div class="alert alert-warning">
        <span class="icon-exclamation-triangle" aria-hidden="true"></span>
        <?php echo Text::_('JBS_LOC_MERGE_DESC'); ?>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label id="merge-target-lbl" for="jform_merge_target" class="form-label">
                <?php echo Text::_('JBS_LOC_MERGE_TARGET'); ?>
            </label>
            <select name="jform[merge_target]" id="jform_merge_target" class="form-select">
                <option value=""><?php echo Text::_('JSELECT'); ?></option>
                <?php echo HTMLHelper::_('select.options', $locations, 'value', 'text'); ?>
            </select>
        </div>
    </div>
</div>
