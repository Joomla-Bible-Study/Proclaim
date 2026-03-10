/**
 * Proclaim Backup/Restore AJAX Handler
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.1.0
 */

((Joomla) => {
    'use strict';

    /**
   * Backup/Restore Manager
   */
    class ProclaimBackupRestore {
        constructor() {
            this.modal = null;
            this.progressBar = null;
            this.statusText = null;
            this.detailText = null;
            this.cancelBtn = null;
            this.closeBtn = null;
            this.liveRegion = null;
            this.triggerElement = null;
            this.isCancelled = false;
            this.token = Joomla.getOptions('csrf.token') || document.querySelector(`input[name^="${Joomla.getOptions('csrf.token', '')}"]`)?.name || '';
        }

        /**
     * Initialize the module
     */
        init() {
            this.createModal();
            this.bindEvents();
        }

        /**
     * Create the progress modal
     */
        createModal() {
            // Create live region for screen reader announcements (WCAG 4.1.3)
            if (!document.getElementById('proclaim-live-region')) {
                const liveRegion = document.createElement('div');
                liveRegion.id = 'proclaim-live-region';
                liveRegion.className = 'visually-hidden';
                liveRegion.setAttribute('aria-live', 'polite');
                liveRegion.setAttribute('aria-atomic', 'true');
                liveRegion.setAttribute('role', 'status');
                document.body.appendChild(liveRegion);
            }
            this.liveRegion = document.getElementById('proclaim-live-region');

            // Check if modal already exists
            if (document.getElementById('proclaim-backup-modal')) {
                this.modal = document.getElementById('proclaim-backup-modal');
                this.progressBar = this.modal.querySelector('.progress-bar');
                this.statusText = this.modal.querySelector('.status-text');
                this.detailText = this.modal.querySelector('.detail-text');
                this.cancelBtn = this.modal.querySelector('.btn-cancel');
                this.closeBtn = this.modal.querySelector('.btn-close-modal');
                return;
            }

            const modalHtml = `
        <div class="modal fade" id="proclaim-backup-modal" tabindex="-1" aria-hidden="true" aria-labelledby="proclaim-modal-title" aria-describedby="proclaim-modal-status" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="proclaim-modal-title">
                  <i class="fas fa-database me-2" aria-hidden="true"></i>
                  <span class="title-text">${Joomla.Text._('JBS_IBM_PROCESSING')}</span>
                </h5>
              </div>
              <div class="modal-body" id="proclaim-modal-status">
                <div class="text-center mb-3">
                  <div class="spinner-border text-primary operation-spinner" role="status" aria-label="${Joomla.Text._('JBS_IBM_PROCESSING')}">
                    <span class="visually-hidden">${Joomla.Text._('JBS_IBM_PROCESSING')}</span>
                  </div>
                </div>
                <p class="status-text text-center fw-bold mb-2" aria-live="polite"></p>
                <div class="progress mb-3" style="height: 25px;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="${Joomla.Text._('JBS_IBM_PROCESSING')}">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                </div>
                <p class="detail-text text-muted small text-center mb-0" aria-live="polite"></p>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger btn-cancel" aria-label="${Joomla.Text._('JCANCEL')}">
                  <i class="fas fa-times me-1" aria-hidden="true"></i>${Joomla.Text._('JCANCEL')}
                </button>
                <button type="button" class="btn btn-success btn-close-modal" style="display: none;" aria-label="${Joomla.Text._('JCLOSE')}">
                  <i class="fas fa-check me-1" aria-hidden="true"></i>${Joomla.Text._('JCLOSE')}
                </button>
              </div>
            </div>
          </div>
        </div>
      `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            this.modal = document.getElementById('proclaim-backup-modal');
            this.progressBar = this.modal.querySelector('.progress-bar');
            this.statusText = this.modal.querySelector('.status-text');
            this.detailText = this.modal.querySelector('.detail-text');
            this.cancelBtn = this.modal.querySelector('.btn-cancel');
            this.closeBtn = this.modal.querySelector('.btn-close-modal');
        }

        /**
     * Bind event listeners
     */
        bindEvents() {
            // Export buttons - store trigger element for focus management (WCAG 2.4.3)
            document.querySelectorAll('[data-proclaim-export]').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.triggerElement = btn;
                    const mode = btn.dataset.proclaimExport; // 'download' or 'save'
                    this.startExport(mode);
                });
            });

            // Import form - store trigger element for focus management
            const importForm = document.getElementById('proclaim-import-form');
            if (importForm) {
                importForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.triggerElement = importForm.querySelector('button[type="submit"]');
                    this.startImport();
                });
            }

            // Cancel button
            if (this.cancelBtn) {
                this.cancelBtn.addEventListener('click', () => {
                    this.isCancelled = true;
                    this.announceToScreenReader(Joomla.Text._('JCANCEL'));
                    this.hideModal();
                });
            }

            // Close button
            if (this.closeBtn) {
                this.closeBtn.addEventListener('click', () => {
                    this.hideModal();
                    // Reload page to show updated backup list
                    window.location.reload();
                });
            }

            // Return focus to trigger element when modal closes (WCAG 2.4.3)
            if (this.modal) {
                this.modal.addEventListener('hidden.bs.modal', () => {
                    if (this.triggerElement && document.body.contains(this.triggerElement)) {
                        this.triggerElement.focus();
                    }
                });
            }
        }

        /**
     * Announce message to screen readers via live region (WCAG 4.1.3)
     */
        announceToScreenReader(message) {
            if (this.liveRegion && message) {
                // Clear and set to trigger announcement
                this.liveRegion.textContent = '';
                setTimeout(() => {
                    this.liveRegion.textContent = message;
                }, 50);
            }
        }

        /**
     * Show the progress modal with accessibility support
     */
        showModal(title) {
            this.isCancelled = false;
            this.modal.querySelector('.title-text').textContent = title;
            this.modal.querySelector('.operation-spinner').style.display = 'inline-block';
            this.modal.querySelector('.operation-spinner').setAttribute('aria-label', title);
            this.progressBar.style.width = '0%';
            this.progressBar.textContent = '0%';
            this.progressBar.classList.remove('bg-success', 'bg-danger');
            this.progressBar.classList.add('progress-bar-animated');
            this.statusText.textContent = '';
            this.detailText.textContent = '';
            this.cancelBtn.style.display = 'inline-block';
            this.closeBtn.style.display = 'none';

            // Reset progress bar ARIA attributes
            const progressContainer = this.progressBar.parentElement;
            if (progressContainer) {
                progressContainer.setAttribute('aria-valuenow', '0');
                progressContainer.setAttribute('aria-label', title);
            }

            const bsModal = new bootstrap.Modal(this.modal);
            bsModal.show();

            // Announce to screen readers (WCAG 4.1.3)
            this.announceToScreenReader(title);
        }

        /**
     * Hide the progress modal
     */
        hideModal() {
            const bsModal = bootstrap.Modal.getInstance(this.modal);
            if (bsModal) {
                bsModal.hide();
            }
        }

        /**
     * Update progress display with accessibility support
     */
        updateProgress(percent, status, detail) {
            const roundedPercent = Math.round(percent);
            this.progressBar.style.width = `${percent}%`;
            this.progressBar.textContent = `${roundedPercent}%`;

            // Update ARIA attributes for progress bar (WCAG 4.1.2)
            const progressContainer = this.progressBar.parentElement;
            if (progressContainer) {
                progressContainer.setAttribute('aria-valuenow', roundedPercent);
            }

            if (status) this.statusText.textContent = status;
            if (detail) this.detailText.textContent = detail;

            // Announce significant updates to screen readers (every 25% or status change)
            if (status && (roundedPercent % 25 === 0 || roundedPercent === 100)) {
                const announcement = detail ? `${status}: ${detail}` : status;
                this.announceToScreenReader(announcement);
            }
        }

        /**
     * Show completion state with accessibility support
     */
        showComplete(success, message) {
            this.modal.querySelector('.operation-spinner').style.display = 'none';
            this.progressBar.classList.remove('progress-bar-animated');
            this.progressBar.classList.add(success ? 'bg-success' : 'bg-danger');
            this.progressBar.style.width = '100%';

            const statusLabel = success ? Joomla.Text._('JBS_IBM_COMPLETE') : Joomla.Text._('JBS_IBM_FAILED');
            this.progressBar.textContent = statusLabel;
            this.statusText.textContent = message;

            // Update ARIA for completion state
            const progressContainer = this.progressBar.parentElement;
            if (progressContainer) {
                progressContainer.setAttribute('aria-valuenow', '100');
                progressContainer.setAttribute('aria-label', statusLabel);
            }

            this.cancelBtn.style.display = 'none';
            this.closeBtn.style.display = 'inline-block';

            // Announce completion to screen readers (WCAG 4.1.3)
            const announcement = `${statusLabel}: ${message}`;
            this.announceToScreenReader(announcement);

            // Move focus to close button for keyboard users (WCAG 2.4.3)
            this.closeBtn.focus();
        }

        /**
     * Get CSRF token for AJAX requests
     */
        getToken() {
            const tokenInput = document.querySelector(`input[name="${Joomla.getOptions('csrf.token')}"]`);
            return tokenInput ? tokenInput.value : '1';
        }

        /**
     * Make AJAX request
     */
        async fetchJson(url, options = {}) {
            const defaultOptions = {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': this.getToken(),
                },
            };

            return window.ProclaimFetch.fetchJson(
                url,
                { ...defaultOptions, ...options },
                { timeout: 60000, retries: 0 },
            );
        }

        /**
     * Start export process
     */
        async startExport(mode) {
            this.showModal(Joomla.Text._('JBS_IBM_EXPORTING_DATABASE'));

            try {
                // Step 1: Get list of tables and export ID
                this.updateProgress(5, Joomla.Text._('JBS_IBM_GETTING_TABLES'), '');

                const tablesUrl = `index.php?option=com_proclaim&task=cwmbackup.getExportTablesXHR&format=json&${Joomla.getOptions('csrf.token')}=1`;
                const tablesResult = await this.fetchJson(tablesUrl);

                if (!tablesResult.success) {
                    throw new Error(tablesResult.message || 'Failed to get tables');
                }

                const { tables } = tablesResult.data;
                const { exportId } = tablesResult.data;
                const totalTables = tables.length;

                // Step 2: Export each table
                for (let i = 0; i < totalTables; i++) {
                    if (this.isCancelled) {
                        throw new Error('Export cancelled by user');
                    }

                    const table = tables[i];
                    const percent = 10 + ((i / totalTables) * 80);

                    this.updateProgress(
                        percent,
                        Joomla.Text._('JBS_IBM_EXPORTING_TABLE'),
                        `${table} (${i + 1}/${totalTables})`,
                    );

                    const exportUrl = `index.php?option=com_proclaim&task=cwmbackup.exportTableXHR&format=json&table=${encodeURIComponent(table)}&exportId=${encodeURIComponent(exportId)}&${Joomla.getOptions('csrf.token')}=1`;
                    const exportResult = await this.fetchJson(exportUrl);

                    if (!exportResult.success) {
                        throw new Error(exportResult.message || `Failed to export table: ${table}`);
                    }
                }

                // Step 3: Finalize export
                this.updateProgress(95, Joomla.Text._('JBS_IBM_FINALIZING'), '');

                const finalizeUrl = `index.php?option=com_proclaim&task=cwmbackup.finalizeExportXHR&format=json&mode=${mode}&exportId=${encodeURIComponent(exportId)}&${Joomla.getOptions('csrf.token')}=1`;
                const finalizeResult = await this.fetchJson(finalizeUrl);

                if (!finalizeResult.success) {
                    throw new Error(finalizeResult.message || 'Failed to finalize export');
                }

                this.updateProgress(100, '', '');

                if (mode === 'download' && finalizeResult.data.downloadUrl) {
                    // Trigger download
                    this.showComplete(true, Joomla.Text._('JBS_IBM_EXPORT_COMPLETE'));
                    window.location.href = finalizeResult.data.downloadUrl;
                } else {
                    this.showComplete(true, `${Joomla.Text._('JBS_IBM_BACKUP_SAVED')}: ${finalizeResult.data.filename || ''}`);
                }
            } catch (error) {
                console.error('Export error:', error);
                this.showComplete(false, error.message);
            }
        }

        /**
     * Start import process
     */
        async startImport() {
            const fileInput = document.getElementById('importdb');
            const backupSelect = document.getElementById('backuprestore');
            const tmpFolder = document.getElementById('install_directory');

            let importSource = null;
            let importType = null;

            // Determine import source
            if (fileInput && fileInput.files.length > 0) {
                [importSource] = fileInput.files;
                importType = 'upload';
            } else if (backupSelect && backupSelect.value && backupSelect.value !== '0') {
                importSource = backupSelect.value;
                importType = 'backup';
            } else if (tmpFolder && tmpFolder.value && tmpFolder.value !== '/') {
                importSource = tmpFolder.value;
                importType = 'folder';
            }

            if (!importSource) {
                Joomla.renderMessages({ error: [Joomla.Text._('JBS_CMN_NO_FILE_SELECTED')] });
                return;
            }

            this.showModal(Joomla.Text._('JBS_IBM_IMPORTING_DATABASE'));

            try {
                let sessionId;

                // Step 1: Upload or prepare file
                this.updateProgress(5, Joomla.Text._('JBS_IBM_PREPARING_IMPORT'), '');

                if (importType === 'upload') {
                    // Upload the file
                    const formData = new FormData();
                    formData.append('importdb', importSource);
                    formData.append(Joomla.getOptions('csrf.token'), '1');

                    const uploadUrl = 'index.php?option=com_proclaim&task=cwmbackup.uploadImportFileXHR&format=json';
                    const uploadResult = await window.ProclaimFetch.fetchJson(
                        uploadUrl,
                        { method: 'POST', body: formData },
                        { timeout: 60000, retries: 0 },
                    );

                    if (!uploadResult.success) {
                        throw new Error(uploadResult.message || 'Failed to upload file');
                    }

                    sessionId = uploadResult.data.sessionId;
                } else {
                    // Prepare from backup folder or tmp
                    const prepareUrl = `index.php?option=com_proclaim&task=cwmbackup.prepareImportXHR&format=json&type=${importType}&source=${encodeURIComponent(importSource)}&${Joomla.getOptions('csrf.token')}=1`;
                    const prepareResult = await this.fetchJson(prepareUrl);

                    if (!prepareResult.success) {
                        throw new Error(prepareResult.message || 'Failed to prepare import');
                    }

                    sessionId = prepareResult.data.sessionId;
                }

                // Step 2: Get import info
                this.updateProgress(15, Joomla.Text._('JBS_IBM_ANALYZING_FILE'), '');

                const infoUrl = `index.php?option=com_proclaim&task=cwmbackup.getImportInfoXHR&format=json&sessionId=${sessionId}&${Joomla.getOptions('csrf.token')}=1`;
                const infoResult = await this.fetchJson(infoUrl);

                if (!infoResult.success) {
                    throw new Error(infoResult.message || 'Failed to analyze import file');
                }

                const { totalBatches } = infoResult.data;

                // Step 3: Import in batches
                for (let batch = 0; batch < totalBatches; batch++) {
                    if (this.isCancelled) {
                        throw new Error('Import cancelled by user');
                    }

                    const percent = 20 + ((batch / totalBatches) * 70);

                    this.updateProgress(
                        percent,
                        Joomla.Text._('JBS_IBM_IMPORTING_DATA'),
                        `${Joomla.Text._('JBS_IBM_BATCH')} ${batch + 1} / ${totalBatches}`,
                    );

                    const batchUrl = `index.php?option=com_proclaim&task=cwmbackup.importBatchXHR&format=json&sessionId=${sessionId}&batch=${batch}&${Joomla.getOptions('csrf.token')}=1`;
                    const batchResult = await this.fetchJson(batchUrl);

                    if (!batchResult.success) {
                        throw new Error(batchResult.message || `Failed to import batch ${batch + 1}`);
                    }
                }

                // Step 4: Finalize import
                this.updateProgress(92, Joomla.Text._('JBS_IBM_FIXING_ASSETS'), '');

                // Check if skip asset fix is enabled (dev testing option)
                const skipAssetFix = document.getElementById('skip_asset_fix')?.checked ? '1' : '0';
                const finalizeUrl = `index.php?option=com_proclaim&task=cwmbackup.finalizeImportXHR&format=json&sessionId=${sessionId}&skipAssetFix=${skipAssetFix}&${Joomla.getOptions('csrf.token')}=1`;
                const finalizeResult = await this.fetchJson(finalizeUrl);

                if (!finalizeResult.success) {
                    throw new Error(finalizeResult.message || 'Failed to finalize import');
                }

                this.updateProgress(100, '', '');
                this.showComplete(true, Joomla.Text._('JBS_IBM_IMPORT_COMPLETE'));
            } catch (error) {
                console.error('Import error:', error);
                this.showComplete(false, error.message);
            }
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        const backupRestore = new ProclaimBackupRestore();
        backupRestore.init();

        // Expose for external use
        window.ProclaimBackupRestore = backupRestore;
    });
})(Joomla);
