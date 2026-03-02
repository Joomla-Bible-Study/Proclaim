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

    const BATCH_SIZE = 15;

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
        direct: 'secondary',
        empty: 'warning',
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
        return window.ProclaimFetch.fetchJson(
            ajaxUrl(task),
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            },
            { timeout: 30000, retries: 1 },
        );
    }

    /**
     * GET JSON from a controller endpoint.
     *
     * @param {string} task  The controller task name
     * @returns {Promise<object>}
     */
    async function getJson(task) {
        return window.ProclaimFetch.fetchJson(
            ajaxUrl(task),
            {},
            { timeout: 30000, retries: 2 },
        );
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
        html += `<th>${Joomla.Text._('JBS_SVR_SERVER_NAME')}</th>`;
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
                html += `<button type="button" class="badge bg-${badge} me-1 border-0 smg-detail-btn"`;
                html += ` data-server-id="${server.id}" data-type="${type}"`;
                html += ` title="${Joomla.Text._('JBS_SMG_DETAIL_TOOLTIP')}"`;
                html += ` style="cursor:pointer">${escapeHtml(label)}: ${count}</button>`;
            }

            html += '</td></tr>';
        }

        html += '</tbody></table>';
        html += '<div id="smg-detail-panel" style="display:none;"></div>';
        tableContainer.innerHTML = html;

        // Bind click handlers on type badges for drill-down
        tableContainer.querySelectorAll('.smg-detail-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                showDetail(parseInt(btn.dataset.serverId, 10), btn.dataset.type);
            });
        });

        // Build config form
        buildConfigForm();
        configPanel.style.display = '';
    }

    // =========================================================================
    // Drill-down Detail
    // =========================================================================

    /**
     * Fetch and display media file details for a server + type.
     *
     * @param {number} serverId  Legacy server ID
     * @param {string} type      Detected type key
     */
    async function showDetail(serverId, type) {
        const panel = document.getElementById('smg-detail-panel');

        if (!panel) {
            return;
        }

        const label = typeLabels[type] || type;
        const server = scanData.find(s => s.id === serverId);
        const serverName = server ? server.server_name : `ID ${serverId}`;

        panel.innerHTML = '<div class="text-center p-3">'
            + '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
            + Joomla.Text._('JBS_ADM_LOADING')
            + '</div>';
        panel.style.display = '';

        try {
            const url = ajaxUrl('serverMigrationDetailXHR')
                + `&serverId=${serverId}&type=${encodeURIComponent(type)}`;
            const result = await window.ProclaimFetch.fetchJson(url, {}, { timeout: 30000, retries: 1 });

            if (!result.success) {
                panel.innerHTML = `<div class="alert alert-danger">${escapeHtml(result.message || 'Failed')}</div>`;
                return;
            }

            renderDetail(panel, serverName, label, type, result.details);
        } catch (err) {
            panel.innerHTML = `<div class="alert alert-danger">${escapeHtml(err.message)}</div>`;
        }
    }

    /**
     * Render the detail table inside the given panel.
     *
     * @param {HTMLElement} panel       Target container
     * @param {string}      serverName  Legacy server name
     * @param {string}      label       Type display label
     * @param {string}      type        Type key
     * @param {Array}       details     Media file detail rows
     */
    function renderDetail(panel, serverName, label, type, details) {
        const badge = TYPE_BADGES[type] || 'secondary';

        let html = '<div class="card mt-3 mb-3">';
        html += `<div class="card-header d-flex justify-content-between align-items-center">`;
        html += `<span><strong>${escapeHtml(serverName)}</strong> &mdash; `;
        html += `<span class="badge bg-${badge}">${escapeHtml(label)}</span>`;
        html += ` (${details.length} ${details.length === 1 ? 'file' : 'files'})</span>`;
        html += `<button type="button" class="btn-close" id="smg-detail-close" aria-label="Close"></button>`;
        html += '</div>';

        if (details.length === 0) {
            html += `<div class="card-body"><p class="text-muted mb-0">${Joomla.Text._('JBS_SMG_DETAIL_NONE')}</p></div>`;
        } else {
            html += '<div class="card-body p-0"><div class="table-responsive">';
            html += '<table class="table table-sm table-striped mb-0">';
            html += '<thead><tr>';
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_ID')}</th>`;
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_STATUS')}</th>`;
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_STUDY')}</th>`;
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_FILENAME')}</th>`;
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_MEDIACODE')}</th>`;
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_MIME')}</th>`;
            html += `<th>${Joomla.Text._('JBS_SMG_DETAIL_PLAYER')}</th>`;
            html += '</tr></thead><tbody>';

            for (const row of details) {
                const editUrl = `index.php?option=com_proclaim&task=cwmmediafile.edit&id=${row.id}`;
                const statusBadge = publishedBadge(row.published);
                html += '<tr>';
                html += `<td><a href="${editUrl}" target="_blank">${row.id}</a></td>`;
                html += `<td class="text-center">${statusBadge}</td>`;
                html += '<td>';

                if (row.study_id) {
                    const studyUrl = `index.php?option=com_proclaim&task=cwmmessage.edit&id=${row.study_id}`;
                    html += `<a href="${studyUrl}" target="_blank">${escapeHtml(row.studytitle || 'ID ' + row.study_id)}</a>`;
                } else {
                    html += '<span class="text-muted">—</span>';
                }

                html += '</td>';
                html += `<td><small>${escapeHtml(row.filename || '—')}</small></td>`;
                html += `<td><small>${escapeHtml(row.mediacode || '—')}</small></td>`;
                html += `<td><small>${escapeHtml(row.mime_type || '—')}</small></td>`;
                html += `<td>${escapeHtml(row.player || '—')}</td>`;
                html += '</tr>';
            }

            html += '</tbody></table></div></div>';
        }

        html += '</div>';
        panel.innerHTML = html;

        // Close button
        const closeBtn = document.getElementById('smg-detail-close');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                panel.style.display = 'none';
                panel.innerHTML = '';
            });
        }
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

                // Empty files have no content — skip-only (use drill-down to investigate)
                if (type === 'empty') {
                    html += '</select>';
                    html += ` <small class="text-muted fst-italic">${Joomla.Text._('JBS_SMG_EMPTY_HINT')}</small>`;
                    html += '</div></div>';
                    continue;
                }

                // Option: Create new (not for unknown — those need manual review)
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

        const beforeUnload = (e) => {
            e.preventDefault();
            e.returnValue = '';
        };
        window.addEventListener('beforeunload', beforeUnload);

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

            // Migrate in batches.  Successfully migrated rows change server_id
            // and drop out of the result set.  Failed rows stay, so we track
            // cumulative failures and use that as the query offset to skip
            // past stuck files instead of re-fetching them every batch.
            let batchErrors = [];
            let failedInGroup = 0;
            let safety = Math.ceil(group.count / BATCH_SIZE) * 2 + 5;

            while ((safety -= 1) >= 0) {
                progressText.textContent = Joomla.Text._('JBS_SMG_MIGRATING')
                    .replace('%d', Math.min(migratedTotal + BATCH_SIZE, totalFiles))
                    .replace('%t', totalFiles);

                const batchResult = await postJson('serverMigrationBatchXHR', {
                    legacyServerId: group.legacyServerId,
                    detectedType: group.detectedType,
                    targetServerId,
                    targetType: group.targetType,
                    offset: failedInGroup,
                    limit: BATCH_SIZE,
                    legacyServerParams: group.legacyServerParams,
                });

                if (!batchResult.success) {
                    allErrors.push(batchResult.message || 'Batch failed');
                    break;
                }

                migratedTotal += batchResult.migrated;
                failedInGroup += batchResult.fetched - batchResult.migrated;

                if (batchResult.errors && batchResult.errors.length) {
                    batchErrors = batchErrors.concat(batchResult.errors);
                }

                // Update progress
                const pct = totalFiles > 0 ? Math.round((migratedTotal / totalFiles) * 100) : 100;
                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '%';

                // No more matching files for this group (all remaining are failures)
                if (batchResult.fetched === 0) {
                    break;
                }
            }

            allErrors = allErrors.concat(batchErrors);
        }

        // Done
        progressBar.style.width = '100%';
        progressBar.textContent = '100%';
        progressBar.classList.remove('progress-bar-animated');

        window.removeEventListener('beforeunload', beforeUnload);
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
     * Return an HTML badge for a Joomla published state value.
     *
     * @param {number} state  Published state (-2=trashed, 0=unpublished, 1=published, 2=archived)
     * @returns {string}  HTML badge markup
     */
    function publishedBadge(state) {
        switch (state) {
            case 1:
                return '<span class="badge bg-success">Published</span>';
            case 0:
                return '<span class="badge bg-secondary">Unpublished</span>';
            case 2:
                return '<span class="badge bg-info">Archived</span>';
            case -2:
                return '<span class="badge bg-danger">Trashed</span>';
            default:
                return `<span class="badge bg-light text-dark">${state}</span>`;
        }
    }

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
