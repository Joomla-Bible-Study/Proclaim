<?php

/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$params = ComponentHelper::getParams('com_proclaim');

$published = (int)$this->state->get('filter.published');

$user = Factory::getApplication()->getSession()->get('user');
?>
<div class="p-3">
    <div class="row">
        <div class="modal-header">
            <button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
            <h3><?php
                echo Text::_('JBS_CMN_BATCH_OPTIONS'); ?></h3>
        </div>
        <div class="row">
            <?php
            if (Multilanguage::isEnabled()) : ?>
                <div class="form-group col-md-6">
                    <div class="controls">
                        <?php
                        echo LayoutHelper::render('joomla.html.batch.language', []); ?>
                    </div>
                </div>
                <?php
            endif; ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php
                    echo LayoutHelper::render('joomla.html.batch.access', []); ?>
                </div>
            </div>
        </div>
    </div>
</div>
