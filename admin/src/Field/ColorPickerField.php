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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Color Picker Field - Searchable dropdown with CSS named colors + custom hex color picker
 *
 * Provides all CSS named colors with search filter, plus ability to enter custom hex colors.
 * Output is valid CSS color value (named color or hex).
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class ColorPickerField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'ColorPicker';

    /**
     * All CSS named colors (sorted alphabetically)
     *
     * @var  array<string, string>
     *
     * @since 10.2.0
     */
    protected const CSS_COLORS = [
        'aliceblue' => '#F0F8FF',
        'antiquewhite' => '#FAEBD7',
        'aqua' => '#00FFFF',
        'aquamarine' => '#7FFFD4',
        'azure' => '#F0FFFF',
        'beige' => '#F5F5DC',
        'bisque' => '#FFE4C4',
        'black' => '#000000',
        'blanchedalmond' => '#FFEBCD',
        'blue' => '#0000FF',
        'blueviolet' => '#8A2BE2',
        'brown' => '#A52A2A',
        'burlywood' => '#DEB887',
        'cadetblue' => '#5F9EA0',
        'chartreuse' => '#7FFF00',
        'chocolate' => '#D2691E',
        'coral' => '#FF7F50',
        'cornflowerblue' => '#6495ED',
        'cornsilk' => '#FFF8DC',
        'crimson' => '#DC143C',
        'cyan' => '#00FFFF',
        'darkblue' => '#00008B',
        'darkcyan' => '#008B8B',
        'darkgoldenrod' => '#B8860B',
        'darkgray' => '#A9A9A9',
        'darkgreen' => '#006400',
        'darkkhaki' => '#BDB76B',
        'darkmagenta' => '#8B008B',
        'darkolivegreen' => '#556B2F',
        'darkorange' => '#FF8C00',
        'darkorchid' => '#9932CC',
        'darkred' => '#8B0000',
        'darksalmon' => '#E9967A',
        'darkseagreen' => '#8FBC8F',
        'darkslateblue' => '#483D8B',
        'darkslategray' => '#2F4F4F',
        'darkturquoise' => '#00CED1',
        'darkviolet' => '#9400D3',
        'deeppink' => '#FF1493',
        'deepskyblue' => '#00BFFF',
        'dimgray' => '#696969',
        'dodgerblue' => '#1E90FF',
        'firebrick' => '#B22222',
        'floralwhite' => '#FFFAF0',
        'forestgreen' => '#228B22',
        'fuchsia' => '#FF00FF',
        'gainsboro' => '#DCDCDC',
        'ghostwhite' => '#F8F8FF',
        'gold' => '#FFD700',
        'goldenrod' => '#DAA520',
        'gray' => '#808080',
        'green' => '#008000',
        'greenyellow' => '#ADFF2F',
        'honeydew' => '#F0FFF0',
        'hotpink' => '#FF69B4',
        'indianred' => '#CD5C5C',
        'indigo' => '#4B0082',
        'ivory' => '#FFFFF0',
        'khaki' => '#F0E68C',
        'lavender' => '#E6E6FA',
        'lavenderblush' => '#FFF0F5',
        'lawngreen' => '#7CFC00',
        'lemonchiffon' => '#FFFACD',
        'lightblue' => '#ADD8E6',
        'lightcoral' => '#F08080',
        'lightcyan' => '#E0FFFF',
        'lightgoldenrodyellow' => '#FAFAD2',
        'lightgray' => '#D3D3D3',
        'lightgreen' => '#90EE90',
        'lightpink' => '#FFB6C1',
        'lightsalmon' => '#FFA07A',
        'lightseagreen' => '#20B2AA',
        'lightskyblue' => '#87CEFA',
        'lightslategray' => '#778899',
        'lightsteelblue' => '#B0C4DE',
        'lightyellow' => '#FFFFE0',
        'lime' => '#00FF00',
        'limegreen' => '#32CD32',
        'linen' => '#FAF0E6',
        'magenta' => '#FF00FF',
        'maroon' => '#800000',
        'mediumaquamarine' => '#66CDAA',
        'mediumblue' => '#0000CD',
        'mediumorchid' => '#BA55D3',
        'mediumpurple' => '#9370DB',
        'mediumseagreen' => '#3CB371',
        'mediumslateblue' => '#7B68EE',
        'mediumspringgreen' => '#00FA9A',
        'mediumturquoise' => '#48D1CC',
        'mediumvioletred' => '#C71585',
        'midnightblue' => '#191970',
        'mintcream' => '#F5FFFA',
        'mistyrose' => '#FFE4E1',
        'moccasin' => '#FFE4B5',
        'navajowhite' => '#FFDEAD',
        'navy' => '#000080',
        'oldlace' => '#FDF5E6',
        'olive' => '#808000',
        'olivedrab' => '#6B8E23',
        'orange' => '#FFA500',
        'orangered' => '#FF4500',
        'orchid' => '#DA70D6',
        'palegoldenrod' => '#EEE8AA',
        'palegreen' => '#98FB98',
        'paleturquoise' => '#AFEEEE',
        'palevioletred' => '#DB7093',
        'papayawhip' => '#FFEFD5',
        'peachpuff' => '#FFDAB9',
        'peru' => '#CD853F',
        'pink' => '#FFC0CB',
        'plum' => '#DDA0DD',
        'powderblue' => '#B0E0E6',
        'purple' => '#800080',
        'rebeccapurple' => '#663399',
        'red' => '#FF0000',
        'rosybrown' => '#BC8F8F',
        'royalblue' => '#4169E1',
        'saddlebrown' => '#8B4513',
        'salmon' => '#FA8072',
        'sandybrown' => '#F4A460',
        'seagreen' => '#2E8B57',
        'seashell' => '#FFF5EE',
        'sienna' => '#A0522D',
        'silver' => '#C0C0C0',
        'skyblue' => '#87CEEB',
        'slateblue' => '#6A5ACD',
        'slategray' => '#708090',
        'snow' => '#FFFAFA',
        'springgreen' => '#00FF7F',
        'steelblue' => '#4682B4',
        'tan' => '#D2B48C',
        'teal' => '#008080',
        'thistle' => '#D8BFD8',
        'tomato' => '#FF6347',
        'transparent' => 'transparent',
        'turquoise' => '#40E0D0',
        'violet' => '#EE82EE',
        'wheat' => '#F5DEB3',
        'white' => '#FFFFFF',
        'whitesmoke' => '#F5F5F5',
        'yellow' => '#FFFF00',
        'yellowgreen' => '#9ACD32',
    ];

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   10.2.0
     */
    protected function getInput(): string
    {
        // Load Bootstrap Tab JavaScript
        HTMLHelper::_('bootstrap.tab');

        $value    = (string) $this->value;
        $disabled = $this->disabled ? ' disabled' : '';
        $readonly = $this->readonly ? ' readonly' : '';
        $required = $this->required ? ' required' : '';

        // Convert legacy 0x format to # hex format (e.g., 0x287585 -> #287585)
        if (preg_match('/^0x([0-9A-Fa-f]{6})$/', $value, $matches)) {
            $value = '#' . strtoupper($matches[1]);
        }

        // Determine if current value is a named color or custom hex
        $isNamedColor = \array_key_exists(strtolower($value), self::CSS_COLORS);

        // Get hex value for color preview
        $hexValue = $isNamedColor ? self::CSS_COLORS[strtolower($value)] : ($value ?: '#000000');
        if ($hexValue === 'transparent') {
            $hexValue = '#FFFFFF';
        }

        $html = '<div class="colorpicker-field-wrapper" style="position:relative;">';

        // Main container with flex layout - clickable to open dropdown
        $html .= '<div class="d-flex align-items-center gap-2">';

        // Color preview swatch (clickable)
        $html .= $this->buildPreviewSwatch($value, $isNamedColor);

        // Display current color name/value (clickable)
        $html .= \sprintf(
            '<input type="text" id="%s_display" class="form-control" value="%s" '
            . 'style="max-width:200px;cursor:pointer;" readonly onclick="toggleColorPalette_%s()" />',
            $this->id,
            htmlspecialchars($value ?: 'black', ENT_QUOTES, 'UTF-8'),
            $this->id
        );

        // Single button to open combined picker
        $html .= \sprintf(
            '<button type="button" class="btn btn-secondary" onclick="toggleColorPalette_%s()" '
            . 'title="%s"%s><span class="icon-color-palette" aria-hidden="true"></span></button>',
            $this->id,
            Text::_('JBS_TPL_SELECT_COLOR'),
            $disabled
        );

        $html .= '</div>';

        // Combined dropdown with tabs for Named Colors and Custom Color
        $html .= \sprintf(
            '<div id="%s_dropdown" class="colorpicker-dropdown" '
            . 'style="display:none;position:absolute;z-index:1000;top:100%%;left:0;'
            . 'min-width:320px;margin-top:4px;padding:12px;background:var(--template-bg-dark,#323a46);'
            . 'border:1px solid var(--template-bg-dark-10,#444);border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.3);">',
            $this->id
        );

        // Tab navigation
        $html .= \sprintf(
            '<ul class="nav nav-tabs mb-3" role="tablist">'
            . '<li class="nav-item" role="presentation">'
            . '<button class="nav-link active" id="%s_tab_named" data-bs-toggle="tab" '
            . 'data-bs-target="#%s_pane_named" type="button" role="tab">%s</button>'
            . '</li>'
            . '<li class="nav-item" role="presentation">'
            . '<button class="nav-link" id="%s_tab_custom" data-bs-toggle="tab" '
            . 'data-bs-target="#%s_pane_custom" type="button" role="tab">%s</button>'
            . '</li>'
            . '</ul>',
            $this->id,
            $this->id,
            Text::_('JBS_TPL_NAMED_COLORS'),
            $this->id,
            $this->id,
            Text::_('JBS_TPL_CUSTOM_COLOR')
        );

        // Tab content
        $html .= '<div class="tab-content">';

        // Named colors tab pane
        $html .= \sprintf(
            '<div class="tab-pane fade show active" id="%s_pane_named" role="tabpanel">',
            $this->id
        );

        // Search input
        $html .= \sprintf(
            '<input type="text" id="%s_search" class="form-control mb-2" placeholder="%s" '
            . 'oninput="filterColors_%s(this.value)" />',
            $this->id,
            Text::_('JBS_TPL_SEARCH_COLORS'),
            $this->id
        );

        // Color grid container
        $html .= \sprintf(
            '<div id="%s_grid" class="colorpicker-grid d-flex flex-wrap gap-1" '
            . 'style="max-height:180px;overflow-y:auto;">',
            $this->id
        );

        // Add color swatches
        foreach (self::CSS_COLORS as $name => $hex) {
            $isSelected = (strtolower($value) === $name);
            $html      .= $this->buildColorSwatch($name, $hex, $isSelected);
        }

        $html .= '</div></div>';

        // Custom color tab pane
        $html .= \sprintf(
            '<div class="tab-pane fade" id="%s_pane_custom" role="tabpanel">'
            . '<div class="d-flex flex-column align-items-center gap-3 py-3">'
            . '<label class="form-label mb-0">%s</label>'
            . '<input type="color" id="%s_picker" value="%s" class="form-control form-control-color" '
            . 'style="width:100px;height:100px;border:none;cursor:pointer;" '
            . 'onchange="previewCustomColor_%s(this.value)"%s%s />'
            . '<div class="d-flex align-items-center gap-2">'
            . '<input type="text" id="%s_hex_input" class="form-control" value="%s" '
            . 'style="width:100px;text-transform:uppercase;" maxlength="7" '
            . 'onchange="updateFromHexInput_%s(this.value)" />'
            . '<button type="button" class="btn btn-primary" onclick="applyCustomColor_%s()">%s</button>'
            . '</div>'
            . '</div></div>',
            $this->id,
            Text::_('JBS_TPL_PICK_CUSTOM_COLOR'),
            $this->id,
            $hexValue,
            $this->id,
            $disabled,
            $readonly,
            $this->id,
            $hexValue,
            $this->id,
            $this->id,
            Text::_('JAPPLY')
        );

        $html .= '</div></div>';

        // Hidden input that stores the actual value
        $html .= \sprintf(
            '<input type="hidden" name="%s" id="%s" value="%s"%s />',
            $this->name,
            $this->id,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            $required
        );

        $html .= '</div>';

        // Add JavaScript and CSS
        $html .= $this->buildStyles();
        $html .= $this->buildJavaScript();

        return $html;
    }

    /**
     * Build the preview swatch HTML
     *
     * @param   string  $value        Current value
     * @param   bool    $isNamedColor Whether value is a named color
     *
     * @return  string  The swatch HTML
     *
     * @since   10.2.0
     */
    protected function buildPreviewSwatch(string $value, bool $isNamedColor): string
    {
        $bgColor = $isNamedColor || $value === '' ? ($value ?: 'black') : $value;
        $transparentStyle = '';

        if ($value === 'transparent') {
            $transparentStyle = 'background-image:linear-gradient(45deg,#ccc 25%,transparent 25%),'
                . 'linear-gradient(-45deg,#ccc 25%,transparent 25%),'
                . 'linear-gradient(45deg,transparent 75%,#ccc 75%),'
                . 'linear-gradient(-45deg,transparent 75%,#ccc 75%);'
                . 'background-size:10px 10px;background-position:0 0,0 5px,5px -5px,-5px 0;';
            $bgColor = 'transparent';
        }

        return \sprintf(
            '<div id="%s_preview" class="colorpicker-preview border rounded" '
            . 'style="width:40px;height:40px;min-width:40px;background-color:%s;%scursor:pointer;" '
            . 'title="%s" onclick="toggleColorPalette_%s()"></div>',
            $this->id,
            $bgColor,
            $transparentStyle,
            $value ?: 'black',
            $this->id
        );
    }

    /**
     * Build a color swatch button
     *
     * @param   string  $name       Color name
     * @param   string  $hex        Hex value
     * @param   bool    $isSelected Whether this color is selected
     *
     * @return  string  The swatch HTML
     *
     * @since   10.2.0
     */
    protected function buildColorSwatch(string $name, string $hex, bool $isSelected): string
    {
        $selectedClass = $isSelected ? ' colorpicker-selected' : '';
        $transparentStyle = '';

        if ($hex === 'transparent') {
            $transparentStyle = 'background-image:linear-gradient(45deg,#ccc 25%,transparent 25%),'
                . 'linear-gradient(-45deg,#ccc 25%,transparent 25%),'
                . 'linear-gradient(45deg,transparent 75%,#ccc 75%),'
                . 'linear-gradient(-45deg,transparent 75%,#ccc 75%);'
                . 'background-size:8px 8px;background-position:0 0,0 4px,4px -4px,-4px 0;';
        }

        return \sprintf(
            '<button type="button" class="colorpicker-swatch%s" '
            . 'data-color="%s" data-hex="%s" title="%s (%s)" '
            . 'style="width:32px;height:32px;border:2px solid %s;border-radius:4px;'
            . 'background-color:%s;%scursor:pointer;padding:0;" '
            . 'onclick="selectColor_%s(\'%s\', \'%s\')"></button>',
            $selectedClass,
            $name,
            $hex,
            ucfirst($name),
            $hex,
            $isSelected ? '#fff' : 'transparent',
            $hex === 'transparent' ? 'transparent' : $hex,
            $transparentStyle,
            $this->id,
            $name,
            $hex
        );
    }

    /**
     * Build the CSS styles
     *
     * @return  string  The style tag
     *
     * @since   10.2.0
     */
    protected function buildStyles(): string
    {
        static $stylesAdded = false;

        if ($stylesAdded) {
            return '';
        }
        $stylesAdded = true;

        return '<style>
.colorpicker-swatch:hover { transform: scale(1.1); border-color: #666 !important; }
.colorpicker-swatch:focus { outline: 2px solid #0d6efd; outline-offset: 2px; }
.colorpicker-selected { border-color: #0d6efd !important; box-shadow: 0 0 0 2px rgba(13,110,253,0.5); }
.colorpicker-swatch[data-hidden="true"] { display: none !important; }
</style>';
    }

    /**
     * Build the JavaScript for color selection and filtering
     *
     * @return  string  The script tag with JavaScript
     *
     * @since   10.2.0
     */
    protected function buildJavaScript(): string
    {
        $id = $this->id;

        $js = <<<JS
function toggleColorPalette_{$id}() {
    var dropdown = document.getElementById('{$id}_dropdown');
    var isVisible = dropdown.style.display !== 'none';

    // Close all other open color palettes first
    document.querySelectorAll('.colorpicker-dropdown').forEach(function(d) {
        d.style.display = 'none';
    });

    if (!isVisible) {
        dropdown.style.display = 'block';
        document.getElementById('{$id}_search').focus();
    }
}

function selectColor_{$id}(name, hex) {
    var hidden = document.getElementById('{$id}');
    var preview = document.getElementById('{$id}_preview');
    var picker = document.getElementById('{$id}_picker');
    var hexInput = document.getElementById('{$id}_hex_input');
    var display = document.getElementById('{$id}_display');
    var dropdown = document.getElementById('{$id}_dropdown');

    // Update hidden value
    hidden.value = name;

    // Update display
    display.value = name;

    // Update preview
    if (name === 'transparent') {
        preview.style.backgroundImage = 'linear-gradient(45deg,#ccc 25%,transparent 25%),' +
            'linear-gradient(-45deg,#ccc 25%,transparent 25%),' +
            'linear-gradient(45deg,transparent 75%,#ccc 75%),' +
            'linear-gradient(-45deg,transparent 75%,#ccc 75%)';
        preview.style.backgroundSize = '10px 10px';
        preview.style.backgroundColor = 'transparent';
    } else {
        preview.style.backgroundImage = '';
        preview.style.backgroundColor = name;
    }
    preview.title = name;

    // Update color picker and hex input if not transparent
    if (hex !== 'transparent') {
        picker.value = hex;
        hexInput.value = hex;
    }

    // Update selected state
    var swatches = document.querySelectorAll('#{$id}_grid .colorpicker-swatch');
    swatches.forEach(function(s) {
        s.classList.remove('colorpicker-selected');
        s.style.borderColor = 'transparent';
    });
    var selected = document.querySelector('#{$id}_grid [data-color="' + name + '"]');
    if (selected) {
        selected.classList.add('colorpicker-selected');
        selected.style.borderColor = '#0d6efd';
    }

    // Close dropdown
    dropdown.style.display = 'none';

    hidden.dispatchEvent(new Event('change', { bubbles: true }));
}

function previewCustomColor_{$id}(hex) {
    // Update hex input to match color picker
    var hexInput = document.getElementById('{$id}_hex_input');
    hexInput.value = hex.toUpperCase();
}

function updateFromHexInput_{$id}(value) {
    // Validate and format hex value
    var hex = value.trim();
    if (!hex.startsWith('#')) hex = '#' + hex;
    if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
        var picker = document.getElementById('{$id}_picker');
        picker.value = hex;
    }
}

function applyCustomColor_{$id}() {
    var hidden = document.getElementById('{$id}');
    var preview = document.getElementById('{$id}_preview');
    var display = document.getElementById('{$id}_display');
    var picker = document.getElementById('{$id}_picker');
    var dropdown = document.getElementById('{$id}_dropdown');

    var hex = picker.value.toUpperCase();

    hidden.value = hex;
    display.value = hex;
    preview.style.backgroundImage = '';
    preview.style.backgroundColor = hex;
    preview.title = hex;

    // Clear selected state from all swatches
    var swatches = document.querySelectorAll('#{$id}_grid .colorpicker-swatch');
    swatches.forEach(function(s) {
        s.classList.remove('colorpicker-selected');
        s.style.borderColor = 'transparent';
    });

    // Close dropdown
    dropdown.style.display = 'none';

    hidden.dispatchEvent(new Event('change', { bubbles: true }));
}

function filterColors_{$id}(query) {
    var swatches = document.querySelectorAll('#{$id}_grid .colorpicker-swatch');
    var q = query.toLowerCase().trim();

    swatches.forEach(function(swatch) {
        var colorName = swatch.getAttribute('data-color');
        var hexValue = swatch.getAttribute('data-hex');

        if (q === '' || colorName.includes(q) || hexValue.toLowerCase().includes(q)) {
            swatch.setAttribute('data-hidden', 'false');
            swatch.style.display = '';
        } else {
            swatch.setAttribute('data-hidden', 'true');
        }
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    var wrapper = e.target.closest('.colorpicker-field-wrapper');
    if (!wrapper || !wrapper.querySelector('#{$id}')) {
        var dropdown = document.getElementById('{$id}_dropdown');
        if (dropdown) dropdown.style.display = 'none';
    }
});
JS;

        return '<script>' . $js . '</script>';
    }
}
