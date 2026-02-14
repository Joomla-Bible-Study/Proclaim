/* global AbortController */
/* jshint latedef: nofunc */
/**
 * AJAX Filtering & Searching for Frontend Sermon Listing
 *
 * Progressive enhancement: intercepts filter changes, search, sort,
 * and pagination to load results via AJAX. Falls back to standard
 * form submit when JS is disabled or on fetch error.
 *
 * @package  Proclaim.Site
 * @since    10.1.0
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const opts = Joomla.getOptions('com_proclaim.sermonFilters') || {};

    if (!opts.enabled || !opts.ajaxUrl) {
        return;
    }

    // DOM containers
    const listContainer    = document.getElementById('proclaim-sermon-list');
    const paginationTop    = document.getElementById('proclaim-pagination-top');
    const paginationBottom = document.getElementById('proclaim-pagination-bottom');
    const mainContent      = document.getElementById('proclaim-main-content');

    if (!listContainer) {
        return;
    }

    // Find the searchtools form — Joomla renders it as #adminForm
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    let abortController = null;
    let searchDebounceTimer = null;

    const DEBOUNCE_MS = 350;

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Build query string from form data, adding CSRF token and overrides.
     *
     * @param {Object} overrides  Extra params to merge (e.g. limitstart)
     * @returns {string}
     */
    function buildQueryString(overrides) {
        const data = new FormData(form);
        const params = new URLSearchParams();

        for (const [key, value] of data.entries()) {
            // Always include filter/list fields (even empty) so the server
            // can reset session state via getUserStateFromRequest().
            // Skip other empty fields to keep the URL short.
            if (value !== '' && value !== null) {
                params.set(key, value);
            } else if (key.indexOf('filter') === 0 || key.indexOf('list') === 0) {
                params.set(key, '');
            }
        }

        // Apply overrides (e.g. limitstart from pagination click)
        if (overrides) {
            for (const [key, value] of Object.entries(overrides)) {
                params.set(key, value);
            }
        }

        // Append CSRF token
        if (opts.csrfToken) {
            params.set(opts.csrfToken, '1');
        }

        return params.toString();
    }

    /**
     * Show loading overlay on the list container.
     */
    function showLoading() {
        listContainer.classList.add('proclaim-ajax-loading');

        // Insert spinner if not already present
        if (!listContainer.querySelector('.proclaim-ajax-spinner')) {
            const spinner = document.createElement('div');
            spinner.className = 'proclaim-ajax-spinner';
            spinner.innerHTML = '<div class="spinner-border text-primary" role="status">' +
                '<span class="visually-hidden">Loading...</span></div>';
            listContainer.appendChild(spinner);
        }
    }

    /**
     * Remove loading overlay.
     */
    function hideLoading() {
        listContainer.classList.remove('proclaim-ajax-loading');
        const spinner = listContainer.querySelector('.proclaim-ajax-spinner');

        if (spinner) {
            spinner.remove();
        }
    }

    /**
     * Update the browser URL via pushState without reloading.
     *
     * @param {URLSearchParams} params
     */
    function updateUrl(params) {
        const url = new URL(window.location.href);

        // Clear existing search params
        url.search = '';

        // Re-apply relevant filter/list params
        for (const [key, value] of params.entries()) {
            if (key !== 'task' && key !== 'format' && value !== '') {
                url.searchParams.set(key, value);
            }
        }

        history.pushState({ proclaimAjax: true }, '', url.toString());
    }

    /**
     * Smooth-scroll to the list area after results load.
     */
    function scrollToList() {
        const target = mainContent || listContainer;

        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Bind click handlers to pagination links.
     */
    function bindPaginationLinks() {
        document.querySelectorAll('#proclaim-pagination-top a, #proclaim-pagination-bottom a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const href = link.getAttribute('href') || '';
                const url = new URL(href, window.location.origin);
                const limitstart = url.searchParams.get('limitstart') || url.searchParams.get('start') || '0';

                fetchResults({ limitstart: limitstart });
            });
        });
    }

    /**
     * Update top and bottom pagination containers from response data.
     *
     * @param {Object} result  The JSON response from filterAjax
     */
    function updatePagination(result) {
        const paginationHtml = result.pagination || '';
        const counterHtml    = result.pagesCounter || '';

        if (paginationTop) {
            if (result.pagesTotal > 1) {
                paginationTop.innerHTML = '<div class="pagination pagination-centered">' +
                    (counterHtml ? '<p class="counter float-right">' + counterHtml + '</p>' : '') +
                    paginationHtml + '</div>';
            } else {
                paginationTop.innerHTML = '';
            }
        }

        if (paginationBottom) {
            if (result.pagesTotal > 1) {
                paginationBottom.innerHTML = '<nav class="pagination__wrapper" aria-label="Pagination">' +
                    (counterHtml ? '<div class="text-end me-3">' + counterHtml + '</div>' : '') +
                    '</nav>' +
                    '<div class="pagination pagination-centered">' + paginationHtml + '</div>';
            } else {
                paginationBottom.innerHTML = '';
            }
        }

        // Re-bind pagination click handlers
        bindPaginationLinks();
    }

    /**
     * Perform the AJAX request and update the DOM.
     *
     * @param {Object} overrides  Extra params to merge (e.g. limitstart)
     */
    async function fetchResults(overrides) {
        // Cancel any in-flight request
        if (abortController) {
            abortController.abort();
        }

        abortController = new AbortController();

        showLoading();

        const qs = buildQueryString(overrides);

        try {
            const response = await fetch(opts.ajaxUrl + '&' + qs, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: abortController.signal,
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Unknown error');
            }

            // Update listing
            if (result.html) {
                listContainer.innerHTML = result.html;
            } else {
                listContainer.innerHTML = '<h4>' +
                    Joomla.Text._('JBS_CMN_STUDY_NOT_FOUND', 'No results found') +
                    '</h4><br />';
            }

            // Update pagination
            updatePagination(result);

            // Update URL
            updateUrl(new URLSearchParams(qs));

            // Scroll to top of results
            scrollToList();
        } catch (err) {
            if (err.name === 'AbortError') {
                return; // Cancelled — a newer request replaced this one
            }

            // Fallback: submit the form normally
            console.warn('Proclaim AJAX filter error:', err.message);
            hideLoading();
            form.submit();
            return;
        }

        hideLoading();
    }

    // ─── Event Binding ─────────────────────────────────────────

    /**
     * Intercept form submission (triggered by Joomla searchtools on filter change).
     *
     * Joomla searchtools calls the native form.submit() method directly
     * (not requestSubmit()), which does NOT fire the "submit" event.
     * We override the method itself so both paths route through AJAX.
     */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        // Reset limitstart on new filter/search
        fetchResults({ limitstart: 0 });
    });

    // Override the native submit() so searchtools' form.submit() goes through AJAX
    form.submit = function () {
        fetchResults({ limitstart: 0 });
    };

    /**
     * Debounced search input handler.
     */
    const searchInput = form.querySelector('input[name="filter_search"], input[name="filter[search]"]');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function () {
                fetchResults({ limitstart: 0 });
            }, DEBOUNCE_MS);
        });
    }

    /**
     * Intercept filter dropdown changes.
     * Joomla searchtools normally calls form.submit() on change;
     * we catch that at the form submit level. But also handle direct change
     * events for selects that might not use searchtools.
     */
    form.querySelectorAll('select[name^="filter_"], select[name^="filter["]').forEach(function (select) {
        select.addEventListener('change', function () {
            fetchResults({ limitstart: 0 });
        });
    });

    /**
     * Intercept sort/ordering changes.
     */
    form.querySelectorAll('select[name^="list_"], select[name^="list["]').forEach(function (select) {
        select.addEventListener('change', function () {
            fetchResults({ limitstart: 0 });
        });
    });

    // Bind initial pagination links
    bindPaginationLinks();

    /**
     * Handle browser back/forward navigation.
     */
    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.proclaimAjax) {
            // Re-fetch with the URL params from the history state
            const url = new URL(window.location.href);
            const overrides = {};

            for (const [key, value] of url.searchParams.entries()) {
                overrides[key] = value;

                // Also sync form fields
                const field = form.querySelector('[name="' + key + '"]');

                if (field) {
                    field.value = value;
                }
            }

            fetchResults(overrides);
        } else {
            // Not our state — reload the page
            window.location.reload();
        }
    });

    // Replace initial state so back/forward works from the first page
    history.replaceState({ proclaimAjax: true }, '', window.location.href);
});
