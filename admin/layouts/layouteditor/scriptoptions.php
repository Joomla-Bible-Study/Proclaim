<?php

/**
 * Layout Editor Script Options — centralized registration
 *
 * Registers all JS language strings (auto-scanned from compiled JS) and
 * dropdown option arrays (extracted dynamically from form fields) needed
 * by the Layout Editor.  Called from both the template editor (edit.php)
 * and the LayoutEditorField (module / menu-item contexts).
 *
 * Expected $displayData keys:
 *   form                   — Joomla\CMS\Form\Form  (required for dynamic options)
 *   templateId             — int|null
 *   templateParams         — array
 *   elementDefinitions     — array
 *   settingsConfig         — array
 *   landingSectionSettings — array
 *   prependInherit         — bool  (true = "Use Template Default"; false = "Use Global Setting")
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/** @var array $displayData */
$form                   = $displayData['form'] ?? null;
$templateId             = $displayData['templateId'] ?? null;
$templateParams         = $displayData['templateParams'] ?? [];
$elementDefinitions     = $displayData['elementDefinitions'] ?? [];
$settingsConfig         = $displayData['settingsConfig'] ?? [];
$landingSectionSettings = $displayData['landingSectionSettings'] ?? [];
$prependInherit         = !empty($displayData['prependInherit']);

$document = Factory::getApplication()->getDocument();

// ─── 1. Auto-scan language strings from compiled JS ─────────────────────
$jsFile = JPATH_ROOT . '/media/com_proclaim/js/layout-editor.js';

if (is_file($jsFile)) {
    $jsContent = file_get_contents($jsFile);

    if ($jsContent && preg_match_all("/trans\(['\"]([A-Z][A-Z0-9_]+)['\"]\)/", $jsContent, $matches)) {
        foreach (array_unique($matches[1]) as $key) {
            Text::script($key);
        }
    }
}

// ─── 2. Extract dropdown options dynamically from form fields ───────────

// The "defer" label depends on context:
//   Template editor → "Use Global Setting" (defers to component-level config)
//   Module/menu     → "Use Template Default" (defers to the assigned template)
$deferLabel = $prependInherit
    ? Text::_('JBS_CMN_USE_TEMPLATE_DEFAULT')
    : Text::_('JBS_TPL_USE_GLOBAL_SETTING');

$extractFieldOptions = function (string $fieldName, string $group = 'params') use ($form): array {
    if (!$form) {
        return [];
    }

    $field = $form->getField($fieldName, $group);

    if (!$field) {
        return [];
    }

    $result = [];

    foreach ($field->options as $option) {
        $result[] = ['value' => (string) $option->value, 'label' => $option->text];
    }

    return $result;
};

// dateFormat: always has a "defer" blank option (template defers to Joomla global via value 8)
$dateFormatOptions = array_merge(
    [['value' => '', 'label' => $deferLabel]],
    $extractFieldOptions('date_format')
);

// showVerses, showVersion: both template and module/menu contexts need a default option.
// Template editor context uses "Use Global Setting"; module/menu uses "Use Template Default".
$showVersesOptions  = $extractFieldOptions('show_verses');
$showVersionOptions = [
    ['value' => '0', 'label' => Text::_('JNO')],
    ['value' => '1', 'label' => Text::_('JYES')],
];

$separatorOptions = $extractFieldOptions('scripture_separator');
array_unshift($showVersesOptions, ['value' => '', 'label' => $deferLabel]);
array_unshift($showVersionOptions, ['value' => '', 'label' => $deferLabel]);
array_unshift($separatorOptions, ['value' => '', 'label' => $deferLabel]);

// These dropdowns have no "defer" option — just the actual choices
$elementTypeOptions     = $extractFieldOptions('scripture1element');
$linkTypeOptions        = $extractFieldOptions('scripture1linktype');
$teacherLinkTypeOptions = $extractFieldOptions('tsteacherlinktype');
$seriesLinkTypeOptions  = $extractFieldOptions('sserieslinktype');

// ─── 3. Register all script options ─────────────────────────────────────
if ($templateId !== null) {
    $document->addScriptOptions('com_proclaim.templateId', (int) $templateId);
}

if (!empty($templateParams)) {
    $document->addScriptOptions('com_proclaim.templateParams', $templateParams);
}

if (!empty($elementDefinitions)) {
    $document->addScriptOptions('com_proclaim.elementDefinitions', $elementDefinitions);
}

if (!empty($settingsConfig)) {
    $document->addScriptOptions('com_proclaim.settingsConfig', $settingsConfig);
}

if (!empty($landingSectionSettings)) {
    $document->addScriptOptions('com_proclaim.landingSectionSettings', $landingSectionSettings);
}

if (!empty($dateFormatOptions)) {
    $document->addScriptOptions('com_proclaim.dateFormatOptions', $dateFormatOptions);
}

if (!empty($showVersesOptions)) {
    $document->addScriptOptions('com_proclaim.showVersesOptions', $showVersesOptions);
}

$document->addScriptOptions('com_proclaim.showVersionOptions', $showVersionOptions);
$document->addScriptOptions('com_proclaim.separatorOptions', $separatorOptions);

if (!empty($elementTypeOptions)) {
    $document->addScriptOptions('com_proclaim.elementTypeOptions', $elementTypeOptions);
}

if (!empty($linkTypeOptions)) {
    $document->addScriptOptions('com_proclaim.linkTypeOptions', $linkTypeOptions);
}

if (!empty($teacherLinkTypeOptions)) {
    $document->addScriptOptions('com_proclaim.teacherLinkTypeOptions', $teacherLinkTypeOptions);
}

if (!empty($seriesLinkTypeOptions)) {
    $document->addScriptOptions('com_proclaim.seriesLinkTypeOptions', $seriesLinkTypeOptions);
}
