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

    // ---- Image Migration ----
    loadMigrationCounts();

    function loadMigrationCounts() {
      fetch(`index.php?option=com_proclaim&task=cwmadmin.getMigrationCountsXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          const html = `<ul class="list-unstyled">
          <li><strong>${strings.studies}:</strong> ${data.studies}</li>
          <li><strong>${strings.teachers}:</strong> ${data.teachers}</li>
          <li><strong>${strings.series}:</strong> ${data.series}</li>
          <li><strong>${strings.total}:</strong> ${data.total}</li>
        </ul>`;
          document.getElementById('migration-counts').innerHTML = html;
          document.getElementById('btn-start-migration').disabled = (data.total === 0);
        })
        .catch(() => {
          document.getElementById('migration-counts').innerHTML =
            `<span class="text-danger">${strings.errorLoading}</span>`;
        });
    }

    document.getElementById('btn-start-migration').addEventListener('click', function () {
      this.disabled = true;
      document.getElementById('migration-progress').style.display = 'block';

      const types = ['studies', 'teachers', 'series'];
      let typeIndex = 0;
      let totalMigrated = 0;

      function migrateType() {
        if (typeIndex >= types.length) {
          document.getElementById('migration-status').innerHTML =
            `<span class="text-success">${strings.migrationComplete} ${totalMigrated} ${strings.recordsMigrated}</span>`;
          loadMigrationCounts();
          return;
        }
        document.getElementById('migration-status').textContent = `${strings.migrating} ${types[typeIndex]}...`;
        migrateBatch(types[typeIndex]);
      }

      function migrateBatch(type) {
        fetch(`index.php?option=com_proclaim&task=cwmadmin.getMigrationBatchXHR&${token}=1&type=${type}&limit=5`)
          .then(r => r.json())
          .then(data => {
            if (data.records.length === 0) {
              typeIndex++;
              migrateType();
              return;
            }
            const promises = data.records.map(record => {
              const params = new URLSearchParams();
              params.append('type', type);
              params.append('id', record.id);
              params.append('title', record.studytitle || record.teachername || record.title || '');
              params.append('old_path', record.image_path);
              return fetch(`index.php?option=com_proclaim&task=cwmadmin.migrateRecordXHR&${token}=1&${params}`)
                .then(r => r.json())
                .then(result => { if (result.success) totalMigrated++; });
            });
            Promise.all(promises).then(() => {
              document.querySelector('#migration-progress .progress-bar').style.width =
                `${((typeIndex + 1) / types.length) * 100}%`;
              if (data.remaining > 0) migrateBatch(type);
              else { typeIndex++; migrateType(); }
            });
          })
          .catch(() => {
            document.getElementById('migration-status').innerHTML =
              `<span class="text-danger">${strings.migrationError}</span>`;
          });
      }

      migrateType();
    });

    // ---- Orphan Cleanup ----
    document.getElementById('btn-scan-orphans').addEventListener('click', function () {
      const btn = this;
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${strings.scanning}`;

      fetch(`index.php?option=com_proclaim&task=cwmadmin.getOrphanedFoldersXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          btn.innerHTML = `<i class="icon-search" aria-hidden="true"></i> ${strings.scanOrphans}`;
          document.getElementById('orphan-results').style.display = 'block';
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
      const params = new URLSearchParams();
      selected.forEach(path => params.append('paths[]', path));

      fetch(`index.php?option=com_proclaim&task=cwmadmin.deleteOrphanedFoldersXHR&${token}=1`, {
        method: 'POST',
        body: params
      })
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          document.getElementById('btn-scan-orphans').click();
        })
        .catch(() => { btn.disabled = false; });
    });

    // ---- WebP Generation ----
    loadWebPCounts();

    function loadWebPCounts() {
      fetch(`index.php?option=com_proclaim&task=cwmadmin.getWebPCountsXHR&${token}=1`)
        .then(r => r.json())
        .then(data => {
          const html = `<ul class="list-unstyled">
          <li><strong>${strings.studies}:</strong> ${data.studies}</li>
          <li><strong>${strings.teachers}:</strong> ${data.teachers}</li>
          <li><strong>${strings.series}:</strong> ${data.series}</li>
          <li><strong>${strings.total}:</strong> ${data.total}</li>
        </ul>`;
          document.getElementById('webp-counts').innerHTML = html;
          document.getElementById('btn-start-webp').disabled = (data.total === 0);
        })
        .catch(() => {
          document.getElementById('webp-counts').innerHTML =
            `<span class="text-danger">${strings.errorLoading}</span>`;
        });
    }

    document.getElementById('btn-start-webp').addEventListener('click', function () {
      this.disabled = true;
      document.getElementById('webp-progress').style.display = 'block';

      const types = ['studies', 'teachers', 'series'];
      let typeIndex = 0;
      let totalConverted = 0;

      function convertType() {
        if (typeIndex >= types.length) {
          document.getElementById('webp-status').innerHTML =
            `<span class="text-success">${strings.webpComplete} ${totalConverted} ${strings.imagesConverted}</span>`;
          loadWebPCounts();
          return;
        }
        document.getElementById('webp-status').textContent = `${strings.converting} ${types[typeIndex]}...`;
        convertBatch(types[typeIndex]);
      }

      function convertBatch(type) {
        fetch(`index.php?option=com_proclaim&task=cwmadmin.migrateToWebPXHR&${token}=1&type=${type}&limit=10`)
          .then(r => r.json())
          .then(data => {
            totalConverted += data.converted;
            document.querySelector('#webp-progress .progress-bar').style.width =
              `${((typeIndex + 1) / types.length) * 100}%`;
            if (data.remaining > 0) convertBatch(type);
            else { typeIndex++; convertType(); }
          })
          .catch(() => {
            document.getElementById('webp-status').innerHTML =
              `<span class="text-danger">${strings.webpError}</span>`;
          });
      }

      convertType();
    });

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
