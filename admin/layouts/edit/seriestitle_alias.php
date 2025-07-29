<?php

/**
 * @package         Proclaim.Site
 * @subpackage      Layout
 *
 * @copyright   (C) 2025 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var $displayData mixed Default is array */
$form = $displayData->getForm();

$title = $form->getField('series_text') ? 'series_text' : ($form->getField('name') ? 'name' : '');

?>
<div class="row title-alias form-vertical mb-3">
    <div class="col-12 col-md-6">
        <?php
        echo $title ? $form->renderField($title) : ''; ?>
    </div>
    <div class="col-12 col-md-6">
        <?php
        echo $form->renderField('alias'); ?>
    </div>
</div>
