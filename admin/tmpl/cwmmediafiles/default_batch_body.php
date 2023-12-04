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

use Joomla\CMS\HTML\HTMLHelper;

$published = $this->state->get('filter.published');
HTMLHelper::addIncludePath(BIBLESTUDY_PATH_ADMIN_HELPERS . '/html');
?>

<div class="row-fluid">
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo HTMLHelper::_('proclaim.players'); ?>
        </div>
    </div>
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo HTMLHelper::_('proclaim.popup'); ?>
        </div>
    </div>
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo HTMLHelper::_('proclaim.Mediatype'); ?>
        </div>
    </div>
</div>
<div class="row-fluid">
    <div class="control-group col-4">
        <div class="controls">
            <?php
            echo HTMLHelper::_('proclaim.link_type'); ?>
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
