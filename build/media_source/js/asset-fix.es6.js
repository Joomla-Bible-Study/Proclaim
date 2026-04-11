/**
 * @package     Proclaim
 * @subpackage  JavaScript
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

'use strict';

/**
 * Proclaim Asset Fix Manager
 * Handles AJAX-based asset checking and fixing
 */
class ProclaimAssetFix {
    constructor() {
        this.modal = null;
        this.progressBar = null;
        this.progressText = null;
        this.statusText = null;
        this.detailsEl = null;
        this.footerEl = null;
        this.tables = [];
        this.totalRecords = 0;
        this.processedRecords = 0;
        this.batchSize = 100;

        this.init();
    }

    /**
     * Initialize event handlers
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.bindEvents());
        } else {
            this.bindEvents();
        }
    }

    /**
     * Bind event handlers
     */
    bindEvents() {
        // Cache DOM elements
        this.progressBar = document.getElementById('fix-progress-bar');
        this.progressText = document.getElementById('fix-progress-text');
        this.statusText = document.getElementById('fix-status-text');
        this.detailsEl = document.getElementById('fix-details');
        this.footerEl = document.getElementById('fix-modal-footer');

        // Initialize Bootstrap modal (wrapped in try-catch to ensure buttons still work)
        try {
            const modalEl = document.getElementById('fixAssetsModal');
            if (modalEl) {
                // Try to get Bootstrap Modal - it may be loaded as a module in Joomla 5+
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    this.modal = new bootstrap.Modal(modalEl);
                } else if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
                    this.modal = new window.bootstrap.Modal(modalEl);
                } else {
                    console.warn('Bootstrap Modal not found, will use fallback');
                }
            }
        } catch (err) {
            console.warn('Failed to initialize Bootstrap Modal:', err.message);
        }

        // Bind button clicks (always runs even if modal init fails)
        document.querySelectorAll('[data-proclaim-action]').forEach((btn) => {
            btn.addEventListener('click', (e) => this.handleAction(e));
        });
    }

    /**
     * Handle button actions
     * @param {Event} e Click event
     */
    handleAction(e) {
        e.preventDefault();
        e.stopPropagation();

        const action = e.currentTarget.dataset.proclaimAction;

        switch (action) {
            case 'refresh':
                this.refreshAssetStatus();
                break;
            case 'cleanup':
                this.startCleanup();
                break;
            case 'fix':
                // Legacy alias — forward to the new cleanup flow.
                this.startCleanup();
                break;
            default:
                break;
        }
    }

    /**
     * Refresh asset status table via AJAX
     */
    async refreshAssetStatus() {
        const tbody = document.getElementById('asset-status-body');
        if (!tbody) return;

        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">${Joomla.Text._('JBS_ADM_CHECKING_ASSETS')}</span>
                </td>
            </tr>
        `;

        try {
            const response = await this.fetchJson('checkAssetsXHR');

            if (response.success && response.data.assets) {
                this.renderAssetTable(response.data.assets);
            } else {
                this.showTableError(response.message || 'Failed to check assets');
            }
        } catch (error) {
            this.showTableError(error.message);
        }
    }

    /**
     * Render asset status table (new model).
     *
     * Columns:
     *  - inherited:      records with asset_id = 0, inheriting from the
     *                    com_proclaim parent. This is the desired state,
     *                    so the count is rendered as plain muted text.
     *  - custom_rules:   records with genuine per-record ACL configured.
     *                    Green when > 0.
     *  - needs_cleanup:  legacy empty-rules rows waiting to be pruned.
     *  - drifted:        asset row whose parent_id is not com_proclaim.
     *  - orphans:        asset row whose source record is gone.
     * @param {Array} assets Asset status data
     */
    renderAssetTable(assets) {
        const tbody = document.getElementById('asset-status-body');
        if (!tbody) return;

        let html = '';

        assets.forEach((asset) => {
            const custom = asset.custom_rules || 0;
            const needs = asset.needs_cleanup || 0;
            const drifted = asset.drifted || 0;
            const orphans = asset.orphans || 0;

            html += `
                <tr>
                    <td>${this.escapeHtml(asset.realname)}</td>
                    <td class="text-center">${asset.numrows}</td>
                    <td class="text-center text-muted">${asset.inherited || 0}</td>
                    <td class="text-center">
                        <span class="${custom > 0 ? 'text-success fw-bold' : 'text-muted'}">${custom}</span>
                    </td>
                    <td class="text-center">
                        <span class="${needs > 0 ? 'text-warning fw-bold' : 'text-muted'}">${needs}</span>
                    </td>
                    <td class="text-center">
                        <span class="${drifted > 0 ? 'text-warning fw-bold' : 'text-muted'}">${drifted}</span>
                    </td>
                    <td class="text-center">
                        <span class="${orphans > 0 ? 'text-warning fw-bold' : 'text-muted'}">${orphans}</span>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Show error in table
     * @param {string} message Error message
     */
    showTableError(message) {
        const tbody = document.getElementById('asset-status-body');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    ${this.escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    /**
     * Run the one-shot asset cleanup: delegates to
     * `cwmassets.cleanupAssetsXHR` which calls `Cwmassets::fixAllAssets()`
     * server-side. Fast because the new-model cleanup is a handful of
     * bulk SQL statements, not a per-record loop.
     */
    async startCleanup() {
        // Show modal.
        const modalEl = document.getElementById('fixAssetsModal');
        if (this.modal) {
            this.modal.show();
        } else if (modalEl) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                this.modal = new bootstrap.Modal(modalEl);
                this.modal.show();
            } else {
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        }

        this.updateProgress(10, Joomla.Text._('JBS_ADM_ASSET_CLEANING'));
        this.footerEl.style.display = 'none';

        try {
            const response = await this.fetchJson('cleanupAssetsXHR');

            if (!response.success) {
                throw new Error(response.message || 'Cleanup failed');
            }

            this.updateProgress(100, Joomla.Text._('JBS_ADM_ASSET_CLEANUP_COMPLETE'));
            this.progressBar.classList.remove('progress-bar-animated');
            this.progressBar.classList.add('bg-success');
            this.footerEl.style.display = 'flex';

            this.refreshAssetStatus();

            setTimeout(() => this.closeModal(), 2000);
        } catch (error) {
            this.showError(error.message);
        }
    }

    /**
     * Update progress display
     * @param {number} percent Progress percentage
     * @param {string} message Status message
     */
    updateProgress(percent, message) {
        if (this.progressBar) {
            this.progressBar.style.width = `${percent}%`;
            this.progressBar.setAttribute('aria-valuenow', percent);
        }
        if (this.progressText) {
            this.progressText.textContent = `${percent}%`;
        }
        if (this.statusText && message) {
            this.statusText.textContent = message;
        }
    }

    /**
     * Show error in modal
     * @param {string} message Error message
     */
    showError(message) {
        const statusMsg = document.getElementById('fix-status-message');
        if (statusMsg) {
            statusMsg.innerHTML = `
                <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>
                <span class="text-danger">${Joomla.Text._('JBS_CMN_ERROR')}: ${this.escapeHtml(message)}</span>
            `;
        }
        if (this.progressBar) {
            this.progressBar.classList.remove('progress-bar-animated');
            this.progressBar.classList.add('bg-danger');
        }
        this.footerEl.style.display = 'flex';
    }

    /**
     * Close the modal
     */
    closeModal() {
        const modalEl = document.getElementById('fixAssetsModal');
        if (this.modal) {
            this.modal.hide();
        } else if (modalEl) {
            // Fallback for CSS-only modal
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
            // Remove backdrop if present
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }

    /**
     * Fetch JSON from server
     * @param {string} task Controller task
     * @param {Object} params Additional parameters
     * @returns {Promise<Object>} Response data
     */
    async fetchJson(task, params = {}) {
        const url = new URL('index.php', window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/'));
        url.searchParams.set('option', 'com_proclaim');
        url.searchParams.set('task', `cwmassets.${task}`);
        url.searchParams.set('format', 'json');

        // Add CSRF token
        const tokenInput = document.querySelector('input[name^="csrf.token"]')
                          || document.querySelector('input[name][value="1"]');
        if (tokenInput) {
            url.searchParams.set(tokenInput.name, '1');
        }

        // Add additional params
        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });

        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            if (response.status === 403 || response.status === 401) {
                Joomla.renderMessages({
                    error: [Joomla.Text._('JLIB_ENVIRONMENT_SESSION_EXPIRED')
                        || 'Your session has expired. Please log in again.'],
                });
                setTimeout(() => { window.location.reload(); }, 3000);
                throw new Error('Session expired');
            }
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const text = await response.text();

        try {
            return JSON.parse(text);
        } catch {
            console.error('Invalid JSON response:', text);
            throw new Error('Invalid response from server');
        }
    }

    /**
     * Escape HTML entities
     * @param {string} str String to escape
     * @returns {string} Escaped string
     */
    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.proclaimAssetFix = new ProclaimAssetFix();
});
