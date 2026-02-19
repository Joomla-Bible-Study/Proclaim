(function () {
    'use strict';

    /**
     * Scripture Version Switcher
     *
     * Searchable Bible version dropdown with language grouping.
     * Site language translations appear first; other languages are
     * revealed via "Show All Languages" toggle. Search filters across
     * all languages regardless of the toggle state, matching both
     * version names and abbreviations (e.g. "nkjv", "esv").
     *
     * UI is only committed on successful fetch; failures restore the
     * previous state and show a non-destructive error banner with
     * diagnostic info. Successfully fetched versions are cached
     * client-side for instant restore.
     *
     * @package  Proclaim.Site
     * @since    10.1.0
     */

    document.addEventListener('DOMContentLoaded', () => {

        // Resolve AJAX endpoint URL from PHP (SEF-safe)
        const scriptureOpts = Joomla.getOptions('com_proclaim.scripture') || {};
        const ajaxBaseUrl = scriptureOpts.ajaxUrl || '';
        const isDebug = Joomla.getOptions('system.debug') || false;

        /**
         * Get a translated string from Joomla.Text with a proper fallback.
         * Joomla.Text._() returns the key itself when not found, so we compare
         * the result against the key to detect missing translations.
         *
         * @param {string} key       Language key
         * @param {string} fallback  Fallback text if key is not registered
         * @returns {string}
         */
        const txt = (key, fallback) => {
            if (!Joomla.Text) {
                return fallback;
            }

            const result = Joomla.Text._(key);

            return (result && result !== key) ? result : fallback;
        };

        /**
         * Maximum number of automatic retries for transient failures.
         * @type {number}
         */
        const MAX_AUTO_RETRIES = 2;

        /**
         * Delay between automatic retries in milliseconds.
         * @type {number}
         */
        const RETRY_DELAY_MS = 3000;

        /**
         * Sleep helper for retry delays.
         *
         * @param {number} ms  Milliseconds to wait
         * @returns {Promise<void>}
         */
        const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

        /**
         * Per-switcher version cache. Keyed by switcher element, value is a Map
         * of version abbreviation to {body, copyright}.
         *
         * @type {WeakMap<HTMLElement, Map<string, {body: string, copyright: string}>>}
         */
        const versionCaches = new WeakMap();

        /**
         * Get or create the version cache for a switcher element.
         *
         * @param {HTMLElement} switcher  The switcher container
         * @returns {Map<string, {body: string, copyright: string}>}
         */
        const getCache = (switcher) => {
            if (!versionCaches.has(switcher)) {
                versionCaches.set(switcher, new Map());
            }

            return versionCaches.get(switcher);
        };

        /**
         * Perform a single AJAX call to the scripture endpoint.
         *
         * @param {string} reference  Scripture reference
         * @param {string} version    Bible version abbreviation
         * @returns {Promise<object>}  Parsed JSON response
         */
        const doFetch = async (reference, version) => {
            const token = Joomla.getOptions('csrf.token') || '';
            const url = `${ajaxBaseUrl
        }&reference=${encodeURIComponent(reference)}`
                + `&version=${encodeURIComponent(version)}`
                + `&${token}=1`;

            const response = await fetch(url);

            if (!response.ok) {
                return { success: false, retryable: true, message: `HTTP ${response.status}` };
            }

            const text = await response.text();

            // Guard against HTML responses (Joomla redirected or gatekeeper)
            if (text.trimStart().startsWith('<!') || text.trimStart().startsWith('<html')) {
                return { success: false, retryable: true, message: 'HTML gatekeeper response' };
            }

            return JSON.parse(text);
        };

        /**
         * Show a non-destructive error banner below restored scripture content.
         * The banner includes diagnostic info and a retry button.
         * Content is from our own CwmscriptureController — not user input.
         *
         * @param {HTMLElement} scriptureText  The .scripture-text container
         * @param {string}      reference      Scripture reference
         * @param {string}      version        Bible version that was attempted
         * @param {string}      errorMessage   Error detail from server
         * @param {string}      provider       Provider name from server (if available)
         * @param {Function}    retryCallback  Function to call when retry is clicked
         */
        const showErrorBanner = (scriptureText, reference, version, errorMessage, provider, retryCallback) => {
            // Remove any existing error banner
            const existing = scriptureText.querySelector('.scripture-error-banner');

            if (existing) {
                existing.remove();
            }

            const banner = document.createElement('div');
            banner.className = 'scripture-error-banner alert alert-warning alert-dismissible fade show mt-2';
            banner.setAttribute('role', 'alert');

            const mainMsg = txt('JBS_CMN_SCRIPTURE_SWITCH_FAILED', 'Could not load the requested Bible version.');
            const retryLabel = txt('JBS_CMN_SCRIPTURE_RETRY', 'Try Again');
            const detailsLabel = txt('JBS_CMN_SCRIPTURE_ERROR_DETAILS', 'Details');

            // Build banner content using DOM methods so server-provided text is never parsed as HTML
            const msgDiv = document.createElement('div');
            msgDiv.textContent = mainMsg;
            banner.appendChild(msgDiv);

            if (errorMessage || provider) {
                const items = [];
                if (version) { items.push(`Version: ${version.toUpperCase()}`); }
                if (provider) { items.push(`Provider: ${provider}`); }
                if (errorMessage) { items.push(`Error: ${errorMessage}`); }

                const details = document.createElement('details');
                details.className = 'mt-1 small';
                const summary = document.createElement('summary');
                summary.textContent = detailsLabel;
                details.appendChild(summary);
                const ul = document.createElement('ul');
                ul.className = 'mb-0';
                items.forEach((item) => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    ul.appendChild(li);
                });
                details.appendChild(ul);
                banner.appendChild(details);
            }

            const retryBtn = document.createElement('button');
            retryBtn.type = 'button';
            retryBtn.className = 'btn btn-sm btn-outline-secondary scripture-retry-banner-btn mt-1 me-2';
            const retryIcon = document.createElement('i');
            retryIcon.className = 'fas fa-redo';
            retryIcon.setAttribute('aria-hidden', 'true');
            retryBtn.appendChild(retryIcon);
            retryBtn.appendChild(document.createTextNode(` ${retryLabel}`));
            banner.appendChild(retryBtn);

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'btn-close';
            closeBtn.setAttribute('data-bs-dismiss', 'alert');
            closeBtn.setAttribute('aria-label', 'Close');
            banner.appendChild(closeBtn);

            scriptureText.appendChild(banner);

            if (retryBtn && retryCallback) {
                retryBtn.addEventListener('click', () => {
                    banner.remove();
                    retryCallback();
                });
            }
        };

        /**
         * Show a "Try Again" button inside a scripture body element.
         * Used only for the PHP-rendered retry buttons on initial page load.
         * Content is from our own CwmscriptureController — not user input.
         *
         * @param {HTMLElement} body       The .scripture-body element
         * @param {string}      reference  Scripture reference
         * @param {string}      version    Bible version abbreviation
         * @param {HTMLElement|null} copyright  The .scripture-copyright element
         */
        const showRetryButton = (body, reference, version, copyright) => {
            // All content originates from our own CwmscriptureController server endpoint
            body.innerHTML = `<p class="text-muted"><em>${
            txt('JBS_CMN_SCRIPTURE_UNAVAILABLE', 'Scripture text temporarily unavailable')
        }</em></p>`
                + '<button type="button" class="btn btn-sm btn-outline-secondary scripture-retry-btn">'
                + `<i class="fas fa-redo" aria-hidden="true"></i> ${
                txt('JBS_CMN_SCRIPTURE_RETRY', 'Try Again')
            }</button>`;

            if (copyright) {
                copyright.style.display = 'none';
            }

            // Attach one-time click handler for manual retry
            const btn = body.querySelector('.scripture-retry-btn');

            if (btn) {
                btn.addEventListener('click', async () => {
                    btn.disabled = true;
                    body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                    try {
                        const data = await doFetch(reference, version);

                        if (data.success && data.text) {
                            let fallbackHtml = '';

                            if (data.fallback && data.translation) {
                                const fallbackMsg = txt('JBS_CMN_SCRIPTURE_FALLBACK', 'Showing in %s (requested version unavailable)')
                                    .replace('%s', data.translation.toUpperCase());
                                fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                            }

                            // Server response from CwmscriptureController
                            body.innerHTML = fallbackHtml + data.text;

                            if (copyright) {
                                copyright.textContent = data.copyright || '';
                                copyright.style.display = data.copyright ? '' : 'none';
                            }
                        } else {
                            showRetryButton(body, reference, version, copyright);
                        }
                    } catch {
                        showRetryButton(body, reference, version, copyright);
                    }
                });
            }
        };

        /**
         * Fetch a new passage via AJAX with automatic retry on transient failures.
         * UI is only committed after a successful response. On failure, previous
         * content is restored and a non-destructive error banner is shown.
         * All body content originates from our own CwmscriptureController endpoint.
         *
         * @param {HTMLElement} switcher  The .scripture-version-switcher container
         * @param {string}      version   Bible version abbreviation
         * @param {string}      label     Display name for the toggle button
         */
        const fetchPassage = async (switcher, version, label) => {
            const { reference } = switcher.dataset;

            // Switcher lives inside .scripture-text; find body/copyright as siblings
            const scriptureText = switcher.closest('.scripture-text');

            if (!scriptureText) {
                return;
            }

            const body = scriptureText.querySelector('.scripture-body');
            const copyright = scriptureText.querySelector('.scripture-copyright');

            if (!body) {
                return;
            }

            const hiddenSelect = switcher.querySelector('.scripture-version-select');
            const toggleText = switcher.querySelector('.scripture-dropdown-text');
            const cache = getCache(switcher);

            // Read current state (the version we're currently displaying)
            const currentVersion = switcher._currentVersion || switcher.dataset.currentVersion || '';

            // Short-circuit: clicking the version that's already displayed
            if (version === currentVersion) {
                return;
            }

            // Check client-side cache first — instant restore, no network
            if (cache.has(version)) {
                const cached = cache.get(version);

                // Cached content from previous successful CwmscriptureController response
                body.innerHTML = cached.body;

                if (copyright) {
                    copyright.textContent = cached.copyright;
                    copyright.style.display = cached.copyright ? '' : 'none';
                }

                if (hiddenSelect) {
                    hiddenSelect.value = version;
                    hiddenSelect.dispatchEvent(new Event('change', { bubbles: false }));
                }

                if (toggleText) {
                    toggleText.textContent = label;
                }

                switcher._currentVersion = version;

                // Remove any stale error banner
                const staleBanner = scriptureText.querySelector('.scripture-error-banner');

                if (staleBanner) {
                    staleBanner.remove();
                }

                return;
            }

            if (!ajaxBaseUrl) {
                if (isDebug) {
                    console.warn('[Proclaim] Scripture switcher: no AJAX URL configured');
                }

                return;
            }

            // Save pre-fetch state for rollback
            const previousBody = body.innerHTML;
            const previousCopyright = copyright ? copyright.textContent : '';

            // Show loading state — but do NOT update dropdown text or select yet
            body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            // Remove any existing error banner while loading
            const existingBanner = scriptureText.querySelector('.scripture-error-banner');

            if (existingBanner) {
                existingBanner.remove();
            }

            let lastErrorMessage = '';
            let lastProvider = '';

            for (let attempt = 0; attempt <= MAX_AUTO_RETRIES; attempt++) {
                try {
                    if (attempt > 0) {
                        body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
                            + `<em class="text-muted">${
                            txt('JBS_CMN_SCRIPTURE_SERVICE_BUSY', 'Bible service is temporarily busy. Retrying...')
                        }</em>`;
                        await sleep(RETRY_DELAY_MS);
                    }

                    const data = await doFetch(reference, version);

                    if (data.success && data.text) {
                        // Show fallback notice if a different version was served
                        let fallbackHtml = '';

                        if (data.fallback && data.translation) {
                            const fallbackMsg = txt('JBS_CMN_SCRIPTURE_FALLBACK', 'Showing in %s (requested version unavailable)')
                                .replace('%s', data.translation.toUpperCase());
                            fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                        }

                        // SUCCESS — now commit UI changes
                        // Body content from CwmscriptureController server response
                        body.innerHTML = fallbackHtml + data.text;

                        if (copyright) {
                            copyright.textContent = data.copyright || '';
                            copyright.style.display = data.copyright ? '' : 'none';
                        }

                        if (hiddenSelect) {
                            hiddenSelect.value = version;
                            hiddenSelect.dispatchEvent(new Event('change', { bubbles: false }));
                        }

                        if (toggleText) {
                            toggleText.textContent = label;
                        }

                        switcher._currentVersion = version;

                        // Cache this version's content for instant restore
                        cache.set(version, {
                            body: fallbackHtml + data.text,
                            copyright: data.copyright || '',
                        });

                        return;
                    } if (data.success && data.isIframe && data.iframeUrl) {
                        const iframeHtml = `<iframe src="${data.iframeUrl}" width="100%" height="400" `
                            + 'style="border:0;" title="Bible Passage"></iframe>';
                        body.innerHTML = iframeHtml;

                        if (copyright) {
                            copyright.style.display = 'none';
                        }

                        if (hiddenSelect) {
                            hiddenSelect.value = version;
                            hiddenSelect.dispatchEvent(new Event('change', { bubbles: false }));
                        }

                        if (toggleText) {
                            toggleText.textContent = label;
                        }

                        switcher._currentVersion = version;

                        return;
                    }

                    // Not successful — track diagnostic info
                    lastErrorMessage = data.message || '';
                    lastProvider = data.provider || '';

                    if (!data.retryable || attempt >= MAX_AUTO_RETRIES) {
                        if (isDebug && data.message) {
                            console.warn('[Proclaim] Scripture fetch error:', data.message);
                        }

                        break;
                    }

                    // Retryable — continue loop
                } catch (error) {
                    lastErrorMessage = error.message || String(error);

                    if (attempt >= MAX_AUTO_RETRIES) {
                        if (isDebug) {
                            console.error('[Proclaim] Scripture fetch failed:', error.message || error);
                        }

                        break;
                    }

                    // Network error — retry
                }
            }

            // FAILURE — restore previous state
            // Restoring previously displayed CwmscriptureController content
            body.innerHTML = previousBody;

            if (copyright) {
                copyright.textContent = previousCopyright;
                copyright.style.display = previousCopyright ? '' : 'none';
            }

            // Dropdown text and select were never changed, so no rollback needed

            // Show non-destructive error banner below the restored content
            showErrorBanner(scriptureText, reference, version, lastErrorMessage, lastProvider, () => {
                fetchPassage(switcher, version, label);
            });
        };

        /**
         * Initialise a single searchable scripture switcher.
         *
         * @param {HTMLElement} switcher  The .scripture-searchable-switcher container
         */
        const initSwitcher = (switcher) => {
            const toggle = switcher.querySelector('.scripture-dropdown-toggle');
            const menu = switcher.querySelector('.scripture-dropdown-menu');
            const searchInput = menu ? menu.querySelector('.scripture-dropdown-search input') : null;
            const showAllBtn = menu ? menu.querySelector('.scripture-dropdown-show-all') : null;
            const otherLangs = menu ? menu.querySelectorAll('.scripture-other-lang') : [];
            const items = menu ? menu.querySelectorAll('.scripture-dropdown-item') : [];

            if (!toggle || !menu) {
                return;
            }

            // Track the currently displayed version
            const initialVersion = switcher.dataset.currentVersion || '';
            switcher._currentVersion = initialVersion;

            // Cache the initial content so switching back is instant
            const scriptureText = switcher.closest('.scripture-text');

            if (scriptureText && initialVersion) {
                const body = scriptureText.querySelector('.scripture-body');
                const copyright = scriptureText.querySelector('.scripture-copyright');

                if (body && body.innerHTML.trim()) {
                    const cache = getCache(switcher);
                    cache.set(initialVersion, {
                        body: body.innerHTML,
                        copyright: copyright ? copyright.textContent : '',
                    });
                }
            }

            let isOpen = false;
            let othersVisible = false;

            // -- Search / filter (defined before openMenu which calls filterItems) --
            const footer = menu.querySelector('.scripture-dropdown-footer');

            const filterItems = (query) => {
                const q = query.toLowerCase().trim();

                items.forEach((item) => {
                    const text = item.textContent.toLowerCase();
                    // Match against both display name and abbreviation (data-value)
                    const abbr = (item.dataset.value || '').toLowerCase();
                    item.style.display = (!q || text.includes(q) || abbr.includes(q)) ? '' : 'none';
                });

                // Show/hide group wrappers based on whether they have visible items
                menu.querySelectorAll('.scripture-dropdown-group').forEach((group) => {
                    const visible = group.querySelectorAll('.scripture-dropdown-item:not([style*="display: none"])');
                    group.style.display = visible.length > 0 ? '' : 'none';
                });

                if (q) {
                    // While searching, reveal other-lang groups that have matches
                    otherLangs.forEach((group) => {
                        const visible = group.querySelectorAll('.scripture-dropdown-item:not([style*="display: none"])');

                        if (visible.length > 0) {
                            group.style.display = '';
                        }
                    });

                    // Hide footer during active search
                    if (footer) {
                        footer.style.display = 'none';
                    }
                } else {
                    // No query — respect the show-all toggle
                    otherLangs.forEach((group) => {
                        group.style.display = othersVisible ? '' : 'none';
                    });

                    if (footer) {
                        footer.style.display = '';
                    }
                }
            };

            if (searchInput) {
                searchInput.addEventListener('input', () => filterItems(searchInput.value));
            }

            // -- Open / close helpers --
            const openMenu = () => {
                menu.style.display = 'flex';
                toggle.setAttribute('aria-expanded', 'true');
                isOpen = true;

                if (searchInput) {
                    searchInput.value = '';
                    filterItems('');
                    requestAnimationFrame(() => searchInput.focus());
                }
            };

            const closeMenu = () => {
                menu.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
                isOpen = false;
            };

            // Toggle button click
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();

                if (isOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (isOpen && !switcher.contains(e.target)) {
                    closeMenu();
                }
            });

            // Close on Escape
            switcher.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && isOpen) {
                    closeMenu();
                    toggle.focus();
                }
            });

            // -- Show All Languages toggle --
            if (showAllBtn) {
                const showText = showAllBtn.textContent; // "Show All Languages"
                const hideText = showAllBtn.dataset.hideText || 'Hide Other Languages';

                showAllBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    othersVisible = !othersVisible;
                    showAllBtn.textContent = othersVisible ? hideText : showText;

                    otherLangs.forEach((group) => {
                        group.style.display = othersVisible ? '' : 'none';
                    });
                });
            }

            // -- Item selection --
            items.forEach((item) => {
                item.addEventListener('click', () => {
                    const version = item.dataset.value;
                    const label = item.textContent;

                    // Short-circuit: already displaying this version
                    if (version === switcher._currentVersion) {
                        closeMenu();

                        return;
                    }

                    // Update active state
                    items.forEach((i) => i.classList.remove('active'));
                    item.classList.add('active');

                    closeMenu();
                    fetchPassage(switcher, version, label);
                });
            });

            // Keyboard navigation inside menu
            menu.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    const visible = [...items].filter((i) => i.style.display !== 'none');
                    const current = visible.indexOf(document.activeElement);
                    let next = e.key === 'ArrowDown' ? current + 1 : current - 1;

                    if (next < 0) {
                        next = visible.length - 1;
                    }

                    if (next >= visible.length) {
                        next = 0;
                    }

                    visible[next]?.focus();
                } else if (e.key === 'Enter' && document.activeElement.classList.contains('scripture-dropdown-item')) {
                    document.activeElement.click();
                }
            });

            // Make items focusable for keyboard nav
            items.forEach((item) => {
                item.setAttribute('tabindex', '0');
            });
        };

        // -- Initialise all searchable switchers on the page --
        document.querySelectorAll('.scripture-searchable-switcher').forEach(initSwitcher);

        // -- Fallback: plain <select> switchers (non-enhanced) --
        document.querySelectorAll('.scripture-version-select').forEach((select) => {
            // Skip hidden selects inside searchable switchers (already handled)
            if (select.closest('.scripture-searchable-switcher')) {
                return;
            }

            // Track current version for the plain select
            select._currentVersion = select.value || '';

            // Cache initial content
            const initContainer = select.closest('.scripture-version-switcher');
            const initScriptureText = initContainer ? initContainer.closest('.scripture-text') : null;

            if (initScriptureText && select._currentVersion) {
                const initBody = initScriptureText.querySelector('.scripture-body');
                const initCopyright = initScriptureText.querySelector('.scripture-copyright');

                if (initBody && initBody.innerHTML.trim()) {
                    if (!versionCaches.has(select)) {
                        versionCaches.set(select, new Map());
                    }

                    versionCaches.get(select).set(select._currentVersion, {
                        body: initBody.innerHTML,
                        copyright: initCopyright ? initCopyright.textContent : '',
                    });
                }
            }

            select.addEventListener('change', async (event) => {
                const version = event.target.value;
                const { reference } = event.target.dataset;
                const container = event.target.closest('.scripture-version-switcher');

                if (!container) {
                    return;
                }

                // Switcher lives inside .scripture-text; find body/copyright as siblings
                const scriptureText = container.closest('.scripture-text');

                if (!scriptureText) {
                    return;
                }

                const body = scriptureText.querySelector('.scripture-body');
                const copyright = scriptureText.querySelector('.scripture-copyright');

                if (!body) {
                    return;
                }

                // Short-circuit: same version already displayed
                if (version === select._currentVersion) {
                    return;
                }

                // Check client-side cache
                const selectCache = versionCaches.has(select) ? versionCaches.get(select) : new Map();

                if (selectCache.has(version)) {
                    const cached = selectCache.get(version);

                    // Cached content from previous successful CwmscriptureController response
                    body.innerHTML = cached.body;

                    if (copyright) {
                        copyright.textContent = cached.copyright;
                        copyright.style.display = cached.copyright ? '' : 'none';
                    }

                    select._currentVersion = version;

                    const staleBanner = scriptureText.querySelector('.scripture-error-banner');

                    if (staleBanner) {
                        staleBanner.remove();
                    }

                    return;
                }

                if (!ajaxBaseUrl) {
                    return;
                }

                // Save pre-fetch state
                const previousVersion = select._currentVersion;
                const previousBody = body.innerHTML;
                const previousCopyright = copyright ? copyright.textContent : '';

                // Show loading — do NOT commit select value change yet
                // Revert the select to previous value during fetch
                select.value = previousVersion;
                body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                select.disabled = true;

                // Remove existing error banner
                const existingBanner = scriptureText.querySelector('.scripture-error-banner');

                if (existingBanner) {
                    existingBanner.remove();
                }

                let lastErrorMessage = '';
                let lastProvider = '';

                for (let attempt = 0; attempt <= MAX_AUTO_RETRIES; attempt++) {
                    try {
                        if (attempt > 0) {
                            body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
                                + `<em class="text-muted">${
                                txt('JBS_CMN_SCRIPTURE_SERVICE_BUSY', 'Bible service is temporarily busy. Retrying...')
                            }</em>`;
                            await sleep(RETRY_DELAY_MS);
                        }

                        const data = await doFetch(reference, version);

                        if (data.success && data.text) {
                            // Show fallback notice if a different version was served
                            let fallbackHtml = '';

                            if (data.fallback && data.translation) {
                                const fallbackMsg = txt('JBS_CMN_SCRIPTURE_FALLBACK', 'Showing in %s (requested version unavailable)')
                                    .replace('%s', data.translation.toUpperCase());
                                fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                            }

                            // SUCCESS — commit UI
                            // Body content from CwmscriptureController server response
                            body.innerHTML = fallbackHtml + data.text;

                            if (copyright) {
                                copyright.textContent = data.copyright || '';
                                copyright.style.display = data.copyright ? '' : 'none';
                            }

                            select.value = version;
                            select._currentVersion = version;
                            select.disabled = false;

                            // Cache
                            if (!versionCaches.has(select)) {
                                versionCaches.set(select, new Map());
                            }

                            versionCaches.get(select).set(version, {
                                body: fallbackHtml + data.text,
                                copyright: data.copyright || '',
                            });

                            return;
                        } if (data.success && data.isIframe && data.iframeUrl) {
                            body.innerHTML = `<iframe src="${data.iframeUrl}" width="100%" height="400" `
                                + 'style="border:0;" title="Bible Passage"></iframe>';

                            if (copyright) {
                                copyright.style.display = 'none';
                            }

                            select.value = version;
                            select._currentVersion = version;
                            select.disabled = false;

                            return;
                        }

                        lastErrorMessage = data.message || '';
                        lastProvider = data.provider || '';

                        if (!data.retryable || attempt >= MAX_AUTO_RETRIES) {
                            if (isDebug && data.message) {
                                console.warn('[Proclaim] Scripture fetch error:', data.message);
                            }

                            break;
                        }
                    } catch (error) {
                        lastErrorMessage = error.message || String(error);

                        if (attempt >= MAX_AUTO_RETRIES) {
                            if (isDebug) {
                                console.error('[Proclaim] Scripture fetch failed:', error.message || error);
                            }

                            break;
                        }
                    }
                }

                // FAILURE — restore previous state
                // Restoring previously displayed CwmscriptureController content
                body.innerHTML = previousBody;

                if (copyright) {
                    copyright.textContent = previousCopyright;
                    copyright.style.display = previousCopyright ? '' : 'none';
                }

                // Select stays at previousVersion (was reverted before fetch)
                select.disabled = false;

                // Show error banner
                showErrorBanner(scriptureText, reference, version, lastErrorMessage, lastProvider, () => {
                    // Trigger the fetch again via the change event path
                    select.value = version;
                    select.dispatchEvent(new Event('change'));
                });
            });
        });

        // -- Handle PHP-rendered "Try Again" buttons (from initial page load failures) --
        document.querySelectorAll('.scripture-unavailable .scripture-retry-btn').forEach((btn) => {
            const container = btn.closest('.scripture-unavailable');

            if (!container || !ajaxBaseUrl) {
                return;
            }

            const { reference } = container.dataset;
            const { version } = container.dataset;
            const body = container.querySelector('.scripture-body');
            const copyright = container.querySelector('.scripture-copyright');

            if (!body || !reference) {
                return;
            }

            btn.addEventListener('click', async () => {
                btn.disabled = true;
                body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                try {
                    const data = await doFetch(reference, version);

                    if (data.success && data.text) {
                        let fallbackHtml = '';

                        if (data.fallback && data.translation) {
                            const fallbackMsg = txt('JBS_CMN_SCRIPTURE_FALLBACK', 'Showing in %s (requested version unavailable)')
                                .replace('%s', data.translation.toUpperCase());
                            fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                        }

                        // Server response from CwmscriptureController
                        body.innerHTML = fallbackHtml + data.text;
                        container.classList.remove('scripture-unavailable');

                        if (copyright) {
                            copyright.textContent = data.copyright || '';
                            copyright.style.display = data.copyright ? '' : 'none';
                        }
                    } else {
                        showRetryButton(body, reference, version, copyright);
                    }
                } catch {
                    showRetryButton(body, reference, version, copyright);
                }
            });
        });
    });

})();
