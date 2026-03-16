<?php

/**
 * Message Wizard — Guided step-by-step message creation
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlangHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmessage\HtmlView $this */

CwmlangHelper::registerAllForJs();

$app   = Factory::getApplication();
$wa    = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_proclaim.message-wizard');
$wa->useStyle('com_proclaim.message-wizard-css');

// Set defaults from admin params for new records
$admin_params = $this->admin_params;
$this->form->setValue('teacher_id', null, $admin_params->get('teacher_id', 0));
$this->form->setValue('location_id', null, $admin_params->get('location_id', 0));
$this->form->setValue('series_id', null, $admin_params->get('series_id', 0));
$this->form->setValue('messagetype', null, $admin_params->get('messagetype', 0));
$this->form->setValue('studydate', null, date('Y-m-d H:i:s'));

$steps = [
    1 => 'JBS_WIZ_STEP_TITLE_DATE',
    2 => 'JBS_WIZ_STEP_TEACHER_SERIES',
    3 => 'JBS_WIZ_STEP_SCRIPTURE',
    4 => 'JBS_WIZ_STEP_MEDIA',
    5 => 'JBS_WIZ_STEP_DESCRIPTION',
    6 => 'JBS_WIZ_STEP_REVIEW',
];
?>

<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=wizard&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="message-form" class="form-validate">

    <!-- Progress indicator -->
    <div class="wizard-progress mb-4">
        <div class="d-flex justify-content-between mb-2">
            <?php foreach ($steps as $num => $label) : ?>
                <div class="wizard-step-indicator text-center flex-fill<?php echo $num === 1 ? ' active' : ''; ?>"
                     data-step="<?php echo $num; ?>">
                    <div class="wizard-step-number rounded-circle d-inline-flex align-items-center justify-content-center mb-1">
                        <?php echo $num; ?>
                    </div>
                    <div class="wizard-step-label small d-none d-md-block"><?php echo Text::_($label); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="progress" style="height: 4px;">
            <div class="progress-bar" id="wizard-progress-bar" role="progressbar" style="width: 16.6%;"
                 aria-valuenow="1" aria-valuemin="1" aria-valuemax="6"></div>
        </div>
    </div>

    <!-- Step 1: Title & Date -->
    <div class="wizard-step active" data-step="1">
        <h3 class="mb-3"><?php echo Text::_('JBS_WIZ_STEP_TITLE_DATE'); ?></h3>
        <div class="card card-body bg-body-tertiary">
            <?php echo $this->form->renderField('studytitle'); ?>
            <?php echo $this->form->renderField('alias'); ?>
            <?php echo $this->form->renderField('studydate'); ?>
        </div>
    </div>

    <!-- Step 2: Teacher & Series -->
    <div class="wizard-step" data-step="2">
        <h3 class="mb-3"><?php echo Text::_('JBS_WIZ_STEP_TEACHER_SERIES'); ?></h3>
        <div class="card card-body bg-body-tertiary">
            <?php echo $this->form->renderField('teachers'); ?>
            <?php echo $this->form->renderField('series_id'); ?>
            <?php echo $this->form->renderField('location_id'); ?>
        </div>
    </div>

    <!-- Step 3: Scripture -->
    <div class="wizard-step" data-step="3">
        <h3 class="mb-3"><?php echo Text::_('JBS_WIZ_STEP_SCRIPTURE'); ?></h3>
        <div class="card card-body bg-body-tertiary">
            <?php echo $this->form->renderField('scriptures'); ?>
        </div>
    </div>

    <!-- Step 4: Media (informational) -->
    <div class="wizard-step" data-step="4">
        <h3 class="mb-3"><?php echo Text::_('JBS_WIZ_STEP_MEDIA'); ?></h3>
        <div class="card card-body bg-body-tertiary">
            <div class="alert alert-info mb-0">
                <span class="icon-info-circle me-2" aria-hidden="true"></span>
                <?php echo Text::_('JBS_WIZ_MEDIA_SAVE_FIRST'); ?>
            </div>
        </div>
    </div>

    <!-- Step 5: Description -->
    <div class="wizard-step" data-step="5">
        <h3 class="mb-3"><?php echo Text::_('JBS_WIZ_STEP_DESCRIPTION'); ?></h3>
        <div class="card card-body bg-body-tertiary">
            <?php echo $this->form->renderField('studyintro'); ?>
        </div>
    </div>

    <!-- Step 6: Review & Save -->
    <div class="wizard-step" data-step="6">
        <h3 class="mb-3"><?php echo Text::_('JBS_WIZ_REVIEW_HEADING'); ?></h3>
        <div class="card card-body bg-body-tertiary">
            <div id="wizard-review-summary">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- Navigation buttons -->
    <div class="wizard-nav d-flex justify-content-between mt-4">
        <button type="button" class="btn btn-secondary" id="wizard-prev-btn" disabled>
            <span class="icon-chevron-left me-1" aria-hidden="true"></span>
            <?php echo Text::_('JBS_WIZ_PREVIOUS'); ?>
        </button>
        <div>
            <button type="button" class="btn btn-primary" id="wizard-next-btn">
                <?php echo Text::_('JBS_WIZ_NEXT'); ?>
                <span class="icon-chevron-right ms-1" aria-hidden="true"></span>
            </button>
            <button type="button" class="btn btn-success d-none" id="wizard-save-btn">
                <span class="icon-save me-1" aria-hidden="true"></span>
                <?php echo Text::_('JBS_WIZ_SAVE_CONTINUE'); ?>
            </button>
        </div>
    </div>

    <!-- Hidden fields -->
    <input type="hidden" name="task" value="">
    <input type="hidden" name="wizard_return" value="1">
    <input type="hidden" name="jform[id]" value="0">
    <input type="hidden" name="jform[published]" value="1">
    <input type="hidden" name="jform[language]" value="*">
    <input type="hidden" name="jform[access]" value="1">
    <input type="hidden" name="jform[messagetype]"
           value="<?php echo (int) $admin_params->get('messagetype', 0); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
