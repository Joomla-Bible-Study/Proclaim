<?php

/**
 * Form sub backup
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

// Start of Form
$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) {
    if ($name == 'jwplayer') {
        if (isset($fieldSet->description) && trim($fieldSet->description)) {
            ?>
            <h3><?php
                echo $this->escape(Text::_($fieldSet->description)); ?></h3>
            <?php
        }
        foreach ($this->form->getFieldset($name) as $field) {
            ?>
            <div class="control-group">
                <div class="control-label"><?php
                    echo $field->label; ?></div>
                <div class="controls"><?php
                    echo $field->input; ?></div>
            </div>
            <?php
        }
    } else {
        ?>
        <div id="jwplayer_pro_section">
            <?php
            foreach ($this->form->getFieldset($name) as $field) {
                ?>
                <div class="control-group">
                    <div class="control-label"><?php
                        echo $field->label; ?></div>
                    <div class="controls"><?php
                        echo $field->input; ?></div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
}
