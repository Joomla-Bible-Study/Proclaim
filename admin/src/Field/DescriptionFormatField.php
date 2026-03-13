<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Description Format Field — textarea with clickable placeholder insert badges.
 *
 * Used in server addon settings to let admins compose their video description
 * template by clicking placeholder tokens that get inserted at the cursor.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class DescriptionFormatField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'DescriptionFormat';

    /**
     * Available placeholders with their language keys.
     *
     * @var  array<string, string>
     *
     * @since 10.2.0
     */
    protected const PLACEHOLDERS = [
        '{title}'      => 'JBS_CMN_TITLE',
        '{series}'     => 'JBS_CMN_SERIES',
        '{teachers}'   => 'JBS_CMN_TEACHERS',
        '{date}'       => 'JBS_CMN_DATE',
        '{scriptures}' => 'JBS_CMN_SCRIPTURE',
        '{studyintro}' => 'JBS_CMN_STUDY_INTRO',
        '{topics}'     => 'JBS_CMN_TOPICS',
        '{chapters}'   => 'JBS_CMN_CHAPTERS',
        '{url}'        => 'JBS_CMN_URL',
    ];

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since 10.2.0
     */
    protected function getInput(): string
    {
        $rows     = (int) ($this->element['rows'] ?? 6);
        $class    = 'form-control' . ($this->element['class'] ? ' ' . $this->element['class'] : '');
        $disabled = $this->disabled ? ' disabled' : '';
        $readonly = $this->readonly ? ' readonly' : '';
        $value    = htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8');

        // Build textarea
        $html = \sprintf(
            '<textarea name="%s" id="%s" class="%s" rows="%d"%s%s>%s</textarea>',
            $this->name,
            $this->id,
            $class,
            $rows,
            $disabled,
            $readonly,
            $value
        );

        // Build placeholder badge row
        $html .= '<div class="mt-2">';
        $html .= '<small class="text-muted d-block mb-1">'
            . Text::_('JBS_ADDON_DESC_FORMAT_INSERT') . '</small>';
        $html .= '<div class="d-flex flex-wrap gap-1">';

        foreach (self::PLACEHOLDERS as $token => $langKey) {
            $label = Text::_($langKey);
            $html .= \sprintf(
                '<button type="button" class="btn btn-outline-secondary btn-sm cwm-desc-token-btn" '
                . 'data-token="%s" data-target="%s" title="%s">%s</button>',
                htmlspecialchars($token, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($token . ' — ' . $label, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= '</div></div>';

        // Add JavaScript (once per page)
        $html .= $this->buildJavaScript();

        return $html;
    }

    /**
     * Build the JavaScript for token insertion at cursor position.
     *
     * @return  string  The script tag
     *
     * @since 10.2.0
     */
    protected function buildJavaScript(): string
    {
        static $jsAdded = false;

        if ($jsAdded) {
            return '';
        }
        $jsAdded = true;

        $js = <<<'JS'
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.cwm-desc-token-btn');
    if (!btn) return;
    e.preventDefault();
    var token  = btn.dataset.token;
    var target = document.getElementById(btn.dataset.target);
    if (!target) return;
    var start = target.selectionStart || 0;
    var end   = target.selectionEnd || 0;
    var text  = target.value;
    target.value = text.substring(0, start) + token + text.substring(end);
    target.selectionStart = target.selectionEnd = start + token.length;
    target.focus();
    target.dispatchEvent(new Event('change', { bubbles: true }));
});
JS;

        return '<script>' . $js . '</script>';
    }
}
