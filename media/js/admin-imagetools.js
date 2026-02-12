(function () {
  'use strict';

  /**
   * @package    Proclaim.Admin
   * @copyright  (C) 2026 CWM Team All rights reserved
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   *
   * Image Tools tab: migration, orphan cleanup, and WebP generation.
   *
   * Reads configuration from #imagetools-config data attributes.
   */
  document.addEventListener('DOMContentLoaded', () => {
    const config = document.getElementById('imagetools-config');
    if (!config) return;

    const token = config.dataset.token;
    const strings = JSON.parse(config.dataset.strings);

    // Store counts for progress calculation
    let migrationTotals = {studies: 0, teachers: 0, series: 0, total: 0};
    let webpTotals = {studies: 0, teachers: 0, series: 0, total: 0};

    // ---- Navigation guard ----
    let activeOperation = null; // null | 'migration' | 'webp' | 'orphan'

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
      document.querySelectorAll('#toolbar button, #toolbar a, .subhead button').forEach(el => {
        el.toggleAttribute('disabled', running);
        if (running) el.style.pointerEvents = 'none';
        else el.style.pointerEvents = '';
      });
      // Disable tab switching
      document.querySelectorAll('[data-bs-toggle="tab"], .nav-link').forEach(el => {
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
      // Allow clicks inside our own imagetools section
      if (target.closest('#imagetools, #imagetools-row2')) return;
      e.preventDefault();
      e.stopPropagation();
    }
    document.addEventListener('click', blockNavigation, true);

    // ---- Image Migration ----
    loadMigrationCounts();

    function loadMigrationCounts() {
      fetch(`index.php?option=com_proclaim&task=cwmadmin.getMigrationCountsXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          migrationTotals = data;

          if (data.total === 0) {
            document.getElementById('migration-counts').innerHTML =
              `<div class="alert alert-success mb-0"><i class="icon-checkmark me-1" aria-hidden="true"></i>${strings.migrationAllDone}</div>`;
          } else {
            const items = [];
            if (data.studies > 0) items.push(`<li>${strings.migrationCountMessages.replace('%s', data.studies)}</li>`);
            if (data.teachers > 0) items.push(`<li>${strings.migrationCountTeachers.replace('%s', data.teachers)}</li>`);
            if (data.series > 0) items.push(`<li>${strings.migrationCountSeries.replace('%s', data.series)}</li>`);
            document.getElementById('migration-counts').innerHTML =
              `<ul class="list-unstyled mb-0">${items.join('')}</ul>
             <div class="mt-2 fw-bold">${strings.total}: ${data.total}</div>`;
          }

          document.getElementById('btn-start-migration').disabled = (data.total === 0);
        })
        .catch((err) => {
          document.getElementById('migration-counts').innerHTML =
            `<span class="text-danger">${strings.errorLoading}: ${err.message || err}</span>`;
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
        const summary = strings.relocatedFound.replace('%s', relocatedDetails.length);
        let html = `<div class="alert alert-info mt-3">
        <h5><i class="icon-checkmark me-1" aria-hidden="true"></i>${summary}</h5>
        <p class="small mb-2">${strings.relocatedDesc}</p>
        <div style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${strings.expectedPath}</th><th>${strings.foundAtPath}</th></tr></thead>
          <tbody>`;
        relocatedDetails.forEach(r => {
          html += `<tr><td>${r.type}</td><td>${r.id}</td><td><small>${r.title}</small></td><td><small class="text-muted">${r.originalPath}</small></td><td><small class="text-success">${r.foundAt}</small></td></tr>`;
        });
        html += '</tbody></table></div></div>';
        // Prepend before error report
        reportEl.innerHTML = html + reportEl.innerHTML;
        reportEl.style.display = 'block';
      }

      function showErrorReport() {
        if (!reportEl || errorDetails.length === 0) return;
        let html = `<div class="alert alert-warning mt-3">
        <h5>${strings.missingFiles} (${errorDetails.length})</h5>
        <p class="small mb-2">${strings.missingFilesDesc}</p>
        <div style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${strings.missingPath}</th></tr></thead>
          <tbody>`;
        errorDetails.forEach(e => {
          html += `<tr><td>${e.type}</td><td>${e.id}</td><td><small>${e.title}</small></td><td><small class="text-danger">${e.path}</small></td></tr>`;
        });
        html += '</tbody></table></div></div>';
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
          let msg = `<span class="text-success">${strings.migrationComplete} ${totalMigrated} ${strings.recordsMigrated}</span>`;
          if (totalRelocated > 0) {
            msg += ` <span class="text-info">(${totalRelocated} ${strings.relocated})</span>`;
          }
          if (totalErrors > 0) {
            msg += ` <span class="text-warning">(${totalErrors} ${strings.migrationErrors})</span>`;
          }
          statusEl.innerHTML = msg;
          showRelocatedReport();
          showErrorReport();
          loadWebPCounts();

          // Show a "Done" button — user must acknowledge before Start Migration re-enables
          const startBtn = document.getElementById('btn-start-migration');
          const doneBtn = document.createElement('button');
          doneBtn.type = 'button';
          doneBtn.className = 'btn btn-success mt-3';
          doneBtn.innerHTML = `<i class="icon-checkmark" aria-hidden="true"></i> ${strings.migrationDone}`;
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
          typeIndex++;
          migrateType();
          return;
        }

        statusEl.innerHTML = `${strings.migrating} ${types[typeIndex]}... <strong>0</strong> / ${migrationTotals[types[typeIndex]]}`;
        migrateBatch(types[typeIndex], 0);
      }

      function migrateBatch(type, typeProcessed) {
        // Collect failed IDs for this type so PHP can skip them in the query
        const typeFailedIds = [];
        failedIds.forEach(key => {
          if (key.startsWith(`${type}-`)) {
            typeFailedIds.push(key.substring(type.length + 1));
          }
        });
        const excludeParam = typeFailedIds.length > 0 ? `&exclude=${typeFailedIds.join(',')}` : '';

        fetch(`index.php?option=com_proclaim&task=cwmadmin.getMigrationBatchXHR&${token}=1&type=${type}&limit=5${excludeParam}`)
          .then(r => r.json())
          .then(data => {
            // No more records to process for this type (PHP already excluded failed IDs)
            if (data.records.length === 0) {
              typeIndex++;
              migrateType();
              return;
            }

            let batchDone = 0;
            const batchTotal = data.records.length;
            const typeTotal = migrationTotals[type];

            const promises = data.records.map(record => {
              const recordTitle = record.studytitle || record.teachername || record.title || '';

              return fetch(`index.php?option=com_proclaim&task=cwmadmin.migrateRecordXHR&${token}=1&type=${type}&id=${record.id}`)
                .then(r => r.json())
                .then(result => {
                  if (result.success) {
                    totalMigrated++;
                    if (result.relocated) {
                      totalRelocated++;
                      relocatedDetails.push({
                        type,
                        id: record.id,
                        title: recordTitle,
                        originalPath: result.originalPath || '',
                        foundAt: result.foundAt || '',
                        newPath: result.newPath || ''
                      });
                    }
                  } else {
                    failedIds.add(`${type}-${record.id}`);
                    totalErrors++;
                    errorDetails.push({
                      type,
                      id: record.id,
                      title: recordTitle,
                      path: result.missingPath || record.image_path || '',
                      error: result.error || 'Unknown error'
                    });
                  }
                })
                .catch(() => {
                  failedIds.add(`${type}-${record.id}`);
                  totalErrors++;
                  errorDetails.push({
                    type,
                    id: record.id,
                    title: recordTitle,
                    path: record.image_path || '',
                    error: 'Network error'
                  });
                })
                .finally(() => {
                  batchDone++;
                  const displayed = Math.min(typeProcessed + batchDone, typeTotal);
                  statusEl.innerHTML = `${strings.migrating} ${types[typeIndex]}... <strong>${displayed}</strong> / ${typeTotal}`;
                  updateBar();
                });
            });

            Promise.all(promises).then(() => {
              const newTypeProcessed = typeProcessed + batchTotal;

              // Move to next type when: no remaining, or counter exceeds total (safety cap)
              if (data.remaining <= 0 || newTypeProcessed >= typeTotal) {
                typeIndex++;
                migrateType();
              } else {
                migrateBatch(type, newTypeProcessed);
              }
            });
          })
          .catch(() => {
            activeOperation = null;
            setOperationRunning(false);
            statusEl.innerHTML = `<span class="text-danger">${strings.migrationError}</span>`;
            showErrorReport();
          });
      }

      migrateType();
    });

    // ---- Orphan Cleanup ----
    document.getElementById('btn-scan-orphans').addEventListener('click', function () {
      const btn = this;

      // Warn if migration or unresolvable cleanup hasn't been done yet
      if (migrationTotals.total > 0) {
        // eslint-disable-next-line no-restricted-globals
        if (!confirm(strings.orphanMigrationWarning)) {
          return;
        }
      }

      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${strings.scanning}`;

      fetch(`index.php?option=com_proclaim&task=cwmadmin.getOrphanedFoldersXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.scanOrphans}`;
          document.getElementById('orphan-results').style.display = 'block';

          // Update step indicator
          const stepEl = document.getElementById('orphan-step-indicator');
          if (stepEl) stepEl.textContent = strings.orphanStep2;

          document.getElementById('orphan-summary').innerHTML =
            `${strings.found} <strong>${data.totals.folders}</strong> ${strings.orphanFolders} (${data.totals.size_formatted})`;

          if (data.totals.folders > 0) {
            let tableHtml = `<table class="table table-sm table-striped">
            <thead><tr>
              <th><input type="checkbox" id="select-all-orphans" aria-label="${strings.selectAll}"></th>
              <th>${strings.folder}</th><th>${strings.files}</th><th>${strings.size}</th>
            </tr></thead><tbody>`;

            ['studies', 'teachers', 'series'].forEach(type => {
              if (data.orphans[type]) {
                data.orphans[type].forEach(orphan => {
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
              document.querySelectorAll('.orphan-checkbox').forEach(cb => { cb.checked = this.checked; });
            });
          } else {
            document.getElementById('orphan-list').innerHTML =
              `<p class="text-success">${strings.noOrphans}</p>`;
            document.getElementById('btn-delete-orphans').style.display = 'none';
          }
        })
        .catch(() => {
          btn.disabled = false;
          btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.scanOrphans}`;
        });
    });

    document.getElementById('btn-delete-orphans').addEventListener('click', function () {
      const selected = [];
      document.querySelectorAll('.orphan-checkbox:checked').forEach(cb => selected.push(cb.value));
      if (selected.length === 0) return;

      const btn = this;
      btn.disabled = true;
      activeOperation = 'orphan';
      setOperationRunning(true);
      const params = new URLSearchParams();
      selected.forEach(path => params.append('paths[]', path));

      fetch(`index.php?option=com_proclaim&task=cwmadmin.deleteOrphanedFoldersXHR&${token}=1`, {
        method: 'POST',
        body: params
      })
        .then(r => r.json())
        .then(data => {
          activeOperation = null;
          setOperationRunning(false);
          btn.disabled = false;
          document.getElementById('btn-scan-orphans').click();
        })
        .catch(() => { activeOperation = null; setOperationRunning(false); btn.disabled = false; });
    });

    // ---- WebP Generation ----
    loadWebPCounts();

    function loadWebPCounts() {
      fetch(`index.php?option=com_proclaim&task=cwmadmin.getWebPCountsXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          webpTotals = data;

          if (data.total === 0) {
            document.getElementById('webp-counts').innerHTML =
              `<div class="alert alert-success mb-0"><i class="icon-checkmark me-1" aria-hidden="true"></i>${strings.webpAllDone}</div>`;
            document.getElementById('btn-start-webp').disabled = true;
          } else {
            const items = [];
            if (data.studies > 0) items.push(`<li>${strings.webpCountMessages.replace('%s', data.studies)}</li>`);
            if (data.teachers > 0) items.push(`<li>${strings.webpCountTeachers.replace('%s', data.teachers)}</li>`);
            if (data.series > 0) items.push(`<li>${strings.webpCountSeries.replace('%s', data.series)}</li>`);
            document.getElementById('webp-counts').innerHTML =
              `<ul class="list-unstyled mb-0">${items.join('')}</ul>
             <div class="mt-2 fw-bold">${strings.total}: ${data.total}</div>`;
            document.getElementById('btn-start-webp').disabled = false;
          }
        })
        .catch(() => {
          document.getElementById('webp-counts').innerHTML =
            `<span class="text-danger">${strings.errorLoading}</span>`;
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
          let msg = `<span class="text-success">${strings.webpComplete} ${totalConverted} ${strings.imagesConverted}</span>`;
          if (totalErrors > 0) {
            msg += ` <span class="text-warning">(${totalErrors} ${strings.migrationErrors})</span>`;
          }
          statusEl.innerHTML = msg;
          loadWebPCounts();
          return;
        }

        // Skip types with zero count
        if (webpTotals[types[typeIndex]] === 0) {
          typeIndex++;
          convertType();
          return;
        }

        statusEl.innerHTML = `${strings.converting} ${types[typeIndex]}... <strong>0</strong> / ${webpTotals[types[typeIndex]]}`;
        convertBatch(types[typeIndex], 0);
      }

      function convertBatch(type, typeConverted) {
        fetch(`index.php?option=com_proclaim&task=cwmadmin.migrateToWebPXHR&${token}=1&type=${type}&limit=10`)
          .then(r => r.json())
          .then(data => {
            totalConverted += data.converted;
            totalErrors += (data.errors || 0);
            const newTypeConverted = typeConverted + data.converted + (data.errors || 0);
            const typeTotal = webpTotals[type];
            const displayed = Math.min(newTypeConverted, typeTotal);
            statusEl.innerHTML = `${strings.converting} ${types[typeIndex]}... <strong>${displayed}</strong> / ${typeTotal}`;
            updateBar();

            if (data.remaining > 0 && newTypeConverted < typeTotal) {
              convertBatch(type, newTypeConverted);
            } else {
              typeIndex++;
              convertType();
            }
          })
          .catch(() => {
            activeOperation = null;
            setOperationRunning(false);
            statusEl.innerHTML = `<span class="text-danger">${strings.webpError}</span>`;
          });
      }

      convertType();
    });

    // ---- Legacy Files Report ----
    document.getElementById('btn-scan-legacy').addEventListener('click', function () {
      const btn = this;
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${strings.scanning}`;
      const resultsEl = document.getElementById('legacy-results');

      fetch(`index.php?option=com_proclaim&task=cwmadmin.getLegacyFolderReportXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.scanLegacy}`;
          resultsEl.style.display = 'block';

          if (data.total_files === 0) {
            resultsEl.innerHTML = `<div class="alert alert-success"><i class="icon-checkmark me-1" aria-hidden="true"></i>${strings.legacyNoFiles}</div>`;
            return;
          }

          const summary = strings.legacyFilesFound
            .replace('%s', data.total_files)
            .replace('%s', formatBytes(data.total_size));

          let html = `<div class="alert alert-warning"><i class="icon-warning me-1" aria-hidden="true"></i>${summary}</div>`;
          html += '<div style="max-height: 400px; overflow-y: auto;">';
          html += `<table class="table table-sm table-striped">
          <thead><tr><th>${strings.folder}</th><th>${strings.files}</th><th>${strings.size}</th><th>${strings.filenames}</th></tr></thead><tbody>`;

          data.folders.forEach(folder => {
            const names = folder.filenames.length <= 5
              ? folder.filenames.join(', ')
              : folder.filenames.slice(0, 5).join(', ') + ` ... +${folder.filenames.length - 5} more`;
            html += `<tr>
            <td><small>${folder.path}</small></td>
            <td>${folder.files}</td>
            <td>${formatBytes(folder.size)}</td>
            <td><small class="text-muted">${names}</small></td>
          </tr>`;
          });

          html += '</tbody></table></div>';
          html += '<p class="small text-muted mt-2">These files can be removed manually after verifying the migration completed successfully.</p>';
          resultsEl.innerHTML = html;
        })
        .catch(() => {
          btn.disabled = false;
          btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.scanLegacy}`;
          resultsEl.style.display = 'block';
          resultsEl.innerHTML = `<span class="text-danger">${strings.errorLoading}</span>`;
        });
    });

    // ---- Clear Unresolvable Images ----
    const previewBtn      = document.getElementById('btn-preview-unresolvable');
    const clearBtn        = document.getElementById('btn-clear-unresolvable');
    const downloadLogBtn  = document.getElementById('btn-download-cleared-log');
    const previewEl       = document.getElementById('unresolvable-preview');
    const unresStatusEl   = document.getElementById('unresolvable-status');

    if (previewBtn) {
      previewBtn.addEventListener('click', function () {
        previewBtn.disabled = true;
        previewBtn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${strings.scanning}`;
        if (previewEl) previewEl.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'none';
        if (unresStatusEl) unresStatusEl.style.display = 'none';

        fetch(`index.php?option=com_proclaim&task=cwmadmin.getUnresolvableCountXHR&${token}=1`)
          .then(r => r.json())
          .then(data => {
            previewBtn.disabled = false;
            previewBtn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.previewUnresolvable}`;

            if (!data.count || data.count === 0) {
              if (previewEl) {
                previewEl.style.display = 'block';
                previewEl.innerHTML = `<div class="alert alert-success"><i class="icon-checkmark me-1" aria-hidden="true"></i>${strings.noUnresolvable}</div>`;
              }
              return;
            }

            // Show preview table
            const summary = strings.unresolvableFound.replace('%s', data.count);
            let html = `<div class="alert alert-warning"><i class="icon-warning me-1" aria-hidden="true"></i>${summary}</div>`;
            html += '<div style="max-height: 300px; overflow-y: auto;">';
            html += `<table class="table table-sm table-striped mb-0">
            <thead><tr><th>Type</th><th>ID</th><th>Title</th><th>${strings.missingPath}</th></tr></thead><tbody>`;
            data.records.forEach(r => {
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
          .catch(err => {
            previewBtn.disabled = false;
            previewBtn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.previewUnresolvable}`;
            if (previewEl) {
              previewEl.style.display = 'block';
              previewEl.innerHTML = `<span class="text-danger">${strings.errorLoading}: ${err.message || err}</span>`;
            }
          });
      });
    }

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        const count = clearBtn.dataset.count || '?';
        const message = strings.confirmClear.replace('%s', count);

        // eslint-disable-next-line no-restricted-globals
        if (!confirm(message)) {
          return;
        }

        clearBtn.disabled = true;
        clearBtn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${strings.clearing}`;

        fetch(`index.php?option=com_proclaim&task=cwmadmin.clearUnresolvableXHR&${token}=1`)
          .then(r => r.json())
          .then(data => {
            clearBtn.style.display = 'none';

            if (data.success) {
              const msg = strings.clearComplete.replace('%s', data.cleared);
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
                unresStatusEl.innerHTML = `<span class="text-danger">${data.error || strings.errorLoading}</span>`;
              }
              clearBtn.disabled = false;
              clearBtn.style.display = '';
              clearBtn.innerHTML = `<i class="icon-trash" aria-hidden="true"></i> ${strings.clearUnresolvable}`;
            }
          })
          .catch(err => {
            clearBtn.disabled = false;
            clearBtn.innerHTML = `<i class="icon-trash" aria-hidden="true"></i> ${strings.clearUnresolvable}`;
            if (unresStatusEl) {
              unresStatusEl.style.display = 'block';
              unresStatusEl.innerHTML = `<span class="text-danger">${strings.errorLoading}: ${err.message || err}</span>`;
            }
          });
      });
    }

    // Show download log button if a log file already exists (check via preview)
    if (downloadLogBtn) {
      fetch(`index.php?option=com_proclaim&task=cwmadmin.downloadClearedLogXHR&${token}=1`, { method: 'HEAD' })
        .then(r => {
          if (r.ok && r.headers.get('content-type')?.includes('text/csv')) {
            downloadLogBtn.style.display = '';
          }
        })
        .catch(() => {});
    }

    // ---- Utility ----
    function formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
  });

})();
