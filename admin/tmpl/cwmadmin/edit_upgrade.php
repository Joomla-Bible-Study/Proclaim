<?php

/**
 * Upgrade Wizard tab for Admin Center
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

?>
<div class="row" id="upgrade-wizard">
    <!-- Detection Panel -->
    <div class="col-12">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description">
                <i class="icon-upload me-2" aria-hidden="true"></i>
                <?php echo Text::_('JBS_UPG_DETECTION_TITLE'); ?>
            </h3>
            <p class="text-muted"><?php echo Text::_('JBS_UPG_DETECTION_DESC'); ?></p>

            <div id="upgrade-detection-status">
                <div class="d-flex align-items-center">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <span><?php echo Text::_('JBS_UPG_DETECTING'); ?></span>
                </div>
            </div>

            <!-- Version warning for < 9.2.0 (hidden until detection) -->
            <div id="upgrade-version-warning" class="alert alert-danger mt-3" style="display:none;" role="alert">
                <i class="icon-warning-2 me-2" aria-hidden="true"></i>
                <strong><?php echo Text::_('JBS_UPG_VERSION_TOO_OLD'); ?></strong>
                <p class="mb-0 mt-1"><?php echo Text::_('JBS_UPG_VERSION_TOO_OLD_DESC'); ?></p>
            </div>

            <!-- Record counts table (populated via JS) -->
            <div id="upgrade-record-counts" class="mt-3" style="display:none;">
                <h4 class="h6"><?php echo Text::_('JBS_UPG_RECORD_COUNTS'); ?></h4>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="upgrade-counts-table">
                        <thead>
                            <tr>
                                <th><?php echo Text::_('JBS_UPG_TABLE'); ?></th>
                                <th class="text-end"><?php echo Text::_('JBS_UPG_RECORDS'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Panel (hidden until detection succeeds) -->
    <div class="col-12" id="upgrade-wizard-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description">
                <i class="icon-cog me-2" aria-hidden="true"></i>
                <?php echo Text::_('JBS_UPG_WIZARD_TITLE'); ?>
            </h3>
            <p class="text-muted"><?php echo Text::_('JBS_UPG_WIZARD_DESC'); ?></p>

            <!-- Step Indicators -->
            <ol class="list-group list-group-numbered mb-4" id="upgrade-steps">
                <li class="list-group-item d-flex justify-content-between align-items-start" data-step="backup">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?php echo Text::_('JBS_UPG_STEP_BACKUP'); ?></div>
                        <small class="text-muted"><?php echo Text::_('JBS_UPG_STEP_BACKUP_DESC'); ?></small>
                    </div>
                    <span class="badge bg-secondary" data-step-badge="backup"><?php echo Text::_('JBS_UPG_PENDING'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-start" data-step="params">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?php echo Text::_('JBS_UPG_STEP_PARAMS'); ?></div>
                        <small class="text-muted"><?php echo Text::_('JBS_UPG_STEP_PARAMS_DESC'); ?></small>
                    </div>
                    <span class="badge bg-secondary" data-step-badge="params"><?php echo Text::_('JBS_UPG_PENDING'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-start" data-step="schema">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?php echo Text::_('JBS_UPG_STEP_SCHEMA'); ?></div>
                        <small class="text-muted"><?php echo Text::_('JBS_UPG_STEP_SCHEMA_DESC'); ?></small>
                    </div>
                    <span class="badge bg-secondary" data-step-badge="schema"><?php echo Text::_('JBS_UPG_PENDING'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-start" data-step="data">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?php echo Text::_('JBS_UPG_STEP_DATA'); ?></div>
                        <small class="text-muted"><?php echo Text::_('JBS_UPG_STEP_DATA_DESC'); ?></small>
                    </div>
                    <span class="badge bg-secondary" data-step-badge="data"><?php echo Text::_('JBS_UPG_PENDING'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-start" data-step="assets">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?php echo Text::_('JBS_UPG_STEP_ASSETS'); ?></div>
                        <small class="text-muted"><?php echo Text::_('JBS_UPG_STEP_ASSETS_DESC'); ?></small>
                    </div>
                    <span class="badge bg-secondary" data-step-badge="assets"><?php echo Text::_('JBS_UPG_PENDING'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-start" data-step="verify">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?php echo Text::_('JBS_UPG_STEP_VERIFY'); ?></div>
                        <small class="text-muted"><?php echo Text::_('JBS_UPG_STEP_VERIFY_DESC'); ?></small>
                    </div>
                    <span class="badge bg-secondary" data-step-badge="verify"><?php echo Text::_('JBS_UPG_PENDING'); ?></span>
                </li>
            </ol>

            <!-- Progress Bar -->
            <div class="mb-3">
                <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                     id="upgrade-progress" aria-label="<?php echo Text::_('JBS_UPG_PROGRESS'); ?>">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                </div>
            </div>

            <!-- Status Text -->
            <p class="text-center mb-3" id="upgrade-status-text" aria-live="polite"></p>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-primary btn-lg" id="btn-start-upgrade">
                    <i class="icon-upload me-1" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_UPG_START'); ?>
                </button>
                <button type="button" class="btn btn-outline-danger" id="btn-cancel-upgrade" style="display:none;">
                    <i class="icon-cancel me-1" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_UPG_CANCEL'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Report Panel (hidden until wizard completes) -->
    <div class="col-12" id="upgrade-report-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description">
                <i class="icon-checkmark me-2" aria-hidden="true"></i>
                <?php echo Text::_('JBS_UPG_REPORT_TITLE'); ?>
            </h3>
            <div id="upgrade-report-content"></div>
        </div>
    </div>
</div>

<!-- Configuration data for JS -->
<div id="upgrade-config"
     data-token="<?php echo Session::getFormToken(); ?>"
     style="display:none;"></div>
