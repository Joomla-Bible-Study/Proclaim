<?php

/**
 * Landing Page Tab - Organized Layout
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmtemplate\HtmlView $this */

// Group the landing page fields by category
$fields = [];
foreach ($this->form->getFieldset('LANDINGPAGE') as $field) {
    $fields[$field->fieldname] = $field;
}

/**
 * Helper function to render a field
 */
$renderField = function ($name) use ($fields) {
    if (!isset($fields[$name])) {
        return '';
    }
    $field = $fields[$name];
    return '<div class="control-group mb-2">
        <div class="control-label">' . $field->label . '</div>
        <div class="controls">' . $field->input . '</div>
    </div>';
};

/**
 * Helper function to render a compact field (label and input on same line)
 */
$renderCompact = function ($name) use ($fields) {
    if (!isset($fields[$name])) {
        return '';
    }
    $field = $fields[$name];
    return '<div class="row mb-2 align-items-center">
        <div class="col-sm-5">' . $field->label . '</div>
        <div class="col-sm-7">' . $field->input . '</div>
    </div>';
};
?>

<div class="row">
    <!-- Left Column: Page Settings & Section Order -->
    <div class="col-lg-6">
        <!-- Page Display Settings -->
        <fieldset class="options-form mb-4">
            <legend><?php echo Text::_('JBS_TPL_LANDINGPAGE_DISPLAY_SETTINGS'); ?></legend>
            <div class="row">
                <div class="col-md-6">
                    <?php echo $renderField('landing_show_page_title'); ?>
                    <?php echo $renderField('landing_show_page_image'); ?>
                    <?php echo $renderField('landing_intro_show'); ?>
                </div>
                <div class="col-md-6">
                    <?php echo $renderField('landing_page_title'); ?>
                    <?php echo $renderField('landing_hide'); ?>
                    <?php echo $renderField('landing_hidelabel'); ?>
                    <?php echo $renderField('landing_default_order'); ?>
                </div>
            </div>
            <?php echo $renderField('landing_intro'); ?>
        </fieldset>

        <!-- Section Display Order -->
        <fieldset class="options-form mb-4">
            <legend><?php echo Text::_('JBS_TPL_LANDINGPAGE_SECTION_ORDER'); ?></legend>
            <p class="text-muted small"><?php echo Text::_('JBS_TPL_LANDINGPAGE_SECTION_ORDER_DESC'); ?></p>
            <div class="row">
                <div class="col-6">
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                        <?php echo $renderCompact('headingorder_' . $i); ?>
                    <?php endfor; ?>
                </div>
                <div class="col-6">
                    <?php for ($i = 5; $i <= 7; $i++) : ?>
                        <?php echo $renderCompact('headingorder_' . $i); ?>
                    <?php endfor; ?>
                </div>
            </div>
        </fieldset>

        <!-- Sort Orders -->
        <fieldset class="options-form mb-4">
            <legend><?php echo Text::_('JBS_TPL_LANDINGPAGE_SORT_ORDERS'); ?></legend>
            <div class="row">
                <div class="col-6">
                    <?php echo $renderCompact('teachers_order'); ?>
                    <?php echo $renderCompact('series_order'); ?>
                    <?php echo $renderCompact('books_order'); ?>
                    <?php echo $renderCompact('topics_order'); ?>
                </div>
                <div class="col-6">
                    <?php echo $renderCompact('locations_order'); ?>
                    <?php echo $renderCompact('messagetypes_order'); ?>
                    <?php echo $renderCompact('years_order'); ?>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- Right Column: Content Section Settings -->
    <div class="col-lg-6">
        <fieldset class="options-form">
            <legend><?php echo Text::_('JBS_TPL_LANDINGPAGE_CONTENT_SECTIONS'); ?></legend>

            <div class="accordion" id="landingSectionsAccordion">
                <!-- Teachers Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseTeachers">
                            <?php echo Text::_('JBS_CMN_TEACHERS'); ?>
                        </button>
                    </h2>
                    <div id="collapseTeachers" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showteachers'); ?>
                            <?php echo $renderField('teacherslabel'); ?>
                            <?php echo $renderField('landingteachersuselimit'); ?>
                            <?php echo $renderField('landingteacherslimit'); ?>
                            <?php echo $renderField('linkto'); ?>
                        </div>
                    </div>
                </div>

                <!-- Series Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseSeries">
                            <?php echo Text::_('JBS_CMN_SERIES'); ?>
                        </button>
                    </h2>
                    <div id="collapseSeries" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showseries'); ?>
                            <?php echo $renderField('serieslabel'); ?>
                            <?php echo $renderField('landingseriesuselimit'); ?>
                            <?php echo $renderField('landingserieslimit'); ?>
                            <?php echo $renderField('series_linkto'); ?>
                        </div>
                    </div>
                </div>

                <!-- Books Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseBooks">
                            <?php echo Text::_('JBS_CMN_BOOKS'); ?>
                        </button>
                    </h2>
                    <div id="collapseBooks" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showbooks'); ?>
                            <?php echo $renderField('bookslabel'); ?>
                            <?php echo $renderField('landingbookslimit'); ?>
                        </div>
                    </div>
                </div>

                <!-- Topics Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseTopics">
                            <?php echo Text::_('JBS_CMN_TOPICS'); ?>
                        </button>
                    </h2>
                    <div id="collapseTopics" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showtopics'); ?>
                            <?php echo $renderField('topicslabel'); ?>
                            <?php echo $renderField('landingtopicslimit'); ?>
                        </div>
                    </div>
                </div>

                <!-- Locations Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseLocations">
                            <?php echo Text::_('JBS_CMN_LOCATIONS'); ?>
                        </button>
                    </h2>
                    <div id="collapseLocations" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showlocations'); ?>
                            <?php echo $renderField('locationslabel'); ?>
                            <?php echo $renderField('landinglocationsuselimit'); ?>
                            <?php echo $renderField('landinglocationslimit'); ?>
                        </div>
                    </div>
                </div>

                <!-- Message Types Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseMessageTypes">
                            <?php echo Text::_('JBS_CMN_MESSAGETYPES'); ?>
                        </button>
                    </h2>
                    <div id="collapseMessageTypes" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showmessagetypes'); ?>
                            <?php echo $renderField('messagetypeslabel'); ?>
                            <?php echo $renderField('landingmessagetypeuselimit'); ?>
                            <?php echo $renderField('landingmessagetypeslimit'); ?>
                        </div>
                    </div>
                </div>

                <!-- Years Section -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseYears">
                            <?php echo Text::_('JBS_CMN_YEARS'); ?>
                        </button>
                    </h2>
                    <div id="collapseYears" class="accordion-collapse collapse"
                         data-bs-parent="#landingSectionsAccordion">
                        <div class="accordion-body">
                            <?php echo $renderField('showyears'); ?>
                            <?php echo $renderField('yearslabel'); ?>
                            <?php echo $renderField('landingyearslimit'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>
