<?php

/**
 * Location Setup Wizard Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmlocationwizard\HtmlView $this */

\defined('_JEXEC') or die();

$wa    = $this->getDocument()->getWebAssetManager();
$token = Session::getFormToken();

$wa->useScript('com_proclaim.location-wizard');
$wa->addInlineStyle('.wizard-step { display: none; } .wizard-step.active { display: block; }');

$scenarioLabels = [
    '2A' => Text::_('JBS_WIZARD_SCENARIO_2A'),
    '2B' => Text::_('JBS_WIZARD_SCENARIO_2B'),
    '2C' => Text::_('JBS_WIZARD_SCENARIO_2C'),
];

$scenarioLabel = $scenarioLabels[$this->scenario] ?? $this->scenario;

// Serialize data for JS
$locationsJson  = json_encode(array_map(static function ($loc) {
    return ['id' => (int) $loc->id, 'title' => $loc->location_text];
}, $this->locations), JSON_THROW_ON_ERROR);

$groupsJson = json_encode(array_map(static function ($grp) {
    return ['id' => (int) $grp->id, 'title' => $grp->title];
}, $this->groups), JSON_THROW_ON_ERROR);

$mappingJson = json_encode($this->currentMapping, JSON_THROW_ON_ERROR);

$wa->addInlineScript(
    'window.ProcWizard = {
        token: ' . json_encode($token) . ',
        baseUrl: "index.php",
        scenario: ' . json_encode($this->scenario) . ',
        locations: ' . $locationsJson . ',
        groups: ' . $groupsJson . ',
        savedMapping: ' . $mappingJson . '
    };'
);
?>

<div class="row justify-content-center" id="proclaim-wizard">
    <div class="col-12 col-xl-10">

        <!-- ============================================================
             Wizard Progress Bar
             ============================================================ -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="text-body-secondary small" id="wizard-step-label">
                        <?php echo Text::sprintf('JBS_WIZARD_STEP_OF', 1, 7); ?>
                    </strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="wizard-dismiss-btn">
                        <?php echo Text::_('JBS_WIZARD_DISMISS'); ?>
                    </button>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-primary transition-all"
                         id="wizard-progress-bar"
                         role="progressbar"
                         style="width: 14%;"
                         aria-valuenow="14"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
                <!-- Step indicators -->
                <div class="d-flex justify-content-between mt-2">
                    <?php
                    $steps = [
                        Text::_('JBS_WIZARD_STEP1_SHORT'),
                        Text::_('JBS_WIZARD_STEP2_SHORT'),
                        Text::_('JBS_WIZARD_STEP3_SHORT'),
                        Text::_('JBS_WIZARD_STEP4_SHORT'),
                        Text::_('JBS_WIZARD_STEP5_SHORT'),
                        Text::_('JBS_WIZARD_STEP6_SHORT'),
                        Text::_('JBS_WIZARD_STEP7_SHORT'),
                    ];

                    foreach ($steps as $idx => $label):
                        $num    = $idx + 1;
                        $active = $num === 1 ? 'fw-bold text-primary' : 'text-body-secondary';
                    ?>
                    <span class="small wizard-step-label <?php echo $active; ?>" data-step="<?php echo $num; ?>"
                          title="<?php echo htmlspecialchars($label); ?>">
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($label); ?></span>
                        <span class="d-md-none"><?php echo $num; ?></span>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ============================================================
             Step 1: Welcome & Detection
             ============================================================ -->
        <div class="wizard-step active card shadow-sm" data-step="1">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP1_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body">
                <p class="lead"><?php echo Text::_('JBS_WIZARD_STEP1_INTRO'); ?></p>

                <!-- Detected scenario card -->
                <div class="alert alert-<?php echo $this->scenario === '2C' ? 'info' : 'warning'; ?> d-flex align-items-start">
                    <i class="fas fa-<?php echo $this->scenario === '2C' ? 'info-circle' : 'exclamation-triangle'; ?> me-3 mt-1 fa-lg"></i>
                    <div>
                        <strong><?php echo Text::sprintf('JBS_WIZARD_DETECTED', $scenarioLabel); ?></strong><br>
                        <span class="small opacity-75" id="wizard-scenario-desc">
                            <?php echo Text::_($this->detectionInfo['description_key']); ?>
                        </span>
                    </div>
                </div>

                <!-- Detection stats -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <div class="card border-0 bg-body-tertiary text-center py-3">
                            <div class="display-6 fw-bold text-primary"><?php echo (int) $this->detectionInfo['locations']; ?></div>
                            <div class="text-body-secondary"><?php echo Text::_('JBS_WIZARD_LOCATIONS'); ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-body-tertiary text-center py-3">
                            <div class="display-6 fw-bold text-primary"><?php echo \count($this->groups); ?></div>
                            <div class="text-body-secondary"><?php echo Text::_('JBS_WIZARD_GROUPS'); ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="card border-0 bg-body-tertiary text-center py-3">
                            <div class="display-6 fw-bold text-<?php echo empty($this->currentMapping) ? 'warning' : 'success'; ?>">
                                <?php echo empty($this->currentMapping) ? Text::_('JBS_WIZARD_NONE') : Text::_('JBS_WIZARD_YES'); ?>
                            </div>
                            <div class="text-body-secondary"><?php echo Text::_('JBS_WIZARD_MAPPING_EXISTS'); ?></div>
                        </div>
                    </div>
                </div>

                <p class="text-body-secondary mb-0">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo Text::_('JBS_WIZARD_TIME_ESTIMATE'); ?>
                </p>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-primary wizard-next-btn">
                    <?php echo Text::_('JBS_WIZARD_START'); ?> <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================
             Step 2: Group Structure Review
             ============================================================ -->
        <div class="wizard-step card shadow-sm" data-step="2">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP2_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body">
                <p><?php echo Text::_('JBS_WIZARD_STEP2_INTRO'); ?></p>

                <!-- Group list -->
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th><?php echo Text::_('JBS_WIZARD_GROUP_ID'); ?></th>
                                <th><?php echo Text::_('JBS_WIZARD_GROUP_NAME'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($this->groups)): ?>
                            <tr>
                                <td colspan="2" class="text-center text-body-secondary">
                                    <?php echo Text::_('JBS_WIZARD_NO_GROUPS'); ?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($this->groups as $group): ?>
                            <tr>
                                <td><code><?php echo (int) $group->id; ?></code></td>
                                <td><?php echo htmlspecialchars($group->title); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP2_HINT'); ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-secondary wizard-prev-btn">
                    <i class="fas fa-arrow-left me-1"></i> <?php echo Text::_('JPREV'); ?>
                </button>
                <button type="button" class="btn btn-primary wizard-next-btn">
                    <?php echo Text::_('JNEXT'); ?> <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================
             Step 3: Location–Group Mapping
             ============================================================ -->
        <div class="wizard-step card shadow-sm" data-step="3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-sitemap me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP3_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body">
                <p><?php echo Text::_('JBS_WIZARD_STEP3_INTRO'); ?></p>

                <?php if (empty($this->locations)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_NO_LOCATIONS'); ?>
                    <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmlocation&layout=edit'); ?>"
                       class="alert-link"><?php echo Text::_('JBS_WIZARD_CREATE_LOCATION'); ?></a>
                </div>
                <?php else: ?>
                <div id="wizard-mapping-container">
                    <?php foreach ($this->locations as $loc): ?>
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <?php echo htmlspecialchars($loc->location_text); ?></strong>
                        </div>
                        <div class="card-body py-2">
                            <p class="small text-body-secondary mb-2"><?php echo Text::_('JBS_WIZARD_SELECT_GROUPS'); ?></p>
                            <div class="row g-2">
                                <?php foreach ($this->groups as $grp): ?>
                                <?php
                                $locId      = (int) $loc->id;
                                $grpId      = (int) $grp->id;
                                $isChecked  = isset($this->currentMapping[(string) $locId])
                                    && \in_array($grpId, (array) $this->currentMapping[(string) $locId], false);
                                ?>
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input wizard-group-check"
                                               type="checkbox"
                                               id="map_<?php echo $locId; ?>_<?php echo $grpId; ?>"
                                               name="mapping[<?php echo $locId; ?>][]"
                                               value="<?php echo $grpId; ?>"
                                               data-location="<?php echo $locId; ?>"
                                               data-group="<?php echo $grpId; ?>"
                                               <?php echo $isChecked ? 'checked' : ''; ?>>
                                        <label class="form-check-label small"
                                               for="map_<?php echo $locId; ?>_<?php echo $grpId; ?>">
                                            <?php echo htmlspecialchars($grp->title); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP3_HINT'); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-secondary wizard-prev-btn">
                    <i class="fas fa-arrow-left me-1"></i> <?php echo Text::_('JPREV'); ?>
                </button>
                <button type="button" class="btn btn-primary wizard-next-btn">
                    <?php echo Text::_('JNEXT'); ?> <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================
             Step 4: Teacher Review
             ============================================================ -->
        <div class="wizard-step card shadow-sm" data-step="4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP4_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body">
                <p><?php echo Text::_('JBS_WIZARD_STEP4_INTRO'); ?></p>

                <div id="wizard-teachers-container">
                    <div class="text-center py-4 text-body-secondary">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <?php echo Text::_('JBS_CMN_LOADING'); ?>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP4_HINT'); ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-secondary wizard-prev-btn">
                    <i class="fas fa-arrow-left me-1"></i> <?php echo Text::_('JPREV'); ?>
                </button>
                <button type="button" class="btn btn-primary wizard-next-btn">
                    <?php echo Text::_('JNEXT'); ?> <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================
             Step 5: Preview & Confirm
             ============================================================ -->
        <div class="wizard-step card shadow-sm" data-step="5">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-eye me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP5_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body">
                <p><?php echo Text::_('JBS_WIZARD_STEP5_INTRO'); ?></p>

                <!-- Preview summary - populated by JS -->
                <div id="wizard-preview-container">
                    <div class="text-center py-4 text-body-secondary">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <?php echo Text::_('JBS_CMN_LOADING'); ?>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP5_HINT'); ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-secondary wizard-prev-btn">
                    <i class="fas fa-arrow-left me-1"></i> <?php echo Text::_('JPREV'); ?>
                </button>
                <button type="button" class="btn btn-primary wizard-next-btn">
                    <?php echo Text::_('JBS_WIZARD_CONFIRM'); ?> <i class="fas fa-check ms-1"></i>
                </button>
            </div>
        </div>

        <!-- ============================================================
             Step 6: Processing
             ============================================================ -->
        <div class="wizard-step card shadow-sm" data-step="6">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="fas fa-cog fa-spin me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP6_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body text-center py-5">
                <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden"><?php echo Text::_('JBS_WIZARD_PROCESSING'); ?></span>
                </div>
                <h5 id="wizard-processing-msg"><?php echo Text::_('JBS_WIZARD_PROCESSING'); ?></h5>
                <div class="progress mt-3 mx-auto" style="max-width: 400px; height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                         role="progressbar"
                         style="width: 100%;"></div>
                </div>
                <p class="text-body-secondary small mt-3"><?php echo Text::_('JBS_WIZARD_DO_NOT_CLOSE'); ?></p>
            </div>
        </div>

        <!-- ============================================================
             Step 7: Complete
             ============================================================ -->
        <div class="wizard-step card shadow-sm" data-step="7">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_STEP7_TITLE'); ?>
                </h4>
            </div>
            <div class="card-body text-center py-4">
                <i class="fas fa-check-circle text-success mb-4" style="font-size: 5rem;"></i>
                <h4><?php echo Text::_('JBS_WIZARD_COMPLETE_HEADING'); ?></h4>
                <p class="text-body-secondary"><?php echo Text::_('JBS_WIZARD_COMPLETE_DESC'); ?></p>

                <div class="row g-3 justify-content-center mt-2">
                    <div class="col-sm-auto">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmlocations'); ?>"
                           class="btn btn-primary">
                            <i class="fas fa-map-marker-alt me-2"></i><?php echo Text::_('JBS_WIZARD_GO_LOCATIONS'); ?>
                        </a>
                    </div>
                    <div class="col-sm-auto">
                        <a href="<?php echo Route::_('index.php?option=com_proclaim'); ?>"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i><?php echo Text::_('JBS_WIZARD_GO_CPANEL'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /col -->
</div><!-- /row -->
<?php echo HTMLHelper::_('form.token'); ?>
