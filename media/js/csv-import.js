(function () {
    'use strict';

    /**
     * CSV Bulk Import for Proclaim Messages
     *
     * Client-side CSV parsing, column auto-detection, preview, AJAX batch processing,
     * and report rendering.
     *
     * @package  Proclaim.Admin
     * @since    10.1.0
     */

    (() => {

        // Column auto-detection aliases (field -> [aliases])
        const COLUMN_ALIASES = {
            studytitle: ['title', 'message title', 'sermon title', 'study title', 'message', 'sermon', 'name'],
            studydate: ['date', 'message date', 'sermon date', 'study date'],
            teacher: ['teacher', 'speaker', 'pastor', 'preacher', 'minister'],
            series: ['series', 'message series', 'sermon series', 'study series'],
            location: ['location', 'venue', 'church', 'campus'],
            messagetype: ['type', 'message type', 'category', 'sermon type'],
            scripture: ['scripture', 'passage', 'reference', 'bible ref'],
            topics: ['topic', 'topics', 'tag', 'tags', 'keyword', 'keywords'],
            studyintro: ['intro', 'introduction', 'summary', 'description', 'excerpt'],
            studytext: ['body', 'text', 'content', 'notes', 'message text', 'message notes', 'study text', 'sermon notes', 'transcript'],
            studynumber: ['number', 'message number', 'study number', 'sermon number', '#'],
            published: ['published', 'status', 'state', 'active'],
            thumbnailm: ['image', 'thumbnail', 'photo', 'picture'],
            created_by_alias: ['author', 'created by'],
            language: ['language', 'lang'],
        };

        const FIELD_LABELS = {
            ignore: '-- Ignore --',
            studytitle: 'Title *',
            studydate: 'Date',
            teacher: 'Teacher',
            series: 'Series',
            location: 'Location',
            messagetype: 'Type',
            scripture: 'Scripture',
            topics: 'Topics',
            studyintro: 'Introduction',
            studytext: 'Body',
            studynumber: 'Number',
            published: 'Published',
            thumbnailm: 'Image',
            created_by_alias: 'Author',
            language: 'Language',
        };

        const BATCH_SIZE = 25;

        let parsedRows = [];
        let columnMappings = [];
        let importRunning = false;

        /**
         * Parse CSV text into an array of arrays.
         * Handles quoted fields, commas in quotes, newlines in quotes, UTF-8 BOM.
         */
        function parseCSV(text, delimiter = ',') {
            // Strip UTF-8 BOM
            let csvText = text;
            if (csvText.charCodeAt(0) === 0xFEFF) {
                csvText = csvText.slice(1);
            }

            const rows = [];
            let row = [];
            let field = '';
            let inQuotes = false;
            let i = 0;

            while (i < csvText.length) {
                const ch = csvText[i];

                if (inQuotes) {
                    if (ch === '"') {
                        if (i + 1 < csvText.length && csvText[i + 1] === '"') {
                            field += '"';
                            i += 2;
                        } else {
                            inQuotes = false;
                            i += 1;
                        }
                    } else {
                        field += ch;
                        i += 1;
                    }
                } else if (ch === '"') {
                    inQuotes = true;
                    i += 1;
                } else if (ch === delimiter) {
                    row.push(field);
                    field = '';
                    i += 1;
                } else if (ch === '\r') {
                    row.push(field);
                    field = '';
                    rows.push(row);
                    row = [];
                    i += 1;
                    if (i < csvText.length && csvText[i] === '\n') {
                        i += 1;
                    }
                } else if (ch === '\n') {
                    row.push(field);
                    field = '';
                    rows.push(row);
                    row = [];
                    i += 1;
                } else {
                    field += ch;
                    i += 1;
                }
            }

            // Last field/row
            if (field !== '' || row.length > 0) {
                row.push(field);
                rows.push(row);
            }

            // Remove trailing empty rows
            while (rows.length > 0 && rows[rows.length - 1].every((c) => c.trim() === '')) {
                rows.pop();
            }

            return rows;
        }

        /**
         * Detect delimiter (comma, tab, semicolon) from first few lines.
         */
        function detectDelimiter(text) {
            const firstLines = text.split(/\r?\n/).slice(0, 5).join('\n');
            const counts = {
                ',': (firstLines.match(/,/g) || []).length,
                '\t': (firstLines.match(/\t/g) || []).length,
                ';': (firstLines.match(/;/g) || []).length,
            };

            let best = ',';
            let max = 0;

            for (const [delim, count] of Object.entries(counts)) {
                if (count > max) {
                    max = count;
                    best = delim;
                }
            }

            return best;
        }

        /**
         * Auto-detect column mapping from header row.
         */
        function autoDetectMappings(headers) {
            const mappings = [];

            for (const header of headers) {
                const normalized = header.toLowerCase().trim();
                let matched = 'ignore';

                for (const [field, aliases] of Object.entries(COLUMN_ALIASES)) {
                    if (aliases.some((alias) => normalized === alias || normalized.includes(alias))) {
                        matched = field;
                        break;
                    }
                }

                mappings.push(matched);
            }

            return mappings;
        }

        /**
         * Create a <select> element for column mapping.
         */
        function createMappingSelect(colIndex, selectedField) {
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm csv-col-mapping';
            select.dataset.col = colIndex;

            for (const [field, label] of Object.entries(FIELD_LABELS)) {
                const option = document.createElement('option');
                option.value = field;
                option.textContent = label;

                if (field === selectedField) {
                    option.selected = true;
                }

                select.appendChild(option);
            }

            return select;
        }

        /**
         * Render the preview table with mapping dropdowns using safe DOM methods.
         */
        function renderPreview(rows, mappings, hasHeader) {
            const previewPanel = document.getElementById('csv-preview-panel');
            const previewTable = document.getElementById('csv-preview-table');

            if (!previewPanel || !previewTable) {
                return;
            }

            const headers = hasHeader ? rows[0] : rows[0].map((_, i) => `Column ${i + 1}`);
            const dataRows = hasHeader ? rows.slice(1, 6) : rows.slice(0, 5);

            // Clear existing content
            previewTable.textContent = '';

            const table = document.createElement('table');
            table.className = 'table table-sm table-bordered table-striped';

            const thead = document.createElement('thead');

            // Mapping row
            const mappingRow = document.createElement('tr');

            for (let i = 0; i < headers.length; i++) {
                const th = document.createElement('th');
                th.appendChild(createMappingSelect(i, mappings[i] || 'ignore'));
                mappingRow.appendChild(th);
            }

            thead.appendChild(mappingRow);

            // Header labels row
            const headerRow = document.createElement('tr');
            headerRow.className = 'table-light';

            for (const h of headers) {
                const th = document.createElement('th');
                th.className = 'small text-muted';
                th.textContent = h;
                headerRow.appendChild(th);
            }

            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Data rows
            const tbody = document.createElement('tbody');

            for (const row of dataRows) {
                const tr = document.createElement('tr');

                for (let i = 0; i < headers.length; i++) {
                    const td = document.createElement('td');
                    td.className = 'small';
                    const val = row[i] || '';
                    td.textContent = val.length > 60 ? `${val.substring(0, 57)}...` : val;
                    tr.appendChild(td);
                }

                tbody.appendChild(tr);
            }

            table.appendChild(tbody);
            previewTable.appendChild(table);

            previewPanel.style.display = '';

            // Show total row count
            const totalRows = hasHeader ? rows.length - 1 : rows.length;
            const countEl = document.getElementById('csv-row-count');

            if (countEl) {
                countEl.textContent = str('JBS_CSV_ROWS_FOUND').replace('%d', totalRows);
            }

            // Enable import button
            const importBtn = document.getElementById('btn-csv-import');

            if (importBtn) {
                importBtn.disabled = false;
            }

            // Bind mapping change events
            previewTable.querySelectorAll('.csv-col-mapping').forEach((select) => {
                select.addEventListener('change', () => {
                    columnMappings[parseInt(select.dataset.col, 10)] = select.value;
                });
            });
        }

        /**
         * Run the import via AJAX batches.
         */
        async function runImport() {
            if (importRunning) {
                return;
            }

            importRunning = true;
            const config = document.getElementById('csv-import-config');
            const { token } = config.dataset;

            const hasHeader = document.getElementById('csv-first-row-header').checked;
            const dataRows = hasHeader ? parsedRows.slice(1) : parsedRows;
            const totalRows = dataRows.length;

            if (totalRows === 0) {
                importRunning = false;

                return;
            }

            // Collect settings
            const settings = {
                auto_create: document.getElementById('csv-auto-create').checked,
                default_published: parseInt(document.getElementById('csv-default-published').value, 10),
                duplicate_handling: document.getElementById('csv-duplicate-handling').value,
            };

            // Show progress panel
            const progressPanel = document.getElementById('csv-progress-panel');
            const progressBar = document.getElementById('csv-progress-bar');
            const progressText = document.getElementById('csv-progress-text');
            const reportPanel = document.getElementById('csv-report-panel');

            progressPanel.style.display = '';
            reportPanel.style.display = 'none';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';

            // Disable buttons
            document.getElementById('btn-csv-import').disabled = true;
            document.getElementById('csv-file-input').disabled = true;

            // Navigation guard
            const beforeUnload = (e) => {
                e.preventDefault();
                e.returnValue = '';
            };
            window.addEventListener('beforeunload', beforeUnload);

            let totalImported = 0;
            let totalSkipped = 0;
            const allErrors = [];
            let allAutoCreated = [];

            // Process in batches
            for (let offset = 0; offset < totalRows; offset += BATCH_SIZE) {
                const batch = dataRows.slice(offset, offset + BATCH_SIZE);
                const pct = Math.round(((offset + batch.length) / totalRows) * 100);

                progressText.textContent = str('JBS_CSV_IMPORTING')
                    .replace('%d', offset + batch.length)
                    .replace('%d', totalRows);

                try {
                    const response = await fetch(
                        `index.php?option=com_proclaim&task=cwmadmin.csvImportBatchXHR&${token}=1`,
                        {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                rows: batch,
                                mappings: columnMappings,
                                settings,
                            }),
                        },
                    );

                    const result = await response.json();

                    totalImported += result.imported || 0;
                    totalSkipped += result.skipped || 0;

                    if (result.errors && result.errors.length > 0) {
                        for (const err of result.errors) {
                            err.row = (err.row || 0) + offset + (hasHeader ? 2 : 1);
                            allErrors.push(err);
                        }
                    }

                    if (result.auto_created && result.auto_created.length > 0) {
                        allAutoCreated = allAutoCreated.concat(result.auto_created);
                    }
                } catch (e) {
                    allErrors.push({
                        row: offset + 1,
                        field: '',
                        message: e.message || str('JBS_CSV_NETWORK_ERROR'),
                    });
                }

                progressBar.style.width = `${pct}%`;
                progressBar.textContent = `${pct}%`;
            }

            // Complete
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            progressBar.classList.remove('progress-bar-animated');
            progressText.textContent = str('JBS_CSV_COMPLETE');

            window.removeEventListener('beforeunload', beforeUnload);
            importRunning = false;

            // Render report
            renderReport(totalImported, totalSkipped, allErrors, allAutoCreated);

            // Re-enable file input
            document.getElementById('csv-file-input').disabled = false;
        }

        /**
         * Render the import report using safe DOM methods.
         */
        function renderReport(imported, skipped, errors, autoCreated) {
            const reportPanel = document.getElementById('csv-report-panel');

            if (!reportPanel) {
                return;
            }

            reportPanel.textContent = '';

            // Summary cards row
            const cardRow = document.createElement('div');
            cardRow.className = 'row g-3 mb-3';

            // Imported card
            cardRow.appendChild(createSummaryCard(imported, str('JBS_CSV_IMPORTED'), 'text-bg-success'));

            // Skipped card
            if (skipped > 0) {
                cardRow.appendChild(createSummaryCard(skipped, str('JBS_CSV_SKIPPED'), 'text-bg-warning'));
            }

            // Errors card
            if (errors.length > 0) {
                cardRow.appendChild(createSummaryCard(errors.length, str('JBS_CSV_ERRORS'), 'text-bg-danger'));
            }

            reportPanel.appendChild(cardRow);

            // Auto-created entities table
            if (autoCreated.length > 0) {
                const seen = new Set();
                const unique = autoCreated.filter((e) => {
                    const key = `${e.type}:${e.id}`;

                    if (seen.has(key)) {
                        return false;
                    }

                    seen.add(key);

                    return true;
                });

                const section = document.createElement('div');
                section.className = 'mb-3';

                const heading = document.createElement('h5');
                heading.textContent = str('JBS_CSV_AUTO_CREATED');
                section.appendChild(heading);

                const tableWrap = document.createElement('div');
                tableWrap.className = 'table-responsive';
                const table = document.createElement('table');
                table.className = 'table table-sm table-striped';

                const thead = document.createElement('thead');
                const hRow = document.createElement('tr');

                for (const label of [str('JBS_CSV_TYPE'), str('JBS_CSV_NAME'), '']) {
                    const th = document.createElement('th');
                    th.textContent = label;
                    hRow.appendChild(th);
                }

                thead.appendChild(hRow);
                table.appendChild(thead);

                const tbody = document.createElement('tbody');

                for (const entity of unique) {
                    const tr = document.createElement('tr');

                    const tdType = document.createElement('td');
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-secondary';
                    badge.textContent = str(`JBS_CSV_TYPE_${entity.type.toUpperCase()}`) || entity.type;
                    tdType.appendChild(badge);
                    tr.appendChild(tdType);

                    const tdName = document.createElement('td');
                    tdName.textContent = entity.name;
                    tr.appendChild(tdName);

                    const tdLink = document.createElement('td');
                    const link = document.createElement('a');
                    link.href = entity.url;
                    link.target = '_blank';
                    link.className = 'btn btn-sm btn-outline-primary';
                    const icon = document.createElement('i');
                    icon.className = 'icon-pencil';
                    icon.setAttribute('aria-hidden', 'true');
                    link.appendChild(icon);
                    link.appendChild(document.createTextNode(` ${str('JBS_CSV_EDIT')}`));
                    tdLink.appendChild(link);
                    tr.appendChild(tdLink);

                    tbody.appendChild(tr);
                }

                table.appendChild(tbody);
                tableWrap.appendChild(table);
                section.appendChild(tableWrap);
                reportPanel.appendChild(section);
            }

            // Error table
            if (errors.length > 0) {
                const section = document.createElement('div');
                section.className = 'mb-3';

                const heading = document.createElement('h5');
                heading.textContent = str('JBS_CSV_ERROR_DETAILS');
                section.appendChild(heading);

                const tableWrap = document.createElement('div');
                tableWrap.className = 'table-responsive';
                const table = document.createElement('table');
                table.className = 'table table-sm table-striped table-danger';

                const thead = document.createElement('thead');
                const hRow = document.createElement('tr');

                for (const label of [str('JBS_CSV_ROW'), str('JBS_CSV_FIELD'), str('JBS_CSV_MESSAGE')]) {
                    const th = document.createElement('th');
                    th.textContent = label;
                    hRow.appendChild(th);
                }

                thead.appendChild(hRow);
                table.appendChild(thead);

                const tbody = document.createElement('tbody');

                for (const err of errors) {
                    const tr = document.createElement('tr');

                    const tdRow = document.createElement('td');
                    tdRow.textContent = err.row;
                    tr.appendChild(tdRow);

                    const tdField = document.createElement('td');
                    tdField.textContent = err.field || '';
                    tr.appendChild(tdField);

                    const tdMsg = document.createElement('td');
                    tdMsg.textContent = err.message;
                    tr.appendChild(tdMsg);

                    tbody.appendChild(tr);
                }

                table.appendChild(tbody);
                tableWrap.appendChild(table);
                section.appendChild(tableWrap);
                reportPanel.appendChild(section);
            }

            reportPanel.style.display = '';
        }

        /**
         * Create a summary card element.
         */
        function createSummaryCard(count, label, colorClass) {
            const col = document.createElement('div');
            col.className = 'col-auto';

            const card = document.createElement('div');
            card.className = `card ${colorClass}`;

            const body = document.createElement('div');
            body.className = 'card-body py-2 px-3';

            const num = document.createElement('div');
            num.className = 'fs-3 fw-bold';
            num.textContent = count;
            body.appendChild(num);

            const text = document.createElement('div');
            text.className = 'small';
            text.textContent = label;
            body.appendChild(text);

            card.appendChild(body);
            col.appendChild(card);

            return col;
        }

        /**
         * Download template CSV.
         */
        async function downloadTemplate() {
            const config = document.getElementById('csv-import-config');
            const { token } = config.dataset;

            try {
                const response = await fetch(
                    `index.php?option=com_proclaim&task=cwmadmin.csvTemplateXHR&${token}=1`,
                );
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'proclaim-import-template.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } catch {
                // Silently fail
            }
        }

        /**
         * Get a translated string, falling back to the key.
         */
        function str(key) {
            if (typeof Joomla !== 'undefined' && Joomla.Text && Joomla.Text._) {
                const val = Joomla.Text._(key);

                // Joomla.Text._() returns the key itself when unregistered
                return val !== key ? val : key;
            }

            return key;
        }

        /**
         * Initialize the CSV import UI.
         */
        function init() {
            const fileInput = document.getElementById('csv-file-input');
            const headerCheck = document.getElementById('csv-first-row-header');
            const importBtn = document.getElementById('btn-csv-import');
            const templateBtn = document.getElementById('btn-csv-template');

            if (!fileInput) {
                return;
            }

            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];

                if (!file) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = (ev) => {
                    const text = ev.target.result;
                    const delimiter = detectDelimiter(text);

                    parsedRows = parseCSV(text, delimiter);

                    if (parsedRows.length < 2) {
                        return;
                    }

                    const hasHeader = headerCheck ? headerCheck.checked : true;
                    const headers = hasHeader ? parsedRows[0] : parsedRows[0].map((_, i) => `Column ${i + 1}`);

                    columnMappings = autoDetectMappings(headers);
                    renderPreview(parsedRows, columnMappings, hasHeader);
                };

                reader.readAsText(file);
            });

            if (headerCheck) {
                headerCheck.addEventListener('change', () => {
                    if (parsedRows.length > 0) {
                        const hasHeader = headerCheck.checked;
                        const headers = hasHeader ? parsedRows[0] : parsedRows[0].map((_, i) => `Column ${i + 1}`);

                        columnMappings = autoDetectMappings(headers);
                        renderPreview(parsedRows, columnMappings, hasHeader);
                    }
                });
            }

            if (importBtn) {
                importBtn.addEventListener('click', () => {
                    runImport();
                });
            }

            if (templateBtn) {
                templateBtn.addEventListener('click', () => {
                    downloadTemplate();
                });
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();

})();
