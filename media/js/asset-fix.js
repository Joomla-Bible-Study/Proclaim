(function () {
    'use strict';

    /**
     * @package     Proclaim
     * @subpackage  JavaScript
     * @copyright   (C) 2026 CWM Team All rights reserved
     * @license     GNU General Public License version 2 or later; see LICENSE.txt
     */


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
            document.querySelectorAll('[data-proclaim-action]').forEach(btn => {
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
                case 'fix':
                    this.startFix();
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
         * Render asset status table
         * @param {Array} assets Asset status data
         */
        renderAssetTable(assets) {
            const tbody = document.getElementById('asset-status-body');
            if (!tbody) return;

            let html = '';

            assets.forEach(asset => {
                html += `
                <tr>
                    <td>${this.escapeHtml(asset.realname)}</td>
                    <td class="text-center">${asset.numrows}</td>
                    <td class="text-center">
                        <span class="${asset.nullrows > 0 ? 'text-danger fw-bold' : ''}">${asset.nullrows}</span>
                    </td>
                    <td class="text-center">
                        <span class="${asset.matchrows > 0 ? 'text-success' : ''}">${asset.matchrows}</span>
                    </td>
                    <td class="text-center">
                        <span class="${asset.arulesrows > 0 ? 'text-danger fw-bold' : ''}">${asset.arulesrows}</span>
                    </td>
                    <td class="text-center">
                        <span class="${asset.nomatchrows > 0 ? 'text-danger fw-bold' : ''}">${asset.nomatchrows}</span>
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
                <td colspan="6" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${this.escapeHtml(message)}
                </td>
            </tr>
        `;
        }

        /**
         * Start the asset fix process
         */
        async startFix() {
            // Reset state
            this.tables = [];
            this.totalRecords = 0;
            this.processedRecords = 0;

            // Show modal
            const modalEl = document.getElementById('fixAssetsModal');
            if (this.modal) {
                this.modal.show();
            } else if (modalEl) {
                // Fallback: try to create modal instance now
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    this.modal = new bootstrap.Modal(modalEl);
                    this.modal.show();
                } else {
                    // Last resort: just show the element with CSS
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            }

            this.updateProgress(0, Joomla.Text._('JBS_ADM_CHECKING_ASSETS'));
            this.footerEl.style.display = 'none';

            try {
                // Step 1: Get list of tables
                const tablesResponse = await this.fetchJson('getAssetTablesXHR');

                if (!tablesResponse.success) {
                    throw new Error(tablesResponse.message || 'Failed to get asset tables');
                }

                this.tables = tablesResponse.data.tables;
                this.totalRecords = tablesResponse.data.totalRecords;

                // Step 2: Fix each table
                await this.fixAllTables();

                // Step 3: Rebuild asset tree
                this.updateProgress(95, Joomla.Text._('JBS_ADM_REBUILDING_TREE'));
                await this.fetchJson('rebuildAssetTreeXHR');

                // Complete
                this.updateProgress(100, Joomla.Text._('JBS_ADM_FIX_COMPLETE'));
                this.progressBar.classList.remove('progress-bar-animated');
                this.progressBar.classList.add('bg-success');
                this.footerEl.style.display = 'flex';

                // Refresh the status table
                this.refreshAssetStatus();

                // Auto-close modal after 3 seconds on success
                setTimeout(() => {
                    this.closeModal();
                }, 3000);

            } catch (error) {
                this.showError(error.message);
            }
        }

        /**
         * Fix all tables sequentially
         */
        async fixAllTables() {
            for (const table of this.tables) {
                if (table.count === 0) continue;

                let offset = 0;

                while (offset < table.count) {
                    const statusMsg = `${Joomla.Text._('JBS_ADM_FIXING_ASSETS')}: ${Joomla.Text._(table.realname) || table.realname}`;
                    this.updateProgress(this.calculateProgress(), statusMsg);
                    this.detailsEl.textContent = `${offset} / ${table.count}`;

                    const response = await this.fetchJson('fixAssetBatchXHR', {
                        table: table.name,
                        assetname: table.assetname,
                        offset: offset,
                        batchSize: this.batchSize
                    });

                    if (!response.success) {
                        throw new Error(response.message || `Failed to fix ${table.name}`);
                    }

                    this.processedRecords += response.data.processed;
                    offset += this.batchSize;

                    this.updateProgress(this.calculateProgress(), statusMsg);
                }
            }
        }

        /**
         * Calculate current progress percentage
         * @returns {number} Progress percentage (0-90, leaving room for tree rebuild)
         */
        calculateProgress() {
            if (this.totalRecords === 0) return 0;
            return Math.min(Math.round((this.processedRecords / this.totalRecords) * 90), 90);
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
                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
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
            const tokenInput = document.querySelector('input[name^="csrf.token"]') ||
                              document.querySelector('input[name][value="1"]');
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
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch (e) {
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

})();
