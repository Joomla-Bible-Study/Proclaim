<?php

/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\HTML\HTMLHelper;

$published = $this->state->get('filter.published');
?>

<div class="row-fluid">
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo Cwmhelper::players(); ?>
        </div>
    </div>
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo Cwmhelper::popup(); ?>
        </div>
    </div>
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo Cwmhelper::mediaType(); ?>
        </div>
    </div>
</div>
<div class="row-fluid">
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo Cwmhelper::linkType(); ?>
        </div>
    </div>
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo HTMLHelper::_('batch.access'); ?>
        </div>
    </div>
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo HTMLHelper::_('batch.language'); ?>
        </div>
    </div>
</div>
