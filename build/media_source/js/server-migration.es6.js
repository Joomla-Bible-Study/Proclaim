/**
 * Legacy Server Migration Tool for Proclaim Admin Center
 *
 * 4-phase workflow: Scan → Configure → Migrate → Cleanup
 * Migrates media files from Legacy servers to core server types.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */

(() => {
    'use strict';

    const BATCH_SIZE = 25;

    /** @type {object|null} Scan results from the server */
    let scanData = null;

    /** @type {object} Existing core servers by type */
    let existingServers = {};

    /** @type {object} Type labels */
    let typeLabels = {};

    /** @type {boolean} Whether migration is in progress */
    let migrating = false;

    /**
     * Badge color per detected type.
     */
    const TYPE_BADGES = {
        youtube: 'danger',
        vimeo: 'info',
        wistia: 'primary',
        resi: 'info',
        soundcloud: 'warning',
        dailymotion: 'info',
        rumble: 'success',
        facebook: 'primary',
        embed: 'secondary',
        article: 'dark',
        virtuemart: 'primary',
        docman: 'info',
        local: 'dark',
        unknown: 'light',
    };

    /**
     * Get the CSRF token from the config element.
     *
     * @returns {string}
     */
    function getToken() {
        const el = document.getElementById('smg-config');
        return el ? el.dataset.token : '';
    }

    /**
     * Build the base URL for AJAX calls.
     *
     * @param {string} task  The controller task name
     * @returns {string}
     */
    function ajaxUrl(task) {
        return `index.php?option=com_proclaim&task=cwmadmin.${task}&${getToken()}=1`;
    }

    /**
     * POST JSON to a controller endpoint.
     *
     * @param {string} task  The controller task name
     * @param {object} body  JSON body
     * @returns {Promise<object>}
     */
    async function postJson(task, body) {
        const response = await fetch(ajaxUrl(task), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return response.json();
    }

    /**
     * GET JSON from a controller endpoint.
     *
     * @param {string} task  The controller task name
     * @returns {Promise<object>}
     */
    async function getJson(task) {
        const response = await fetch(ajaxUrl(task));
        return response.json();
    }

    // =========================================================================
    // Phase 1: Scan
    // =========================================================================

    /**
     * Run the scan and display results.
     */
    async function scanServers() {
        const spinner = document.getElementById('smg-scan-spinner');
        const btn = document.getElementById('btn-smg-scan');

        spinner.style.display = '';
        btn.disabled = true;

        try {
            const result = await getJson('serverMigrationScanXHR');

            if (!result.success) {
                Joomla.renderMessages({ error: [result.message || 'Scan failed'] });
                return;
            }

            scanData = result.servers;
            existingServers = result.existing || {};
            typeLabels = result.labels || {};

            renderScanResults();
        } catch (err) {
            Joomla.renderMessages({ error: [err.message] });
        } finally {
            spinner.style.display = 'none';
            btn.disabled = false;
        }
    }

    /**
     * Render scan results table and build config form.
     */
    function renderScanResults() {
        const resultsPanel = document.getElementById('smg-results-panel');
        const configPanel = document.getElementById('smg-config-panel');
        const totalBadge = document.getElementById('smg-total-count');
        const noLegacy = document.getElementById('smg-no-legacy');
        const tableContainer = document.getElementById('smg-results-table');

        resultsPanel.style.display = '';

        if (!scanData || scanData.length === 0) {
            noLegacy.style.display = '';
            tableContainer.innerHTML = '';
            configPanel.style.display = 'none';
            return;
        }

        noLegacy.style.display = 'none';

        const totalFiles = scanData.reduce((sum, s) => sum + s.total, 0);
        totalBadge.textContent = Joomla.Text._('JBS_SMG_TOTAL_FILES').replace('%d', totalFiles);

        // Build results table
        let html = '<table class="table table-striped table-sm">';
        html += '<thead><tr>';
        html += `<th>${Joomla.Text._('JBS_SVR_SERVER')}</th>`;
        html += `<th class="text-center">${Joomla.Text._('JBS_SMG_MEDIA_COUNT')}</th>`;
        html += `<th>${Joomla.Text._('JBS_SMG_DETECTED_TYPES')}</th>`;
        html += '</tr></thead><tbody>';

        for (const server of scanData) {
            html += '<tr>';
            html += `<td><strong>${escapeHtml(server.server_name)}</strong>`;
            html += server.published === 0
                ? ' <span class="badge bg-secondary">Unpublished</span>'
                : '';
            html += `<br><small class="text-muted">ID: ${server.id}</small></td>`;
            html += `<td class="text-center">${server.total}</td>`;
            html += '<td>';

            for (const [type, count] of Object.entries(server.types)) {
                const badge = TYPE_BADGES[type] || 'secondary';
                const label = typeLabels[type] || type;
                html += `<span class="badge bg-${badge} me-1">${escapeHtml(label)}: ${count}</span>`;
            }

            html += '</td></tr>';
        }

        html += '</tbody></table>';
        tableContainer.innerHTML = html;

        // Build config form
        buildConfigForm();
        configPanel.style.display = '';
    }

    /**
     * Build the configuration form for target server selection.
     */
    function buildConfigForm() {
        const container = document.getElementById('smg-config-form');
        const startBtn = document.getElementById('btn-smg-start');
        let html = '';

        for (const server of scanData) {
            if (server.total === 0) {
                continue;
            }

            html += `<div class="card mb-3"><div class="card-header fw-bold">${escapeHtml(server.server_name)}</div>`;
            html += '<div class="card-body">';

            for (const [type, count] of Object.entries(server.types)) {
                const label = typeLabels[type] || type;
                const groupKey = `${server.id}_${type}`;
                const selectId = `smg-target-${groupKey}`;

                html += '<div class="row mb-2 align-items-center">';
                html += `<div class="col-auto">`;
                html += `<span class="badge bg-${TYPE_BADGES[type] || 'secondary'}">${escapeHtml(label)}</span>`;
                html += ` <small class="text-muted">(${count} files)</small>`;
                html += '</div>';
                html += `<div class="col">`;
                html += `<select class="form-select form-select-sm smg-target-select" id="${selectId}"`;
                html += ` data-server-id="${server.id}" data-type="${type}" data-count="${count}">`;

                // Option: Skip
                html += `<option value="skip">${Joomla.Text._('JBS_SMG_SKIP')}</option>`;

                // Option: Create new
                if (type !== 'unknown') {
                    html += `<option value="create_new">${Joomla.Text._('JBS_SMG_CREATE_NEW')} ${escapeHtml(label)}</option>`;
                }

                // Option: Use existing servers of matching type
                const targetType = type === 'unknown' ? null : type;
                if (targetType && existingServers[targetType]) {
                    for (const srv of existingServers[targetType]) {
                        html += `<option value="existing_${srv.id}">${escapeHtml(srv.server_name)} (ID: ${srv.id})</option>`;
                    }
                }

                // Also show all other server types as options (in case user wants to re-map)
                for (const [srvType, srvList] of Object.entries(existingServers)) {
                    if (srvType === targetType) {
                        continue;
                    }

                    for (const srv of srvList) {
                        html += `<option value="existing_${srv.id}">[${srvType}] ${escapeHtml(srv.server_name)} (ID: ${srv.id})</option>`;
                    }
                }

                html += '</select></div></div>';
            }

            html += '</div></div>';
        }

        container.innerHTML = html;

        // Enable start button if there are any non-skip selections
        const selects = container.querySelectorAll('.smg-target-select');
        const updateStartBtn = () => {
            const hasWork = Array.from(selects).some(sel => sel.value !== 'skip');
            startBtn.disabled = !hasWork;
        };

        selects.forEach(sel => sel.addEventListener('change', updateStartBtn));
        updateStartBtn();
    }

    // =========================================================================
    // Phase 3: Migration
    // =========================================================================

    /**
     * Build migration plan from config form and execute.
     */
    async function startMigration() {
        if (migrating) {
            return;
        }

        migrating = true;

        const progressPanel = document.getElementById('smg-progress-panel');
        const progressBar = document.getElementById('smg-progress-bar');
        const progressText = document.getElementById('smg-progress-text');

        progressPanel.style.display = '';
        document.getElementById('btn-smg-start').disabled = true;

        // Build plan from selects
        const selects = document.querySelectorAll('.smg-target-select');
        const groups = [];

        for (const sel of selects) {
            if (sel.value === 'skip') {
                continue;
            }

            const serverId = parseInt(sel.dataset.serverId, 10);
            const type = sel.dataset.type;
            const count = parseInt(sel.dataset.count, 10);
            const server = scanData.find(s => s.id === serverId);

            groups.push({
                legacyServerId: serverId,
                legacyServerParams: server ? server.params : {},
                detectedType: type,
                targetValue: sel.value,
                targetType: type === 'unknown' ? 'embed' : type,
                count,
            });
        }

        const totalFiles = groups.reduce((sum, g) => sum + g.count, 0);
        let migratedTotal = 0;
        let allErrors = [];

        for (const group of groups) {
            // Resolve target server ID
            let targetServerId;

            if (group.targetValue === 'create_new') {
                progressText.textContent = Joomla.Text._('JBS_SMG_CREATING_SERVER')
                    .replace('%s', typeLabels[group.targetType] || group.targetType);

                const createResult = await postJson('serverMigrationCreateServerXHR', {
                    type: group.targetType,
                    name: (typeLabels[group.targetType] || group.targetType) + ' (Migrated)',
                });

                if (!createResult.success) {
                    allErrors.push(createResult.message || 'Failed to create server');
                    continue;
                }

                targetServerId = createResult.serverId;
            } else {
                // existing_123
                targetServerId = parseInt(group.targetValue.replace('existing_', ''), 10);
            }

            // Migrate in batches
            let offset = 0;
            let batchErrors = [];

            while (offset < group.count + BATCH_SIZE) {
                progressText.textContent = Joomla.Text._('JBS_SMG_MIGRATING')
                    .replace('%d', Math.min(migratedTotal + BATCH_SIZE, totalFiles))
                    .replace('%t', totalFiles);

                const batchResult = await postJson('serverMigrationBatchXHR', {
                    legacyServerId: group.legacyServerId,
                    detectedType: group.detectedType,
                    targetServerId,
                    targetType: group.targetType,
                    offset,
                    limit: BATCH_SIZE,
                    legacyServerParams: group.legacyServerParams,
                });

                if (!batchResult.success) {
                    allErrors.push(batchResult.message || 'Batch failed');
                    break;
                }

                migratedTotal += batchResult.migrated;

                if (batchResult.errors && batchResult.errors.length) {
                    batchErrors = batchErrors.concat(batchResult.errors);
                }

                // Update progress
                const pct = totalFiles > 0 ? Math.round((migratedTotal / totalFiles) * 100) : 100;
                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '%';

                // If fewer fetched than limit, we've exhausted this group
                if (batchResult.fetched < BATCH_SIZE) {
                    break;
                }

                offset += BATCH_SIZE;
            }

            allErrors = allErrors.concat(batchErrors);
        }

        // Done
        progressBar.style.width = '100%';
        progressBar.textContent = '100%';
        progressBar.classList.remove('progress-bar-animated');

        migrating = false;
        showReport(migratedTotal, totalFiles, allErrors);
    }

    // =========================================================================
    // Phase 4: Report & Cleanup
    // =========================================================================

    /**
     * Display migration report.
     *
     * @param {number} migrated  Number migrated
     * @param {number} total     Total attempted
     * @param {string[]} errors  Error messages
     */
    function showReport(migrated, total, errors) {
        const reportPanel = document.getElementById('smg-report-panel');
        const reportContent = document.getElementById('smg-report-content');

        reportPanel.style.display = '';

        let html = '<div class="alert alert-success">';
        html += `<i class="icon-checkmark me-2" aria-hidden="true"></i>`;
        html += Joomla.Text._('JBS_SMG_REPORT_SUCCESS').replace('%d', migrated).replace('%t', total);
        html += '</div>';

        if (errors.length > 0) {
            html += '<div class="alert alert-warning">';
            html += `<strong>${Joomla.Text._('JBS_SMG_REPORT_ERRORS')} (${errors.length}):</strong>`;
            html += '<ul class="mb-0 mt-1">';
            for (const err of errors.slice(0, 20)) {
                html += `<li>${escapeHtml(err)}</li>`;
            }
            if (errors.length > 20) {
                html += `<li>... and ${errors.length - 20} more</li>`;
            }
            html += '</ul></div>';
        }

        reportContent.innerHTML = html;
    }

    /**
     * Run the cleanup step: unpublish empty legacy servers.
     */
    async function cleanup() {
        const btn = document.getElementById('btn-smg-cleanup');
        const resultDiv = document.getElementById('smg-cleanup-result');

        btn.disabled = true;

        try {
            const result = await getJson('serverMigrationCleanupXHR');

            if (!result.success) {
                resultDiv.innerHTML = `<div class="alert alert-danger">${escapeHtml(result.message)}</div>`;
            } else {
                let msg = Joomla.Text._('JBS_SMG_CLEANUP_DONE')
                    .replace('%d', result.unpublished);

                if (result.skipped > 0) {
                    msg += ' ' + Joomla.Text._('JBS_SMG_CLEANUP_SKIPPED').replace('%d', result.skipped);
                }

                resultDiv.innerHTML = `<div class="alert alert-success"><i class="icon-checkmark me-2" aria-hidden="true"></i>${msg}</div>`;
            }

            resultDiv.style.display = '';
        } catch (err) {
            resultDiv.innerHTML = `<div class="alert alert-danger">${escapeHtml(err.message)}</div>`;
            resultDiv.style.display = '';
        } finally {
            btn.disabled = false;
        }
    }

    // =========================================================================
    // Utility
    // =========================================================================

    /**
     * Escape HTML entities.
     *
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // =========================================================================
    // Init
    // =========================================================================

    function init() {
        const scanBtn = document.getElementById('btn-smg-scan');
        const startBtn = document.getElementById('btn-smg-start');
        const cleanupBtn = document.getElementById('btn-smg-cleanup');

        if (scanBtn) {
            scanBtn.addEventListener('click', scanServers);
        }

        if (startBtn) {
            startBtn.addEventListener('click', startMigration);
        }

        if (cleanupBtn) {
            cleanupBtn.addEventListener('click', cleanup);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
