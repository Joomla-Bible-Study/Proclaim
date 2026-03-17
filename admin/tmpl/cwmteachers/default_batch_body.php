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

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmteachers\HtmlView $this */
?>
<div class="p-3">
    <div class="row">
        <?php if (Multilanguage::isEnabled()) : ?>
            <div class="form-group col-md-6">
                <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
            </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label id="batch-landing-show-lbl" for="batch-landing-show">
                <?php echo Text::_('JBS_CMN_LANDING_SHOW'); ?>
            </label>
            <select name="batch[landing_show]" id="batch-landing-show" class="form-select">
                <option value=""><?php echo Text::_('JBS_CMN_BATCH_NO_CHANGE'); ?></option>
                <option value="0"><?php echo Text::_('JBS_CMN_NO_SHOW_LANDING'); ?></option>
                <option value="1"><?php echo Text::_('JBS_CMN_SHOW_ABOVE_LANDING'); ?></option>
                <option value="2"><?php echo Text::_('JBS_CMN_SHOW_BELOW_LANDING'); ?></option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label id="batch-list-show-lbl" for="batch-list-show">
                <?php echo Text::_('JBS_TCH_SHOW_LIST_VIEW'); ?>
            </label>
            <select name="batch[list_show]" id="batch-list-show" class="form-select">
                <option value=""><?php echo Text::_('JBS_CMN_BATCH_NO_CHANGE'); ?></option>
                <option value="0"><?php echo Text::_('JHIDE'); ?></option>
                <option value="1"><?php echo Text::_('JSHOW'); ?></option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label id="batch-landing-ordering-lbl" for="batch-landing-ordering">
                <?php echo Text::_('JBS_TCH_LANDING_ORDERING'); ?>
            </label>
            <input type="number" name="batch[landing_ordering]" id="batch-landing-ordering"
                   class="form-control" value="" min="0" step="1"
                   placeholder="<?php echo Text::_('JBS_CMN_BATCH_NO_CHANGE'); ?>">
        </div>
    </div>
</div>
