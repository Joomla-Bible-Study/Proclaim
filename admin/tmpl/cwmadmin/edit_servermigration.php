<?php

/**
 * Server Migration tab for Admin Center
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
<div class="row" id="servermigration">
    <!-- Phase 1: Scan -->
    <div class="col-12" id="smg-scan-panel">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_SMG_SCAN_TITLE'); ?></h3>
            <p class="text-muted"><?php echo Text::_('JBS_SMG_SCAN_DESC'); ?></p>

            <button type="button" class="btn btn-primary" id="btn-smg-scan">
                <i class="icon-search" aria-hidden="true"></i>
                <?php echo Text::_('JBS_SMG_SCAN_BTN'); ?>
            </button>

            <div id="smg-scan-spinner" class="mt-3" style="display:none;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <?php echo Text::_('JBS_ADM_LOADING'); ?>
            </div>
        </div>
    </div>

    <!-- Phase 1 Results -->
    <div class="col-12" id="smg-results-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="tab-description mb-0"><?php echo Text::_('JBS_SMG_RESULTS_TITLE'); ?></h3>
                <span class="badge bg-primary" id="smg-total-count"></span>
            </div>

            <div id="smg-no-legacy" class="alert alert-success" style="display:none;">
                <i class="icon-checkmark me-2" aria-hidden="true"></i>
                <?php echo Text::_('JBS_SMG_NO_LEGACY'); ?>
            </div>

            <div id="smg-results-table" class="table-responsive"></div>
        </div>
    </div>

    <!-- Phase 2: Configure -->
    <div class="col-12" id="smg-config-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_SMG_CONFIG_TITLE'); ?></h3>
            <p class="text-muted"><?php echo Text::_('JBS_SMG_CONFIG_DESC'); ?></p>

            <div id="smg-config-form"></div>

            <div class="mt-3">
                <button type="button" class="btn btn-success btn-lg" id="btn-smg-start" disabled>
                    <i class="icon-play" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_SMG_START_BTN'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Phase 3: Migration Progress -->
    <div class="col-12" id="smg-progress-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_SMG_PROGRESS_TITLE'); ?></h3>
            <div class="progress mb-2" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="smg-progress-bar"
                     style="width: 0%">0%</div>
            </div>
            <p id="smg-progress-text" class="text-center mb-0" aria-live="polite"></p>
        </div>
    </div>

    <!-- Phase 4: Report & Cleanup -->
    <div class="col-12" id="smg-report-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_SMG_REPORT_TITLE'); ?></h3>
            <div id="smg-report-content"></div>

            <div class="mt-3">
                <button type="button" class="btn btn-warning" id="btn-smg-cleanup">
                    <i class="icon-trash" aria-hidden="true"></i>
                    <?php echo Text::_('JBS_SMG_CLEANUP_BTN'); ?>
                </button>
            </div>

            <div id="smg-cleanup-result" class="mt-3" style="display:none;"></div>
        </div>
    </div>
</div>

<!-- Config data for JavaScript -->
<div id="smg-config" class="d-none"
     data-token="<?php echo Session::getFormToken(); ?>"
></div>
