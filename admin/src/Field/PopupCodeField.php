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
use Joomla\CMS\Language\Text;

/**
 * Popup Code Field - Text field with clickable code insertion buttons
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class PopupCodeField extends TextField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'PopupCode';

    /**
     * Available codes for insertion
     *
     * @var  array
     *
     * @since 10.2.0
     */
    protected array $codes = [
        '{{scripture}}' => 'JBS_CMN_SCRIPTURE',
        '{{title}}' => 'JBS_CMN_TITLE',
        '{{studydate}}' => 'JBS_CMN_DATE',
        '{{teacher}}' => 'JBS_CMN_TEACHER',
        '{{teacherimage}}' => 'JBS_CMN_TEACHER_IMAGE',
        '{{filename}}' => 'JBS_TPL_MEDIA_FILENAME',
        '{{description}}' => 'JBS_CMN_DESCRIPTION',
        '{{length}}' => 'JBS_CMN_DURATION',
        '{{series}}' => 'JBS_CMN_SERIES',
        '{{series_thumbnail}}' => 'JBS_CMN_SERIES_THUMBNAIL',
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
        // Get the standard text input
        $html = parent::getInput();

        // Build the code buttons
        $buttons = '<div class="popup-code-buttons mt-2">';
        $buttons .= '<small class="text-muted me-2">' . Text::_('JBS_TPL_INSERT_CODE') . ':</small>';

        foreach ($this->codes as $code => $labelKey) {
            $label = Text::_($labelKey);
            $buttons .= sprintf(
                '<button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1 popup-code-btn" '
                . 'data-code="%s" data-target="%s" title="%s">%s</button>',
                htmlspecialchars($code, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($code, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            );
        }

        $buttons .= '</div>';

        // Add the JavaScript for code insertion (only once per page)
        static $jsAdded = false;
        if (!$jsAdded) {
            $js = <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('popup-code-btn')) {
            e.preventDefault();
            var code = e.target.dataset.code;
            var targetId = e.target.dataset.target;
            var input = document.getElementById(targetId);
            if (input) {
                var start = input.selectionStart;
                var end = input.selectionEnd;
                var text = input.value;
                input.value = text.substring(0, start) + code + text.substring(end);
                input.selectionStart = input.selectionEnd = start + code.length;
                input.focus();
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });
});
JS;
            $html .= '<script>' . $js . '</script>';
            $jsAdded = true;
        }

        return $html . $buttons;
    }
}
