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

use CWM\Component\Proclaim\Administrator\Helper\Cwmhtml;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmessages\HtmlView $this */
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
            <?php echo Cwmhtml::teacher(); ?>
        </div>
        <div class="form-group col-md-6">
            <?php echo Cwmhtml::series(); ?>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo Cwmhtml::messageType(); ?>
        </div>
    </div>
</div>
