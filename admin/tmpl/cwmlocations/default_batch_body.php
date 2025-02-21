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
use Joomla\CMS\Layout\LayoutHelper;

$params = ComponentHelper::getParams('com_proclaim');

$published = (int)$this->state->get('filter.published');

$user = Factory::getApplication()->getSession()->get('user');
?>
<div class="p-3">
    <div class="row">
        <div class="form-group col-md-6">
            <div class="controls">
                <?php
                echo LayoutHelper::render('joomla.html.batch.access', []); ?>
            </div>
        </div>
    </div>
</div>
