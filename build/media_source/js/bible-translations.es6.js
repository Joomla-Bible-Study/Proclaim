/**
 * Bible Translations Management
 *
 * Handles the Scripture tab in the admin center: provider status badges,
 * local translations table with download/remove, search/filter toolbar,
 * usage tracking, and auto-download of bundled translations.
 *
 * Expects a <div id="bible-translations-config"> element with data attributes
 * for the AJAX base URL, form token, and all translated UI strings.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */

document.addEventListener('DOMContentLoaded', () => {
    const config = document.getElementById('bible-translations-config');

    if (!config) {
        return;
    }

    const { token } = config.dataset;
    const baseUrl = 'index.php?option=com_proclaim&task=cwmadmin.';

    // Escape HTML to prevent XSS when interpolating external data into innerHTML
    const esc = (str) => {
        const el = document.createElement('span');
        el.textContent = String(str ?? '');
        return el.innerHTML;
    };
    const adminLanguage = (config.dataset.adminLanguage || 'en-GB').substring(0, 2).toLowerCase();

    // Translated strings from data attributes
    const strings = {
        loading: config.dataset.strLoading,
        noTranslations: config.dataset.strNoTranslations,
        loadError: config.dataset.strLoadError,
        title: config.dataset.strTitle,
        abbreviation: config.dataset.strAbbreviation,
        source: config.dataset.strSource,
        status: config.dataset.strStatus,
        verses: config.dataset.strVerses,
        installed: config.dataset.strInstalled,
        notInstalled: config.dataset.strNotInstalled,
        download: config.dataset.strDownload,
        downloading: config.dataset.strDownloading,
        remove: config.dataset.strRemove,
        downloadFailed: config.dataset.strDownloadFailed,
        confirmRemove: config.dataset.strConfirmRemove,
        bundledDone: config.dataset.strBundledDone,
        statusReady: config.dataset.strStatusReady,
        statusInstalled: config.dataset.strStatusInstalled,
        statusNone: config.dataset.strStatusNone,
        statusUnknown: config.dataset.strStatusUnknown,
        removeAll: config.dataset.strRemoveAll,
        confirmRemoveAll: config.dataset.strConfirmRemoveAll,
        size: config.dataset.strSize,
        totalSize: config.dataset.strTotalSize,
        syncing: config.dataset.strSyncing,
        syncComplete: config.dataset.strSyncComplete,
        syncFailed: config.dataset.strSyncFailed,
        gdprDisabled: config.dataset.strGdprDisabled,
        online: config.dataset.strOnline,
        language: config.dataset.strLanguage,
        allLanguages: config.dataset.strAllLanguages,
        filterAll: config.dataset.strFilterAll,
        filterInstalled: config.dataset.strFilterInstalled,
        filterNotInstalled: config.dataset.strFilterNotInstalled,
        filterInUse: config.dataset.strFilterInUse,
        searchPlaceholder: config.dataset.strSearchPlaceholder,
        usageCount: config.dataset.strUsageCount,
        usageBadge: config.dataset.strUsageBadge,
        suggested: config.dataset.strSuggested,
        showingCount: config.dataset.strShowingCount,
        suggestedDesc: config.dataset.strSuggestedDesc,
        onlineOnly: config.dataset.strOnlineOnly,
        onlineOnlyDesc: config.dataset.strOnlineOnlyDesc,
        coreTranslation: config.dataset.strCoreTranslation,
        coreCannotRemove: config.dataset.strCoreCannotRemove,
        providerDisableConfirm: config.dataset.strProviderDisableConfirm,
        providerCleanupDone: config.dataset.strProviderCleanupDone,
    };

    const gdprMode = parseInt(config.dataset.gdprMode, 10) === 1;

    // Track whether bundled auto-download is running to prevent re-triggering
    let autoDownloadRunning = false;

    // Store all translations for client-side filtering
    let allTranslations = [];
    let allTotalSize = 0;

    // Current filter state
    const filters = {
        search: '',
        language: adminLanguage,
        status: 'all',
    };

    // --- Provider status badges ---

    /**
     * Inject a badge inline with a provider switcher field.
     */
    const injectBadge = (fieldId, badgeHtml) => {
        const field = document.getElementById(fieldId);

        if (!field) {
            return;
        }

        const controls = field.closest('.controls') || field.closest('fieldset')?.parentElement;

        if (!controls) {
            return;
        }

        controls.style.display = 'flex';
        controls.style.alignItems = 'center';
        controls.style.gap = '0.75rem';
        const wrapper = document.createElement('span');
        wrapper.innerHTML = badgeHtml;
        controls.appendChild(wrapper.firstElementChild || wrapper);
    };

    // Static badge for getbible provider
    injectBadge(
        'params_provider_getbible0',
        `<span class="badge bg-success"><i class="icon-checkmark-circle" aria-hidden="true"></i> ${strings.statusReady}</span>`,
    );

    // Static badge for api.bible provider
    injectBadge(
        'params_provider_api_bible0',
        `<span class="badge bg-info"><i class="icon-globe" aria-hidden="true"></i> ${strings.online}</span>`,
    );

    // --- GDPR overlay for external provider toggles ---
    if (gdprMode) {
        const providerCard = document.querySelector('#scripture-settings .card');

        if (providerCard) {
            const overlay = document.createElement('div');
            overlay.className = 'alert alert-info mt-2 mb-0';
            overlay.innerHTML = `<i class="icon-lock" aria-hidden="true"></i> ${strings.gdprDisabled}`;
            providerCard.querySelector('.card-body')?.appendChild(overlay);

            // Disable the provider toggle inputs
            providerCard.querySelectorAll('input[type="radio"]').forEach((input) => {
                if (input.name.includes('provider_getbible') || input.name.includes('provider_api_bible')) {
                    input.disabled = true;
                }
            });
        }
    }

    // --- Provider disable cleanup ---

    /**
     * Count non-installed translations from a given source in the loaded data.
     */
    const countNonInstalledBySource = (source) => allTranslations.filter(
        (t) => t.source === source && parseInt(t.installed, 10) === 0 && parseInt(t.bundled, 10) === 0,
    ).length;

    /**
     * Clean up non-installed translation entries when a provider is disabled.
     */
    const cleanupProvider = (source) => {
        fetch(`${baseUrl}cleanupProviderXHR&${token}=1&source=${encodeURIComponent(source)}`)
            .then((r) => r.json())
            .then((result) => {
                if (result.success && result.count > 0) {
                    Joomla.renderMessages({ message: [result.message] });
                    loadTranslations(true);
                }
            })
            .catch(() => {
                // Silent fail — cleanup is optional
            });
    };

    /**
     * Attach provider toggle listeners for disable warnings.
     */
    const attachProviderToggle = (fieldName, source) => {
        const radios = document.querySelectorAll(`input[name="${fieldName}"]`);

        radios.forEach((radio) => {
            radio.addEventListener('change', () => {
                // Only act when switching to "off" (value = 0)
                if (radio.value !== '0' || !radio.checked) {
                    return;
                }

                const pendingCount = countNonInstalledBySource(source);

                if (pendingCount > 0 && confirm(strings.providerDisableConfirm.replace('%s', pendingCount))) {
                    cleanupProvider(source);
                }
            });
        });
    };

    // Only API.Bible gets cleanup on disable — its translations require an online API
    // and cannot be served locally. GetBible translations CAN be downloaded locally,
    // so we keep the catalog even when the online provider is disabled.
    attachProviderToggle('jform[params][provider_api_bible]', 'api_bible');

    // --- API.Bible sync button ---
    const syncBtn = document.getElementById('btn-sync-api-bible');

    if (syncBtn) {
        // Show/hide sync and key rows based on api_bible toggle state
        const syncRow = document.getElementById('api-bible-sync-row');
        const keyRow = document.getElementById('api-bible-key-row');

        const updateSyncVisibility = () => {
            const checked = document.querySelector('input[name="jform[params][provider_api_bible]"]:checked');
            const isOn = checked && checked.value === '1';

            if (syncRow) {
                syncRow.style.display = isOn ? '' : 'none';
            }

            if (keyRow) {
                keyRow.style.display = isOn ? '' : 'none';
            }
        };

        // Listen for toggle changes
        document.querySelectorAll('input[name="jform[params][provider_api_bible]"]').forEach((radio) => {
            radio.addEventListener('change', updateSyncVisibility);
        });
        updateSyncVisibility();

        syncBtn.addEventListener('click', () => {
            const statusEl = document.getElementById('api-bible-sync-status');
            syncBtn.disabled = true;
            statusEl.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${strings.syncing}`;

            fetch(`${baseUrl}syncApiBibleTranslationsXHR&${token}=1`)
                .then((r) => r.json())
                .then((result) => {
                    syncBtn.disabled = false;

                    if (result.success) {
                        statusEl.innerHTML = `<span class="text-success">${esc(strings.syncComplete.replace('%s', result.count))}</span>`;
                        Joomla.renderMessages({ message: [result.message] });
                        loadTranslations(true);
                    } else {
                        const errMsg = result.message || strings.syncFailed;
                        statusEl.innerHTML = `<span class="text-danger">${esc(errMsg)}</span>`;
                        Joomla.renderMessages({ error: [errMsg] });
                    }
                })
                .catch((err) => {
                    syncBtn.disabled = false;
                    statusEl.innerHTML = `<span class="text-danger">${esc(strings.syncFailed)}: ${esc(err.message || 'Network error')}</span>`;
                });
        });
    }

    // Dynamic badge for local translation count (placed in card header)
    const localBadge = document.createElement('span');
    localBadge.id = 'local-provider-status';
    localBadge.className = 'badge bg-info ms-3';
    localBadge.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

    const translationsHeader = document.getElementById('translations-card-header');

    if (translationsHeader) {
        translationsHeader.appendChild(localBadge);
    }

    /**
     * Refresh the local provider badge with current translation count.
     */
    const refreshLocalBadge = () => {
        fetch(`${baseUrl}getScriptureStatusXHR&${token}=1`)
            .then((response) => response.json())
            .then((data) => {
                const badge = document.getElementById('local-provider-status');

                if (data.local_count > 0) {
                    badge.className = 'badge bg-success ms-3';
                    badge.innerHTML = `<i class="icon-checkmark-circle" aria-hidden="true"></i> ${data.local_count} ${strings.statusInstalled}`;
                } else {
                    badge.className = 'badge bg-warning text-dark ms-3';
                    badge.innerHTML = `<i class="icon-warning" aria-hidden="true"></i> ${strings.statusNone}`;
                }
            })
            .catch(() => {
                const badge = document.getElementById('local-provider-status');
                badge.className = 'badge bg-secondary ms-3';
                badge.textContent = strings.statusUnknown;
            });
    };

    // Initial badge load
    refreshLocalBadge();

    // --- Local translations management ---

    // Language name mapping for common ISO codes
    const languageNames = {
        en: 'English',
        de: 'German',
        fr: 'French',
        es: 'Spanish',
        pt: 'Portuguese',
        ru: 'Russian',
        it: 'Italian',
        hu: 'Hungarian',
        ro: 'Romanian',
        ko: 'Korean',
        zh: 'Chinese',
        la: 'Latin',
        nl: 'Dutch',
        no: 'Norwegian',
        cs: 'Czech',
        pl: 'Polish',
        sv: 'Swedish',
        da: 'Danish',
        fi: 'Finnish',
        el: 'Greek',
        he: 'Hebrew',
        ar: 'Arabic',
        ja: 'Japanese',
        hi: 'Hindi',
        tr: 'Turkish',
        uk: 'Ukrainian',
        vi: 'Vietnamese',
        th: 'Thai',
        af: 'Afrikaans',
        sw: 'Swahili',
        tl: 'Tagalog',
    };

    const getLanguageName = (code) => {
        if (!code) {
            return strings.language || 'Other';
        }

        const short = code.substring(0, 2).toLowerCase();
        return languageNames[short] || code;
    };

    /**
     * Extract unique language codes from translations and return sorted list.
     */
    const getAvailableLanguages = (translations) => {
        const langs = new Map();

        translations.forEach((t) => {
            const code = t.language ? t.language.substring(0, 2).toLowerCase() : '';
            const name = getLanguageName(t.language);

            if (!langs.has(code)) {
                langs.set(code, name);
            }
        });

        return [...langs.entries()].sort((a, b) => a[1].localeCompare(b[1]));
    };

    /**
     * Build the filter toolbar HTML and insert it before the table.
     */
    const buildFilterToolbar = () => {
        const existing = document.getElementById('translations-filter-toolbar');

        if (existing) {
            return;
        }

        const toolbar = document.createElement('div');
        toolbar.id = 'translations-filter-toolbar';
        toolbar.className = 'row g-2 mb-3 align-items-end';

        // Search input
        const searchCol = document.createElement('div');
        searchCol.className = 'col-12 col-md-4';
        searchCol.innerHTML = `
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="icon-search" aria-hidden="true"></i></span>
                <input type="text" class="form-control" id="translations-search"
                       placeholder="${esc(strings.searchPlaceholder)}" value="${esc(filters.search)}">
            </div>`;
        toolbar.appendChild(searchCol);

        // Language dropdown
        const langCol = document.createElement('div');
        langCol.className = 'col-6 col-md-3';
        langCol.innerHTML = `
            <select class="form-select form-select-sm" id="translations-lang-filter">
                <option value="">${strings.allLanguages}</option>
            </select>`;
        toolbar.appendChild(langCol);

        // Status dropdown
        const statusCol = document.createElement('div');
        statusCol.className = 'col-6 col-md-3';
        statusCol.innerHTML = `
            <select class="form-select form-select-sm" id="translations-status-filter">
                <option value="all">${strings.filterAll}</option>
                <option value="installed">${strings.filterInstalled}</option>
                <option value="not_installed">${strings.filterNotInstalled}</option>
                <option value="in_use">${strings.filterInUse}</option>
            </select>`;
        toolbar.appendChild(statusCol);

        // Showing count
        const countCol = document.createElement('div');
        countCol.className = 'col-12 col-md-2 text-end';
        countCol.innerHTML = '<small class="text-muted" id="translations-showing-count"></small>';
        toolbar.appendChild(countCol);

        const container = document.getElementById('translations-list');
        container.parentNode.insertBefore(toolbar, container);

        // Bind events
        const searchInput = document.getElementById('translations-search');
        let debounceTimer;

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filters.search = searchInput.value.trim().toLowerCase();
                applyFilters();
            }, 200);
        });

        document.getElementById('translations-lang-filter').addEventListener('change', function () {
            filters.language = this.value;
            applyFilters();
        });

        document.getElementById('translations-status-filter').addEventListener('change', function () {
            filters.status = this.value;
            applyFilters();
        });
    };

    /**
     * Populate the language dropdown from the current translations data.
     */
    const populateLanguageDropdown = () => {
        const select = document.getElementById('translations-lang-filter');

        if (!select) {
            return;
        }

        const languages = getAvailableLanguages(allTranslations);
        const currentValue = filters.language;

        // Rebuild options
        select.innerHTML = `<option value="">${strings.allLanguages}</option>`;

        languages.forEach(([code, name]) => {
            const count = allTranslations.filter((t) => {
                const tCode = t.language ? t.language.substring(0, 2).toLowerCase() : '';
                return tCode === code;
            }).length;
            const option = document.createElement('option');
            option.value = code;
            option.textContent = `${name} (${count})`;

            if (code === currentValue) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    };

    /**
     * Apply current filters to the stored translations and re-render the table.
     */
    function applyFilters() {
        const filtered = allTranslations.filter((t) => {
            // Search filter: match name or abbreviation
            if (filters.search) {
                const haystack = `${t.name} ${t.abbreviation}`.toLowerCase();

                if (!haystack.includes(filters.search)) {
                    return false;
                }
            }

            // Language filter
            if (filters.language) {
                const tLang = t.language ? t.language.substring(0, 2).toLowerCase() : '';

                if (tLang !== filters.language) {
                    return false;
                }
            }

            // Status filter
            const isInstalled = parseInt(t.installed, 10) === 1;
            const usageCount = parseInt(t.usage_count, 10) || 0;

            if (filters.status === 'installed' && !isInstalled) {
                return false;
            }

            if (filters.status === 'not_installed' && isInstalled) {
                return false;
            }

            if (filters.status === 'in_use' && usageCount === 0) {
                return false;
            }

            return true;
        });

        // Update the showing count
        const countEl = document.getElementById('translations-showing-count');

        if (countEl) {
            countEl.textContent = strings.showingCount
                .replace('%s', filtered.length)
                .replace('%s', allTranslations.length);
        }

        renderTranslationsTable(filtered, allTotalSize);
    }

    /**
     * Fetch and render the translations table.
     *
     * @param {boolean} silent  When true, skip the loading spinner (used after
     *                          auto-download so the table doesn't flash).
     */
    function loadTranslations(silent = false) {
        const container = document.getElementById('translations-list');

        // Lock height to prevent page bounce during refresh
        container.style.minHeight = `${container.offsetHeight}px`;

        if (!silent) {
            container.innerHTML = `<div class="text-center py-3"><span class="spinner-border spinner-border-sm" role="status"></span> ${strings.loading}</div>`;
        }

        fetch(`${baseUrl}getTranslationsXHR&${token}=1`)
            .then((r) => r.json())
            .then((data) => {
                if (!data.success || !data.translations || data.translations.length === 0) {
                    container.innerHTML = `<p class="text-muted">${strings.noTranslations}</p>`;
                    return;
                }

                // Store full dataset for client-side filtering
                allTranslations = data.translations;
                allTotalSize = data.total_size || 0;

                // Build filter toolbar (once)
                buildFilterToolbar();
                populateLanguageDropdown();

                // Apply current filters and render
                applyFilters();

                // Handle bundled auto-downloads
                handleBundledAutoDownload(data.translations);

                // Release locked height
                container.style.minHeight = '';
            })
            .catch(() => {
                container.innerHTML = `<div class="alert alert-warning">${strings.loadError}</div>`;
                container.style.minHeight = '';
            });
    }

    /**
     * Format byte size to human-readable string.
     */
    const formatSize = (bytes) => {
        if (!bytes || bytes === 0) {
            return '-';
        }

        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        let size = parseFloat(bytes);

        while (size >= 1024 && i < units.length - 1) {
            size /= 1024;
            i += 1;
        }

        return `${size.toFixed(i > 0 ? 1 : 0)} ${units[i]}`;
    };

    /**
     * Render the translations table from a filtered list.
     */
    function renderTranslationsTable(translations, totalSize) {
        const container = document.getElementById('translations-list');

        if (translations.length === 0) {
            container.innerHTML = `<p class="text-muted">${strings.noTranslations}</p>`;
            updateRemoveAllVisibility(false);
            return;
        }

        // Separate suggested translations (in use, not installed, and downloadable — not api_bible)
        const suggested = translations.filter(
            (t) => parseInt(t.usage_count, 10) > 0 && parseInt(t.installed, 10) === 0 && t.source !== 'api_bible',
        );
        const rest = translations.filter(
            (t) => !(parseInt(t.usage_count, 10) > 0 && parseInt(t.installed, 10) === 0 && t.source !== 'api_bible'),
        );

        let html = '';

        // Suggested section (in use but not installed)
        if (suggested.length > 0) {
            html += '<div class="alert alert-warning py-2 px-3 mb-2">';
            html += '<div class="d-flex align-items-center gap-2 mb-1">';
            html += '<i class="icon-notification" aria-hidden="true"></i>';
            html += `<strong>${strings.suggested}</strong>`;
            html += '</div>';
            html += `<small>${strings.suggestedDesc}</small>`;
            html += '</div>';
            html += buildTable(suggested, false, true);
        }

        // Main table
        html += buildTable(rest, true, false);

        // Total row
        if (totalSize > 0 && filters.status !== 'not_installed') {
            html += `<div class="text-end text-muted small mt-1"><strong>${strings.totalSize}:</strong> ${formatSize(totalSize)}</div>`;
        }

        container.innerHTML = html;
        bindTableButtons(container);

        // Show/hide "Remove All" button
        const hasInstalled = allTranslations.some((t) => parseInt(t.installed, 10) === 1);
        updateRemoveAllVisibility(hasInstalled);
    }

    /**
     * Build a table HTML string from a list of translations.
     *
     * @param {Array}   translations     Filtered translation objects
     * @param {boolean} showGroupHeaders Show language group headers
     * @param {boolean} isSuggested      Render as suggested (skip "Not installed" badge)
     */
    function buildTable(translations, showGroupHeaders, isSuggested = false) {
        if (translations.length === 0) {
            return '';
        }

        let html = '<div class="table-responsive"><table class="table table-striped table-sm mb-2">';
        html += '<thead><tr>';
        html += `<th>${strings.title}</th>`;
        html += `<th>${strings.abbreviation}</th>`;
        html += `<th>${strings.source}</th>`;
        html += `<th>${strings.status}</th>`;
        html += `<th>${strings.verses}</th>`;
        html += `<th>${strings.size}</th>`;
        html += '<th style="min-width:130px"></th>';
        html += '</tr></thead><tbody>';

        // Sort by language then name for grouping
        const sorted = [...translations].sort((a, b) => {
            const langA = getLanguageName(a.language);
            const langB = getLanguageName(b.language);
            const langCmp = langA.localeCompare(langB);

            if (langCmp !== 0) {
                return langCmp;
            }

            return (a.name || '').localeCompare(b.name || '');
        });

        let currentLang = null;

        sorted.forEach((t) => {
            const lang = getLanguageName(t.language);

            // Insert language group header (only when not filtering by single language)
            if (showGroupHeaders && !filters.language && lang !== currentLang) {
                currentLang = lang;
                html += `<tr class="table-active"><td colspan="7"><strong>${esc(lang)}</strong></td></tr>`;
            }

            const isInstalled = parseInt(t.installed, 10) === 1;
            const isBundled = parseInt(t.bundled, 10) === 1;
            const usageCount = parseInt(t.usage_count, 10) || 0;

            const isOnlineOnly = t.source === 'api_bible';

            // Status badges
            let statusBadge;

            if (isSuggested) {
                // Suggested items: show usage count prominently, skip redundant "Not installed"
                statusBadge = `<span class="badge bg-warning text-dark" title="${strings.usageCount.replace('%s', usageCount)}">${strings.usageCount.replace('%s', usageCount)}</span>`;
            } else if (isOnlineOnly && !isInstalled) {
                // API.Bible: only show "In Use" badge if referenced, otherwise empty
                statusBadge = usageCount > 0
                    ? `<span class="badge bg-info" title="${strings.usageCount.replace('%s', usageCount)}">${strings.usageBadge}</span>`
                    : '';
            } else {
                statusBadge = isInstalled
                    ? `<span class="badge bg-success">${strings.installed}</span>`
                    : `<span class="badge bg-secondary">${strings.notInstalled}</span>`;

                if (isBundled) {
                    statusBadge += ` <span class="badge bg-dark" title="${strings.coreCannotRemove}">${strings.coreTranslation}</span>`;
                }

                if (usageCount > 0) {
                    statusBadge += ` <span class="badge bg-info" title="${strings.usageCount.replace('%s', usageCount)}">${strings.usageBadge}</span>`;
                }
            }

            const verseCount = isInstalled ? t.verse_count : '-';
            const dataSize = isInstalled
                ? formatSize(t.data_size)
                : (t.estimated_size > 0 ? `~${formatSize(t.estimated_size)}` : '-');

            let actionBtn;

            if (isInstalled && isBundled) {
                // Core translations cannot be removed
                actionBtn = `<span class="text-muted small" title="${strings.coreCannotRemove}"><i class="icon-lock" aria-hidden="true"></i></span>`;
            } else if (isInstalled) {
                actionBtn = `<button type="button" class="btn btn-sm btn-danger btn-remove-translation" data-abbr="${esc(t.abbreviation)}">${strings.remove}</button>`;
            } else if (isOnlineOnly) {
                // API.Bible translations are online-only, cannot be downloaded locally
                actionBtn = `<span class="badge bg-light text-dark border" title="${esc(strings.onlineOnlyDesc)}"><i class="icon-globe" aria-hidden="true"></i> ${strings.onlineOnly}</span>`;
            } else {
                actionBtn = `<button type="button" class="btn btn-sm btn-primary btn-download-translation" data-abbr="${esc(t.abbreviation)}">${strings.download}</button>`;
            }

            html += '<tr>';
            html += `<td>${esc(t.name)}</td>`;
            html += `<td><code>${esc(t.abbreviation.toUpperCase())}</code></td>`;
            html += `<td>${esc(t.source || 'getbible')}</td>`;
            html += `<td>${statusBadge}</td>`;
            html += `<td>${verseCount}</td>`;
            html += `<td>${dataSize}</td>`;
            html += `<td>${actionBtn}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    /**
     * Bind download and remove button event handlers within a container.
     */
    function bindTableButtons(container) {
        // Bind download buttons
        container.querySelectorAll('.btn-download-translation').forEach((btn) => {
            btn.addEventListener('click', function () {
                const abbr = this.getAttribute('data-abbr');
                this.disabled = true;
                this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${strings.downloading}`;

                fetch(`${baseUrl}downloadTranslationXHR&${token}=1&abbreviation=${encodeURIComponent(abbr)}`)
                    .then((r) => r.json())
                    .then((result) => {
                        if (result.success) {
                            Joomla.renderMessages({ message: [result.message] });
                        } else {
                            Joomla.renderMessages({ error: [result.message] });
                        }

                        loadTranslations(true);
                        refreshLocalBadge();
                    })
                    .catch(() => {
                        Joomla.renderMessages({ error: [strings.downloadFailed] });
                        loadTranslations(true);
                    });
            });
        });

        // Bind remove buttons
        container.querySelectorAll('.btn-remove-translation').forEach((btn) => {
            btn.addEventListener('click', function () {
                const abbr = this.getAttribute('data-abbr');

                if (!confirm(strings.confirmRemove)) {
                    return;
                }

                this.disabled = true;

                fetch(`${baseUrl}removeTranslationXHR&${token}=1&abbreviation=${encodeURIComponent(abbr)}`)
                    .then((r) => r.json())
                    .then((result) => {
                        if (result.success) {
                            Joomla.renderMessages({ message: [result.message] });
                        } else {
                            Joomla.renderMessages({ error: [result.message] });
                        }

                        loadTranslations(true);
                        refreshLocalBadge();
                    })
                    .catch(() => {
                        loadTranslations(true);
                    });
            });
        });
    }

    /**
     * Show or hide the "Remove All" button.
     */
    function updateRemoveAllVisibility(hasInstalled) {
        const removeAllBtn = document.getElementById('btn-remove-all-translations');

        if (removeAllBtn) {
            removeAllBtn.classList.toggle('d-none', !hasInstalled);
        }
    }

    /**
     * Handle auto-downloading bundled translations that aren't installed yet.
     */
    function handleBundledAutoDownload(translations) {
        if (autoDownloadRunning) {
            return;
        }

        const pending = translations.filter((t) => parseInt(t.bundled, 10) === 1 && parseInt(t.installed, 10) === 0);

        if (pending.length === 0) {
            return;
        }

        autoDownloadRunning = true;
        const container = document.getElementById('translations-list');

        pending.forEach((t) => {
            const btn = container.querySelector(`.btn-download-translation[data-abbr="${CSS.escape(t.abbreviation)}"]`);

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${strings.downloading}`;
            }
        });

        autoDownloadBundled(pending);
    }

    function autoDownloadBundled(queue) {
        let index = 0;
        const downloaded = [];

        const next = () => {
            if (index >= queue.length) {
                autoDownloadRunning = false;

                if (downloaded.length > 0) {
                    Joomla.renderMessages({ message: [strings.bundledDone.replace('%s', downloaded.join(', '))] });
                }

                // Silent refresh — no spinner, just swap the table in place
                loadTranslations(true);
                refreshLocalBadge();
                return;
            }

            const abbr = queue[index].abbreviation;
            index += 1;

            fetch(`${baseUrl}downloadTranslationXHR&${token}=1&abbreviation=${encodeURIComponent(abbr)}`)
                .then((r) => r.json())
                .then((result) => {
                    if (result.success) {
                        downloaded.push(abbr.toUpperCase());
                    }

                    next();
                })
                .catch(() => {
                    next();
                });
        };

        next();
    }

    // Refresh button
    const refreshBtn = document.getElementById('btn-refresh-translations');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => loadTranslations());
    }

    // Remove All button
    const removeAllBtn = document.getElementById('btn-remove-all-translations');

    if (removeAllBtn) {
        removeAllBtn.addEventListener('click', () => {
            if (!confirm(strings.confirmRemoveAll)) {
                return;
            }

            removeAllBtn.disabled = true;

            fetch(`${baseUrl}removeAllTranslationsXHR&${token}=1`)
                .then((r) => r.json())
                .then((result) => {
                    if (result.success) {
                        Joomla.renderMessages({ message: [result.message] });
                    } else {
                        Joomla.renderMessages({ error: [result.message] });
                    }

                    removeAllBtn.disabled = false;
                    loadTranslations(true);
                    refreshLocalBadge();
                })
                .catch(() => {
                    removeAllBtn.disabled = false;
                    loadTranslations(true);
                });
        });
    }

    // Initial load (show spinner on first load)
    loadTranslations();
});
