/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Image Tools tab: migration, orphan cleanup, and WebP generation.
 *
 * Reads CSRF token from #imagetools-config[data-token].
 * All UI strings use Joomla.Text._('JBS_ADM_KEY') — auto-registered via CwmlangHelper.
 */
document.addEventListener('DOMContentLoaded', () => {
    const config = document.getElementById('imagetools-config');
    if (!config) return;

    const { token } = config.dataset;

    // Store counts for progress calculation
    let migrationTotals = {
        studies: 0, teachers: 0, series: 0, total: 0,
    };
    let webpTotals = {
        studies: 0, teachers: 0, series: 0, total: 0,
    };

    // ---- Navigation guard ----
    let activeOperation = null; // null | 'migration' | 'webp' | 'orphan'

    // ---- Pipeline state ----
    let pipelineMode = false;
    let pipelineCancelled = false;
    let pipelineStepDone = null; // resolve() for current step's Promise

    function onBeforeUnload(e) {
        if (activeOperation) {
            e.preventDefault();
            // Modern browsers ignore custom text but still show a prompt
            e.returnValue = '';
        }
    }
    window.addEventListener('beforeunload', onBeforeUnload);

    // Show/hide the big warning banner and disable form controls during operations
    function setOperationRunning(running) {
        const banner = document.getElementById('imagetools-nav-warning');
        if (banner) {
            banner.style.cssText = running ? 'display: flex !important;' : 'display: none !important;';
        }
        // Disable the Joomla toolbar Save/Close buttons and tab nav links
        document.querySelectorAll('#toolbar button, #toolbar a, .subhead button').forEach((el) => {
            el.toggleAttribute('disabled', running);
            if (running) el.style.pointerEvents = 'none';
            else el.style.pointerEvents = '';
        });
        // Disable tab switching
        document.querySelectorAll('[data-bs-toggle="tab"], .nav-link').forEach((el) => {
            if (running) {
                el.classList.add('disabled');
                el.style.pointerEvents = 'none';
            } else {
                el.classList.remove('disabled');
                el.style.pointerEvents = '';
            }
        });
    }

    // Intercept clicks outside imagetools section while running
    function blockNavigation(e) {
        if (!activeOperation) return;
        const target = e.target.closest('a[href], button[type="submit"], .nav-link, [data-bs-toggle="tab"]');
        if (!target) return;
        // Allow clicks inside our own imagetools section (including accordion toggles and pipelines)
        if (target.closest('#imagetools, #imagetools-row2, #imagetools-pipeline-panel, #cleanup-pipeline-panel, #imagetools-accordion')) return;
        e.preventDefault();
        e.stopPropagation();
    }
    document.addEventListener('click', blockNavigation, true);

    // ---- Image Migration ----

    function loadMigrationCounts() {
        return fetch(`index.php?option=com_proclaim&task=cwmadmin.getMigrationCountsXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                migrationTotals = data;

                if (data.total === 0) {
                    document.getElementById('migration-counts').innerHTML = `<div class="alert alert-success mb-0"><i class="icon-checkmark me-1" aria-hidden="true"></i>${Joomla.Text._('JBS_ADM_MIGRATION_ALL_DONE')}</div>`;
                } else {
                    const items = [];
                    if (data.studies > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_MIGRATION_COUNT_MESSAGES').replace('%s', data.studies)}</li>`);
                    if (data.teachers > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_MIGRATION_COUNT_TEACHERS').replace('%s', data.teachers)}</li>`);
                    if (data.series > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_MIGRATION_COUNT_SERIES').replace('%s', data.series)}</li>`);
                    document.getElementById('migration-counts').innerHTML = `<ul class="list-unstyled mb-0">${items.join('')}</ul>
             <div class="mt-2 fw-bold">${Joomla.Text._('JBS_CMN_TOTAL')}: ${data.total}</div>`;
                }

                document.getElementById('btn-start-migration').disabled = (data.total === 0);
            })
            .catch((err) => {
                document.getElementById('migration-counts').innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}: ${err.message || err}</span>`;
            });
    }

    document.getElementById('btn-start-migration').addEventListener('click', function () {
        this.disabled = true;
        activeOperation = 'migration';
        setOperationRunning(true);
        const progressEl = document.getElementById('migration-progress');
        const barEl = progressEl.querySelector('.progress-bar');
        const statusEl = document.getElementById('migration-status');
        const reportEl = document.getElementById('migration-error-report');
        progressEl.style.display = 'block';
        if (reportEl) reportEl.style.display = 'none';
        barEl.classList.add('progress-bar-striped', 'progress-bar-animated');

        const types = ['studies', 'teachers', 'series'];
        let typeIndex = 0;
        let totalMigrated = 0;
        let totalErrors = 0;
        let totalRelocated = 0;
        const errorDetails = []; // Collect {type, id, title, path, error}
        const relocatedDetails = []; // Collect {type, id, title, originalPath, foundAt, newPath}
        const failedIds = new Set(); // Track 'type-id' keys that already failed — prevents duplicate processing
        const grandTotal = migrationTotals.total;

        function updateBar() {
            const pct = grandTotal > 0 ? Math.min(100, Math.round(((totalMigrated + totalErrors) / grandTotal) * 100)) : 0;
            barEl.style.width = `${pct}%`;
            barEl.textContent = `${pct}%`;
        }

        function showRelocatedReport() {
            if (!reportEl || relocatedDetails.length === 0) return;
            const summary = Joomla.Text._('JBS_ADM_RELOCATED_FOUND').replace('%s', relocatedDetails.length);
            let html = `<div class="alert alert-info mt-3">
        <h5><i class="icon-checkmark me-1" aria-hidden="true"></i>${summary}</h5>
        <p class="small mb-2">${Joomla.Text._('JBS_ADM_RELOCATED_DESC')}</p>
        <div style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${Joomla.Text._('JBS_ADM_EXPECTED_PATH')}</th><th>${Joomla.Text._('JBS_ADM_FOUND_AT_PATH')}</th></tr></thead>
          <tbody>`;
            relocatedDetails.forEach((r) => {
                html += `<tr><td>${r.type}</td><td>${r.id}</td><td><small>${r.title}</small></td><td><small class="text-muted">${r.originalPath}</small></td><td><small class="text-success">${r.foundAt}</small></td></tr>`;
            });
            html += '</tbody></table></div></div>';
            // Prepend before error report
            reportEl.innerHTML = html + reportEl.innerHTML;
            reportEl.style.display = 'block';
        }

        function showErrorReport() {
            if (!reportEl || errorDetails.length === 0) return;

            // Split errors: "Source file not found" vs other processing errors
            const missing = errorDetails.filter((e) => e.error === 'Source file not found');
            const processing = errorDetails.filter((e) => e.error !== 'Source file not found');
            let html = '';

            if (missing.length > 0) {
                html += `<div class="alert alert-warning mt-3">
          <h5>${Joomla.Text._('JBS_ADM_MISSING_FILES')} (${missing.length})</h5>
          <p class="small mb-2">${Joomla.Text._('JBS_ADM_MISSING_FILES_DESC')}</p>
          <div style="max-height: 300px; overflow-y: auto;">
          <table class="table table-sm table-striped mb-0">
            <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${Joomla.Text._('JBS_ADM_MISSING_PATH')}</th></tr></thead>
            <tbody>`;
                missing.forEach((e) => {
                    html += `<tr><td>${e.type}</td><td>${e.id}</td><td><small>${e.title}</small></td><td><small class="text-danger">${e.path}</small></td></tr>`;
                });
                html += '</tbody></table></div></div>';
            }

            if (processing.length > 0) {
                html += `<div class="alert alert-danger mt-3">
          <h5>${Joomla.Text._('JBS_ADM_PROCESSING_ERRORS')} (${processing.length})</h5>
          <p class="small mb-2">${Joomla.Text._('JBS_ADM_PROCESSING_ERRORS_DESC')}</p>
          <div style="max-height: 300px; overflow-y: auto;">
          <table class="table table-sm table-striped mb-0">
            <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${Joomla.Text._('JBS_ADM_MISSING_PATH')}</th><th>${Joomla.Text._('JBS_ADM_ERROR_REASON')}</th></tr></thead>
            <tbody>`;
                processing.forEach((e) => {
                    html += `<tr><td>${e.type}</td><td>${e.id}</td><td><small>${e.title}</small></td><td><small>${e.path}</small></td><td><small class="text-danger">${e.error}</small></td></tr>`;
                });
                html += '</tbody></table></div></div>';
            }

            reportEl.innerHTML = html;
            reportEl.style.display = 'block';
        }

        function migrateType() {
            if (typeIndex >= types.length) {
                activeOperation = null;
                setOperationRunning(false);
                barEl.classList.remove('progress-bar-striped', 'progress-bar-animated');
                barEl.style.width = '100%';
                barEl.textContent = '100%';
                let msg = `<span class="text-success">${Joomla.Text._('JBS_ADM_MIGRATION_COMPLETE')} ${totalMigrated} ${Joomla.Text._('JBS_ADM_RECORDS_MIGRATED')}</span>`;
                if (totalRelocated > 0) {
                    msg += ` <span class="text-info">(${totalRelocated} ${Joomla.Text._('JBS_ADM_RELOCATED')})</span>`;
                }
                if (totalErrors > 0) {
                    msg += ` <span class="text-warning">(${totalErrors} ${Joomla.Text._('JBS_ADM_MIGRATION_ERRORS')})</span>`;
                }
                statusEl.innerHTML = msg;
                showRelocatedReport();
                showErrorReport();
                loadWebPCounts();

                if (pipelineMode && pipelineStepDone) {
                    const resolve = pipelineStepDone;
                    pipelineStepDone = null;
                    resolve();
                    return; // skip Done button in pipeline mode
                }

                // Show a "Done" button — user must acknowledge before Start Migration re-enables
                const startBtn = document.getElementById('btn-start-migration');
                const doneBtn = document.createElement('button');
                doneBtn.type = 'button';
                doneBtn.className = 'btn btn-success mt-3';
                doneBtn.innerHTML = `<i class="icon-checkmark" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_MIGRATION_DONE')}`;
                doneBtn.addEventListener('click', () => {
                    doneBtn.remove();
                    loadMigrationCounts(); // Refreshes counts and re-enables Start Migration if needed
                });
                // Insert after the start button
                startBtn.parentNode.insertBefore(doneBtn, startBtn.nextSibling);
                return;
            }

            // Skip types with zero count
            if (migrationTotals[types[typeIndex]] === 0) {
                typeIndex += 1;
                migrateType();
                return;
            }

            statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_MIGRATING')} ${types[typeIndex]}... <strong>0</strong> / ${migrationTotals[types[typeIndex]]}`;
            migrateBatch(types[typeIndex], 0);
        }

        function migrateBatch(type, typeProcessed) {
            // Collect failed IDs for this type so PHP can skip them in the query
            const typeFailedIds = [];
            failedIds.forEach((key) => {
                if (key.startsWith(`${type}-`)) {
                    typeFailedIds.push(key.substring(type.length + 1));
                }
            });
            const excludeParam = typeFailedIds.length > 0 ? `&exclude=${typeFailedIds.join(',')}` : '';

            fetch(`index.php?option=com_proclaim&task=cwmadmin.getMigrationBatchXHR&${token}=1&type=${type}&limit=5${excludeParam}`)
                .then((r) => r.json())
                .then((data) => {
                    // No more records to process for this type (PHP already excluded failed IDs)
                    if (data.records.length === 0) {
                        typeIndex += 1;
                        migrateType();
                        return;
                    }

                    let batchDone = 0;
                    const batchTotal = data.records.length;
                    const typeTotal = migrationTotals[type];

                    const promises = data.records.map((record) => {
                        const recordTitle = record.studytitle || record.teachername || record.title || '';

                        return fetch(`index.php?option=com_proclaim&task=cwmadmin.migrateRecordXHR&${token}=1&type=${type}&id=${record.id}`)
                            .then((r) => r.json())
                            .then((result) => {
                                if (result.success) {
                                    totalMigrated += 1;
                                    if (result.relocated) {
                                        totalRelocated += 1;
                                        relocatedDetails.push({
                                            type,
                                            id: record.id,
                                            title: recordTitle,
                                            originalPath: result.originalPath || '',
                                            foundAt: result.foundAt || '',
                                            newPath: result.newPath || '',
                                        });
                                    }
                                } else {
                                    failedIds.add(`${type}-${record.id}`);
                                    totalErrors += 1;
                                    errorDetails.push({
                                        type,
                                        id: record.id,
                                        title: recordTitle,
                                        path: result.missingPath || record.image_path || '',
                                        error: result.error || 'Unknown error',
                                    });
                                }
                            })
                            .catch(() => {
                                failedIds.add(`${type}-${record.id}`);
                                totalErrors += 1;
                                errorDetails.push({
                                    type,
                                    id: record.id,
                                    title: recordTitle,
                                    path: record.image_path || '',
                                    error: 'Network error',
                                });
                            })
                            .finally(() => {
                                batchDone += 1;
                                const displayed = Math.min(typeProcessed + batchDone, typeTotal);
                                statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_MIGRATING')} ${types[typeIndex]}... <strong>${displayed}</strong> / ${typeTotal}`;
                                updateBar();
                            });
                    });

                    Promise.all(promises).then(() => {
                        const newTypeProcessed = typeProcessed + batchTotal;

                        // Move to next type when: no remaining, or counter exceeds total (safety cap)
                        if (data.remaining <= 0 || newTypeProcessed >= typeTotal) {
                            typeIndex += 1;
                            migrateType();
                        } else {
                            migrateBatch(type, newTypeProcessed);
                        }
                    });
                })
                .catch(() => {
                    activeOperation = null;
                    setOperationRunning(false);
                    statusEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_MIGRATION_ERROR')}</span>`;
                    showErrorReport();
                    if (pipelineMode && pipelineStepDone) {
                        const resolve = pipelineStepDone;
                        pipelineStepDone = null;
                        resolve();
                    }
                });
        }

        migrateType();
    });

    // ---- Orphan Cleanup ----
    document.getElementById('btn-scan-orphans').addEventListener('click', function () {
        const btn = this;

        // Warn if migration or unresolvable cleanup hasn't been done yet
        if (migrationTotals.total > 0) {
            if (!confirm(Joomla.Text._('JBS_ADM_ORPHAN_MIGRATION_WARNING'))) {
                return;
            }
        }

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${Joomla.Text._('JBS_ADM_SCANNING')}`;

        fetch(`index.php?option=com_proclaim&task=cwmadmin.getOrphanedFoldersXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                btn.disabled = false;
                btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_SCAN_ORPHANS')}`;
                document.getElementById('orphan-results').style.display = 'block';

                // Update step indicator
                const stepEl = document.getElementById('orphan-step-indicator');
                if (stepEl) stepEl.textContent = Joomla.Text._('JBS_ADM_ORPHAN_STEP2');

                document.getElementById('orphan-summary').innerHTML = `${Joomla.Text._('JBS_ADM_FOUND')} <strong>${data.totals.folders}</strong> ${Joomla.Text._('JBS_ADM_ORPHAN_FOLDERS')} (${data.totals.size_formatted})`;

                if (data.totals.folders > 0) {
                    let tableHtml = `<table class="table table-sm table-striped">
            <thead><tr>
              <th><input type="checkbox" id="select-all-orphans" aria-label="${Joomla.Text._('JBS_ADM_SELECT_ALL')}"></th>
              <th>${Joomla.Text._('JBS_ADM_FOLDER')}</th><th>${Joomla.Text._('JBS_ADM_FILES')}</th><th>${Joomla.Text._('JBS_ADM_SIZE')}</th>
            </tr></thead><tbody>`;

                    ['studies', 'teachers', 'series'].forEach((type) => {
                        if (data.orphans[type]) {
                            data.orphans[type].forEach((orphan) => {
                                tableHtml += `<tr>
                  <td><input type="checkbox" class="orphan-checkbox" value="${orphan.path}" aria-label="${orphan.path}"></td>
                  <td><small>${orphan.path}</small></td>
                  <td>${orphan.files}</td>
                  <td>${formatBytes(orphan.size)}</td>
                </tr>`;
                            });
                        }
                    });

                    tableHtml += '</tbody></table>';
                    document.getElementById('orphan-list').innerHTML = tableHtml;
                    document.getElementById('btn-delete-orphans').style.display = 'inline-block';
                    document.getElementById('select-all-orphans').addEventListener('change', function () {
                        document.querySelectorAll('.orphan-checkbox').forEach((cb) => { cb.checked = this.checked; });
                    });
                } else {
                    document.getElementById('orphan-list').innerHTML = `<p class="text-success">${Joomla.Text._('JBS_ADM_NO_ORPHANS')}</p>`;
                    document.getElementById('btn-delete-orphans').style.display = 'none';
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_SCAN_ORPHANS')}`;
            });
    });

    document.getElementById('btn-delete-orphans').addEventListener('click', function () {
        const selected = [];
        document.querySelectorAll('.orphan-checkbox:checked').forEach((cb) => selected.push(cb.value));
        if (selected.length === 0) return;

        const btn = this;
        btn.disabled = true;
        activeOperation = 'orphan';
        setOperationRunning(true);
        const params = new URLSearchParams();
        selected.forEach((path) => params.append('paths[]', path));

        fetch(`index.php?option=com_proclaim&task=cwmadmin.deleteOrphanedFoldersXHR&${token}=1`, {
            method: 'POST',
            body: params,
        })
            .then((r) => r.json())
            .then(() => {
                activeOperation = null;
                setOperationRunning(false);
                btn.disabled = false;
                document.getElementById('btn-scan-orphans').click();
            })
            .catch(() => { activeOperation = null; setOperationRunning(false); btn.disabled = false; });
    });

    // ---- WebP Generation ----

    function loadWebPCounts() {
        return fetch(`index.php?option=com_proclaim&task=cwmadmin.getWebPCountsXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                webpTotals = data;

                if (data.total === 0) {
                    document.getElementById('webp-counts').innerHTML = `<div class="alert alert-success mb-0"><i class="icon-checkmark me-1" aria-hidden="true"></i>${Joomla.Text._('JBS_ADM_WEBP_ALL_DONE')}</div>`;
                    document.getElementById('btn-start-webp').disabled = true;
                } else {
                    const items = [];
                    if (data.studies > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_WEBP_COUNT_MESSAGES').replace('%s', data.studies)}</li>`);
                    if (data.teachers > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_WEBP_COUNT_TEACHERS').replace('%s', data.teachers)}</li>`);
                    if (data.series > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_WEBP_COUNT_SERIES').replace('%s', data.series)}</li>`);
                    document.getElementById('webp-counts').innerHTML = `<ul class="list-unstyled mb-0">${items.join('')}</ul>
             <div class="mt-2 fw-bold">${Joomla.Text._('JBS_CMN_TOTAL')}: ${data.total}</div>`;
                    document.getElementById('btn-start-webp').disabled = false;
                }
            })
            .catch(() => {
                document.getElementById('webp-counts').innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}</span>`;
            });
    }

    document.getElementById('btn-start-webp').addEventListener('click', function () {
        this.disabled = true;
        activeOperation = 'webp';
        setOperationRunning(true);
        const progressEl = document.getElementById('webp-progress');
        const barEl = progressEl.querySelector('.progress-bar');
        const statusEl = document.getElementById('webp-status');
        progressEl.style.display = 'block';
        barEl.classList.add('progress-bar-striped', 'progress-bar-animated');

        const types = ['studies', 'teachers', 'series'];
        let typeIndex = 0;
        let totalConverted = 0;
        let totalErrors = 0;
        const grandTotal = webpTotals.total;

        function updateBar() {
            const pct = grandTotal > 0 ? Math.min(100, Math.round(((totalConverted + totalErrors) / grandTotal) * 100)) : 0;
            barEl.style.width = `${pct}%`;
            barEl.textContent = `${pct}%`;
        }

        function convertType() {
            if (typeIndex >= types.length) {
                activeOperation = null;
                setOperationRunning(false);
                barEl.classList.remove('progress-bar-striped', 'progress-bar-animated');
                barEl.style.width = '100%';
                barEl.textContent = '100%';
                let msg = `<span class="text-success">${Joomla.Text._('JBS_ADM_WEBP_COMPLETE')} ${totalConverted} ${Joomla.Text._('JBS_ADM_IMAGES_CONVERTED')}</span>`;
                if (totalErrors > 0) {
                    msg += ` <span class="text-warning">(${totalErrors} ${Joomla.Text._('JBS_ADM_MIGRATION_ERRORS')})</span>`;
                }
                statusEl.innerHTML = msg;
                loadWebPCounts();
                if (pipelineMode && pipelineStepDone) {
                    const resolve = pipelineStepDone;
                    pipelineStepDone = null;
                    resolve();
                }
                return;
            }

            // Skip types with zero count
            if (webpTotals[types[typeIndex]] === 0) {
                typeIndex += 1;
                convertType();
                return;
            }

            statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_CONVERTING')} ${types[typeIndex]}... <strong>0</strong> / ${webpTotals[types[typeIndex]]}`;
            convertBatch(types[typeIndex], 0);
        }

        function convertBatch(type, typeConverted) {
            fetch(`index.php?option=com_proclaim&task=cwmadmin.migrateToWebPXHR&${token}=1&type=${type}&limit=5`)
                .then((r) => {
                    if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                    return r.text();
                })
                .then((text) => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch {
                        // PHP returned non-JSON (fatal error, HTML error page)
                        throw new Error(`Invalid response: ${text.substring(0, 200)}`);
                    }

                    // PHP-level error (exception caught in controller)
                    if (data.error) {
                        statusEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_WEBP_ERROR')}: ${data.error}</span>`;
                        // Still continue to next type — don't abort entirely
                        typeIndex += 1;
                        convertType();
                        return;
                    }

                    totalConverted += data.converted;
                    totalErrors += (data.errors || 0);
                    const newTypeConverted = typeConverted + data.converted + (data.errors || 0);
                    const typeTotal = webpTotals[type];
                    const displayed = Math.min(newTypeConverted, typeTotal);
                    statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_CONVERTING')} ${types[typeIndex]}... <strong>${displayed}</strong> / ${typeTotal}`;
                    updateBar();

                    if (data.remaining > 0 && newTypeConverted < typeTotal) {
                        convertBatch(type, newTypeConverted);
                    } else {
                        typeIndex += 1;
                        convertType();
                    }
                })
                .catch((err) => {
                    activeOperation = null;
                    setOperationRunning(false);
                    statusEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_WEBP_ERROR')}: ${err.message || err}</span>`;
                    if (pipelineMode && pipelineStepDone) {
                        const resolve = pipelineStepDone;
                        pipelineStepDone = null;
                        resolve();
                    }
                });
        }

        convertType();
    });

    // ---- Thumbnail & WebP Regeneration (studies + teachers + series) ----

    function loadThumbRegenCounts() {
        fetch(`index.php?option=com_proclaim&task=cwmadmin.getThumbRegenCountXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                if (data.total === 0) {
                    document.getElementById('thumb-regen-counts').innerHTML = `<div class="alert alert-success mb-0"><i class="icon-checkmark me-1" aria-hidden="true"></i>${Joomla.Text._('JBS_ADM_THUMB_REGEN_ALL_DONE')}</div>`;
                } else {
                    const parts = [];
                    if (data.studies > 0) parts.push(Joomla.Text._('JBS_ADM_THUMB_REGEN_COUNT_MESSAGES').replace('%s', data.studies));
                    if (data.teachers > 0) parts.push(Joomla.Text._('JBS_ADM_THUMB_REGEN_COUNT_TEACHERS').replace('%s', data.teachers));
                    if (data.series > 0) parts.push(Joomla.Text._('JBS_ADM_THUMB_REGEN_COUNT_SERIES').replace('%s', data.series));
                    document.getElementById('thumb-regen-counts').innerHTML = `<div class="mb-0">${parts.join(', ')} (${data.total} total)</div>`;
                }
                // Always enable — regeneration is a force-redo action regardless of count
                document.getElementById('btn-start-thumb-regen').disabled = false;
            })
            .catch(() => {
                document.getElementById('thumb-regen-counts').innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}</span>`;
            });
    }

    document.getElementById('btn-start-thumb-regen').addEventListener('click', function () {
        this.disabled = true;
        activeOperation = 'thumbregen';
        setOperationRunning(true);
        const progressEl = document.getElementById('thumb-regen-progress');
        const barEl = progressEl.querySelector('.progress-bar');
        const statusEl = document.getElementById('thumb-regen-status');
        progressEl.style.display = 'block';
        barEl.classList.add('progress-bar-striped', 'progress-bar-animated');

        let totalProcessed = 0;
        let totalErrors = 0;

        // Re-fetch counts for accurate progress
        fetch(`index.php?option=com_proclaim&task=cwmadmin.getThumbRegenCountXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                const grandTotal = data.total;
                const types = ['studies', 'teachers', 'series'].filter((t) => data[t] > 0);
                processNextType(types, 0, grandTotal);
            });

        function updateBar(grandTotal) {
            const pct = grandTotal > 0 ? Math.min(100, Math.round(((totalProcessed + totalErrors) / grandTotal) * 100)) : 0;
            barEl.style.width = `${pct}%`;
            barEl.textContent = `${pct}%`;
        }

        function processNextType(types, typeIdx, grandTotal) {
            if (typeIdx >= types.length) {
                // All types done
                activeOperation = null;
                setOperationRunning(false);
                barEl.classList.remove('progress-bar-striped', 'progress-bar-animated');
                barEl.style.width = '100%';
                barEl.textContent = '100%';
                let msg = `<span class="text-success">${Joomla.Text._('JBS_ADM_THUMB_REGEN_COMPLETE')} ${totalProcessed} processed.</span>`;
                if (totalErrors > 0) {
                    msg += ` <span class="text-warning">(${totalErrors} errors)</span>`;
                }
                statusEl.innerHTML = msg;
                loadThumbRegenCounts();
                return;
            }

            const currentType = types[typeIdx];
            let offset = 0;
            processTypeBatch();

            function processTypeBatch() {
                statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_REGENERATING')} ${currentType}... <strong>${totalProcessed}</strong> / ${grandTotal}`;

                fetch(`index.php?option=com_proclaim&task=cwmadmin.regenerateThumbsXHR&${token}=1&type=${currentType}&limit=10&offset=${offset}`)
                    .then((r) => {
                        if (!r.ok) throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                        return r.text();
                    })
                    .then((text) => {
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch {
                            throw new Error(`Invalid response: ${text.substring(0, 200)}`);
                        }

                        if (data.error) {
                            statusEl.innerHTML = `<span class="text-danger">Error: ${data.error}</span>`;
                            activeOperation = null;
                            setOperationRunning(false);
                            return;
                        }

                        totalProcessed += data.processed;
                        totalErrors += (data.errors || 0);
                        offset += data.processed + (data.errors || 0);
                        updateBar(grandTotal);
                        statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_REGENERATING')} ${currentType}... <strong>${totalProcessed}</strong> / ${grandTotal}`;

                        if (data.remaining > 0) {
                            processTypeBatch();
                        } else {
                            // Move to next type
                            processNextType(types, typeIdx + 1, grandTotal);
                        }
                    })
                    .catch((err) => {
                        activeOperation = null;
                        setOperationRunning(false);
                        statusEl.innerHTML = `<span class="text-danger">Error: ${err.message || err}</span>`;
                    });
            }
        }
    });

    // ---- Recover Bare-ID Folders ----
    let recoveryTotals = {
        studies: 0, teachers: 0, series: 0, total: 0,
    };

    function loadRecoveryCounts() {
        return fetch(`index.php?option=com_proclaim&task=cwmadmin.getRecoveryCountsXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                recoveryTotals = data;

                if (data.total === 0) {
                    document.getElementById('recovery-counts').innerHTML = `<div class="alert alert-success mb-0"><i class="icon-checkmark me-1" aria-hidden="true"></i>${Joomla.Text._('JBS_ADM_RECOVER_IMAGES_NONE')}</div>`;
                    document.getElementById('btn-start-recovery').disabled = true;
                } else {
                    const items = [];
                    if (data.studies > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_RECOVER_COUNT_MESSAGES').replace('%s', data.studies)}</li>`);
                    if (data.teachers > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_RECOVER_COUNT_TEACHERS').replace('%s', data.teachers)}</li>`);
                    if (data.series > 0) items.push(`<li>${Joomla.Text._('JBS_ADM_RECOVER_COUNT_SERIES').replace('%s', data.series)}</li>`);
                    document.getElementById('recovery-counts').innerHTML = `<ul class="list-unstyled mb-0">${items.join('')}</ul>
             <div class="mt-2 fw-bold">${Joomla.Text._('JBS_CMN_TOTAL')}: ${data.total}</div>`;
                    document.getElementById('btn-start-recovery').disabled = false;
                }
            })
            .catch(() => {
                document.getElementById('recovery-counts').innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}</span>`;
            });
    }

    document.getElementById('btn-start-recovery').addEventListener('click', function () {
        this.disabled = true;
        activeOperation = 'recovery';
        setOperationRunning(true);
        const progressEl = document.getElementById('recovery-progress');
        const barEl = progressEl.querySelector('.progress-bar');
        const statusEl = document.getElementById('recovery-status');
        progressEl.style.display = 'block';
        barEl.classList.add('progress-bar-striped', 'progress-bar-animated');

        const types = ['studies', 'teachers', 'series'];
        let typeIndex = 0;
        let totalRecovered = 0;
        let totalErrors = 0;
        let totalSkipped = 0;
        const allErrorDetails = [];
        const grandTotal = recoveryTotals.total;

        function updateBar() {
            const pct = grandTotal > 0 ? Math.min(100, Math.round(((totalRecovered + totalErrors) / grandTotal) * 100)) : 0;
            barEl.style.width = `${pct}%`;
            barEl.textContent = `${pct}%`;
        }

        function recoverType() {
            if (typeIndex >= types.length) {
                activeOperation = null;
                setOperationRunning(false);
                barEl.classList.remove('progress-bar-striped', 'progress-bar-animated');
                barEl.style.width = '100%';
                barEl.textContent = '100%';
                let msg = `<span class="text-success">${Joomla.Text._('JBS_ADM_RECOVER_COMPLETE')} ${totalRecovered} ${Joomla.Text._('JBS_ADM_FOLDERS_RECOVERED')}</span>`;
                if (totalSkipped > 0) {
                    msg += ` <span class="text-muted">(${totalSkipped} skipped — no DB record)</span>`;
                }
                if (totalErrors > 0) {
                    msg += ` <span class="text-warning">(${totalErrors} ${Joomla.Text._('JBS_ADM_MIGRATION_ERRORS')})</span>`;
                }
                statusEl.innerHTML = msg;
                // Show error details if any
                if (allErrorDetails.length > 0) {
                    const detailHtml = allErrorDetails.map((d) => `<li class="small text-danger">${d}</li>`).join('');
                    statusEl.innerHTML += `<ul class="mt-2 mb-0">${detailHtml}</ul>`;
                }
                loadRecoveryCounts();
                loadMigrationCounts();
                if (pipelineMode && pipelineStepDone) {
                    const resolve = pipelineStepDone;
                    pipelineStepDone = null;
                    resolve();
                }
                return;
            }

            if (recoveryTotals[types[typeIndex]] === 0) {
                typeIndex += 1;
                recoverType();
                return;
            }

            statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_RECOVERING')} ${types[typeIndex]}...`;
            recoverBatch(types[typeIndex]);
        }

        function recoverBatch(type) {
            fetch(`index.php?option=com_proclaim&task=cwmadmin.recoverBareIdFoldersXHR&${token}=1&type=${type}&limit=10`)
                .then((r) => r.json())
                .then((data) => {
                    totalRecovered += data.recovered;
                    totalErrors += data.errors;
                    totalSkipped += data.skipped;
                    if (data.errorDetails && data.errorDetails.length > 0) {
                        allErrorDetails.push(...data.errorDetails);
                    }
                    updateBar();
                    statusEl.innerHTML = `${Joomla.Text._('JBS_ADM_RECOVERING')} ${types[typeIndex]}... <strong>${totalRecovered}</strong> / ${grandTotal}`;

                    // Stop if remaining folders exist but none were recovered in this batch —
                    // the remaining folders are all failing/skipping and would loop forever.
                    if (data.remaining > 0 && data.recovered > 0) {
                        recoverBatch(type);
                    } else {
                        typeIndex += 1;
                        recoverType();
                    }
                })
                .catch(() => {
                    activeOperation = null;
                    setOperationRunning(false);
                    statusEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_MIGRATION_ERROR')}</span>`;
                    if (pipelineMode && pipelineStepDone) {
                        const resolve = pipelineStepDone;
                        pipelineStepDone = null;
                        resolve();
                    }
                });
        }

        recoverType();
    });

    // ---- Legacy Files Report ----
    document.getElementById('btn-scan-legacy').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${Joomla.Text._('JBS_ADM_SCANNING')}`;
        const resultsEl = document.getElementById('legacy-results');

        fetch(`index.php?option=com_proclaim&task=cwmadmin.getLegacyFolderReportXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                btn.disabled = false;
                btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_SCAN_LEGACY')}`;
                resultsEl.style.display = 'block';

                if (data.total_files === 0) {
                    resultsEl.innerHTML = `<div class="alert alert-success"><i class="icon-checkmark me-1" aria-hidden="true"></i>${Joomla.Text._('JBS_ADM_LEGACY_NO_FILES')}</div>`;
                    return;
                }

                const summary = Joomla.Text._('JBS_ADM_LEGACY_FILES_FOUND')
                    .replace('%s', data.total_files)
                    .replace('%s', formatBytes(data.total_size));

                let html = `<div class="alert alert-warning"><i class="icon-warning me-1" aria-hidden="true"></i>${summary}</div>`;
                html += '<div style="max-height: 400px; overflow-y: auto;">';
                html += `<table class="table table-sm table-striped">
          <thead><tr>
            <th><input type="checkbox" id="select-all-legacy" aria-label="${Joomla.Text._('JBS_ADM_SELECT_ALL')}"></th>
            <th>${Joomla.Text._('JBS_ADM_FOLDER')}</th><th>${Joomla.Text._('JBS_ADM_FILES')}</th><th>${Joomla.Text._('JBS_ADM_SIZE')}</th><th>${Joomla.Text._('JBS_ADM_FILENAMES')}</th>
          </tr></thead><tbody>`;

                data.folders.forEach((folder) => {
                    const names = folder.filenames.length <= 5
                        ? folder.filenames.join(', ')
                        : `${folder.filenames.slice(0, 5).join(', ')} ... +${folder.filenames.length - 5} more`;
                    html += `<tr>
            <td><input type="checkbox" class="legacy-checkbox" value="${folder.path}" aria-label="${folder.path}"></td>
            <td><small>${folder.path}</small></td>
            <td>${folder.files}</td>
            <td>${formatBytes(folder.size)}</td>
            <td><small class="text-muted">${names}</small></td>
          </tr>`;
                });

                html += '</tbody></table></div>';
                html += `<div class="mt-3 d-flex gap-2">
          <button type="button" class="btn btn-danger" id="btn-delete-legacy" style="display:none;">
            <i class="icon-trash" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_DELETE_SELECTED')}
          </button>
        </div>`;
                resultsEl.innerHTML = html;

                // Wire up select-all checkbox
                const selectAllLegacy = document.getElementById('select-all-legacy');
                if (selectAllLegacy) {
                    selectAllLegacy.addEventListener('change', function () {
                        document.querySelectorAll('.legacy-checkbox').forEach((cb) => { cb.checked = this.checked; });
                        updateLegacyDeleteBtn();
                    });
                }
                // Show/hide delete button based on selection
                document.querySelectorAll('.legacy-checkbox').forEach((cb) => {
                    cb.addEventListener('change', updateLegacyDeleteBtn);
                });

                function updateLegacyDeleteBtn() {
                    const checked = document.querySelectorAll('.legacy-checkbox:checked').length;
                    const delBtn = document.getElementById('btn-delete-legacy');
                    if (delBtn) delBtn.style.display = checked > 0 ? '' : 'none';
                }

                // Delete selected legacy folders
                const delLegacyBtn = document.getElementById('btn-delete-legacy');
                if (delLegacyBtn) {
                    delLegacyBtn.addEventListener('click', () => {
                        const selected = [];
                        document.querySelectorAll('.legacy-checkbox:checked').forEach((cb) => selected.push(cb.value));
                        if (selected.length === 0) return;

                        const message = Joomla.Text._('JBS_ADM_CONFIRM_DELETE_LEGACY').replace('%s', selected.length);

                        if (!confirm(message)) return;

                        delLegacyBtn.disabled = true;
                        delLegacyBtn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${Joomla.Text._('JBS_ADM_DELETING')}`;

                        const params = new URLSearchParams();
                        selected.forEach((path) => params.append('paths[]', path));

                        fetch(`index.php?option=com_proclaim&task=cwmadmin.deleteLegacyFoldersXHR&${token}=1`, {
                            method: 'POST',
                            body: params,
                        })
                            .then((r) => r.json())
                            .then((result) => {
                                delLegacyBtn.disabled = false;
                                delLegacyBtn.innerHTML = `<i class="icon-trash" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_DELETE_SELECTED')}`;

                                if (result.deleted > 0) {
                                    // Re-scan to refresh the list
                                    btn.click();
                                }
                            })
                            .catch(() => {
                                delLegacyBtn.disabled = false;
                                delLegacyBtn.innerHTML = `<i class="icon-trash" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_DELETE_SELECTED')}`;
                            });
                    });
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_SCAN_LEGACY')}`;
                resultsEl.style.display = 'block';
                resultsEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}</span>`;
            });
    });

    // ---- Clear Unresolvable Images ----
    const previewBtn = document.getElementById('btn-preview-unresolvable');
    const clearBtn = document.getElementById('btn-clear-unresolvable');
    const downloadLogBtn = document.getElementById('btn-download-cleared-log');
    const previewEl = document.getElementById('unresolvable-preview');
    const unresStatusEl = document.getElementById('unresolvable-status');

    if (previewBtn) {
        previewBtn.addEventListener('click', () => {
            previewBtn.disabled = true;
            previewBtn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${Joomla.Text._('JBS_ADM_SCANNING')}`;
            if (previewEl) previewEl.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';
            if (unresStatusEl) unresStatusEl.style.display = 'none';

            fetch(`index.php?option=com_proclaim&task=cwmadmin.getUnresolvableCountXHR&${token}=1`)
                .then((r) => r.json())
                .then((data) => {
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_PREVIEW_UNRESOLVABLE')}`;

                    if (!data.count || data.count === 0) {
                        if (previewEl) {
                            previewEl.style.display = 'block';
                            previewEl.innerHTML = `<div class="alert alert-success"><i class="icon-checkmark me-1" aria-hidden="true"></i>${Joomla.Text._('JBS_ADM_NO_UNRESOLVABLE')}</div>`;
                        }
                        return;
                    }

                    // Show preview table
                    const summary = Joomla.Text._('JBS_ADM_UNRESOLVABLE_FOUND').replace('%s', data.count);
                    let html = `<div class="alert alert-warning"><i class="icon-warning me-1" aria-hidden="true"></i>${summary}</div>`;
                    html += '<div style="max-height: 300px; overflow-y: auto;">';
                    html += `<table class="table table-sm table-striped mb-0">
            <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${Joomla.Text._('JBS_ADM_MISSING_PATH')}</th></tr></thead><tbody>`;
                    data.records.forEach((r) => {
                        html += `<tr><td>${r.type}</td><td>${r.id}</td><td><small>${r.title}</small></td><td><small class="text-danger">${r.path}</small></td></tr>`;
                    });
                    html += '</tbody></table></div>';

                    if (previewEl) {
                        previewEl.innerHTML = html;
                        previewEl.style.display = 'block';
                    }

                    // Show clear button
                    if (clearBtn) {
                        clearBtn.style.display = '';
                        clearBtn.dataset.count = data.count;
                    }
                })
                .catch((err) => {
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_PREVIEW_UNRESOLVABLE')}`;
                    if (previewEl) {
                        previewEl.style.display = 'block';
                        previewEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}: ${err.message || err}</span>`;
                    }
                });
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            const count = clearBtn.dataset.count || '?';
            const message = Joomla.Text._('JBS_ADM_CONFIRM_CLEAR_UNRESOLVABLE').replace('%s', count);

            if (!confirm(message)) {
                return;
            }

            clearBtn.disabled = true;
            clearBtn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${Joomla.Text._('JBS_ADM_CLEARING')}`;

            fetch(`index.php?option=com_proclaim&task=cwmadmin.clearUnresolvableXHR&${token}=1`)
                .then((r) => r.json())
                .then((data) => {
                    clearBtn.style.display = 'none';

                    if (data.success) {
                        const msg = Joomla.Text._('JBS_ADM_CLEAR_COMPLETE').replace('%s', data.cleared);
                        if (unresStatusEl) {
                            unresStatusEl.style.display = 'block';
                            unresStatusEl.innerHTML = `<div class="alert alert-success"><i class="icon-checkmark me-1" aria-hidden="true"></i>${msg}</div>`;
                        }
                        // Show download log button
                        if (downloadLogBtn) downloadLogBtn.style.display = '';
                        // Refresh migration counts
                        loadMigrationCounts();
                    } else {
                        if (unresStatusEl) {
                            unresStatusEl.style.display = 'block';
                            unresStatusEl.innerHTML = `<span class="text-danger">${data.error || Joomla.Text._('JBS_ADM_ERROR_LOADING')}</span>`;
                        }
                        clearBtn.disabled = false;
                        clearBtn.style.display = '';
                        clearBtn.innerHTML = `<i class="icon-trash" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_CLEAR_UNRESOLVABLE_BTN')}`;
                    }
                })
                .catch((err) => {
                    clearBtn.disabled = false;
                    clearBtn.innerHTML = `<i class="icon-trash" aria-hidden="true"></i> ${Joomla.Text._('JBS_ADM_CLEAR_UNRESOLVABLE_BTN')}`;
                    if (unresStatusEl) {
                        unresStatusEl.style.display = 'block';
                        unresStatusEl.innerHTML = `<span class="text-danger">${Joomla.Text._('JBS_ADM_ERROR_LOADING')}: ${err.message || err}</span>`;
                    }
                });
        });
    }

    // Show download log button if a log file already exists (check via preview)
    if (downloadLogBtn) {
        fetch(`index.php?option=com_proclaim&task=cwmadmin.downloadClearedLogXHR&${token}=1`, { method: 'HEAD' })
            .then((r) => {
                if (r.ok && r.headers.get('content-type')?.includes('text/csv')) {
                    downloadLogBtn.style.display = '';
                }
            })
            .catch(() => {});
    }

    // ---- Pipeline ----
    function setPipelineBadge(stepId, state) {
        const badge = document.querySelector(`[data-pipeline-badge="${stepId}"]`);
        if (!badge) return;
        if (state === 'clear') {
            badge.style.display = 'none';
            return;
        }
        badge.style.display = '';
        const stateClasses = {
            pending: 'bg-secondary',
            running: 'bg-primary',
            done: 'bg-success',
            skipped: 'bg-info text-dark',
            error: 'bg-danger',
        };
        badge.className = `badge ms-auto ${stateClasses[state] || 'bg-secondary'}`;
        const pipelineKeys = {
            running: 'JBS_ADM_PIPELINE_RUNNING', done: 'JBS_ADM_PIPELINE_DONE', skipped: 'JBS_ADM_PIPELINE_SKIPPED', error: 'JBS_ADM_PIPELINE_ERROR',
        };
        badge.textContent = Joomla.Text._(pipelineKeys[state] || '') || state;
    }

    function setPipelineStatus(text) {
        const el = document.getElementById('pipeline-status-text');
        if (el) el.textContent = text;
    }

    function setPipelineProgress(pct, done = false) {
        const wrap = document.getElementById('pipeline-progress-wrap');
        const bar = document.getElementById('pipeline-progress-bar');
        if (!wrap || !bar) return;
        wrap.style.display = 'block';
        bar.style.width = `${pct}%`;
        bar.setAttribute('aria-valuenow', pct);
        if (done) {
            bar.classList.remove('progress-bar-striped', 'progress-bar-animated');
        } else {
            bar.classList.add('progress-bar-striped', 'progress-bar-animated');
        }
    }

    function runStepAsync(btnId) {
        return new Promise((resolve) => {
            pipelineStepDone = resolve;
            const btn = document.getElementById(btnId);
            if (btn && !btn.disabled) {
                btn.click();
            } else {
                // Button disabled — nothing to do, resolve immediately
                pipelineStepDone = null;
                resolve();
            }
        });
    }

    const runPipeline = async () => {
        pipelineMode = true;
        pipelineCancelled = false;
        const runBtn = document.getElementById('btn-run-pipeline');
        const cancelBtn = document.getElementById('btn-cancel-pipeline');
        if (runBtn) runBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = '';
        if (cancelBtn) cancelBtn.disabled = false;

        ['migrate', 'recover', 'webp'].forEach((s) => setPipelineBadge(s, 'clear'));
        setPipelineStatus('');
        setPipelineProgress(0);

        try {
            // Step 1: Image Migration
            setPipelineBadge('migrate', 'running');
            setPipelineStatus(Joomla.Text._('JBS_ADM_PIPELINE_RUNNING'));
            await loadMigrationCounts();
            if (migrationTotals.total > 0) {
                await runStepAsync('btn-start-migration');
                setPipelineBadge('migrate', 'done');
            } else {
                setPipelineBadge('migrate', 'skipped');
            }
            setPipelineProgress(35);

            if (pipelineCancelled) throw new Error('cancelled');

            // Step 2: Recover Bare-ID Folders
            setPipelineBadge('recover', 'running');
            await loadRecoveryCounts();
            if (recoveryTotals.total > 0) {
                await runStepAsync('btn-start-recovery');
                setPipelineBadge('recover', 'done');
            } else {
                setPipelineBadge('recover', 'skipped');
            }
            setPipelineProgress(70);

            if (pipelineCancelled) throw new Error('cancelled');

            // Step 3: WebP Generation
            setPipelineBadge('webp', 'running');
            await loadWebPCounts();
            if (webpTotals.total > 0) {
                await runStepAsync('btn-start-webp');
                setPipelineBadge('webp', 'done');
            } else {
                setPipelineBadge('webp', 'skipped');
            }
            setPipelineProgress(100, true);

            setPipelineStatus(Joomla.Text._('JBS_ADM_PIPELINE_COMPLETE'));
        } catch (err) {
            if (err.message === 'cancelled') {
                setPipelineStatus(Joomla.Text._('JBS_ADM_PIPELINE_CANCELLED'));
            } else {
                setPipelineStatus(`${Joomla.Text._('JBS_ADM_PIPELINE_ERROR')}: ${err.message || err}`);
            }
        } finally {
            pipelineMode = false;
            pipelineStepDone = null;
            if (runBtn) runBtn.style.display = '';
            if (cancelBtn) cancelBtn.style.display = 'none';
        }
    };

    const runPipelineBtn = document.getElementById('btn-run-pipeline');
    if (runPipelineBtn) {
        runPipelineBtn.addEventListener('click', runPipeline);
    }

    const cancelPipelineBtn = document.getElementById('btn-cancel-pipeline');
    if (cancelPipelineBtn) {
        cancelPipelineBtn.addEventListener('click', () => {
            pipelineCancelled = true;
            setPipelineStatus(Joomla.Text._('JBS_ADM_PIPELINE_CANCELLING'));
            cancelPipelineBtn.disabled = true;
        });
    }

    // ---- Cleanup Pipeline ----
    let cleanupPipelineCancelled = false;
    let cleanupPipelineConfirmResolve = null;

    function setCleanupBadge(stepId, state) {
        const badge = document.querySelector(`[data-cleanup-badge="${stepId}"]`);
        if (!badge) return;
        if (state === 'clear') {
            badge.style.display = 'none';
            return;
        }
        badge.style.display = '';
        const stateClasses = {
            running: 'bg-primary',
            done: 'bg-success',
            skipped: 'bg-info text-dark',
            error: 'bg-danger',
        };
        badge.className = `badge ms-auto ${stateClasses[state] || 'bg-secondary'}`;
        const cleanupKeys = {
            running: 'JBS_ADM_CLEANUP_PIPELINE_RUNNING', done: 'JBS_ADM_CLEANUP_PIPELINE_DONE', skipped: 'JBS_ADM_CLEANUP_PIPELINE_SKIPPED', error: 'JBS_ADM_CLEANUP_PIPELINE_ERROR',
        };
        badge.textContent = Joomla.Text._(cleanupKeys[state] || '') || state;
    }

    function setCleanupStatus(text) {
        const el = document.getElementById('cleanup-pipeline-status-text');
        if (el) el.textContent = text;
    }

    function setCleanupProgress(pct, done = false) {
        const wrap = document.getElementById('cleanup-pipeline-progress-wrap');
        const bar = document.getElementById('cleanup-pipeline-progress-bar');
        if (!wrap || !bar) return;
        wrap.style.display = 'block';
        bar.style.width = `${pct}%`;
        bar.setAttribute('aria-valuenow', pct);
        if (done) {
            bar.classList.remove('progress-bar-striped', 'progress-bar-animated');
        } else {
            bar.classList.add('progress-bar-striped', 'progress-bar-animated');
        }
    }

    function showCleanupConfirm(count, messageKey) {
        return new Promise((resolve) => {
            cleanupPipelineConfirmResolve = resolve;
            const confirmDiv = document.getElementById('cleanup-pipeline-confirm');
            const textEl = document.getElementById('cleanup-pipeline-confirm-text');
            if (!confirmDiv || !textEl) { resolve(false); return; }
            textEl.textContent = Joomla.Text._(messageKey).replace('%s', count);
            confirmDiv.style.display = '';
        });
    }

    const cleanupConfirmDeleteBtn = document.getElementById('btn-cleanup-confirm-delete');
    const cleanupConfirmSkipBtn = document.getElementById('btn-cleanup-confirm-skip');
    const cleanupConfirmDiv = document.getElementById('cleanup-pipeline-confirm');

    function resolveCleanupConfirm(value) {
        if (cleanupConfirmDiv) cleanupConfirmDiv.style.display = 'none';
        if (cleanupPipelineConfirmResolve) {
            const resolve = cleanupPipelineConfirmResolve;
            cleanupPipelineConfirmResolve = null;
            resolve(value);
        }
    }

    if (cleanupConfirmDeleteBtn) {
        cleanupConfirmDeleteBtn.addEventListener('click', () => resolveCleanupConfirm(true));
    }
    if (cleanupConfirmSkipBtn) {
        cleanupConfirmSkipBtn.addEventListener('click', () => resolveCleanupConfirm(false));
    }

    const runCleanupPipeline = async () => {
        cleanupPipelineCancelled = false;
        const runBtn = document.getElementById('btn-run-cleanup-pipeline');
        const cancelBtn = document.getElementById('btn-cancel-cleanup-pipeline');
        if (runBtn) runBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = '';
        if (cancelBtn) cancelBtn.disabled = false;

        ['unresolvable', 'legacy', 'orphans'].forEach((s) => setCleanupBadge(s, 'clear'));
        setCleanupStatus('');
        setCleanupProgress(0);

        try {
            // Step 1: Clear Unresolvable References (auto — DB only, no file deletion)
            setCleanupBadge('unresolvable', 'running');
            setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_RUNNING'));

            const unresData = await fetch(
                `index.php?option=com_proclaim&task=cwmadmin.getUnresolvableCountXHR&${token}=1`,
            ).then((r) => r.json());

            if (unresData.count > 0) {
                const clearResult = await fetch(
                    `index.php?option=com_proclaim&task=cwmadmin.clearUnresolvableXHR&${token}=1`,
                ).then((r) => r.json());
                if (clearResult.success) {
                    setCleanupBadge('unresolvable', 'done');
                    setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_CLEARED').replace('%s', clearResult.cleared));
                    loadMigrationCounts(); // refresh dependent counts
                } else {
                    setCleanupBadge('unresolvable', 'error');
                }
            } else {
                setCleanupBadge('unresolvable', 'skipped');
            }
            setCleanupProgress(35);

            if (cleanupPipelineCancelled) throw new Error('cancelled');

            // Step 2: Legacy Files (scan → inline confirm → delete all)
            setCleanupBadge('legacy', 'running');
            setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_RUNNING'));

            const legacyData = await fetch(
                `index.php?option=com_proclaim&task=cwmadmin.getLegacyFolderReportXHR&${token}=1`,
            ).then((r) => r.json());

            if (legacyData.total_files > 0) {
                const shouldDelete = await showCleanupConfirm(legacyData.total_files, 'JBS_ADM_CLEANUP_PIPELINE_CONFIRM_LEGACY');
                if (shouldDelete) {
                    const paths = (legacyData.folders || []).map((f) => f.path);
                    const params = new URLSearchParams();
                    paths.forEach((p) => params.append('paths[]', p));
                    activeOperation = 'legacy';
                    setOperationRunning(true);
                    const result = await fetch(
                        `index.php?option=com_proclaim&task=cwmadmin.deleteLegacyFoldersXHR&${token}=1`,
                        { method: 'POST', body: params },
                    ).then((r) => r.json());
                    activeOperation = null;
                    setOperationRunning(false);
                    setCleanupBadge('legacy', result.deleted > 0 ? 'done' : 'error');
                } else {
                    setCleanupBadge('legacy', 'skipped');
                }
            } else {
                setCleanupBadge('legacy', 'skipped');
            }
            setCleanupProgress(70);

            if (cleanupPipelineCancelled) throw new Error('cancelled');

            // Step 3: Orphan Folders (scan → inline confirm → delete all)
            setCleanupBadge('orphans', 'running');
            setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_RUNNING'));

            const orphanData = await fetch(
                `index.php?option=com_proclaim&task=cwmadmin.getOrphanedFoldersXHR&${token}=1`,
            ).then((r) => r.json());

            const orphanCount = orphanData.totals ? orphanData.totals.folders : 0;

            if (orphanCount > 0) {
                const shouldDelete = await showCleanupConfirm(orphanCount, 'JBS_ADM_CLEANUP_PIPELINE_CONFIRM_ORPHANS');
                if (shouldDelete) {
                    const paths = [];
                    if (orphanData.orphans) {
                        ['studies', 'teachers', 'series'].forEach((type) => {
                            (orphanData.orphans[type] || []).forEach((o) => paths.push(o.path));
                        });
                    }
                    const params = new URLSearchParams();
                    paths.forEach((p) => params.append('paths[]', p));
                    activeOperation = 'orphan';
                    setOperationRunning(true);
                    await fetch(
                        `index.php?option=com_proclaim&task=cwmadmin.deleteOrphanedFoldersXHR&${token}=1`,
                        { method: 'POST', body: params },
                    ).then((r) => r.json());
                    activeOperation = null;
                    setOperationRunning(false);
                    setCleanupBadge('orphans', 'done');
                } else {
                    setCleanupBadge('orphans', 'skipped');
                }
            } else {
                setCleanupBadge('orphans', 'skipped');
            }
            setCleanupProgress(100, true);
            setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_COMPLETE'));
        } catch (err) {
            if (activeOperation) {
                activeOperation = null;
                setOperationRunning(false);
            }
            // Hide any pending confirm if we're aborting
            resolveCleanupConfirm(false);
            if (err.message === 'cancelled') {
                setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_CANCELLED'));
            } else {
                setCleanupStatus(`${Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_ERROR')}: ${err.message || err}`);
            }
        } finally {
            cleanupPipelineConfirmResolve = null;
            if (runBtn) runBtn.style.display = '';
            if (cancelBtn) cancelBtn.style.display = 'none';
        }
    };

    const runCleanupPipelineBtn = document.getElementById('btn-run-cleanup-pipeline');
    if (runCleanupPipelineBtn) {
        runCleanupPipelineBtn.addEventListener('click', runCleanupPipeline);
    }

    const cancelCleanupPipelineBtn = document.getElementById('btn-cancel-cleanup-pipeline');
    if (cancelCleanupPipelineBtn) {
        cancelCleanupPipelineBtn.addEventListener('click', () => {
            cleanupPipelineCancelled = true;
            setCleanupStatus(Joomla.Text._('JBS_ADM_CLEANUP_PIPELINE_CANCELLING'));
            cancelCleanupPipelineBtn.disabled = true;
        });
    }

    // ---- Lazy init: defer count loading until imagetools tab is first shown ----
    let imagetoolsInitDone = false;
    function initImagetoolsCounts() {
        if (imagetoolsInitDone) return;
        imagetoolsInitDone = true;
        loadMigrationCounts();
        loadWebPCounts();
        loadThumbRegenCounts();
        loadRecoveryCounts();
    }
    document.addEventListener('shown.bs.tab', (e) => {
        if (e.target.dataset.bsTarget === '#imagetools') initImagetoolsCounts();
    });
    // Handle hash-recall: tab may already be active on page load
    if (document.getElementById('imagetools')?.classList.contains('active')) {
        initImagetoolsCounts();
    }

    // ---- Utility ----
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / k ** i).toFixed(2))} ${sizes[i]}`;
    }
});
