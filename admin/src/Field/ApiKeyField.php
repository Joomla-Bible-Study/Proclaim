<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\TextField;

/**
 * API Key Field - Masked text input with reveal toggle
 *
 * Renders as a password field with an eye toggle button.
 * When a value exists, shows dots with the last 4 characters as a placeholder hint.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class ApiKeyField extends TextField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.1.0
     */
    protected $type = 'ApiKey';

    /**
     * Get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   10.1.0
     */
    protected function getInput(): string
    {
        $value = (string) $this->value;

        // If no value, render a normal text input
        if ($value === '') {
            return parent::getInput();
        }

        $name         = $this->name;
        $id           = $this->id;
        $class        = $this->class ? ' ' . $this->class : '';
        $disabled     = $this->disabled ? ' disabled' : '';
        $readonly     = $this->readonly ? ' readonly' : '';
        $required     = $this->required ? ' required' : '';
        $hint         = $this->hint ? ' placeholder="' . htmlspecialchars($this->hint, ENT_COMPAT, 'UTF-8') . '"' : '';
        $autocomplete = $this->autocomplete ?? '';
        $acAttr       = $autocomplete !== '' ? ' autocomplete="' . htmlspecialchars($autocomplete, ENT_COMPAT, 'UTF-8') . '"' : '';

        // Build last-4-char hint for placeholder
        $lastFour    = substr($value, -4);
        $maskedHint  = str_repeat("\u{2022}", 20) . $lastFour;

        $toggleId    = $id . '_toggle';

        $html = '<div class="input-group">';
        $html .= '<input type="password"'
            . ' name="' . $name . '"'
            . ' id="' . $id . '"'
            . ' value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"'
            . ' placeholder="' . htmlspecialchars($maskedHint, ENT_COMPAT, 'UTF-8') . '"'
            . ' class="form-control' . $class . '"'
            . $disabled . $readonly . $required . $acAttr
            . ' />';
        $html .= '<button type="button" class="btn btn-secondary" id="' . $toggleId . '"'
            . ' aria-label="Toggle visibility">';
        $html .= '<span class="icon-eye" aria-hidden="true"></span>';
        $html .= '</button>';
        $html .= '</div>';

        $html .= '<script>'
            . 'document.getElementById(' . json_encode($toggleId) . ').addEventListener("click", function() {'
            . '  var input = document.getElementById(' . json_encode($id) . ');'
            . '  var icon = this.querySelector("span");'
            . '  if (input.type === "password") {'
            . '    input.type = "text";'
            . '    icon.className = "icon-eye-close";'
            . '  } else {'
            . '    input.type = "password";'
            . '    icon.className = "icon-eye";'
            . '  }'
            . '});'
            . '</script>';

        return $html;
    }
}
