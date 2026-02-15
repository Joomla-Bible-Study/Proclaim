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
     * @package  Proclaim.Site
     * @since    10.1.0
     */

    document.addEventListener('DOMContentLoaded', () => {
        // Resolve AJAX endpoint URL from PHP (SEF-safe)
        const scriptureOpts = Joomla.getOptions('com_proclaim.scripture') || {};
        const ajaxBaseUrl = scriptureOpts.ajaxUrl || '';
        const isDebug = Joomla.getOptions('system.debug') || false;

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
         * Perform a single AJAX call to the scripture endpoint.
         *
         * @param {string} reference  Scripture reference
         * @param {string} version    Bible version abbreviation
         * @returns {Promise<object>}  Parsed JSON response
         */
        const doFetch = async (reference, version) => {
            const token = Joomla.getOptions('csrf.token') || '';
            const url = ajaxBaseUrl
                + `&reference=${encodeURIComponent(reference)}`
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
         * Show a "Try Again" button inside a scripture body element.
         *
         * @param {HTMLElement} body       The .scripture-body element
         * @param {string}      reference  Scripture reference
         * @param {string}      version    Bible version abbreviation
         * @param {HTMLElement|null} copyright  The .scripture-copyright element
         */
        const showRetryButton = (body, reference, version, copyright) => {
            body.innerHTML = '<p class="text-muted"><em>'
                + (Joomla.Text ? Joomla.Text._('JBS_CMN_SCRIPTURE_UNAVAILABLE') || 'Scripture text temporarily unavailable' : 'Scripture text temporarily unavailable')
                + '</em></p>'
                + '<button type="button" class="btn btn-sm btn-outline-secondary scripture-retry-btn">'
                + '<i class="fas fa-redo" aria-hidden="true"></i> '
                + (Joomla.Text ? Joomla.Text._('JBS_CMN_SCRIPTURE_RETRY') || 'Try Again' : 'Try Again')
                + '</button>';

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
                                const fallbackMsg = (Joomla.Text
                                    ? Joomla.Text._('JBS_CMN_SCRIPTURE_FALLBACK') || 'Showing in %s (requested version unavailable)'
                                    : 'Showing in %s (requested version unavailable)')
                                    .replace('%s', data.translation.toUpperCase());
                                fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                            }

                            body.innerHTML = fallbackHtml + data.text;

                            if (copyright) {
                                copyright.textContent = data.copyright || '';
                                copyright.style.display = data.copyright ? '' : 'none';
                            }
                        } else {
                            showRetryButton(body, reference, version, copyright);
                        }
                    } catch (error) {
                        showRetryButton(body, reference, version, copyright);
                    }
                });
            }
        };

        /**
         * Fetch a new passage via AJAX with automatic retry on transient failures.
         *
         * @param {HTMLElement} switcher  The .scripture-version-switcher container
         * @param {string}      version   Bible version abbreviation
         * @param {string}      label     Display name for the toggle button
         */
        const fetchPassage = async (switcher, version, label) => {
            const reference = switcher.dataset.reference;

            // Switcher lives inside .scripture-text; find body/copyright as siblings
            const scriptureText = switcher.closest('.scripture-text')
                || switcher.closest('.scripture-popup-content');

            if (!scriptureText) {
                return;
            }

            const body = scriptureText.querySelector('.scripture-body');
            const copyright = scriptureText.querySelector('.scripture-copyright');

            if (!body) {
                return;
            }

            // Update the hidden <select> to keep form state in sync
            const hiddenSelect = switcher.querySelector('.scripture-version-select');

            if (hiddenSelect) {
                hiddenSelect.value = version;
                hiddenSelect.dispatchEvent(new Event('change', { bubbles: false }));
            }

            // Update button text
            const toggleText = switcher.querySelector('.scripture-dropdown-text');

            if (toggleText) {
                toggleText.textContent = label;
            }

            if (!ajaxBaseUrl) {
                if (isDebug) {
                    // eslint-disable-next-line no-console
                    console.warn('[Proclaim] Scripture switcher: no AJAX URL configured');
                }

                return;
            }

            // Show loading state
            const originalText = body.innerHTML;
            body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            for (let attempt = 0; attempt <= MAX_AUTO_RETRIES; attempt++) {
                try {
                    if (attempt > 0) {
                        body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
                            + '<em class="text-muted">'
                            + (Joomla.Text ? Joomla.Text._('JBS_CMN_SCRIPTURE_SERVICE_BUSY') || 'Bible service is temporarily busy. Retrying...' : 'Bible service is temporarily busy. Retrying...')
                            + '</em>';
                        await sleep(RETRY_DELAY_MS);
                    }

                    const data = await doFetch(reference, version);

                    if (data.success && data.text) {
                        // Show fallback notice if a different version was served
                        let fallbackHtml = '';

                        if (data.fallback && data.translation) {
                            const fallbackMsg = (Joomla.Text
                                ? Joomla.Text._('JBS_CMN_SCRIPTURE_FALLBACK') || 'Showing in %s (requested version unavailable)'
                                : 'Showing in %s (requested version unavailable)')
                                .replace('%s', data.translation.toUpperCase());
                            fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                        }

                        body.innerHTML = fallbackHtml + data.text;

                        if (copyright) {
                            copyright.textContent = data.copyright || '';
                            copyright.style.display = data.copyright ? '' : 'none';
                        }

                        return;
                    } else if (data.success && data.isIframe && data.iframeUrl) {
                        body.innerHTML = `<iframe src="${data.iframeUrl}" width="100%" height="400" `
                            + `style="border:0;" title="Bible Passage"></iframe>`;

                        if (copyright) {
                            copyright.style.display = 'none';
                        }

                        return;
                    }

                    // Not successful — check if retryable
                    if (!data.retryable || attempt >= MAX_AUTO_RETRIES) {
                        if (isDebug && data.message) {
                            // eslint-disable-next-line no-console
                            console.warn('[Proclaim] Scripture fetch error:', data.message);
                        }

                        showRetryButton(body, reference, version, copyright);

                        return;
                    }

                    // Retryable — continue loop
                } catch (error) {
                    if (attempt >= MAX_AUTO_RETRIES) {
                        if (isDebug) {
                            // eslint-disable-next-line no-console
                            console.error('[Proclaim] Scripture fetch failed:', error.message || error);
                        }

                        showRetryButton(body, reference, version, copyright);

                        return;
                    }

                    // Network error — retry
                }
            }

            // Should not reach here, but safety fallback
            body.innerHTML = originalText;
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

            let isOpen = false;
            let othersVisible = false;

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

            // -- Search / filter --
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

            select.addEventListener('change', async (event) => {
                const version = event.target.value;
                const reference = event.target.dataset.reference;
                const container = event.target.closest('.scripture-version-switcher');

                if (!container) {
                    return;
                }

                // Switcher lives inside .scripture-text; find body/copyright as siblings
                const scriptureText = container.closest('.scripture-text')
                    || container.closest('.scripture-popup-content');

                if (!scriptureText) {
                    return;
                }

                const body = scriptureText.querySelector('.scripture-body');
                const copyright = scriptureText.querySelector('.scripture-copyright');

                if (!body || !ajaxBaseUrl) {
                    return;
                }

                const originalText = body.innerHTML;
                body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                select.disabled = true;

                for (let attempt = 0; attempt <= MAX_AUTO_RETRIES; attempt++) {
                    try {
                        if (attempt > 0) {
                            body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
                                + '<em class="text-muted">'
                                + (Joomla.Text ? Joomla.Text._('JBS_CMN_SCRIPTURE_SERVICE_BUSY') || 'Bible service is temporarily busy. Retrying...' : 'Bible service is temporarily busy. Retrying...')
                                + '</em>';
                            await sleep(RETRY_DELAY_MS);
                        }

                        const data = await doFetch(reference, version);

                        if (data.success && data.text) {
                            // Show fallback notice if a different version was served
                            let fallbackHtml = '';

                            if (data.fallback && data.translation) {
                                const fallbackMsg = (Joomla.Text
                                    ? Joomla.Text._('JBS_CMN_SCRIPTURE_FALLBACK') || 'Showing in %s (requested version unavailable)'
                                    : 'Showing in %s (requested version unavailable)')
                                    .replace('%s', data.translation.toUpperCase());
                                fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                            }

                            body.innerHTML = fallbackHtml + data.text;

                            if (copyright) {
                                copyright.textContent = data.copyright || '';
                                copyright.style.display = data.copyright ? '' : 'none';
                            }

                            select.disabled = false;

                            return;
                        } else if (data.success && data.isIframe && data.iframeUrl) {
                            body.innerHTML = `<iframe src="${data.iframeUrl}" width="100%" height="400" `
                                + `style="border:0;" title="Bible Passage"></iframe>`;

                            if (copyright) {
                                copyright.style.display = 'none';
                            }

                            select.disabled = false;

                            return;
                        }

                        if (!data.retryable || attempt >= MAX_AUTO_RETRIES) {
                            showRetryButton(body, reference, version, copyright);
                            select.disabled = false;

                            return;
                        }
                    } catch (error) {
                        if (attempt >= MAX_AUTO_RETRIES) {
                            if (isDebug) {
                                // eslint-disable-next-line no-console
                                console.error('[Proclaim] Scripture fetch failed:', error.message || error);
                            }

                            showRetryButton(body, reference, version, copyright);
                            select.disabled = false;

                            return;
                        }
                    }
                }

                body.innerHTML = originalText;
                select.disabled = false;
            });
        });

        // -- Handle PHP-rendered "Try Again" buttons (from initial page load failures) --
        document.querySelectorAll('.scripture-unavailable .scripture-retry-btn').forEach((btn) => {
            const container = btn.closest('.scripture-unavailable');

            if (!container || !ajaxBaseUrl) {
                return;
            }

            const reference = container.dataset.reference;
            const version = container.dataset.version;
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
                            const fallbackMsg = (Joomla.Text
                                ? Joomla.Text._('JBS_CMN_SCRIPTURE_FALLBACK') || 'Showing in %s (requested version unavailable)'
                                : 'Showing in %s (requested version unavailable)')
                                .replace('%s', data.translation.toUpperCase());
                            fallbackHtml = `<div class="scripture-fallback-notice text-muted small mb-1"><em>${fallbackMsg}</em></div>`;
                        }

                        body.innerHTML = fallbackHtml + data.text;
                        container.classList.remove('scripture-unavailable');

                        if (copyright) {
                            copyright.textContent = data.copyright || '';
                            copyright.style.display = data.copyright ? '' : 'none';
                        }
                    } else {
                        showRetryButton(body, reference, version, copyright);
                    }
                } catch (error) {
                    showRetryButton(body, reference, version, copyright);
                }
            });
        });
    });

})();
