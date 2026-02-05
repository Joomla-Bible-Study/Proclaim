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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Layout Editor Field - Visual drag-and-drop layout configuration
 *
 * This field renders the Layout Editor as a visual interface to the existing
 * XML-defined form fields (scripture1row, scripture1col, etc.). It does NOT
 * create its own hidden fields - instead it reads from and writes to the
 * existing form fields defined in the XML.
 *
 * Usage in XML:
 * <field name="layout_editor" type="LayoutEditor"
 *        context="messages"
 *        show-view-settings="false"
 *        label="JBS_MDL_LAYOUT_EDITOR"/>
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class LayoutEditorField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.1.0
     */
    protected $type = 'LayoutEditor';

    /**
     * Element definitions for the messages context
     *
     * @var  array
     *
     * @since 10.1.0
     */
    protected array $messageElements = [
        ['id' => 'scripture1', 'label' => 'JBS_CMN_SCRIPTURE'],
        ['id' => 'scripture2', 'label' => 'JBS_CMN_SCRIPTURE2'],
        ['id' => 'secondary', 'label' => 'JBS_CMN_SECONDARY_REFERENCES'],
        ['id' => 'jbsmedia', 'label' => 'JBS_CMN_MEDIA'],
        ['id' => 'title', 'label' => 'JBS_CMN_TITLE'],
        ['id' => 'date', 'label' => 'JBS_CMN_DATE'],
        ['id' => 'teacher', 'label' => 'JBS_CMN_TEACHER'],
        ['id' => 'teacherimage', 'label' => 'JBS_CMN_TEACHER_IMAGE'],
        ['id' => 'teacher-title', 'label' => 'JBS_CMN_TEACHER_TITLE'],
        ['id' => 'duration', 'label' => 'JBS_CMN_DURATION'],
        ['id' => 'studyintro', 'label' => 'JBS_CMN_STUDY_INTRO'],
        ['id' => 'series', 'label' => 'JBS_CMN_SERIES'],
        ['id' => 'seriesthumbnail', 'label' => 'JBS_CMN_SERIES_THUMBNAIL'],
        ['id' => 'seriesdescription', 'label' => 'JBS_CMN_SERIES_DESCRIPTION'],
        ['id' => 'submitted', 'label' => 'JBS_CMN_SUBMITTED_BY'],
        ['id' => 'hits', 'label' => 'JBS_CMN_HITS'],
        ['id' => 'downloads', 'label' => 'JBS_CMN_DOWNLOADS'],
        ['id' => 'studynumber', 'label' => 'JBS_CMN_STUDYNUMBER'],
        ['id' => 'topic', 'label' => 'JBS_CMN_TOPICS'],
        ['id' => 'locations', 'label' => 'JBS_CMN_LOCATION'],
        ['id' => 'messagetype', 'label' => 'JBS_CMN_MESSAGETYPE'],
        ['id' => 'thumbnail', 'label' => 'JBS_CMN_THUMBNAIL'],
        ['id' => 'custom', 'label' => 'JBS_TPL_CUSTOM_TEXT'],
    ];

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   10.1.0
     */
    protected function getInput(): string
    {
        // Get configuration from XML attributes
        $context          = (string) ($this->element['context'] ?? 'messages');
        $showViewSettings = filter_var($this->element['show-view-settings'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $lazyLoad         = filter_var($this->element['lazy'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        // Load assets - for lazy loading, we still need them but initialization is deferred
        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.sortable')
           ->useScript('com_proclaim.layout-editor')
           ->useStyle('com_proclaim.layout-editor');

        // Load language strings for JavaScript
        $this->loadLanguageStrings();

        // Get current params from the form (these come from the XML-defined fields)
        $params = $this->getLayoutParams();

        // Build element definitions with translated labels
        $elementDefinitions = $this->buildElementDefinitions($context);

        // Generate unique ID for this instance
        $editorId = 'layout-editor-' . $this->id;

        // Build the HTML - just the container, no hidden fields
        // The Layout Editor reads/writes to the existing XML-defined form fields
        $html = [];

        $html[]   = '<div class="layout-editor-field-wrapper">';
        $lazyAttr = $lazyLoad ? ' data-lazy-init="true"' : '';
        $html[]   = '<div id="' . $editorId . '" class="layout-editor-container" data-context="' . $context . '"' . $lazyAttr . '>';
        $html[]   = '    <div id="layout-editor-loading" class="text-center py-4">';
        $html[]   = '        <span class="spinner-border spinner-border-sm" role="status"></span>';
        $html[]   = '        <span class="ms-2">' . Text::_('JBS_TPL_LOADING') . '</span>';
        $html[]   = '    </div>';
        $html[]   = '</div>';
        $html[]   = '</div>';

        // Inline script to initialize the Layout Editor
        $html[] = $this->renderInitScript($editorId, $context, $showViewSettings, $elementDefinitions, $params, $lazyLoad);

        return implode("\n", $html);
    }

    /**
     * Load language strings needed by JavaScript
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function loadLanguageStrings(): void
    {
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
        Text::script('JBS_TPL_VERSES_SHOW_VERSES');
        Text::script('JBS_TPL_VERSES_SHOW_VERSES_DESC');
        Text::script('JBS_TPL_VIEW_SETTINGS');
        Text::script('JBS_TPL_DRAG_TO_REORDER');
        Text::script('JBS_TPL_UNSAVED_CHANGES');
        Text::script('JBS_TPL_UNSAVED_CHANGES_CONFIRM');
        Text::script('JBS_TPL_MODAL_UNSAVED_CHANGES');
        Text::script('JCLOSE');
        Text::script('JCANCEL');
        Text::script('JAPPLY');
    }

    /**
     * Get current layout parameters from form data
     *
     * These values come from the XML-defined form fields that Joomla populates.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    protected function getLayoutParams(): array
    {
        $form = $this->form;
        $data = $form->getData();

        if ($data instanceof Registry) {
            $params = $data->get('params');
            if (\is_object($params)) {
                return (array) $params;
            }
            if (\is_array($params)) {
                return $params;
            }
        }

        return [];
    }

    /**
     * Build element definitions with translated labels
     *
     * @param   string  $context  The context (messages, details, etc.)
     *
     * @return  array
     *
     * @since   10.1.0
     */
    protected function buildElementDefinitions(string $context): array
    {
        $elements = [];

        foreach ($this->messageElements as $element) {
            $elements[] = [
                'id'    => $element['id'],
                'label' => Text::_($element['label']),
            ];
        }

        return [
            $context => [
                'label'    => Text::_('JBS_TPL_MESSAGES_LIST'),
                'prefix'   => '',
                'elements' => $elements,
            ],
        ];
    }

    /**
     * Render the initialization script
     *
     * @param   string  $editorId          The editor container ID
     * @param   string  $context           The layout context
     * @param   bool    $showViewSettings  Whether to show View Settings button
     * @param   array   $elementDefs       Element definitions
     * @param   array   $params            Current parameters
     * @param   bool    $lazyLoad          Whether to defer initialization until tab is visible
     *
     * @return  string
     *
     * @since   10.1.0
     */
    protected function renderInitScript(
        string $editorId,
        string $context,
        bool $showViewSettings,
        array $elementDefs,
        array $params,
        bool $lazyLoad = false
    ): string {
        $elementDefsJson = json_encode($elementDefs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        $paramsJson      = json_encode($params, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
        $showSettings    = $showViewSettings ? 'true' : 'false';

        // Build link type options (matching LinkOptionsField values)
        $linkTypeOptions = [
            ['value' => '0', 'label' => Text::_('JBS_TPL_NO_LINK')],
            ['value' => '1', 'label' => Text::_('JBS_TPL_LINK_TO_DETAILS')],
            ['value' => '4', 'label' => Text::_('JBS_TPL_LINK_TO_DETAILS_TOOLTIP')],
            ['value' => '2', 'label' => Text::_('JBS_TPL_LINK_TO_MEDIA')],
            ['value' => '9', 'label' => Text::_('JBS_TPL_LINK_TO_DOWNLOAD')],
            ['value' => '5', 'label' => Text::_('JBS_TPL_LINK_TO_MEDIA_TOOLTIP')],
            ['value' => '3', 'label' => Text::_('JBS_TPL_LINK_TO_TEACHERS_PROFILE')],
        ];
        $linkTypeOptionsJson = json_encode($linkTypeOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

        // Date format options
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
        $dateFormatOptionsJson = json_encode($dateFormatOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

        // Show verses options (for scripture elements)
        $showVersesOptions = [
            ['value' => '', 'label' => Text::_('JBS_TPL_USE_GLOBAL_SETTING')],
            ['value' => '0', 'label' => Text::_('JBS_TPL_SHOW_ONLY_CHAPTERS')],
            ['value' => '1', 'label' => Text::_('JBS_TPL_SHOW_VERSES_AND_CHAPTERS')],
        ];
        $showVersesOptionsJson = json_encode($showVersesOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

        // Element type options (HTML wrapper)
        $elementTypeOptions = [
            ['value' => '0', 'label' => Text::_('JNONE')],
            ['value' => '1', 'label' => 'P'],
            ['value' => '2', 'label' => 'H1'],
            ['value' => '3', 'label' => 'H2'],
            ['value' => '4', 'label' => 'H3'],
            ['value' => '5', 'label' => 'H4'],
            ['value' => '6', 'label' => 'H5'],
            ['value' => '7', 'label' => 'Blockquote'],
            ['value' => '8', 'label' => 'DIV'],
        ];
        $elementTypeOptionsJson = json_encode($elementTypeOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

        $lazyLoadJs = $lazyLoad ? 'true' : 'false';

        return <<<SCRIPT
<script>
(function() {
    'use strict';

    var lazyLoad = {$lazyLoadJs};

    // Store options for the Layout Editor
    if (typeof Joomla !== 'undefined' && Joomla.optionsStorage) {
        Joomla.optionsStorage['com_proclaim.elementDefinitions'] = {$elementDefsJson};
        Joomla.optionsStorage['com_proclaim.templateParams'] = {$paramsJson};
        Joomla.optionsStorage['com_proclaim.linkTypeOptions'] = {$linkTypeOptionsJson};
        Joomla.optionsStorage['com_proclaim.dateFormatOptions'] = {$dateFormatOptionsJson};
        Joomla.optionsStorage['com_proclaim.showVersesOptions'] = {$showVersesOptionsJson};
        Joomla.optionsStorage['com_proclaim.elementTypeOptions'] = {$elementTypeOptionsJson};
    }

    function initModuleLayoutEditor() {
        var container = document.getElementById('{$editorId}');
        if (!container || container.dataset.initialized) {
            return;
        }

        container.dataset.initialized = 'true';

        // Remove loading indicator
        var loading = container.querySelector('#layout-editor-loading');
        if (loading) {
            loading.remove();
        }

        // Initialize the Layout Editor
        // It reads/writes to the existing jform[params][fieldname] fields defined in XML
        if (typeof window.ProclaimLayoutEditor !== 'undefined') {
            window.proclaimLayoutEditor = window.ProclaimLayoutEditor.create(container, {
                context: '{$context}',
                contexts: ['{$context}'],
                showViewSettings: {$showSettings},
                showContextTabs: false,
                formId: container.closest('form')?.id || 'module-form',
                paramsPrefix: 'jform[params]'
            });
        } else if (typeof window.LayoutEditor !== 'undefined') {
            window.proclaimLayoutEditor = new window.LayoutEditor(container, {
                context: '{$context}',
                contexts: ['{$context}'],
                showViewSettings: {$showSettings},
                showContextTabs: false,
                formId: container.closest('form')?.id || 'module-form',
                paramsPrefix: 'jform[params]'
            });
        } else {
            console.error('Layout Editor not loaded');
        }
    }

    /**
     * Check if the container is in a visible/active tab
     */
    function isContainerVisible(container) {
        // Check for Joomla 5 joomla-tab-element
        var tabElement = container.closest('joomla-tab-element');
        if (tabElement) {
            return tabElement.hasAttribute('active');
        }

        // Check for Bootstrap tab-pane
        var tabPane = container.closest('.tab-pane');
        if (tabPane) {
            return tabPane.classList.contains('active') || tabPane.classList.contains('show');
        }

        // Check for Bootstrap accordion
        var accordion = container.closest('.accordion-collapse');
        if (accordion) {
            return accordion.classList.contains('show');
        }

        // Not in a tab/accordion, assume visible
        return true;
    }

    /**
     * Set up lazy loading - wait for tab to be shown before initializing
     */
    function setupLazyInit() {
        var container = document.getElementById('{$editorId}');
        if (!container) {
            return;
        }

        // If already visible, initialize now
        if (isContainerVisible(container)) {
            initModuleLayoutEditor();
            return;
        }

        // Listen for Joomla 5 tab events
        var joomlaTab = container.closest('joomla-tab');
        if (joomlaTab) {
            var tabElement = container.closest('joomla-tab-element');
            var initOnShow = function(event) {
                if (tabElement && (event.target === tabElement || tabElement.hasAttribute('active'))) {
                    initModuleLayoutEditor();
                    joomlaTab.removeEventListener('joomla.tab.shown', initOnShow);
                }
            };
            joomlaTab.addEventListener('joomla.tab.shown', initOnShow);
            return;
        }

        // Listen for Bootstrap tab events
        var tabPane = container.closest('.tab-pane');
        if (tabPane && tabPane.id) {
            var tabButton = document.querySelector('[data-bs-target="#' + tabPane.id + '"], [href="#' + tabPane.id + '"]');
            if (tabButton) {
                tabButton.addEventListener('shown.bs.tab', function() {
                    initModuleLayoutEditor();
                }, { once: true });
                return;
            }
        }

        // Listen for Bootstrap accordion events
        var accordion = container.closest('.accordion-collapse');
        if (accordion) {
            accordion.addEventListener('shown.bs.collapse', function() {
                initModuleLayoutEditor();
            }, { once: true });
            return;
        }

        // Fallback: just initialize
        initModuleLayoutEditor();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (lazyLoad) {
                setupLazyInit();
            } else {
                initModuleLayoutEditor();
            }
        });
    } else {
        if (lazyLoad) {
            setupLazyInit();
        } else {
            var scheduleInit = window.requestIdleCallback || function(cb) { setTimeout(cb, 100); };
            scheduleInit(initModuleLayoutEditor, { timeout: 2000 });
        }
    }
})();
</script>
SCRIPT;
    }
}
