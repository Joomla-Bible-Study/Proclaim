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

// Pass template params to JavaScript for initial loading
// The form fields are lazy-loaded, so we need to provide the data directly
$params = $this->item->params;
if (is_string($params)) {
    $paramsArray = json_decode($params, true) ?: [];
} elseif (is_object($params)) {
    $paramsArray = $params->toArray();
} else {
    $paramsArray = (array) $params;
}

/** @var \Joomla\CMS\Document\Document $document */
$document = $this->document ?? \Joomla\CMS\Factory::getApplication()->getDocument();
$document->addScriptOptions('com_proclaim.templateParams', $paramsArray);
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
