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
use Joomla\CMS\Layout\LayoutHelper;
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
        ['id' => 'scriptures', 'label' => 'JBS_CMN_ALL_SCRIPTURES'],
        ['id' => 'scripture1', 'label' => 'JBS_CMN_SCRIPTURE', 'deprecated' => true],
        ['id' => 'scripture2', 'label' => 'JBS_CMN_SCRIPTURE2', 'deprecated' => true],
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
           ->useScript('com_proclaim.layout-editor-field')
           ->useStyle('com_proclaim.layout-editor');

        // Get current params from the form (these come from the XML-defined fields)
        $params = $this->getLayoutParams();

        // Build element definitions with translated labels
        $elementDefinitions = $this->buildElementDefinitions($context);

        // Generate unique ID for this instance
        $editorId = 'layout-editor-' . $this->id;

        // Register all Layout Editor script options via centralized layout
        $this->registerScriptOptions($elementDefinitions, $params);

        // Build the HTML with data attributes for the external JS to read
        $html = [];

        $html[] = '<div class="layout-editor-field-wrapper">';
        $html[] = '<div id="' . $editorId . '" class="layout-editor-container"'
            . ' data-layout-editor-field="true"'
            . ' data-context="' . htmlspecialchars($context) . '"'
            . ' data-show-view-settings="' . ($showViewSettings ? 'true' : 'false') . '"'
            . ' data-lazy-init="' . ($lazyLoad ? 'true' : 'false') . '"'
            . ' data-params-prefix="jform[params]">';
        $html[] = '    <div id="layout-editor-loading" class="text-center py-4">';
        $html[] = '        <span class="spinner-border spinner-border-sm" role="status"></span>';
        $html[] = '        <span class="ms-2">' . Text::_('JBS_TPL_LOADING') . '</span>';
        $html[] = '    </div>';
        $html[] = '</div>';
        $html[] = '</div>';

        return implode("\n", $html);
    }

    /**
     * Register all Layout Editor script options via centralized layout
     *
     * @param   array  $elementDefs  Element definitions
     * @param   array  $params       Current parameters
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function registerScriptOptions(array $elementDefs, array $params): void
    {
        LayoutHelper::render('layouteditor.scriptoptions', [
            'form'               => $this->form,
            'templateParams'     => $params,
            'elementDefinitions' => $elementDefs,
            'prependInherit'     => true,
        ], JPATH_ADMINISTRATOR . '/components/com_proclaim/layouts');
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

}
