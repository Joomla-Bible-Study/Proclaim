<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

if (!array_key_exists('field', $displayData)) {
    return;
}

$field      = $displayData['field'];
$label      = Text::_($field->label);
$value      = $field->value;
$class      = $field->params->get('render_class');
$showLabel  = $field->params->get('showlabel');
$labelClass = $field->params->get('label_render_class');

if ($field->context == 'com_contact.mail') {
    // Prepare the value for the contact form mail
    $value = html_entity_decode($value);

    echo ($showLabel ? $label . ': ' : '') . $value . "\r\n";

    return;
}

if (!strlen($value)) {
    return;
}

?>
<dt class="contact-field-entry <?php
echo $class; ?>">
    <?php
    if ($showLabel == 1) : ?>
        <span class="field-label <?php
        echo $labelClass; ?>"><?php
            echo htmlentities($label, ENT_QUOTES | ENT_IGNORE, 'UTF-8'); ?>: </span>
        <?php
    endif; ?>
</dt>
<dd class="contact-field-entry <?php
echo $class; ?>">
    <span class="field-value"><?php
        echo $value; ?></span>
</dd>
