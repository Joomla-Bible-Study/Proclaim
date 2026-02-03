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

// Register language strings for JavaScript
Text::script('JBS_TPL_LAYOUT_HELP');
Text::script('JBS_TPL_AVAILABLE_ELEMENTS');
Text::script('JBS_TPL_ROW');
Text::script('JBS_TPL_DROP_ELEMENTS_HERE');
Text::script('JBS_TPL_ELEMENT_SETTINGS');
Text::script('JBS_TPL_REMOVE_ELEMENT');
Text::script('JBS_TPL_MESSAGES_LIST');
Text::script('JBS_TPL_STUDY_DETAILS');
Text::script('JBS_TPL_TEACHERS_LIST');
Text::script('JBS_TPL_TEACHER_DETAILS');
Text::script('JBS_TPL_SERIES_LIST');
Text::script('JBS_TPL_SERIES_DETAILS');
Text::script('JBS_TPL_LANDING_PAGE');
Text::script('JBS_TPL_COLSPAN');
Text::script('JBS_TPL_COLSPAN_DESC');
Text::script('JBS_TPL_ELEMENT');
Text::script('JBS_TPL_ELEMENT_DESC');
Text::script('JBS_TPL_TYPE_OF_LINK');
Text::script('JBS_TPL_TYPE_OF_LINK_DESC');
Text::script('JBS_TPL_CUSTOMCLASS');
Text::script('JBS_TPL_CUSTOMCLASS_DESC');
Text::script('JCLOSE');
Text::script('JCANCEL');
Text::script('JAPPLY');

// Settings panel language strings
Text::script('JBS_TPL_SETTINGS_PANEL');
Text::script('JBS_TPL_TOGGLE_SETTINGS');
Text::script('JBS_TPL_VERSES_DATES_CSS');
Text::script('JBS_TPL_LIST_ITEMS');
Text::script('JBS_TPL_FILTERS');
Text::script('JBS_TPL_TOOLTIP');
Text::script('JBS_TPL_STUDY_DETAILS_VIEW');
Text::script('JBS_TPL_TEACHERDETAILS');
Text::script('JBS_TPL_TEACHER');
Text::script('JBS_TPL_TEACHERDISPLAY');
Text::script('JBS_TPL_TEACHERDETAILSDISPLAY');
Text::script('JBS_TPL_SERIESLIST');
Text::script('JBS_TPL_SERIESLISTDISPLAY');
Text::script('JBS_TPL_SERIESDETAILS');
Text::script('JBS_TPL_SERIESDETAILSDISPLAY');
Text::script('JBS_TPL_PLEASE_WAIT');
Text::script('JBS_TPL_LOADING');

// Link type options (matches LinkOptionsField.php)
Text::script('JBS_TPL_NO_LINK');
Text::script('JBS_TPL_LINK_TO_DETAILS');
Text::script('JBS_TPL_LINK_TO_DETAILS_TOOLTIP');
Text::script('JBS_TPL_LINK_TO_MEDIA');
Text::script('JBS_TPL_LINK_TO_DOWNLOAD');
Text::script('JBS_TPL_LINK_TO_MEDIA_TOOLTIP');
Text::script('JBS_TPL_LINK_TO_TEACHERS_PROFILE');
Text::script('JBS_TPL_LINK_TO_FIRST_ARTICLE');
Text::script('JBS_TPL_LINK_TO_VIRTUEMART');
Text::script('JBS_TPL_LINK_TO_DOCMAN');
Text::script('JBS_TPL_VIEW_SETTINGS');

// Element options (matches ElementOptionsField.php)
Text::script('JBS_CMN_NONE');
Text::script('JBS_TPL_PARAGRAPH');
Text::script('JBS_TPL_HEADER1');
Text::script('JBS_TPL_HEADER2');
Text::script('JBS_TPL_HEADER3');
Text::script('JBS_TPL_HEADER4');
Text::script('JBS_TPL_HEADER5');
Text::script('JBS_TPL_BLOCKQUOTE');

// Element labels
Text::script('JBS_CMN_SCRIPTURE');
Text::script('JBS_CMN_SCRIPTURE2');
Text::script('JBS_CMN_TITLE');
Text::script('JBS_CMN_DATE');
Text::script('JBS_CMN_TEACHER');
Text::script('JBS_CMN_TEACHER_IMAGE');
Text::script('JBS_CMN_TEACHER_TITLE');
Text::script('JBS_CMN_DURATION');
Text::script('JBS_CMN_STUDYINTRO');
Text::script('JBS_CMN_SERIES');
Text::script('JBS_CMN_SERIES_THUMBNAIL');
Text::script('JBS_CMN_SERIES_DESCRIPTION');
Text::script('JBS_CMN_MEDIA');
Text::script('JBS_CMN_TOPICS');
Text::script('JBS_CMN_LOCATIONS');
Text::script('JBS_CMN_HITS');
Text::script('JBS_CMN_DOWNLOADS');
Text::script('JBS_CMN_STUDYNUMBER');
Text::script('JBS_CMN_MESSAGETYPE');
Text::script('JBS_CMN_THUMBNAIL');
Text::script('JBS_CMN_CUSTOM');
Text::script('JBS_CMN_DESCRIPTION');

// Landing page section labels
Text::script('JBS_CMN_TEACHERS');
Text::script('JBS_CMN_SERIES');
Text::script('JBS_CMN_BOOKS');
Text::script('JBS_CMN_TOPICS');
Text::script('JBS_CMN_LOCATIONS');
Text::script('JBS_CMN_MESSAGETYPES');
Text::script('JBS_CMN_YEARS');
Text::script('JBS_TPL_DRAG_TO_REORDER');
Text::script('JBS_TPL_TOGGLE_SECTION');
Text::script('JBS_TPL_SECTION_DISABLED');

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

// Pass template ID for lazy-loading fieldsets
$templateId = (int) $this->item->id;
$document->addScriptOptions('com_proclaim.templateId', $templateId);

// Settings panel fieldsets configuration by context
// Note: DISPLAY fieldsets (TEACHERDISPLAY, SERIESDISPALY, etc.) are excluded
// because they contain row/col/colspan settings handled by the visual Layout Editor
$settingsConfig = [
    'messages' => [
        ['fieldset' => 'VERSES', 'label' => Text::_('JBS_TPL_VERSES_DATES_CSS')],
        ['fieldset' => 'LISTITEMS', 'label' => Text::_('JBS_TPL_LIST_ITEMS')],
        ['fieldset' => 'FILTERS', 'label' => Text::_('JBS_TPL_FILTERS')],
        ['fieldset' => 'TOOLTIP', 'label' => Text::_('JBS_TPL_TOOLTIP')],
    ],
    'details' => [
        ['fieldset' => 'DETAILS', 'label' => Text::_('JBS_TPL_STUDY_DETAILS_VIEW')],
    ],
    'teachers' => [
        ['fieldset' => 'TEACHERDETAILS', 'label' => Text::_('JBS_TPL_TEACHERDETAILS')],
        ['fieldset' => 'TEACHER', 'label' => Text::_('JBS_TPL_TEACHER')],
    ],
    'teacherDetails' => [
        ['fieldset' => 'TEACHERDETAILS', 'label' => Text::_('JBS_TPL_TEACHERDETAILS')],
    ],
    'series' => [
        ['fieldset' => 'SERIES', 'label' => Text::_('JBS_TPL_SERIESLIST')],
    ],
    'seriesDetails' => [
        ['fieldset' => 'SERIESDETAIL', 'label' => Text::_('JBS_TPL_SERIESDETAILS')],
    ],
    'landingPage' => [
        ['fieldset' => 'LANDINGPAGE_PAGESETTINGS', 'label' => Text::_('JBS_TPL_LANDINGPAGE_DISPLAY_SETTINGS')],
    ],
];
$document->addScriptOptions('com_proclaim.settingsConfig', $settingsConfig);

// Landing page section-specific fieldsets (for gear button on each section card)
$landingSectionSettings = [
    'teachers' => 'LANDINGPAGE_TEACHERS',
    'series' => 'LANDINGPAGE_SERIES',
    'books' => 'LANDINGPAGE_BOOKS',
    'topics' => 'LANDINGPAGE_TOPICS',
    'locations' => 'LANDINGPAGE_LOCATIONS',
    'messagetypes' => 'LANDINGPAGE_MESSAGETYPES',
    'years' => 'LANDINGPAGE_YEARS',
];
$document->addScriptOptions('com_proclaim.landingSectionSettings', $landingSectionSettings);
?>

<div id="layout-editor-container" data-context="messages">
    <noscript>
        <div class="alert alert-warning">
            <?php echo Text::_('JBS_TPL_LAYOUT_REQUIRES_JS'); ?>
        </div>
    </noscript>
</div>

<div class="alert alert-info mt-3">
    <span class="icon-info-circle" aria-hidden="true"></span>
    <?php echo Text::_('JBS_TPL_LAYOUT_CLASSIC_NOTE'); ?>
</div>

<!-- Hidden fields for Landing Page backward compatibility (managed by Layout Editor) -->
<div style="display: none;">
    <?php echo $this->form->renderFieldset('LANDINGPAGE'); ?>
</div>
