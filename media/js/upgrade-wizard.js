(function () {
  'use strict';

  /**
   * Proclaim 9.x → 10.x Upgrade Wizard
   *
   * Sequential AJAX-driven upgrade wizard for in-place migration.
   * Follows the CSV Import pattern: fetch() with JSON, progress bar,
   * error aggregation, navigation guard.
   *
   * @package  Proclaim.Admin
   * @since    10.1.0
   */
  ((document) => {

    /**
     * Resolve a Joomla language string with key-comparison fallback.
     * Joomla.Text._() returns the raw key when unregistered (truthy),
     * so we compare the result against the key to detect misses.
     *
     * @param {string} key      Language key
     * @param {string} fallback Fallback text
     * @returns {string}
     */
    const str = (key, fallback) => {
      if (typeof Joomla !== 'undefined' && Joomla.Text && Joomla.Text._) {
        const val = Joomla.Text._(key);
        if (val && val !== key) {
          return val;
        }
      }
      return fallback || key;
    };

    /**
     * Safely remove all child nodes from an element.
     *
     * @param {HTMLElement} element
     */
    const clearChildren = (element) => {
      while (element.firstChild) {
        element.removeChild(element.firstChild);
      }
    };

    /**
     * Build the base AJAX URL with token.
     *
     * @param {string} task  Controller task name
     * @returns {string}
     */
    const buildUrl = (task) => {
      const config = document.getElementById('upgrade-config');
      const token = config ? config.dataset.token : '';
      return `index.php?option=com_proclaim&task=cwmadmin.${task}&${token}=1`;
    };

    /**
     * Make an AJAX request to a controller endpoint.
     *
     * @param {string} task  Controller task name
     * @returns {Promise<Object>}
     */
    const ajaxCall = async (task) => {
      const response = await fetch(buildUrl(task), {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return response.json();
    };

    // Step definitions: order matters
    const STEPS = [
      { id: 'backup',  task: 'upgradeBackupXHR',  critical: true },
      { id: 'params',  task: 'upgradeParamsXHR',   critical: false },
      { id: 'schema',  task: 'upgradeSchemaXHR',   critical: true },
      { id: 'data',    task: 'upgradeDataXHR',     critical: true },
      { id: 'assets',  task: 'upgradeAssetsXHR',   critical: true },
      { id: 'verify',  task: 'upgradeVerifyXHR',   critical: true },
    ];

    let cancelled = false;
    let running = false;

    /**
     * Set badge state for a step indicator.
     *
     * @param {string} stepId  Step identifier
     * @param {string} state   One of: pending, active, done, error
     */
    const setBadge = (stepId, state) => {
      const badge = document.querySelector(`[data-step-badge="${stepId}"]`);
      if (!badge) return;

      badge.className = 'badge';
      switch (state) {
        case 'active':
          badge.classList.add('bg-primary');
          badge.textContent = str('JBS_UPG_RUNNING', 'Running...');
          break;
        case 'done':
          badge.classList.add('bg-success');
          badge.textContent = str('JBS_UPG_DONE', 'Done');
          break;
        case 'error':
          badge.classList.add('bg-danger');
          badge.textContent = str('JBS_UPG_ERROR', 'Error');
          break;
        default:
          badge.classList.add('bg-secondary');
          badge.textContent = str('JBS_UPG_PENDING', 'Pending');
      }
    };

    /**
     * Update the progress bar.
     *
     * @param {number} percent  0-100
     */
    const setProgress = (percent) => {
      const bar = document.querySelector('#upgrade-progress .progress-bar');
      if (!bar) return;

      const p = Math.min(100, Math.max(0, Math.round(percent)));
      bar.style.width = `${p}%`;
      bar.textContent = `${p}%`;
      bar.closest('.progress').setAttribute('aria-valuenow', p);
    };

    /**
     * Set the status text.
     *
     * @param {string} text
     */
    const setStatus = (text) => {
      const statusEl = document.getElementById('upgrade-status-text');
      if (statusEl) statusEl.textContent = text;
    };

    /**
     * Create a safe element with text content and optional class.
     *
     * @param {string} tag   HTML tag
     * @param {string} text  Text content
     * @param {string} cls   CSS classes
     * @returns {HTMLElement}
     */
    const el = (tag, text, cls) => {
      const node = document.createElement(tag);
      if (text) node.textContent = text;
      if (cls) node.className = cls;
      return node;
    };

    /**
     * Run the detection step when the upgrade tab is shown.
     */
    const runDetection = async () => {
      const statusEl = document.getElementById('upgrade-detection-status');
      if (!statusEl) return;

      try {
        const result = await ajaxCall('detectUpgradeXHR');

        if (!result.detected) {
          statusEl.textContent = str('JBS_UPG_NOT_DETECTED', 'No 9.x schema detected.');
          return;
        }

        // Show detected version
        clearChildren(statusEl);
        const versionAlert = el('div', '', 'alert alert-info');
        const icon = el('i', '', 'icon-info-circle me-2');
        icon.setAttribute('aria-hidden', 'true');
        versionAlert.appendChild(icon);

        const versionText = document.createTextNode(
          str('JBS_UPG_DETECTED', 'Proclaim 9.x schema detected') + ': v' + (result.version || '?')
        );
        versionAlert.appendChild(versionText);
        statusEl.appendChild(versionAlert);

        // Check minimum version
        if (result.version && !result.meets_minimum) {
          const warningEl = document.getElementById('upgrade-version-warning');
          if (warningEl) warningEl.style.display = '';
          return;
        }

        // Populate record counts table
        if (result.record_counts && Object.keys(result.record_counts).length > 0) {
          const countsEl = document.getElementById('upgrade-record-counts');
          const tbody = document.querySelector('#upgrade-counts-table tbody');

          if (tbody && countsEl) {
            clearChildren(tbody);
            let totalRecords = 0;

            Object.entries(result.record_counts).forEach(([table, count]) => {
              const row = document.createElement('tr');
              const tdName = el('td', table);
              const tdCount = el('td', String(count), 'text-end');
              row.appendChild(tdName);
              row.appendChild(tdCount);
              tbody.appendChild(row);
              totalRecords += count;
            });

            // Total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'fw-bold';
            const tdTotal = el('td', str('JBS_UPG_TOTAL', 'Total'));
            const tdTotalCount = el('td', String(totalRecords), 'text-end');
            totalRow.appendChild(tdTotal);
            totalRow.appendChild(tdTotalCount);
            tbody.appendChild(totalRow);

            countsEl.style.display = '';
          }
        }

        // Show the wizard panel
        const wizardPanel = document.getElementById('upgrade-wizard-panel');
        if (wizardPanel) wizardPanel.style.display = '';

      } catch (error) {
        clearChildren(statusEl);
        const errAlert = el('div', str('JBS_UPG_DETECT_ERROR', 'Detection failed') + ': ' + error.message, 'alert alert-danger');
        statusEl.appendChild(errAlert);
      }
    };

    /**
     * Run the full upgrade wizard sequentially.
     */
    const runUpgrade = async () => {
      if (running) return;
      running = true;
      cancelled = false;

      const startBtn = document.getElementById('btn-start-upgrade');
      const cancelBtn = document.getElementById('btn-cancel-upgrade');

      if (startBtn) startBtn.style.display = 'none';
      if (cancelBtn) cancelBtn.style.display = '';

      // Navigation guard
      const beforeUnloadHandler = (e) => {
        e.preventDefault();
        e.returnValue = '';
      };
      window.addEventListener('beforeunload', beforeUnloadHandler);

      const results = [];
      let hasError = false;

      for (let i = 0; i < STEPS.length; i++) {
        if (cancelled) {
          setStatus(str('JBS_UPG_CANCELLED', 'Upgrade cancelled.'));
          break;
        }

        const step = STEPS[i];
        const percent = Math.round((i / STEPS.length) * 100);

        setBadge(step.id, 'active');
        setProgress(percent);
        setStatus(str('JBS_UPG_RUNNING_STEP', 'Running') + ': ' + str('JBS_UPG_STEP_' + step.id.toUpperCase(), step.id));

        try {
          const result = await ajaxCall(step.task);
          results.push({ step: step.id, success: true, data: result });
          setBadge(step.id, 'done');
        } catch (error) {
          results.push({ step: step.id, success: false, error: error.message });
          setBadge(step.id, 'error');

          if (step.critical) {
            hasError = true;
            setStatus(str('JBS_UPG_STEP_FAILED', 'Step failed') + ': ' + step.id + ' - ' + error.message);
            break;
          }
        }
      }

      // Completion
      if (!hasError && !cancelled) {
        setProgress(100);
        setStatus(str('JBS_UPG_COMPLETE', 'Upgrade completed successfully!'));
      }

      // Remove navigation guard
      window.removeEventListener('beforeunload', beforeUnloadHandler);

      // Show report
      showReport(results, hasError, cancelled);

      // Reset buttons
      if (cancelBtn) cancelBtn.style.display = 'none';
      if (startBtn && (hasError || cancelled)) {
        startBtn.style.display = '';
        startBtn.textContent = str('JBS_UPG_RETRY', 'Retry Upgrade');
      }

      running = false;
    };

    /**
     * Render the upgrade report using safe DOM methods.
     *
     * @param {Array}   results   Per-step results
     * @param {boolean} hasError  Whether a critical error stopped the wizard
     * @param {boolean} wasCancelled  Whether the user cancelled
     */
    const showReport = (results, hasError, wasCancelled) => {
      const panel = document.getElementById('upgrade-report-panel');
      const content = document.getElementById('upgrade-report-content');
      if (!panel || !content) return;

      clearChildren(content);

      // Summary alert
      let alertClass, summaryText;
      if (wasCancelled) {
        alertClass = 'alert-warning';
        summaryText = str('JBS_UPG_REPORT_CANCELLED', 'The upgrade was cancelled. You can safely retry.');
      } else if (hasError) {
        alertClass = 'alert-danger';
        summaryText = str('JBS_UPG_REPORT_ERROR', 'The upgrade encountered an error. Check the details below and retry.');
      } else {
        alertClass = 'alert-success';
        summaryText = str('JBS_UPG_REPORT_SUCCESS', 'The upgrade completed successfully! Reload the page to continue.');
      }

      const summary = el('div', summaryText, 'alert ' + alertClass);
      content.appendChild(summary);

      // Step details table
      const table = document.createElement('table');
      table.className = 'table table-sm table-striped';

      const thead = document.createElement('thead');
      const headRow = document.createElement('tr');
      headRow.appendChild(el('th', str('JBS_UPG_STEP', 'Step')));
      headRow.appendChild(el('th', str('JBS_UPG_RESULT', 'Result')));
      headRow.appendChild(el('th', str('JBS_UPG_DETAILS', 'Details')));
      thead.appendChild(headRow);
      table.appendChild(thead);

      const tbody = document.createElement('tbody');
      results.forEach((r) => {
        const row = document.createElement('tr');

        const tdStep = el('td', r.step);
        const tdResult = el('td', '');
        const badge = el('span', r.success ? str('JBS_UPG_SUCCESS', 'Success') : str('JBS_UPG_FAILED', 'Failed'),
          'badge ' + (r.success ? 'bg-success' : 'bg-danger'));
        tdResult.appendChild(badge);

        const tdDetail = el('td', '');
        if (r.error) {
          tdDetail.textContent = r.error;
        } else if (r.data) {
          // Summarize useful data
          if (r.data.filename) {
            tdDetail.textContent = 'Backup: ' + r.data.filename;
          } else if (r.data.converted !== undefined) {
            tdDetail.textContent = r.data.converted + ' converted';
          } else if (r.data.steps) {
            const failed = r.data.steps.filter((s) => !s.success);
            tdDetail.textContent = r.data.steps.length + ' sub-steps' +
              (failed.length > 0 ? ' (' + failed.length + ' warnings)' : '');
          } else if (r.data.checks) {
            const ok = r.data.checks.filter((c) => c.status === 'ok').length;
            tdDetail.textContent = ok + '/' + r.data.checks.length + ' checks passed';
          } else if (r.data.message) {
            tdDetail.textContent = r.data.message;
          }
        }

        row.appendChild(tdStep);
        row.appendChild(tdResult);
        row.appendChild(tdDetail);
        tbody.appendChild(row);
      });
      table.appendChild(tbody);
      content.appendChild(table);

      // Reload button on success
      if (!hasError && !wasCancelled) {
        const reloadBtn = el('button', str('JBS_UPG_RELOAD', 'Reload Page'), 'btn btn-success mt-3');
        reloadBtn.addEventListener('click', () => window.location.reload());
        content.appendChild(reloadBtn);
      }

      panel.style.display = '';
    };

    /**
     * Initialize the upgrade wizard.
     */
    const init = () => {
      // Only initialize if the upgrade wizard container exists
      if (!document.getElementById('upgrade-wizard')) return;

      // Start button
      const startBtn = document.getElementById('btn-start-upgrade');
      if (startBtn) {
        startBtn.addEventListener('click', (e) => {
          e.preventDefault();
          runUpgrade();
        });
      }

      // Cancel button
      const cancelBtn = document.getElementById('btn-cancel-upgrade');
      if (cancelBtn) {
        cancelBtn.addEventListener('click', (e) => {
          e.preventDefault();
          cancelled = true;
          setStatus(str('JBS_UPG_CANCELLING', 'Cancelling after current step...'));
        });
      }

      // Listen for tab activation to trigger detection
      const tabLink = document.querySelector('button[data-bs-target="#upgrade"]');
      if (tabLink) {
        tabLink.addEventListener('shown.bs.tab', () => {
          runDetection();
        });
      }

      // If the tab is already active (e.g., direct link), run detection immediately
      const upgradeTab = document.getElementById('upgrade');
      if (upgradeTab && upgradeTab.classList.contains('show')) {
        runDetection();
      }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }
  })(document);

})();
