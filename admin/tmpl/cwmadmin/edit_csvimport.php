<?php

/**
 * CSV Import tab for Admin Center
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
<div class="row" id="csvimport">
    <!-- Upload Panel -->
    <div class="col-12">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_CSV_UPLOAD_TITLE'); ?></h3>
            <p class="text-muted"><?php echo Text::_('JBS_CSV_UPLOAD_DESC'); ?></p>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="csv-file-input" class="form-label"><?php echo Text::_('JBS_CSV_SELECT_FILE'); ?></label>
                    <input type="file" class="form-control" id="csv-file-input"
                           accept=".csv,.tsv,.txt" />
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary" id="btn-csv-template">
                        <i class="icon-download" aria-hidden="true"></i>
                        <?php echo Text::_('JBS_CSV_DOWNLOAD_TEMPLATE'); ?>
                    </button>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="csv-first-row-header" checked />
                <label class="form-check-label" for="csv-first-row-header">
                    <?php echo Text::_('JBS_CSV_FIRST_ROW_HEADER'); ?>
                </label>
            </div>
        </div>
    </div>

    <!-- Preview & Mapping Panel -->
    <div class="col-12" id="csv-preview-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="tab-description mb-0"><?php echo Text::_('JBS_CSV_PREVIEW_TITLE'); ?></h3>
                <span class="badge bg-primary" id="csv-row-count"></span>
            </div>
            <p class="text-muted"><?php echo Text::_('JBS_CSV_PREVIEW_DESC'); ?></p>
            <div class="table-responsive" id="csv-preview-table"></div>
        </div>
    </div>

    <!-- Settings Panel -->
    <div class="col-12 col-lg-6">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_CSV_SETTINGS_TITLE'); ?></h3>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="csv-auto-create" checked />
                <label class="form-check-label" for="csv-auto-create">
                    <?php echo Text::_('JBS_CSV_AUTO_CREATE'); ?>
                </label>
                <div class="form-text"><?php echo Text::_('JBS_CSV_AUTO_CREATE_DESC'); ?></div>
            </div>

            <div class="mb-3">
                <label for="csv-default-published" class="form-label">
                    <?php echo Text::_('JBS_CSV_DEFAULT_PUBLISHED'); ?>
                </label>
                <select class="form-select" id="csv-default-published">
                    <option value="1" selected><?php echo Text::_('JPUBLISHED'); ?></option>
                    <option value="0"><?php echo Text::_('JUNPUBLISHED'); ?></option>
                </select>
            </div>

            <div class="mb-3">
                <label for="csv-duplicate-handling" class="form-label">
                    <?php echo Text::_('JBS_CSV_DUPLICATE_HANDLING'); ?>
                </label>
                <select class="form-select" id="csv-duplicate-handling">
                    <option value="skip" selected><?php echo Text::_('JBS_CSV_DUPLICATE_SKIP'); ?></option>
                    <option value="update"><?php echo Text::_('JBS_CSV_DUPLICATE_UPDATE'); ?></option>
                    <option value="create"><?php echo Text::_('JBS_CSV_DUPLICATE_CREATE'); ?></option>
                </select>
                <div class="form-text"><?php echo Text::_('JBS_CSV_DUPLICATE_HANDLING_DESC'); ?></div>
            </div>
        </div>
    </div>

    <!-- Import Button -->
    <div class="col-12 col-lg-6">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_CSV_IMPORT_TITLE'); ?></h3>
            <p class="text-muted"><?php echo Text::_('JBS_CSV_IMPORT_DESC'); ?></p>

            <div class="alert alert-info">
                <i class="icon-info-circle me-2" aria-hidden="true"></i>
                <?php echo Text::_('JBS_CSV_IMPORT_HINT'); ?>
            </div>

            <button type="button" class="btn btn-primary btn-lg" id="btn-csv-import" disabled>
                <i class="icon-upload" aria-hidden="true"></i>
                <?php echo Text::_('JBS_CSV_START_IMPORT'); ?>
            </button>
        </div>
    </div>

    <!-- Progress Panel -->
    <div class="col-12" id="csv-progress-panel" style="display:none;">
        <div class="cwmadmin-panel mb-4">
            <h3 class="tab-description"><?php echo Text::_('JBS_CSV_PROGRESS_TITLE'); ?></h3>
            <div class="progress mb-2" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="csv-progress-bar"
                     style="width: 0%">0%</div>
            </div>
            <p id="csv-progress-text" class="text-center mb-0" aria-live="polite"></p>
        </div>
    </div>

    <!-- Report Panel -->
    <div class="col-12" id="csv-report-panel" style="display:none;"></div>
</div>

<!-- Config data for JavaScript -->
<div id="csv-import-config" class="d-none"
     data-token="<?php echo Session::getFormToken(); ?>"
></div>
