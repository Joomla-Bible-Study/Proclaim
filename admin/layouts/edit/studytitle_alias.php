<?php

/**
 * @package         Proclaim.Site
 * @subpackage      Layout
 *
 * @copyright   (C) 2025 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/* @var $displayData mixed Default is array */
$form = $displayData->getForm();

$studyTitle = $form->getField('studytitle') ? 'studytitle' : ($form->getField('name') ? 'name' : '');

?>
<div class="row title-alias form-vertical mb-3">
    <div class="col-12 col-md-6">
        <?php
        echo $studyTitle ? $form->renderField($studyTitle) : ''; ?>
    </div>
    <div class="col-12 col-md-6">
        <?php
        echo $form->renderField('alias'); ?>
    </div>
</div>
