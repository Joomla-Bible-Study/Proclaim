/**
 * Metadata Fix Progress System
 *
 * Provides AJAX-based batch processing for fixing media file metadata
 * (size, MIME type, duration) with real-time progress updates.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
    'use strict';

    /**
   * Metadata Fix Progress Manager
   */
    class MetadataFixProgress {
        constructor(options = {}) {
            this.baseUrl = options.baseUrl || 'index.php';
            this.token = options.token || '';
            this.translations = options.translations || {};

            this.files = [];
            this.currentIndex = 0;
            this.results = {
                fixed: 0,
                failed: 0,
                skipped: 0,
                fixedItems: [],
                errors: [],
            };
            this.isRunning = false;
            this.isCancelled = false;

            this.modal = null;
            this.progressBar = null;
            this.statusText = null;
            this.resultsList = null;
            this.startButton = null;
            this.cancelButton = null;
            this.closeButton = null;
        }

        /**
     * Initialize and show the progress modal
     */
        async init() {
            this.createModal();
            this.showModal();
            await this.loadFiles();

            // Auto-start if files were found
            if (this.files.length > 0) {
                this.start();
            }
        }

        /**
     * Create the progress modal HTML
     */
        createModal() {
            // Remove existing modal if present
            const existing = document.getElementById('metadataFixModal');
            if (existing) {
                existing.remove();
            }

            const modalHtml = `
        <div class="modal fade" id="metadataFixModal" tabindex="-1" aria-labelledby="metadataFixModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="metadataFixModalLabel">${this.t('JBS_PDC_FIX_METADATA_PROGRESS_TITLE')}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="metadataFixStatus" class="mb-3 text-center">
                  <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <span id="metadataFixStatusText">${this.t('JBS_PDC_FIX_METADATA_PROGRESS')}</span>
                </div>
                <div class="progress mb-3" style="height: 25px;">
                  <div id="metadataFixProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div id="metadataFixSummary" class="text-center" style="display: none;">
                  <div class="row text-center">
                    <div class="col">
                      <div class="fs-3 text-success" id="metadataFixedCount">0</div>
                      <small class="text-muted">${this.t('JBS_PDC_FIX_METADATA_FIXED')}</small>
                    </div>
                    <div class="col">
                      <div class="fs-3 text-danger" id="metadataFailedCount">0</div>
                      <small class="text-muted">${this.t('JBS_PDC_FIX_METADATA_FAILED')}</small>
                    </div>
                    <div class="col">
                      <div class="fs-3 text-secondary" id="metadataSkippedCount">0</div>
                      <small class="text-muted">${this.t('JBS_PDC_FIX_METADATA_SKIPPED')}</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="metadataFixCancel" style="display: none;">
                  <span class="icon-cancel" aria-hidden="true"></span> ${this.t('JBS_PDC_FIX_METADATA_CANCEL')}
                </button>
                <button type="button" class="btn btn-secondary" id="metadataFixClose" data-bs-dismiss="modal">
                  ${this.t('JBS_PDC_FIX_METADATA_CLOSE')}
                </button>
              </div>
            </div>
          </div>
        </div>
      `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            this.modal = new bootstrap.Modal(document.getElementById('metadataFixModal'));
            this.progressBar = document.getElementById('metadataFixProgressBar');
            this.statusText = document.getElementById('metadataFixStatusText');
            this.summaryDiv = document.getElementById('metadataFixSummary');
            this.fixedCount = document.getElementById('metadataFixedCount');
            this.failedCount = document.getElementById('metadataFailedCount');
            this.skippedCount = document.getElementById('metadataSkippedCount');
            this.cancelButton = document.getElementById('metadataFixCancel');
            this.closeButton = document.getElementById('metadataFixClose');

            // Event listeners
            this.cancelButton.addEventListener('click', () => this.cancel());

            // Re-validate when modal is closed (if any fixes were made)
            document.getElementById('metadataFixModal').addEventListener('hidden.bs.modal', () => {
                if (this.results.fixed > 0) {
                    this.revalidate();
                }
            });
        }

        /**
     * Show the modal
     */
        showModal() {
            this.modal.show();
        }

        /**
     * Load list of files needing metadata fix
     */
        async loadFiles() {
            try {
                const response = await fetch(
                    `${this.baseUrl}?option=com_proclaim&task=cwmpodcasts.getMediaFilesForMetadata&${this.token}=1&format=json`,
                    { method: 'GET', headers: { Accept: 'application/json' } },
                );

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Failed to load files');
                }

                this.files = data.data.files || [];

                // Update UI
                document.querySelector('#metadataFixStatus .spinner-border').style.display = 'none';

                if (this.files.length === 0) {
                    this.statusText.textContent = this.t('JBS_PDC_FIX_METADATA_NO_FILES');
                    document.querySelector('#metadataFixStatus .spinner-border').style.display = 'none';
                    this.closeButton.style.display = '';
                } else {
                    this.statusText.textContent = this.t('JBS_PDC_FIX_METADATA_FILES_FOUND').replace('%d', this.files.length);
                    // Show summary counters and cancel button - auto-start will begin
                    this.summaryDiv.style.display = '';
                    this.closeButton.style.display = 'none';
                    this.cancelButton.style.display = '';
                }
            } catch (error) {
                this.statusText.textContent = `Error: ${error.message}`;
                document.querySelector('#metadataFixStatus .spinner-border').style.display = 'none';
                this.closeButton.style.display = '';
            }
        }

        /**
     * Start processing files
     */
        async start() {
            this.isRunning = true;
            this.isCancelled = false;
            this.currentIndex = 0;
            this.results = {
                fixed: 0, failed: 0, skipped: 0, fixedItems: [], errors: [],
            };

            // Process files one by one
            for (let i = 0; i < this.files.length; i++) {
                if (this.isCancelled) {
                    break;
                }

                this.currentIndex = i;
                const file = this.files[i];

                // Update progress
                const percent = Math.round(((i + 1) / this.files.length) * 100);
                this.progressBar.style.width = `${percent}%`;
                this.progressBar.textContent = `${i + 1} / ${this.files.length}`;
                this.progressBar.setAttribute('aria-valuenow', percent);
                this.statusText.textContent = file.title;

                // Process single file
                const result = await this.processFile(file.id);

                // Update results and counters
                if (result.type === 'fixed') {
                    this.results.fixed += 1;
                    this.fixedCount.textContent = this.results.fixed;
                    // Track fixed messages
                    this.results.fixedItems.push(result.message);
                } else if (result.type === 'failed') {
                    this.results.failed += 1;
                    this.failedCount.textContent = this.results.failed;
                    // Track error messages
                    this.results.errors.push(result.message);
                } else {
                    this.results.skipped += 1;
                    this.skippedCount.textContent = this.results.skipped;
                }
            }

            // Done
            this.isRunning = false;
            this.onComplete();
        }

        /**
     * Process a single file
     */
        async processFile(mediaId) {
            try {
                const response = await fetch(
                    `${this.baseUrl}?option=com_proclaim&task=cwmpodcasts.fixSingleMetadata&media_id=${mediaId}&${this.token}=1&format=json`,
                    { method: 'GET', headers: { Accept: 'application/json' } },
                );

                const data = await response.json();

                if (!data.success) {
                    return {
                        status: 'error',
                        message: data.message || 'Unknown error',
                        type: 'failed',
                    };
                }

                return data.data;
            } catch (error) {
                return {
                    status: 'error',
                    message: error.message,
                    type: 'failed',
                };
            }
        }

        /**
     * Cancel processing
     */
        cancel() {
            this.isCancelled = true;
            this.cancelButton.disabled = true;
            this.cancelButton.textContent = 'Cancelling...';
        }

        /**
     * Called when processing is complete
     */
        onComplete() {
            document.querySelector('#metadataFixStatus .spinner-border').style.display = 'none';

            if (this.isCancelled) {
                this.statusText.textContent = this.t('JBS_PDC_FIX_METADATA_CANCELLED');
                this.progressBar.classList.remove('progress-bar-animated');
                this.progressBar.classList.add('bg-warning');
            } else {
                this.statusText.textContent = this.t('JBS_PDC_FIX_METADATA_COMPLETE_PROGRESS');
                this.progressBar.classList.remove('progress-bar-animated');
                this.progressBar.classList.add('bg-success');
            }

            // Update buttons
            this.cancelButton.style.display = 'none';
            this.closeButton.style.display = '';
        }

        /**
     * Re-run validation by submitting the validate task
     */
        async revalidate() {
            // Store results in session first
            try {
                await fetch(
                    `${this.baseUrl}?option=com_proclaim&task=cwmpodcasts.storeFixResults&${this.token}=1&format=json`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({
                            fixed: this.results.fixed,
                            failed: this.results.failed,
                            skipped: this.results.skipped,
                            fixedItems: this.results.fixedItems,
                            errors: this.results.errors,
                        }),
                    },
                );
            } catch {
                // Continue with validation even if storing fails
            }

            // Create a form to submit the validation task
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${this.baseUrl}?option=com_proclaim&task=cwmpodcasts.validate`;

            // Add CSRF token
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = this.token;
            tokenInput.value = '1';
            form.appendChild(tokenInput);

            document.body.appendChild(form);
            form.submit();
        }

        /**
     * Get translation
     */
        t(key) {
            return this.translations[key] || key;
        }

        /**
     * Escape HTML
     */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Expose to global scope
    window.MetadataFixProgress = MetadataFixProgress;
})();
