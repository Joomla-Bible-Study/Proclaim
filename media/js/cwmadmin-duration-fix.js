(function () {
  'use strict';

  /**
   * Duration Fix Progress System
   *
   * Provides AJAX-based batch processing for fixing media file durations
   * with real-time progress updates.
   *
   * @package    Proclaim.Admin
   * @copyright  (C) 2026 CWM Team All rights reserved
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */

  ((Joomla) => {

    /**
     * Duration Fix Progress Manager
     */
    class DurationFixProgress {
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
          details: []
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
      }

      /**
       * Create the progress modal HTML
       */
      createModal() {
        // Remove existing modal if present
        const existing = document.getElementById('durationFixModal');
        if (existing) {
          existing.remove();
        }

        const modalHtml = `
        <div class="modal fade" id="durationFixModal" tabindex="-1" aria-labelledby="durationFixModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="durationFixModalLabel">${this.t('JBS_PDC_FIX_DURATION_PROGRESS_TITLE')}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="durationFixStatus" class="mb-3">
                  <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <span id="durationFixStatusText">${this.t('JBS_PDC_FIX_DURATION_PROGRESS')}</span>
                </div>
                <div class="progress mb-3" style="height: 25px;">
                  <div id="durationFixProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div id="durationFixNote" class="alert alert-info mb-3" style="display: none;"></div>
                <div id="durationFixResults" class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                  <ul class="list-unstyled mb-0" id="durationFixResultsList"></ul>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="durationFixStart" style="display: none;">
                  <span class="icon-play" aria-hidden="true"></span> ${this.t('JBS_PDC_FIX_DURATION_START')}
                </button>
                <button type="button" class="btn btn-danger" id="durationFixCancel" style="display: none;">
                  <span class="icon-cancel" aria-hidden="true"></span> ${this.t('JBS_PDC_FIX_DURATION_CANCEL')}
                </button>
                <button type="button" class="btn btn-secondary" id="durationFixClose" data-bs-dismiss="modal">
                  ${this.t('JBS_PDC_FIX_DURATION_CLOSE')}
                </button>
              </div>
            </div>
          </div>
        </div>
      `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        this.modal = new bootstrap.Modal(document.getElementById('durationFixModal'));
        this.progressBar = document.getElementById('durationFixProgressBar');
        this.statusText = document.getElementById('durationFixStatusText');
        this.resultsList = document.getElementById('durationFixResultsList');
        this.startButton = document.getElementById('durationFixStart');
        this.cancelButton = document.getElementById('durationFixCancel');
        this.closeButton = document.getElementById('durationFixClose');
        this.noteDiv = document.getElementById('durationFixNote');

        // Event listeners
        this.startButton.addEventListener('click', () => this.start());
        this.cancelButton.addEventListener('click', () => this.cancel());
      }

      /**
       * Show the modal
       */
      showModal() {
        this.modal.show();
      }

      /**
       * Load list of files needing duration fix
       */
      async loadFiles() {
        try {
          const response = await fetch(
            `${this.baseUrl}?option=com_proclaim&task=cwmpodcasts.getMediaFilesForDuration&${this.token}=1&format=json`,
            { method: 'GET', headers: { 'Accept': 'application/json' } }
          );

          const data = await response.json();

          if (!data.success) {
            throw new Error(data.message || 'Failed to load files');
          }

          this.files = data.data.files || [];
          const hasFFprobe = data.data.ffprobe;

          // Update UI
          document.querySelector('#durationFixStatus .spinner-border').style.display = 'none';

          if (this.files.length === 0) {
            this.statusText.textContent = this.t('JBS_PDC_FIX_DURATION_NO_FILES');
            this.closeButton.style.display = '';
          } else {
            this.statusText.textContent = this.t('JBS_PDC_FIX_DURATION_FILES_FOUND').replace('%d', this.files.length);
            this.startButton.style.display = '';
            this.closeButton.style.display = '';

            // Show FFprobe note
            this.noteDiv.style.display = '';
            if (hasFFprobe) {
              this.noteDiv.className = 'alert alert-success mb-3';
              this.noteDiv.textContent = this.t('JBS_PDC_FIX_DURATION_FFPROBE_NOTE');
            } else {
              this.noteDiv.className = 'alert alert-warning mb-3';
              this.noteDiv.textContent = this.t('JBS_PDC_FIX_DURATION_NO_FFPROBE_NOTE');
            }
          }
        } catch (error) {
          this.statusText.textContent = `Error: ${error.message}`;
          document.querySelector('#durationFixStatus .spinner-border').style.display = 'none';
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
        this.results = { fixed: 0, failed: 0, skipped: 0, details: [] };

        // Update UI
        this.startButton.style.display = 'none';
        this.cancelButton.style.display = '';
        this.closeButton.style.display = 'none';
        this.noteDiv.style.display = 'none';
        document.querySelector('#durationFixStatus .spinner-border').style.display = '';

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
          this.progressBar.textContent = `${percent}%`;
          this.progressBar.setAttribute('aria-valuenow', percent);
          this.statusText.textContent = this.t('JBS_PDC_FIX_DURATION_PROCESSING').replace('%s', file.title);

          // Process single file
          const result = await this.processFile(file.id);

          // Update results
          if (result.type === 'fixed') {
            this.results.fixed++;
          } else if (result.type === 'failed') {
            this.results.failed++;
          } else {
            this.results.skipped++;
          }
          this.results.details.push(result.message);

          // Add to results list
          this.addResultItem(result);
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
            `${this.baseUrl}?option=com_proclaim&task=cwmpodcasts.fixSingleDuration&media_id=${mediaId}&${this.token}=1&format=json`,
            { method: 'GET', headers: { 'Accept': 'application/json' } }
          );

          const data = await response.json();

          if (!data.success) {
            return {
              status: 'error',
              message: data.message || 'Unknown error',
              type: 'failed'
            };
          }

          return data.data;
        } catch (error) {
          return {
            status: 'error',
            message: error.message,
            type: 'failed'
          };
        }
      }

      /**
       * Add a result item to the list
       */
      addResultItem(result) {
        const li = document.createElement('li');
        li.className = 'mb-1';

        let icon = 'info-circle';
        let textClass = 'text-muted';

        if (result.type === 'fixed') {
          icon = 'check-circle';
          textClass = 'text-success';
        } else if (result.type === 'failed') {
          icon = 'times-circle';
          textClass = 'text-danger';
        } else if (result.type === 'skipped') {
          icon = 'minus-circle';
          textClass = 'text-secondary';
        }

        li.innerHTML = `<span class="icon-${icon} ${textClass}" aria-hidden="true"></span> ${this.escapeHtml(result.message)}`;
        this.resultsList.appendChild(li);

        // Auto-scroll to bottom
        this.resultsList.parentElement.scrollTop = this.resultsList.parentElement.scrollHeight;
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
        document.querySelector('#durationFixStatus .spinner-border').style.display = 'none';

        if (this.isCancelled) {
          this.statusText.textContent = 'Cancelled';
          this.progressBar.classList.remove('progress-bar-animated');
          this.progressBar.classList.add('bg-warning');
        } else {
          this.statusText.textContent = this.t('JBS_PDC_FIX_DURATION_COMPLETE_PROGRESS');
          this.progressBar.classList.remove('progress-bar-animated');
          this.progressBar.classList.add('bg-success');
        }

        // Show summary
        const summaryText = this.t('JBS_PDC_FIX_DURATION_SUMMARY')
          .replace('%d', this.results.fixed)
          .replace('%d', this.results.failed)
          .replace('%d', this.results.skipped);

        const summary = document.createElement('div');
        summary.className = 'alert alert-info mt-3';
        summary.innerHTML = `<strong>${summaryText}</strong>`;
        this.resultsList.parentElement.appendChild(summary);

        // Update buttons
        this.cancelButton.style.display = 'none';
        this.closeButton.style.display = '';
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
    window.DurationFixProgress = DurationFixProgress;

  })(Joomla);

})();
