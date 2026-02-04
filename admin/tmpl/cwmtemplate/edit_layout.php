<?php

/**
 * Layout Editor Tab
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

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplate\HtmlView $this */

/**
 * This file provides the layout editor tab content.
 * It creates a visual drag-and-drop interface for arranging template display elements.
 *
 * The editor is initialized by the layout-editor.js script which:
 * - Reads existing values from the hidden form fields
 * - Provides a visual canvas with 6 rows
 * - Allows drag-and-drop positioning of elements
 * - Syncs changes back to the form fields on save
 */

// =====================================================================
// UI Language Strings (used directly via Joomla.Text._() in JavaScript)
// These are for dynamic UI elements that aren't part of option arrays
// =====================================================================
Text::script('JBS_TPL_LAYOUT_HELP');
Text::script('JBS_TPL_AVAILABLE_ELEMENTS');
Text::script('JBS_TPL_ROW');
Text::script('JBS_TPL_DROP_ELEMENTS_HERE');
Text::script('JBS_TPL_ELEMENT_SETTINGS');
Text::script('JBS_TPL_REMOVE_ELEMENT');
Text::script('JBS_TPL_COLSPAN');
Text::script('JBS_TPL_COLSPAN_DESC');
Text::script('JBS_TPL_ELEMENT');
Text::script('JBS_TPL_ELEMENT_DESC');
Text::script('JBS_TPL_TYPE_OF_LINK');
Text::script('JBS_TPL_TYPE_OF_LINK_DESC');
Text::script('JBS_TPL_CUSTOMCLASS');
Text::script('JBS_TPL_CUSTOMCLASS_DESC');
Text::script('JBS_TPL_DATE_FORMAT');
Text::script('JBS_TPL_DATE_FORMAT_DESC');
Text::script('JBS_TPL_VIEW_SETTINGS');
Text::script('JBS_TPL_DRAG_TO_REORDER');
Text::script('JBS_TPL_TOGGLE_SECTION');
Text::script('JBS_TPL_SECTION_DISABLED');
Text::script('JBS_TPL_PLEASE_WAIT');
Text::script('JBS_TPL_LOADING');
Text::script('JBS_TPL_UNSAVED_CHANGES');
Text::script('JBS_TPL_UNSAVED_CHANGES_CONFIRM');
Text::script('JBS_TPL_MODAL_UNSAVED_CHANGES');
Text::script('JCLOSE');
Text::script('JCANCEL');
Text::script('JAPPLY');

// Pass template params to JavaScript for initial loading
// The form fields are lazy-loaded, so we need to provide the data directly
$params = $this->item->params;
if (\is_string($params)) {
    $paramsArray = json_decode($params, true) ?: [];
} elseif (\is_object($params)) {
    $paramsArray = $params->toArray();
} else {
    $paramsArray = (array) $params;
}

$document = $this->getDocument();
$document->addScriptOptions('com_proclaim.templateParams', $paramsArray);

// Pass pre-translated date format options to JavaScript
// This is more maintainable than duplicating strings in JS and registering via Text::script
$dateFormatOptions = [
    ['value' => '', 'label' => Text::_('JBS_TPL_USE_GLOBAL_SETTING')],
    ['value' => '0', 'label' => Text::_('JBS_TPL_DATE_FORMAT_MMM_D_YYYY')],
    ['value' => '1', 'label' => Text::_('JBS_TPL_DATE_FORMAT_MMM_D')],
    ['value' => '2', 'label' => Text::_('JBS_TPL_DATE_FORMAT_M_D_YYYY')],
    ['value' => '3', 'label' => Text::_('JBS_TPL_DATE_FORMAT_M_D')],
    ['value' => '4', 'label' => Text::_('JBS_TPL_DATE_FORMAT_WD_MMMM_D_YYYY')],
    ['value' => '5', 'label' => Text::_('JBS_TPL_DATE_FORMAT_MMMM_D_YYYY')],
    ['value' => '6', 'label' => Text::_('JBS_TPL_DATE_FORMAT_D_MMMM_YYYY')],
    ['value' => '7', 'label' => Text::_('JBS_TPL_DATE_FORMAT_D_M_YYYY')],
    ['value' => '8', 'label' => Text::_('JBS_TPL_DATE_FORMAT_USE_GLOBAL')],
    ['value' => '9', 'label' => Text::_('JBS_TPL_DATE_FORMAT_YYYY_MM_DD')],
];
$document->addScriptOptions('com_proclaim.dateFormatOptions', $dateFormatOptions);

// Link type options (pre-translated) - different options for different contexts
// Full options for Messages List and Study Details
// VirtueMart and DOCman options only shown if enabled in component settings
$componentParams = \Joomla\CMS\Component\ComponentHelper::getParams('com_proclaim');
$enableVirtuemart = (int) $componentParams->get('enable_virtuemart', 0);
$enableDocman     = (int) $componentParams->get('enable_docman', 0);

$linkTypeOptions = [
    ['value' => '0', 'label' => Text::_('JBS_TPL_NO_LINK')],
    ['value' => '1', 'label' => Text::_('JBS_TPL_LINK_TO_DETAILS')],
    ['value' => '4', 'label' => Text::_('JBS_TPL_LINK_TO_DETAILS_TOOLTIP')],
    ['value' => '2', 'label' => Text::_('JBS_TPL_LINK_TO_MEDIA')],
    ['value' => '9', 'label' => Text::_('JBS_TPL_LINK_TO_DOWNLOAD')],
    ['value' => '5', 'label' => Text::_('JBS_TPL_LINK_TO_MEDIA_TOOLTIP')],
    ['value' => '3', 'label' => Text::_('JBS_TPL_LINK_TO_TEACHERS_PROFILE')],
    ['value' => '6', 'label' => Text::_('JBS_TPL_LINK_TO_FIRST_ARTICLE')],
];

// Add VirtueMart option only if enabled
if ($enableVirtuemart) {
    $linkTypeOptions[] = ['value' => '7', 'label' => Text::_('JBS_TPL_LINK_TO_VIRTUEMART')];
}

// Add DOCman option only if enabled
if ($enableDocman) {
    $linkTypeOptions[] = ['value' => '8', 'label' => Text::_('JBS_TPL_LINK_TO_DOCMAN')];
}

$document->addScriptOptions('com_proclaim.linkTypeOptions', $linkTypeOptions);

// Teacher-specific link options (for Teachers List and Teacher Details)
$teacherLinkTypeOptions = [
    ['value' => '0', 'label' => Text::_('JBS_TPL_NO_LINK')],
    ['value' => '3', 'label' => Text::_('JBS_TPL_LINK_TO_TEACHERS_PROFILE')],
];
$document->addScriptOptions('com_proclaim.teacherLinkTypeOptions', $teacherLinkTypeOptions);

// Series-specific link options (for Series List and Series Details)
$seriesLinkTypeOptions = [
    ['value' => '0', 'label' => Text::_('JBS_TPL_NO_LINK')],
    ['value' => '1', 'label' => Text::_('JBS_TPL_LINK_TO_DETAILS')],
];
$document->addScriptOptions('com_proclaim.seriesLinkTypeOptions', $seriesLinkTypeOptions);

// Element type options (HTML wrapper elements, pre-translated)
$elementTypeOptions = [
    ['value' => '0', 'label' => Text::_('JBS_CMN_NONE')],
    ['value' => '1', 'label' => Text::_('JBS_TPL_PARAGRAPH')],
    ['value' => '2', 'label' => Text::_('JBS_TPL_HEADER1')],
    ['value' => '3', 'label' => Text::_('JBS_TPL_HEADER2')],
    ['value' => '4', 'label' => Text::_('JBS_TPL_HEADER3')],
    ['value' => '5', 'label' => Text::_('JBS_TPL_HEADER4')],
    ['value' => '6', 'label' => Text::_('JBS_TPL_HEADER5')],
    ['value' => '7', 'label' => Text::_('JBS_TPL_BLOCKQUOTE')],
];
$document->addScriptOptions('com_proclaim.elementTypeOptions', $elementTypeOptions);

// =====================================================================
// Element definitions extracted from template.xml (single source of truth)
// This ensures Layout Editor elements match the form XML definitions
// =====================================================================
$form = $this->form;

/**
 * Get translated label from a fieldset's XML label attribute.
 *
 * @param string $fieldsetName The fieldset name to get the label from
 *
 * @return string|null Translated label or null if not found
 */
$getFieldsetLabel = function (string $fieldsetName) use ($form): ?string {
    $fieldsets = $form->getFieldsets();

    if (isset($fieldsets[$fieldsetName]) && !empty($fieldsets[$fieldsetName]->label)) {
        return Text::_($fieldsets[$fieldsetName]->label);
    }

    return null;
};

/**
 * Extract element definitions from form fieldsets by finding '*row' fields.
 * The label attribute on row fields defines the element's display name.
 *
 * @param array  $fieldsetNames Array of fieldset names to parse
 * @param string $prefix        Prefix to strip from field names (e.g., 'ts', 'd')
 *
 * @return array Array of ['id' => elementId, 'label' => translatedLabel]
 */
$extractElements = function (array $fieldsetNames, string $prefix) use ($form): array {
    $elements = [];
    $seen     = [];

    foreach ($fieldsetNames as $fieldsetName) {
        $fields = $form->getFieldset($fieldsetName);

        foreach ($fields as $field) {
            $name = $field->fieldname;

            // Only fields ending with 'row' define element positions
            if (!str_ends_with($name, 'row')) {
                continue;
            }

            // Extract element ID: remove prefix and 'row' suffix
            $elementId = substr($name, \strlen($prefix), -3);

            // Skip duplicates (same element may appear in multiple fieldsets)
            if (isset($seen[$elementId])) {
                continue;
            }
            $seen[$elementId] = true;

            // Get translated label from field's XML label attribute
            $labelKey = $field->getAttribute('label');

            $elements[] = [
                'id'    => $elementId,
                'label' => Text::_($labelKey),
            ];
        }
    }

    return $elements;
};

// Build element definitions dynamically from form XML fieldsets
// Fieldset mappings follow the template.xml structure
// Context labels are read from the first fieldset's label attribute
$elementDefinitions = [
    'messages' => [
        'label'    => $getFieldsetLabel('DISPLAYELEMENTS1') ?? Text::_('JBS_TPL_MESSAGES_LIST'),
        'prefix'   => '',
        'elements' => $extractElements(
            ['DISPLAYELEMENTS1', 'DISPLAYELEMENTS2', 'DISPLAYELEMENTS3',
             'DISPLAYELEMENTS4', 'DISPLAYELEMENTS5', 'DISPLAYELEMENTS6'],
            ''
        ),
    ],
    'details' => [
        'label'    => $getFieldsetLabel('DDISPLAYELEMENTS1') ?? Text::_('JBS_TPL_STUDY_DETAILS'),
        'prefix'   => 'd',
        'elements' => $extractElements(
            ['DDISPLAYELEMENTS1', 'DDISPLAYELEMENTS2', 'DDISPLAYELEMENTS3',
             'DDISPLAYELEMENTS4', 'DDISPLAYELEMENTS5', 'DDISPLAYELEMENTS6'],
            'd'
        ),
    ],
    'teachers' => [
        'label'    => $getFieldsetLabel('TEACHERDISPLAY') ?? Text::_('JBS_TPL_TEACHERS_LIST'),
        'prefix'   => 'ts',
        'elements' => $extractElements(['TEACHERDISPLAY'], 'ts'),
    ],
    'teacherDetails' => [
        'label'    => $getFieldsetLabel('TEACHERDETAILSDISPLAY') ?? Text::_('JBS_TPL_TEACHER_DETAILS'),
        'prefix'   => 'td',
        'elements' => $extractElements(['TEACHERDETAILSDISPLAY'], 'td'),
    ],
    'series' => [
        'label'    => $getFieldsetLabel('SERIESDISPLAY') ?? Text::_('JBS_TPL_SERIES_LIST'),
        'prefix'   => 's',
        'elements' => $extractElements(['SERIESDISPLAY'], 's'),
    ],
    'seriesDetails' => [
        'label'    => $getFieldsetLabel('SERIESDETAILDISPLAY') ?? Text::_('JBS_TPL_SERIES_DETAILS'),
        'prefix'   => 'sd',
        'elements' => $extractElements(['SERIESDETAILDISPLAY'], 'sd'),
    ],
    'landingPage' => [
        'label'       => $getFieldsetLabel('LANDINGPAGE') ?? Text::_('JBS_TPL_LANDING_PAGE'),
        'prefix'      => '',
        'isOrderOnly' => true,
        // Landing page sections extracted from headingorder list field options
        // Each option value is the section ID, option text is the language key
        'elements'    => (function () use ($form): array {
            $sections = [];

            // Get the headingorder_1 field which has all sections as options
            $field = $form->getField('headingorder_1', 'params');

            if ($field) {
                // Access the XML element's options
                $element = $field->element ?? null;

                if ($element) {
                    foreach ($element->option as $option) {
                        $sectionId = (string) $option['value'];
                        $labelKey  = (string) $option;

                        $sections[] = [
                            'id'         => $sectionId,
                            'label'      => Text::_($labelKey),
                            'showParam'  => 'show' . $sectionId,
                            'labelParam' => $sectionId . 'label',
                        ];
                    }
                }
            }

            // Fallback if field parsing fails
            if (empty($sections)) {
                $sections = [
                    ['id' => 'teachers', 'label' => Text::_('JBS_CMN_TEACHERS'), 'showParam' => 'showteachers', 'labelParam' => 'teacherslabel'],
                    ['id' => 'series', 'label' => Text::_('JBS_CMN_SERIES'), 'showParam' => 'showseries', 'labelParam' => 'serieslabel'],
                    ['id' => 'books', 'label' => Text::_('JBS_CMN_BOOKS'), 'showParam' => 'showbooks', 'labelParam' => 'bookslabel'],
                    ['id' => 'topics', 'label' => Text::_('JBS_CMN_TOPICS'), 'showParam' => 'showtopics', 'labelParam' => 'topicslabel'],
                    ['id' => 'locations', 'label' => Text::_('JBS_CMN_LOCATIONS'), 'showParam' => 'showlocations', 'labelParam' => 'locationslabel'],
                    ['id' => 'messagetypes', 'label' => Text::_('JBS_CMN_MESSAGETYPES'), 'showParam' => 'showmessagetypes', 'labelParam' => 'messagetypeslabel'],
                    ['id' => 'years', 'label' => Text::_('JBS_CMN_YEARS'), 'showParam' => 'showyears', 'labelParam' => 'yearslabel'],
                ];
            }

            return $sections;
        })(),
    ],
];
$document->addScriptOptions('com_proclaim.elementDefinitions', $elementDefinitions);

// Pass template ID for lazy-loading fieldsets
$templateId = (int) $this->item->id;
$document->addScriptOptions('com_proclaim.templateId', $templateId);

// =====================================================================
// Settings panel configuration extracted from XML fieldsets
// Fieldsets with layoutContext attribute appear in View Settings panel
// Fieldsets with landingSection attribute provide section-specific settings
// =====================================================================
$fieldsets      = $form->getFieldsets();
$settingsConfig = [];
$landingSectionSettings = [];

foreach ($fieldsets as $fieldsetName => $fieldset) {
    // Check for layoutContext attribute (View Settings panel)
    if (!empty($fieldset->layoutContext)) {
        $contexts = array_map('trim', explode(',', $fieldset->layoutContext));
        $label    = !empty($fieldset->label) ? Text::_($fieldset->label) : $fieldsetName;

        foreach ($contexts as $context) {
            if (!isset($settingsConfig[$context])) {
                $settingsConfig[$context] = [];
            }
            $settingsConfig[$context][] = [
                'fieldset' => $fieldsetName,
                'label'    => $label,
            ];
        }
    }

    // Check for landingSection attribute (Landing Page section settings)
    if (!empty($fieldset->landingSection)) {
        $label = !empty($fieldset->label) ? Text::_($fieldset->label) : $fieldsetName;
        $landingSectionSettings[$fieldset->landingSection] = [
            'fieldset' => $fieldsetName,
            'label'    => $label,
        ];
    }
}

$document->addScriptOptions('com_proclaim.settingsConfig', $settingsConfig);
$document->addScriptOptions('com_proclaim.landingSectionSettings', $landingSectionSettings);
?>

<div id="layout-editor-container" data-context="messages">
    <noscript>
        <div class="alert alert-warning">
            <?php echo Text::_('JBS_TPL_LAYOUT_REQUIRES_JS'); ?>
        </div>
    </noscript>
    <!-- Loading placeholder - replaced when Layout Editor initializes -->
    <div id="layout-editor-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden"><?php echo Text::_('JLIB_HTML_BEHAVIOR_LOADING'); ?></span>
        </div>
        <p class="mt-3 text-muted"><?php echo Text::_('JBS_TPL_LAYOUT_LOADING'); ?></p>
    </div>
</div>

<div class="alert alert-info mt-3">
    <span class="icon-info-circle" aria-hidden="true"></span>
    <?php echo Text::_('JBS_TPL_LAYOUT_CLASSIC_NOTE'); ?>
</div>

<!-- Hidden fields for Landing Page backward compatibility (managed by Layout Editor) -->
<div style="display: none;">
    <?php echo $this->form->renderFieldset('LANDINGPAGE'); ?>
</div>

<?php
// When loaded via AJAX, we need to provide script options and language strings inline
// since addScriptOptions() and Text::script() won't work for AJAX-loaded content
$isAjax = Factory::getApplication()->input->get('format') === 'raw';

if ($isAjax) :
    // Collect all the options that were set via addScriptOptions
    $scriptOptions = [
        'com_proclaim.templateParams'         => $paramsArray,
        'com_proclaim.dateFormatOptions'      => $dateFormatOptions,
        'com_proclaim.linkTypeOptions'        => $linkTypeOptions,
        'com_proclaim.teacherLinkTypeOptions' => $teacherLinkTypeOptions,
        'com_proclaim.seriesLinkTypeOptions'  => $seriesLinkTypeOptions,
        'com_proclaim.elementTypeOptions'     => $elementTypeOptions,
        'com_proclaim.elementDefinitions'     => $elementDefinitions,
        'com_proclaim.templateId'             => $templateId,
        'com_proclaim.settingsConfig'         => $settingsConfig,
        'com_proclaim.landingSectionSettings' => $landingSectionSettings,
    ];

    // Collect language strings that were registered via Text::script()
    $languageStrings = [
        'JBS_TPL_LAYOUT_HELP'             => Text::_('JBS_TPL_LAYOUT_HELP'),
        'JBS_TPL_AVAILABLE_ELEMENTS'      => Text::_('JBS_TPL_AVAILABLE_ELEMENTS'),
        'JBS_TPL_ROW'                     => Text::_('JBS_TPL_ROW'),
        'JBS_TPL_DROP_ELEMENTS_HERE'      => Text::_('JBS_TPL_DROP_ELEMENTS_HERE'),
        'JBS_TPL_ELEMENT_SETTINGS'        => Text::_('JBS_TPL_ELEMENT_SETTINGS'),
        'JBS_TPL_REMOVE_ELEMENT'          => Text::_('JBS_TPL_REMOVE_ELEMENT'),
        'JBS_TPL_COLSPAN'                 => Text::_('JBS_TPL_COLSPAN'),
        'JBS_TPL_COLSPAN_DESC'            => Text::_('JBS_TPL_COLSPAN_DESC'),
        'JBS_TPL_ELEMENT'                 => Text::_('JBS_TPL_ELEMENT'),
        'JBS_TPL_ELEMENT_DESC'            => Text::_('JBS_TPL_ELEMENT_DESC'),
        'JBS_TPL_TYPE_OF_LINK'            => Text::_('JBS_TPL_TYPE_OF_LINK'),
        'JBS_TPL_TYPE_OF_LINK_DESC'       => Text::_('JBS_TPL_TYPE_OF_LINK_DESC'),
        'JBS_TPL_CUSTOMCLASS'             => Text::_('JBS_TPL_CUSTOMCLASS'),
        'JBS_TPL_CUSTOMCLASS_DESC'        => Text::_('JBS_TPL_CUSTOMCLASS_DESC'),
        'JBS_TPL_DATE_FORMAT'             => Text::_('JBS_TPL_DATE_FORMAT'),
        'JBS_TPL_DATE_FORMAT_DESC'        => Text::_('JBS_TPL_DATE_FORMAT_DESC'),
        'JBS_TPL_VIEW_SETTINGS'           => Text::_('JBS_TPL_VIEW_SETTINGS'),
        'JBS_TPL_DRAG_TO_REORDER'         => Text::_('JBS_TPL_DRAG_TO_REORDER'),
        'JBS_TPL_TOGGLE_SECTION'          => Text::_('JBS_TPL_TOGGLE_SECTION'),
        'JBS_TPL_SECTION_DISABLED'        => Text::_('JBS_TPL_SECTION_DISABLED'),
        'JBS_TPL_PLEASE_WAIT'             => Text::_('JBS_TPL_PLEASE_WAIT'),
        'JBS_TPL_LOADING'                 => Text::_('JBS_TPL_LOADING'),
        'JBS_TPL_UNSAVED_CHANGES'         => Text::_('JBS_TPL_UNSAVED_CHANGES'),
        'JBS_TPL_UNSAVED_CHANGES_CONFIRM' => Text::_('JBS_TPL_UNSAVED_CHANGES_CONFIRM'),
        'JBS_TPL_MODAL_UNSAVED_CHANGES'   => Text::_('JBS_TPL_MODAL_UNSAVED_CHANGES'),
        'JCLOSE'                          => Text::_('JCLOSE'),
        'JCANCEL'                         => Text::_('JCANCEL'),
        'JAPPLY'                          => Text::_('JAPPLY'),
    ];
    ?>
    <script>
    // Merge script options and language strings for AJAX-loaded content
    (function() {
        var options = <?php echo json_encode($scriptOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var strings = <?php echo json_encode($languageStrings, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        // Merge script options
        if (typeof Joomla !== 'undefined' && Joomla.optionsStorage) {
            Object.keys(options).forEach(function(key) {
                Joomla.optionsStorage[key] = options[key];
            });
        }

        // Merge language strings
        if (typeof Joomla !== 'undefined' && Joomla.Text && Joomla.Text.strings) {
            Object.keys(strings).forEach(function(key) {
                Joomla.Text.strings[key] = strings[key];
            });
        }
    })();
    </script>
<?php endif; ?>
