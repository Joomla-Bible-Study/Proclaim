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
 * Custom Field - Text/Textarea field with modal code insertion picker
 *
 * Configurable via XML attributes:
 * - codeset="podcast" - Uses podcast codes with {single braces}
 * - codeset="template" - Uses template codes with {{double braces}}
 * - input="text" or input="textarea" - Determines input type
 *
 * @package  Proclaim.Admin
 * @since 10.1.0
 */
class CustomField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.1.0
     */
    protected $type = 'Custom';

    /**
     * Flag to track if modal HTML has been added to the page
     *
     * @var  bool
     *
     * @since 10.1.0
     */
    protected static bool $modalAdded = false;

    /**
     * Podcast codes for insertion (single braces)
     *
     * @var  array<string, string>
     *
     * @since 10.1.0
     */
    protected const PODCAST_CODES = [
        '{scripture1}'    => 'JBS_CMN_SCRIPTURE',
        '{scripture2}'    => 'JBS_CMN_SCRIPTURE2',
        '{secondary}'     => 'JBS_CMN_SECONDARY_REFERENCES',
        '{duration}'      => 'JBS_CMN_DURATION',
        '{studytitle}'    => 'JBS_CMN_TITLE',
        '{teacher}'       => 'JBS_CMN_TEACHER',
        '{date}'          => 'JBS_CMN_DATE',
        '{studyintro}'    => 'JBS_CMN_STUDY_INTRO',
        '{location_text}' => 'JBS_CMN_LOCATION',
        '{message_type}'  => 'JBS_CMN_MESSAGETYPE',
    ];

    /**
     * Template codes for insertion (double braces)
     *
     * @var  array<string, string>
     *
     * @since 10.1.0
     */
    protected const TEMPLATE_CODES = [
        '{{scripture}}'        => 'JBS_CMN_SCRIPTURE',
        '{{title}}'            => 'JBS_CMN_TITLE',
        '{{studydate}}'        => 'JBS_CMN_DATE',
        '{{teacher}}'          => 'JBS_CMN_TEACHER',
        '{{teacherimage}}'     => 'JBS_CMN_TEACHER_IMAGE',
        '{{filename}}'         => 'JBS_TPL_MEDIA_FILENAME',
        '{{description}}'      => 'JBS_CMN_DESCRIPTION',
        '{{length}}'           => 'JBS_CMN_DURATION',
        '{{series}}'           => 'JBS_CMN_SERIES',
        '{{series_thumbnail}}' => 'JBS_CMN_SERIES_THUMBNAIL',
    ];

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since 10.1.0
     */
    protected function getInput(): string
    {
        // Load Bootstrap Modal JavaScript
        HTMLHelper::_('bootstrap.modal');

        // Determine input type from XML attribute (default: text)
        $inputType = $this->element['input'] ?? 'text';

        // Get codeset from XML attribute (default: template)
        $codeset = (string) ($this->element['codeset'] ?? 'template');
        $codes   = $codeset === 'podcast' ? self::PODCAST_CODES : self::TEMPLATE_CODES;

        // Generate unique modal ID for this codeset
        $modalId = 'customCodeModal_' . $codeset;

        // Build the input element wrapped with button in a flex container
        $html = '<div class="d-flex align-items-start gap-2">';
        $html .= $this->buildInputHtml($inputType);
        $html .= $this->buildButtonHtml($modalId);
        $html .= '</div>';

        // Add modal HTML (only once per codeset type)
        $html .= $this->buildModalHtml($modalId, $codeset, $codes);

        // Add JavaScript (only once per page)
        $html .= $this->buildJavaScript();

        return $html;
    }

    /**
     * Build the input element HTML (text or textarea)
     *
     * @param   string  $inputType  The input type (text or textarea)
     *
     * @return  string  The input HTML
     *
     * @since 10.1.0
     */
    protected function buildInputHtml(string $inputType): string
    {
        $class    = 'form-control' . ($this->element['class'] ? ' ' . $this->element['class'] : '');
        $size     = (int) ($this->element['size'] ?? 50);
        $disabled = $this->disabled ? ' disabled' : '';
        $readonly = $this->readonly ? ' readonly' : '';
        $required = $this->required ? ' required' : '';
        $value    = htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8');

        if ($inputType === 'textarea') {
            $rows = (int) ($this->element['rows'] ?? 3);
            $cols = (int) ($this->element['cols'] ?? 50);

            return \sprintf(
                '<textarea name="%s" id="%s" class="%s" rows="%d" cols="%d"%s%s%s>%s</textarea>',
                $this->name,
                $this->id,
                $class,
                $rows,
                $cols,
                $disabled,
                $readonly,
                $required,
                $value
            );
        }

        // Default: text input
        return \sprintf(
            '<input type="text" name="%s" id="%s" value="%s" class="%s" size="%d"%s%s%s />',
            $this->name,
            $this->id,
            $value,
            $class,
            $size,
            $disabled,
            $readonly,
            $required
        );
    }

    /**
     * Build the Insert Code button HTML
     *
     * @param   string  $modalId  The modal ID to target
     *
     * @return  string  The button HTML
     *
     * @since 10.1.0
     */
    protected function buildButtonHtml(string $modalId): string
    {
        $label = Text::_('JBS_TPL_INSERT_CODE');

        return \sprintf(
            '<button type="button" class="btn btn-secondary text-nowrap custom-code-btn" '
            . 'data-bs-toggle="modal" data-bs-target="#%s" data-target-input="%s" '
            . 'title="%s" aria-label="%s">'
            . '<span class="icon-code" aria-hidden="true"></span> %s</button>',
            htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
            $label,
            $label,
            $label
        );
    }

    /**
     * Build the modal HTML for code selection
     *
     * @param   string               $modalId  The modal ID
     * @param   string               $codeset  The codeset name (for tracking)
     * @param   array<string,string> $codes    The codes array
     *
     * @return  string  The modal HTML
     *
     * @since 10.1.0
     */
    protected function buildModalHtml(string $modalId, string $codeset, array $codes): string
    {
        // Track which modals have been added (by codeset)
        static $addedModals = [];

        if (isset($addedModals[$codeset])) {
            return '';
        }
        $addedModals[$codeset] = true;

        // Build code buttons for the modal body (AAA accessible: large touch targets, clear labels)
        $codeButtons = '';
        foreach ($codes as $code => $labelKey) {
            $label = Text::_($labelKey);
            $codeButtons .= \sprintf(
                '<button type="button" class="btn btn-secondary btn-lg m-2 custom-code-insert" '
                . 'data-code="%s" title="%s" style="min-height:48px;">'
                . '%s <code class="ms-2 fs-6" style="color:#fff;background:transparent;">%s</code></button>',
                htmlspecialchars($code, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($code, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($code, ENT_QUOTES, 'UTF-8')
            );
        }

        // Build the Bootstrap 5 modal (AAA accessible: proper padding, font sizes, contrast)
        return \sprintf(
            '<div class="modal fade" id="%s" tabindex="-1" aria-labelledby="%sLabel" aria-hidden="true">'
            . '<div class="modal-dialog modal-lg modal-dialog-centered">'
            . '<div class="modal-content">'
            . '<div class="modal-header px-4 py-3">'
            . '<h2 class="modal-title" id="%sLabel">%s</h2>'
            . '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="%s"></button>'
            . '</div>'
            . '<div class="modal-body px-4 py-4">'
            . '<p class="fs-6 mb-3">%s</p>'
            . '<div class="d-flex flex-wrap justify-content-start">%s</div>'
            . '</div>'
            . '<div class="modal-footer px-4 py-3">'
            . '<button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">%s</button>'
            . '</div>'
            . '</div>'
            . '</div>'
            . '</div>',
            htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'),
            Text::_('JBS_TPL_INSERT_CODE'),
            Text::_('JCLOSE'),
            Text::_('JBS_TPL_SELECT_CODE_TO_INSERT'),
            $codeButtons,
            Text::_('JCLOSE')
        );
    }

    /**
     * Build the JavaScript for code insertion
     *
     * @return  string  The script tag with JavaScript
     *
     * @since 10.1.0
     */
    protected function buildJavaScript(): string
    {
        static $jsAdded = false;

        if ($jsAdded) {
            return '';
        }
        $jsAdded = true;

        $js = <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    // Track which input field triggered the modal
    var currentTargetInput = null;

    // When Insert Code button is clicked, store the target input ID
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.custom-code-btn');
        if (btn) {
            currentTargetInput = btn.dataset.targetInput;
        }
    });

    // When a code button inside modal is clicked, insert the code
    document.addEventListener('click', function(e) {
        if (e.target.closest('.custom-code-insert')) {
            e.preventDefault();
            var btn = e.target.closest('.custom-code-insert');
            var code = btn.dataset.code;

            if (currentTargetInput) {
                var input = document.getElementById(currentTargetInput);
                if (input) {
                    var start = input.selectionStart || 0;
                    var end = input.selectionEnd || 0;
                    var text = input.value;
                    input.value = text.substring(0, start) + code + text.substring(end);
                    input.selectionStart = input.selectionEnd = start + code.length;
                    input.focus();
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Close the modal
            var modal = btn.closest('.modal');
            if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        }
    });
});
JS;

        return '<script>' . $js . '</script>';
    }
}
