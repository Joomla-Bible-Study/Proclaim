(function () {
    'use strict';

    /* global AbortController, IntersectionObserver */
    /* jshint latedef: nofunc */
    /**
     * AJAX Filtering & Searching for Frontend Sermon Listing
     *
     * Progressive enhancement: intercepts filter changes, search, sort,
     * and pagination to load results via AJAX. Falls back to standard
     * form submit when JS is disabled or on fetch error.
     *
     * Supports three pagination styles (set via template param):
     * - "pagination"  — standard page links (replace content)
     * - "loadmore"    — "Load More" button appends next page
     * - "infinite"    — IntersectionObserver auto-loads next page on scroll
     *
     * @package  Proclaim.Site
     * @since    10.1.0
     */

    document.addEventListener('DOMContentLoaded', () => {

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

        // Pagination style configuration
        const paginationStyle = opts.paginationStyle || 'pagination';
        const pageLimit       = opts.limit || 20;

        // Scroll/load-more state
        let currentOffset  = 0;
        let totalItems     = 0;
        let isLoadingMore  = false;
        let allItemsLoaded = false;
        let scrollObserver = null;

        // DOM elements for scroll modes
        const loadMoreContainer = document.getElementById('proclaim-load-more');
        const loadMoreBtn       = loadMoreContainer ? loadMoreContainer.querySelector('button') : null;
        const itemCounter       = document.getElementById('proclaim-item-counter');
        const scrollSentinel    = document.getElementById('proclaim-scroll-sentinel');

        /**
         * Helper to get translated text with fallback.
         *
         * @param {string} key       Language key
         * @param {string} fallback  Fallback text
         * @returns {string}
         */
        function txt(key, fallback) {
            var result = Joomla.Text._(key, fallback);

            // Joomla.Text._() returns the raw key when unregistered
            return (result === key) ? fallback : result;
        }

        // ─── Helpers ─────────────────────────────────────────────

        /**
         * Build query string from form data, adding CSRF token and overrides.
         *
         * @param {Object} overrides  Extra params to merge (e.g. limitstart)
         * @returns {string}
         */
        function buildQueryString(overrides) {
            var data = new FormData(form);
            var params = new URLSearchParams();

            for (var pair of data.entries()) {
                if (pair[1] !== '' && pair[1] !== null) {
                    params.set(pair[0], pair[1]);
                } else if (pair[0].indexOf('filter') === 0 || pair[0].indexOf('list') === 0) {
                    params.set(pair[0], '');
                }
            }

            // Apply overrides (e.g. limitstart from pagination click)
            if (overrides) {
                for (var key in overrides) {
                    if (overrides.hasOwnProperty(key)) {
                        params.set(key, overrides[key]);
                    }
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
                var spinner = document.createElement('div');
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
            var spinner = listContainer.querySelector('.proclaim-ajax-spinner');

            if (spinner) {
                spinner.remove();
            }
        }

        /**
         * Show a small loading indicator on the Load More button.
         */
        function showLoadMoreSpinner() {
            if (loadMoreBtn) {
                loadMoreBtn.disabled = true;
                loadMoreBtn.dataset.originalText = loadMoreBtn.textContent;
                loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" ' +
                    'aria-hidden="true"></span>' + txt('JBS_CMN_LOADING', 'Loading...');
            }
        }

        /**
         * Restore the Load More button to its normal state.
         */
        function hideLoadMoreSpinner() {
            if (loadMoreBtn && loadMoreBtn.dataset.originalText) {
                loadMoreBtn.disabled = false;
                loadMoreBtn.textContent = loadMoreBtn.dataset.originalText;
            }
        }

        /**
         * Update the "Showing X of Y" counter.
         *
         * @param {number} shown  Number of items currently displayed
         * @param {number} total  Total items available
         */
        function updateCounter(shown, total) {
            if (!itemCounter) {
                return;
            }

            if (shown >= total) {
                itemCounter.textContent = txt('JBS_CMN_ALL_ITEMS_LOADED', 'All items loaded');
            } else {
                var template = txt('JBS_CMN_SHOWING_X_OF_Y', 'Showing %s of %s');
                itemCounter.textContent = template.replace('%s', shown).replace('%s', total);
            }
        }

        /**
         * Count the number of sermon items currently in the list container.
         *
         * @returns {number}
         */
        function countDisplayedItems() {
            // The fluid listing renders items as column divs inside a .row wrapper.
            var row = listContainer.querySelector('.row');

            if (row) {
                return row.children.length;
            }

            return listContainer.children.length;
        }

        /**
         * Update the browser URL via pushState without reloading.
         *
         * @param {URLSearchParams} params
         */
        function updateUrl(params) {
            var url = new URL(window.location.href);

            // Clear existing search params
            url.search = '';

            // Re-apply relevant filter/list params
            for (var pair of params.entries()) {
                if (pair[0] !== 'task' && pair[0] !== 'format' && pair[1] !== '') {
                    url.searchParams.set(pair[0], pair[1]);
                }
            }

            history.pushState({ proclaimAjax: true }, '', url.toString());
        }

        /**
         * Smooth-scroll to the list area after results load.
         */
        function scrollToList() {
            var target = mainContent || listContainer;

            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        /**
         * Bind click handlers to pagination links.
         */
        function bindPaginationLinks() {
            document.querySelectorAll('#proclaim-pagination-top a, #proclaim-pagination-bottom a').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    var href = link.getAttribute('href') || '';
                    var url = new URL(href, window.location.origin);
                    var limitstart = url.searchParams.get('limitstart') || url.searchParams.get('start') || '0';

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
            var paginationHtml = result.pagination || '';
            var counterHtml    = result.pagesCounter || '';

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
         * Hide the Load More button and show "all loaded" message.
         */
        function handleAllItemsLoaded() {
            allItemsLoaded = true;

            if (loadMoreContainer) {
                loadMoreContainer.style.display = 'none';
            }

            if (scrollObserver && scrollSentinel) {
                scrollObserver.unobserve(scrollSentinel);
            }
        }

        /**
         * Reset scroll/load-more state when filters change.
         */
        function resetScrollState() {
            currentOffset  = 0;
            totalItems     = 0;
            isLoadingMore  = false;
            allItemsLoaded = false;

            if (loadMoreContainer) {
                loadMoreContainer.style.display = '';
                hideLoadMoreSpinner();
            }

            if (itemCounter) {
                itemCounter.textContent = '';
            }

            // Re-observe sentinel for infinite scroll
            if (scrollObserver && scrollSentinel) {
                scrollObserver.observe(scrollSentinel);
            }
        }

        /**
         * Perform the AJAX request and update the DOM.
         *
         * @param {Object}  overrides   Extra params to merge (e.g. limitstart)
         * @param {boolean} appendMode  If true, append results instead of replacing
         */
        async function fetchResults(overrides, appendMode) {
            // Cancel any in-flight request
            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();

            if (appendMode) {
                showLoadMoreSpinner();
                isLoadingMore = true;
            } else {
                showLoading();
            }

            var qs = buildQueryString(overrides);

            try {
                var response = await fetch(opts.ajaxUrl + '&' + qs, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: abortController.signal,
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                var result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Unknown error');
                }

                if (appendMode) {
                    // Append new items to existing list
                    if (result.html) {
                        var temp = document.createElement('div');
                        temp.innerHTML = result.html;

                        // The listing helper wraps items in a .row container.
                        // Extract inner content from the response's row.
                        var newRow = temp.querySelector('.row');
                        var existingRow = listContainer.querySelector('.row');

                        if (newRow && existingRow) {
                            // Append each child of the new row to the existing row
                            while (newRow.firstChild) {
                                existingRow.appendChild(newRow.firstChild);
                            }
                        } else if (result.html) {
                            // Fallback: just append the raw HTML
                            listContainer.insertAdjacentHTML('beforeend', result.html);
                        }
                    }

                    // Update tracking
                    totalItems = result.total || 0;
                    var displayed = countDisplayedItems();
                    updateCounter(displayed, totalItems);

                    // Check if we've loaded everything
                    var currentPage = Math.floor(currentOffset / pageLimit) + 1;

                    if (currentPage >= result.pagesTotal || !result.html) {
                        handleAllItemsLoaded();
                    }

                    hideLoadMoreSpinner();
                    isLoadingMore = false;
                } else {
                    // Replace mode (standard or filter reset)
                    if (result.html) {
                        listContainer.innerHTML = result.html;
                    } else {
                        listContainer.innerHTML = '<h4>' +
                            txt('JBS_CMN_STUDY_NOT_FOUND', 'No results found') +
                            '</h4><br />';
                    }

                    // For scroll modes, update state after replace
                    if (paginationStyle !== 'pagination') {
                        totalItems = result.total || 0;
                        var displayedAfterReplace = countDisplayedItems();
                        updateCounter(displayedAfterReplace, totalItems);

                        if (result.pagesTotal <= 1 || !result.html) {
                            handleAllItemsLoaded();
                        }
                    }

                    // Only show standard pagination in pagination mode
                    if (paginationStyle === 'pagination') {
                        updatePagination(result);
                    }

                    // Update URL
                    updateUrl(new URLSearchParams(qs));

                    // Scroll to top of results
                    scrollToList();
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return; // Cancelled — a newer request replaced this one
                }

                // Fallback: submit the form normally
                console.warn('Proclaim AJAX filter error:', err.message);

                if (appendMode) {
                    hideLoadMoreSpinner();
                    isLoadingMore = false;
                } else {
                    hideLoading();
                }

                form.submit();
                return;
            }

            if (!appendMode) {
                hideLoading();
            }
        }

        /**
         * Load the next page of results (append mode).
         */
        function loadNextPage() {
            if (isLoadingMore || allItemsLoaded) {
                return;
            }

            currentOffset += pageLimit;
            fetchResults({ limitstart: currentOffset }, true);
        }

        // ─── Event Binding ─────────────────────────────────────────

        /**
         * Intercept form submission (triggered by Joomla searchtools on filter change).
         */
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            // Reset to page 1 on filter/search change
            resetScrollState();
            fetchResults({ limitstart: 0 });
        });

        // Override the native submit() so searchtools' form.submit() goes through AJAX
        form.submit = function () {
            resetScrollState();
            fetchResults({ limitstart: 0 });
        };

        /**
         * Debounced search input handler.
         */
        var searchInput = form.querySelector('input[name="filter_search"], input[name="filter[search]"]');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function () {
                    resetScrollState();
                    fetchResults({ limitstart: 0 });
                }, DEBOUNCE_MS);
            });
        }

        /**
         * Intercept filter dropdown changes.
         */
        form.querySelectorAll('select[name^="filter_"], select[name^="filter["]').forEach(function (select) {
            select.addEventListener('change', function () {
                resetScrollState();
                fetchResults({ limitstart: 0 });
            });
        });

        /**
         * Intercept sort/ordering changes.
         */
        form.querySelectorAll('select[name^="list_"], select[name^="list["]').forEach(function (select) {
            select.addEventListener('change', function () {
                resetScrollState();
                fetchResults({ limitstart: 0 });
            });
        });

        // ─── Pagination Style Setup ────────────────────────────────

        if (paginationStyle === 'pagination') {
            // Standard pagination — bind page link clicks
            bindPaginationLinks();
        }

        if (paginationStyle === 'loadmore' && loadMoreBtn) {
            // Load More button click handler
            loadMoreBtn.addEventListener('click', function () {
                loadNextPage();
            });
        }

        if (paginationStyle === 'infinite' && scrollSentinel) {
            // IntersectionObserver for infinite scroll
            scrollObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && !isLoadingMore && !allItemsLoaded) {
                        loadNextPage();
                    }
                });
            }, {
                root: null,
                rootMargin: '200px',
                threshold: 0
            });

            scrollObserver.observe(scrollSentinel);
        }

        /**
         * Handle browser back/forward navigation.
         */
        window.addEventListener('popstate', function (e) {
            if (e.state && e.state.proclaimAjax) {
                var url = new URL(window.location.href);
                var overrides = {};

                for (var pair of url.searchParams.entries()) {
                    overrides[pair[0]] = pair[1];

                    // Also sync form fields
                    var field = form.querySelector('[name="' + pair[0] + '"]');

                    if (field) {
                        field.value = pair[1];
                    }
                }

                resetScrollState();
                fetchResults(overrides);
            } else {
                // Not our state — reload the page
                window.location.reload();
            }
        });

        // Replace initial state so back/forward works from the first page
        history.replaceState({ proclaimAjax: true }, '', window.location.href);
    });

})();
