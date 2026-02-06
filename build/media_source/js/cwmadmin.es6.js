/**
 * Proclaim CWM Admin Page Handler
 *
 * Handles lazy loading of statistics, form submissions, and thumbnail resize operations.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.1.0
 */

((Joomla) => {
  'use strict';

  /**
   * CWM Admin Page Manager
   */
  class ProclaimCwmadmin {
    constructor() {
      this.token = Joomla.getOptions('csrf.token') || '';
      this.form = null;
      this.thumbnailModal = null;
      this.aliasModal = null;
      this.playerToolsModal = null;
      this.liveRegion = null;
    }

    /**
     * Initialize the module
     */
    init() {
      this.form = document.getElementById('item-admin');
      this.createLiveRegion();
      this.bindEvents();
      this.loadPlayerStats();
      this.loadPopupStats();
      this.setupJoomlaSubmitbutton();
    }

    /**
     * Create ARIA live region for screen reader announcements
     */
    createLiveRegion() {
      if (!document.getElementById('cwmadmin-live-region')) {
        const liveRegion = document.createElement('div');
        liveRegion.id = 'cwmadmin-live-region';
        liveRegion.className = 'visually-hidden';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.setAttribute('role', 'status');
        document.body.appendChild(liveRegion);
      }
      this.liveRegion = document.getElementById('cwmadmin-live-region');
    }

    /**
     * Announce message to screen readers
     * @param {string} message - Message to announce
     */
    announceToScreenReader(message) {
      if (this.liveRegion && message) {
        this.liveRegion.textContent = '';
        setTimeout(() => {
          this.liveRegion.textContent = message;
        }, 50);
      }
    }

    /**
     * Get CSRF token value for AJAX requests
     * @returns {string} Token value
     */
    getToken() {
      const tokenInput = document.querySelector('input[name="' + this.token + '"]');
      return tokenInput ? tokenInput.value : '1';
    }

    /**
     * Make AJAX request and return JSON
     * @param {string} url - Request URL
     * @param {object} options - Fetch options
     * @returns {Promise<object>} Response JSON
     */
    async fetchJson(url, options = {}) {
      const defaultOptions = {
        method: 'GET',
        headers: {
          'X-CSRF-Token': this.getToken()
        }
      };

      const response = await fetch(url, { ...defaultOptions, ...options });

      if (!response.ok) {
        if (response.status === 403 || response.status === 401) {
          Joomla.renderMessages({
            error: [Joomla.Text._('JLIB_ENVIRONMENT_SESSION_EXPIRED')
              || 'Your session has expired. Please log in again.']
          });
          setTimeout(() => { window.location.reload(); }, 3000);
          throw new Error('Session expired');
        }
        throw new Error(`HTTP ${response.status}`);
      }

      return response.json();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
      // Tool form buttons
      document.querySelectorAll('[data-cwmadmin-tool]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const tooltype = btn.dataset.cwmadminTool;
          const task = btn.dataset.cwmadminTask || 'cwmadmin.tools';
          this.submitToolForm(tooltype, task);
        });
      });

      // Initialize Bootstrap 5 modal for thumbnail resize
      const modalEl = document.getElementById('dialog_thumbnail_resize');
      if (modalEl && typeof bootstrap !== 'undefined') {
        this.thumbnailModal = new bootstrap.Modal(modalEl, {
          backdrop: 'static',
          keyboard: false
        });
      }

      // Initialize Bootstrap 5 modal for alias update
      const aliasModalEl = document.getElementById('alias-update-modal');
      if (aliasModalEl && typeof bootstrap !== 'undefined') {
        this.aliasModal = new bootstrap.Modal(aliasModalEl, {
          backdrop: 'static',
          keyboard: false
        });

        // Bind alias update button
        const aliasBtn = document.getElementById('btn-alias-update');
        if (aliasBtn) {
          aliasBtn.addEventListener('click', () => this.handleAliasUpdate());
        }

        // Bind alias modal close button
        const aliasCloseBtn = aliasModalEl.querySelector('.btn-close-alias-modal');
        if (aliasCloseBtn) {
          aliasCloseBtn.addEventListener('click', () => {
            if (this.aliasModal) this.aliasModal.hide();
          });
        }
      }

      // Initialize Bootstrap 5 modal for player tools
      const playerToolsModalEl = document.getElementById('player-tools-modal');
      if (playerToolsModalEl && typeof bootstrap !== 'undefined') {
        this.playerToolsModal = new bootstrap.Modal(playerToolsModalEl, {
          backdrop: 'static',
          keyboard: false
        });

        // Bind player tools buttons
        document.querySelectorAll('[data-player-tool]').forEach(btn => {
          btn.addEventListener('click', () => this.handlePlayerTools(btn));
        });

        // Bind player tools modal close button
        const playerToolsCloseBtn = playerToolsModalEl.querySelector('.btn-close-player-tools-modal');
        if (playerToolsCloseBtn) {
          playerToolsCloseBtn.addEventListener('click', () => {
            if (this.playerToolsModal) this.playerToolsModal.hide();
          });
        }
      }
    }

    /**
     * Load player statistics via AJAX
     */
    async loadPlayerStats() {
      const container = document.getElementById('player-stats-container');
      if (!container) return;

      try {
        const url = `index.php?option=com_proclaim&task=cwmadmin.getPlayerStatsXHR&${this.token}=1`;
        const result = await this.fetchJson(url);

        if (result.success && result.data) {
          container.innerHTML = result.data.html;
          this.announceToScreenReader(Joomla.Text._('JBS_ADM_STATS_LOADED') || 'Statistics loaded');
        } else {
          container.innerHTML = `<span class="text-danger">${result.message || 'Error loading stats'}</span>`;
        }
      } catch (error) {
        console.error('Error loading player stats:', error);
        container.innerHTML = '<span class="text-danger">Error loading statistics</span>';
      }
    }

    /**
     * Load popup statistics via AJAX
     */
    async loadPopupStats() {
      const container = document.getElementById('popup-stats-container');
      if (!container) return;

      try {
        const url = `index.php?option=com_proclaim&task=cwmadmin.getPopupStatsXHR&${this.token}=1`;
        const result = await this.fetchJson(url);

        if (result.success && result.data) {
          container.innerHTML = result.data.html;
        } else {
          container.innerHTML = `<span class="text-danger">${result.message || 'Error loading stats'}</span>`;
        }
      } catch (error) {
        console.error('Error loading popup stats:', error);
        container.innerHTML = '<span class="text-danger">Error loading statistics</span>';
      }
    }

    /**
     * Submit tool form with specified tooltype and task
     * @param {string} tooltype - Tool type (players, popups, mediaimages, etc.)
     * @param {string} task - Task to execute
     */
    submitToolForm(tooltype, task) {
      if (!this.form) {
        console.error('Form not found');
        return;
      }

      const tooltypeInput = this.form.querySelector('[name="tooltype"]');
      const taskInput = this.form.querySelector('[name="task"]');

      if (tooltypeInput) tooltypeInput.value = tooltype;
      if (taskInput) taskInput.value = task;

      this.form.submit();
    }

    /**
     * Setup Joomla.submitbutton for form validation and thumbnail resize
     */
    setupJoomlaSubmitbutton() {
      const self = this;

      Joomla.submitbutton = function(task) {
        // Tasks that bypass validation
        const bypassTasks = [
          'cwmadmin.cancel',
          'cwmadmin.resetHits',
          'cwmadmin.resetDownloads',
          'cwmadmin.resetPlays',
          'cwmadmin.aliasUpdate'
        ];

        if (bypassTasks.includes(task)) {
          Joomla.submitform(task, document.getElementById('item-admin'));
          return;
        }

        // Validate form
        if (!document.formvalidator.isValid(document.getElementById('item-admin'))) {
          const errorText = Joomla.Text._('JGLOBAL_VALIDATION_FORM_FAILED') || 'Form validation failed';
          Joomla.renderMessages({ error: [errorText] });
          return;
        }

        // Check for save/apply tasks that might trigger thumbnail resize
        if (task === 'cwmadmin.save' || task === 'cwmadmin.apply') {
          self.handleThumbnailResize(task);
        }
      };
    }

    /**
     * Handle alias update via AJAX with modal and auto-close
     */
    async handleAliasUpdate() {
      const modal = document.getElementById('alias-update-modal');
      const spinner = modal?.querySelector('.alias-spinner');
      const statusText = modal?.querySelector('.alias-status-text');
      const resultText = modal?.querySelector('.alias-result-text');
      const footer = modal?.querySelector('.modal-footer');

      // Reset modal state
      if (spinner) spinner.style.display = 'block';
      if (statusText) statusText.textContent = Joomla.Text._('JBS_ADM_ALIAS_UPDATING') || 'Updating aliases...';
      if (resultText) resultText.textContent = '';
      if (footer) footer.style.display = 'none';

      // Show modal
      if (this.aliasModal) {
        this.aliasModal.show();
      }

      try {
        const url = `index.php?option=com_proclaim&task=cwmadmin.aliasUpdateXHR&${this.token}=1`;
        const result = await this.fetchJson(url);

        // Hide spinner
        if (spinner) spinner.style.display = 'none';

        if (result.success) {
          // Show success state
          if (statusText) {
            statusText.innerHTML = '<i class="icon-checkmark text-success me-2"></i>' +
              (Joomla.Text._('JBS_ADM_ALIAS_COMPLETE') || 'Alias update complete!');
          }
          if (resultText) {
            const count = result.count || 0;
            if (count > 0) {
              resultText.textContent = result.message || `${count} aliases updated`;
            } else {
              resultText.textContent = Joomla.Text._('JBS_ADM_ALIAS_NONE') || 'No aliases needed to be updated.';
            }
          }

          this.announceToScreenReader(result.message || 'Alias update complete');

          // Auto-close after 3 seconds
          setTimeout(() => {
            if (this.aliasModal) this.aliasModal.hide();
          }, 3000);

        } else {
          // Show error state
          if (statusText) {
            statusText.innerHTML = '<i class="icon-warning text-danger me-2"></i>' +
              (Joomla.Text._('JBS_ADM_ERROR') || 'Error');
          }
          if (resultText) {
            resultText.textContent = result.message || 'An error occurred';
          }
          if (footer) footer.style.display = 'flex';
        }

      } catch (error) {
        console.error('Alias update error:', error);

        // Hide spinner and show error
        if (spinner) spinner.style.display = 'none';
        if (statusText) {
          statusText.innerHTML = '<i class="icon-warning text-danger me-2"></i>' +
            (Joomla.Text._('JBS_ADM_ERROR') || 'Error');
        }
        if (resultText) {
          resultText.textContent = error.message || 'An error occurred';
        }
        if (footer) footer.style.display = 'flex';
      }
    }

    /**
     * Handle player tools operations via AJAX with modal and auto-close
     * @param {HTMLElement} btn - The button that was clicked
     */
    async handlePlayerTools(btn) {
      const toolType = btn.dataset.playerTool;
      const fromFieldId = btn.dataset.fromField;
      const toFieldId = btn.dataset.toField;
      const title = btn.dataset.title || 'Player Tools';

      const fromField = document.getElementById(fromFieldId);
      const toField = document.getElementById(toFieldId);

      if (!fromField || !toField) {
        Joomla.renderMessages({ error: ['Form fields not found'] });
        return;
      }

      const fromValue = fromField.value;
      const toValue = toField.value;

      // Validate selections
      if (!fromValue || fromValue === 'x' || !toValue || toValue === 'x') {
        Joomla.renderMessages({
          error: [Joomla.Text._('JBS_ADM_SELECT_FROM_TO') || 'Please select both From and To values.']
        });
        return;
      }

      const modal = document.getElementById('player-tools-modal');
      const modalTitle = modal?.querySelector('.modal-title-text');
      const spinner = modal?.querySelector('.player-tools-spinner');
      const statusText = modal?.querySelector('.player-tools-status-text');
      const resultText = modal?.querySelector('.player-tools-result-text');
      const footer = modal?.querySelector('.modal-footer');

      // Reset modal state
      if (modalTitle) modalTitle.textContent = title;
      if (spinner) spinner.style.display = 'block';
      if (statusText) statusText.textContent = Joomla.Text._('JBS_ADM_PLAYER_TOOLS_PROCESSING') || 'Processing changes...';
      if (resultText) resultText.textContent = '';
      if (footer) footer.style.display = 'none';

      // Show modal
      if (this.playerToolsModal) {
        this.playerToolsModal.show();
      }

      try {
        // Build URL based on tool type
        let url;
        switch (toolType) {
          case 'players':
            url = `index.php?option=com_proclaim&task=cwmadmin.changePlayersXHR&${this.token}=1&from=${encodeURIComponent(fromValue)}&to=${encodeURIComponent(toValue)}`;
            break;
          case 'popups':
            url = `index.php?option=com_proclaim&task=cwmadmin.changePopupXHR&${this.token}=1&from=${encodeURIComponent(fromValue)}&to=${encodeURIComponent(toValue)}`;
            break;
          case 'playerbymediatype':
            url = `index.php?option=com_proclaim&task=cwmadmin.changePlayerByMediaTypeXHR&${this.token}=1&mediatype=${encodeURIComponent(fromValue)}&player=${encodeURIComponent(toValue)}`;
            break;
          default:
            throw new Error('Unknown tool type: ' + toolType);
        }

        const result = await this.fetchJson(url);

        // Hide spinner
        if (spinner) spinner.style.display = 'none';

        if (result.success) {
          // Show success state
          if (statusText) {
            statusText.innerHTML = '<i class="icon-checkmark text-success me-2"></i>' +
              (Joomla.Text._('JBS_ADM_PLAYER_TOOLS_COMPLETE') || 'Operation complete!');
          }
          if (resultText) {
            resultText.textContent = result.message || `${result.count} records updated`;
          }

          this.announceToScreenReader(result.message || 'Operation complete');

          // Reload stats to reflect changes
          this.loadPlayerStats();
          this.loadPopupStats();

          // Auto-close after 3 seconds
          setTimeout(() => {
            if (this.playerToolsModal) this.playerToolsModal.hide();
          }, 3000);

        } else {
          // Show error state
          if (statusText) {
            statusText.innerHTML = '<i class="icon-warning text-danger me-2"></i>' +
              (Joomla.Text._('JBS_ADM_ERROR') || 'Error');
          }
          if (resultText) {
            resultText.textContent = result.message || 'An error occurred';
          }
          if (footer) footer.style.display = 'flex';
        }

      } catch (error) {
        console.error('Player tools error:', error);

        // Hide spinner and show error
        if (spinner) spinner.style.display = 'none';
        if (statusText) {
          statusText.innerHTML = '<i class="icon-warning text-danger me-2"></i>' +
            (Joomla.Text._('JBS_ADM_ERROR') || 'Error');
        }
        if (resultText) {
          resultText.textContent = error.message || 'An error occurred';
        }
        if (footer) footer.style.display = 'flex';
      }
    }

    /**
     * Handle thumbnail resize check and execution
     * @param {string} task - Save task to execute after resize
     */
    async handleThumbnailResize(task) {
      const thumbnailChanges = [];

      // Check for thumbnail size changes
      const teacherOld = document.getElementById('thumbnail_teacher_size_old');
      const seriesOld = document.getElementById('thumbnail_series_size_old');
      const studyOld = document.getElementById('thumbnail_study_size_old');

      const teacherNew = document.getElementById('jform_params_thumbnail_teacher_size');
      const seriesNew = document.getElementById('jform_params_thumbnail_series_size');
      const studyNew = document.getElementById('jform_params_thumbnail_study_size');

      if (teacherOld && teacherNew && teacherOld.value !== teacherNew.value && teacherOld.value !== '') {
        thumbnailChanges.push('teachers');
      }
      if (seriesOld && seriesNew && seriesOld.value !== seriesNew.value && seriesOld.value !== '') {
        thumbnailChanges.push('series');
      }
      if (studyOld && studyNew && studyOld.value !== studyNew.value && studyOld.value !== '') {
        thumbnailChanges.push('studies');
      }

      // If no thumbnail changes, just submit the form
      if (thumbnailChanges.length === 0) {
        Joomla.submitform(task, document.getElementById('item-admin'));
        return;
      }

      // Confirm thumbnail resize
      const confirmMsg = Joomla.Text._('JBS_ADM_THUMBNAIL_RESIZE_CONFIRM') ||
        `You modified the default thumbnail size(s). Thumbnails will be recreated for: ${thumbnailChanges.join(', ')}. Click OK to continue.`;

      if (!confirm(confirmMsg)) {
        return;
      }

      // Show modal and start resize process
      await this.performThumbnailResize(thumbnailChanges, task);
    }

    /**
     * Perform thumbnail resize operation
     * @param {string[]} imageTypes - Types to resize (teachers, series, studies)
     * @param {string} task - Task to execute after completion
     */
    async performThumbnailResize(imageTypes, task) {
      const modal = document.getElementById('dialog_thumbnail_resize');
      const progressBar = modal?.querySelector('.progress-bar');
      const statusText = modal?.querySelector('.status-text');
      const progressContainer = progressBar?.parentElement;

      // Show modal
      if (this.thumbnailModal) {
        this.thumbnailModal.show();
      }

      try {
        // Get list of images to resize
        const listUrl = `index.php?option=com_proclaim&task=cwmadmin.getThumbnailListXHR&${this.token}=1&images=${encodeURIComponent(JSON.stringify(imageTypes))}`;
        const listResponse = await this.fetchJson(listUrl);

        const totalPaths = listResponse.total || 0;

        if (totalPaths === 0) {
          // No images to resize, submit form
          if (this.thumbnailModal) this.thumbnailModal.hide();
          Joomla.submitform(task, document.getElementById('item-admin'));
          return;
        }

        let counter = 0;
        const paths = listResponse.paths || [];

        // Process each image type
        for (const pathGroup of paths) {
          const type = pathGroup[0]?.type;
          const images = pathGroup[0]?.images || [];

          if (!images.length) continue;

          // Get new size for this type
          let newSize = 100;
          switch (type) {
            case 'teachers':
              newSize = document.getElementById('jform_params_thumbnail_teacher_size')?.value || 100;
              break;
            case 'studies':
              newSize = document.getElementById('jform_params_thumbnail_study_size')?.value || 100;
              break;
            case 'series':
              newSize = document.getElementById('jform_params_thumbnail_series_size')?.value || 100;
              break;
          }

          // Resize each image
          for (const imagePath of images) {
            const resizeUrl = `index.php?option=com_proclaim&task=cwmadmin.createThumbnailXHR&${this.token}=1&image_path=${encodeURIComponent(imagePath)}&new_size=${newSize}`;
            await this.fetchJson(resizeUrl);

            counter++;
            const progress = (counter / totalPaths) * 100;

            // Update progress bar
            if (progressBar) {
              progressBar.style.width = `${progress}%`;
              progressBar.textContent = `${Math.round(progress)}%`;
            }
            if (progressContainer) {
              progressContainer.setAttribute('aria-valuenow', Math.round(progress));
            }
            if (statusText) {
              statusText.textContent = `${counter} / ${totalPaths}`;
            }
          }
        }

        // Complete - hide modal and submit form
        if (this.thumbnailModal) this.thumbnailModal.hide();
        Joomla.submitform(task, document.getElementById('item-admin'));

      } catch (error) {
        console.error('Thumbnail resize error:', error);
        if (this.thumbnailModal) this.thumbnailModal.hide();
        Joomla.renderMessages({ error: ['Error resizing thumbnails: ' + error.message] });
      }
    }
  }

  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    const cwmadmin = new ProclaimCwmadmin();
    cwmadmin.init();

    // Expose for external use if needed
    window.ProclaimCwmadmin = cwmadmin;
  });

})(Joomla);
