/**
 * Bible Translations Management
 *
 * Handles the Scripture tab in the admin center: provider status badges,
 * local translations table with download/remove, and auto-download of
 * bundled translations.
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

    const token = config.dataset.token;
    const baseUrl = 'index.php?option=com_proclaim&task=cwmadmin.';

    // Translated strings from data attributes
    const strings = {
        loading:          config.dataset.strLoading,
        noTranslations:   config.dataset.strNoTranslations,
        loadError:        config.dataset.strLoadError,
        title:            config.dataset.strTitle,
        abbreviation:     config.dataset.strAbbreviation,
        source:           config.dataset.strSource,
        status:           config.dataset.strStatus,
        verses:           config.dataset.strVerses,
        installed:        config.dataset.strInstalled,
        notInstalled:     config.dataset.strNotInstalled,
        download:         config.dataset.strDownload,
        downloading:      config.dataset.strDownloading,
        remove:           config.dataset.strRemove,
        downloadFailed:   config.dataset.strDownloadFailed,
        confirmRemove:    config.dataset.strConfirmRemove,
        bundledDone:      config.dataset.strBundledDone,
        statusReady:      config.dataset.strStatusReady,
        statusInstalled:  config.dataset.strStatusInstalled,
        statusNone:       config.dataset.strStatusNone,
        statusUnknown:    config.dataset.strStatusUnknown,
        removeAll:        config.dataset.strRemoveAll,
        confirmRemoveAll: config.dataset.strConfirmRemoveAll,
        size:             config.dataset.strSize,
        totalSize:        config.dataset.strTotalSize,
        syncing:          config.dataset.strSyncing,
        syncComplete:     config.dataset.strSyncComplete,
        syncFailed:       config.dataset.strSyncFailed,
        gdprDisabled:     config.dataset.strGdprDisabled,
        online:           config.dataset.strOnline,
        language:         config.dataset.strLanguage,
    };

    const gdprMode = parseInt(config.dataset.gdprMode) === 1;

    // Track whether bundled auto-download is running to prevent re-triggering
    let autoDownloadRunning = false;

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
        `<span class="badge bg-success"><i class="icon-checkmark-circle" aria-hidden="true"></i> ${strings.statusReady}</span>`
    );

    // Static badge for api.bible provider
    injectBadge(
        'params_provider_api_bible0',
        `<span class="badge bg-info"><i class="icon-globe" aria-hidden="true"></i> ${strings.online}</span>`
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

    // --- API.Bible sync button ---
    const syncBtn = document.getElementById('btn-sync-api-bible');

    if (syncBtn) {
        // Show/hide sync row based on api_bible toggle state
        const apiBibleToggle = document.querySelector('input[name="jform[params][provider_api_bible]"]');
        const syncRow = document.getElementById('api-bible-sync-row');

        const updateSyncVisibility = () => {
            const checked = document.querySelector('input[name="jform[params][provider_api_bible]"]:checked');

            if (syncRow && checked) {
                syncRow.style.display = checked.value === '1' ? '' : 'none';
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
                        statusEl.innerHTML = `<span class="text-success">${strings.syncComplete.replace('%s', result.count)}</span>`;
                        Joomla.renderMessages({'message': [result.message]});
                        loadTranslations(true);
                    } else {
                        statusEl.innerHTML = `<span class="text-danger">${strings.syncFailed}</span>`;
                        Joomla.renderMessages({'error': [result.message || strings.syncFailed]});
                    }
                })
                .catch(() => {
                    syncBtn.disabled = false;
                    statusEl.innerHTML = `<span class="text-danger">${strings.syncFailed}</span>`;
                });
        });
    }

    // Dynamic badge for local translation count (placed in card header)
    const localBadge = document.createElement('span');
    localBadge.id = 'local-provider-status';
    localBadge.className = 'badge bg-info';
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
                    badge.className = 'badge bg-success';
                    badge.innerHTML = `<i class="icon-checkmark-circle" aria-hidden="true"></i> ${data.local_count} ${strings.statusInstalled}`;
                } else {
                    badge.className = 'badge bg-warning text-dark';
                    badge.innerHTML = `<i class="icon-warning" aria-hidden="true"></i> ${strings.statusNone}`;
                }
            })
            .catch(() => {
                const badge = document.getElementById('local-provider-status');
                badge.className = 'badge bg-secondary';
                badge.textContent = strings.statusUnknown;
            });
    };

    // Initial badge load
    refreshLocalBadge();

    // --- Local translations management ---

    /**
     * Fetch and render the translations table.
     *
     * @param {boolean} silent  When true, skip the loading spinner (used after
     *                          auto-download so the table doesn't flash).
     */
    const loadTranslations = (silent = false) => {
        const container = document.getElementById('translations-list');

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

                renderTranslations(data.translations, data.total_size || 0);
            })
            .catch(() => {
                container.innerHTML = `<div class="alert alert-warning">${strings.loadError}</div>`;
            });
    };

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
            i++;
        }

        return size.toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
    };

    // Language name mapping for common ISO codes
    const languageNames = {
        en: 'English', de: 'German', fr: 'French', es: 'Spanish',
        pt: 'Portuguese', ru: 'Russian', it: 'Italian', hu: 'Hungarian',
        ro: 'Romanian', ko: 'Korean', zh: 'Chinese', la: 'Latin',
        nl: 'Dutch', no: 'Norwegian', cs: 'Czech', pl: 'Polish',
        sv: 'Swedish', da: 'Danish', fi: 'Finnish', el: 'Greek',
        he: 'Hebrew', ar: 'Arabic', ja: 'Japanese', hi: 'Hindi',
        tr: 'Turkish', uk: 'Ukrainian', vi: 'Vietnamese', th: 'Thai',
        af: 'Afrikaans', sw: 'Swahili', tl: 'Tagalog',
    };

    const getLanguageName = (code) => {
        if (!code) {
            return strings.language || 'Other';
        }

        const short = code.substring(0, 2).toLowerCase();
        return languageNames[short] || code;
    };

    const renderTranslations = (translations, totalSize) => {
        const container = document.getElementById('translations-list');
        let html = '<div class="table-responsive"><table class="table table-striped table-sm">';
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

            // Insert language group header
            if (lang !== currentLang) {
                currentLang = lang;
                html += `<tr class="table-light"><td colspan="7"><strong>${lang}</strong></td></tr>`;
            }

            const isInstalled = parseInt(t.installed) === 1;
            const statusBadge = isInstalled
                ? `<span class="badge bg-success">${strings.installed}</span>`
                : `<span class="badge bg-secondary">${strings.notInstalled}</span>`;
            const verseCount = isInstalled ? t.verse_count : '-';
            const dataSize = isInstalled
                ? formatSize(t.data_size)
                : (t.estimated_size > 0 ? '~' + formatSize(t.estimated_size) : '-');
            const actionBtn = isInstalled
                ? `<button type="button" class="btn btn-sm btn-danger btn-remove-translation" data-abbr="${t.abbreviation}">${strings.remove}</button>`
                : `<button type="button" class="btn btn-sm btn-primary btn-download-translation" data-abbr="${t.abbreviation}">${strings.download}</button>`;

            html += '<tr>';
            html += `<td>${t.name}</td>`;
            html += `<td><code>${t.abbreviation.toUpperCase()}</code></td>`;
            html += `<td>${t.source || 'getbible'}</td>`;
            html += `<td>${statusBadge}</td>`;
            html += `<td>${verseCount}</td>`;
            html += `<td>${dataSize}</td>`;
            html += `<td>${actionBtn}</td>`;
            html += '</tr>';
        });

        // Total row if any translations are installed
        if (totalSize > 0) {
            html += `<tr class="fw-bold"><td colspan="5" class="text-end">${strings.totalSize}</td>`;
            html += `<td>${formatSize(totalSize)}</td><td></td></tr>`;
        }

        html += '</tbody></table></div>';
        container.innerHTML = html;

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
                            Joomla.renderMessages({'message': [result.message]});
                        } else {
                            Joomla.renderMessages({'error': [result.message]});
                        }

                        loadTranslations(true);
                        refreshLocalBadge();
                    })
                    .catch(() => {
                        Joomla.renderMessages({'error': [strings.downloadFailed]});
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
                            Joomla.renderMessages({'message': [result.message]});
                        } else {
                            Joomla.renderMessages({'error': [result.message]});
                        }

                        loadTranslations(true);
                        refreshLocalBadge();
                    })
                    .catch(() => {
                        loadTranslations(true);
                    });
            });
        });

        // Show/hide "Remove All" button based on whether any translations are installed
        const hasInstalled = translations.some((t) => parseInt(t.installed) === 1);
        const removeAllBtn = document.getElementById('btn-remove-all-translations');

        if (removeAllBtn) {
            removeAllBtn.classList.toggle('d-none', !hasInstalled);
        }

        // Auto-download bundled translations that aren't installed yet (once only)
        if (!autoDownloadRunning) {
            const pending = translations.filter((t) => parseInt(t.bundled) === 1 && parseInt(t.installed) === 0);

            if (pending.length > 0) {
                autoDownloadRunning = true;

                pending.forEach((t) => {
                    const btn = container.querySelector(`.btn-download-translation[data-abbr="${t.abbreviation}"]`);

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${strings.downloading}`;
                    }
                });
                autoDownloadBundled(pending);
            }
        }
    };

    const autoDownloadBundled = (queue) => {
        let index = 0;
        const downloaded = [];

        const next = () => {
            if (index >= queue.length) {
                autoDownloadRunning = false;

                if (downloaded.length > 0) {
                    Joomla.renderMessages({'message': [strings.bundledDone.replace('%s', downloaded.join(', '))]});
                }

                // Silent refresh — no spinner, just swap the table in place
                loadTranslations(true);
                refreshLocalBadge();
                return;
            }

            const abbr = queue[index].abbreviation;
            index++;

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
    };

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
                        Joomla.renderMessages({'message': [result.message]});
                    } else {
                        Joomla.renderMessages({'error': [result.message]});
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
