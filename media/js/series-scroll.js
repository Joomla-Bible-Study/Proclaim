(function () {
    'use strict';

    /* global AbortController, IntersectionObserver */
    /* jshint latedef: nofunc */
    /**
     * Infinite Scroll / Load More for Frontend Series Listing
     *
     * Lightweight AJAX pagination for the series grid. Supports three modes:
     * - "pagination"  — no JS enhancement (standard page links)
     * - "loadmore"    — "Load More" button appends next page
     * - "infinite"    — IntersectionObserver auto-loads next page on scroll
     *
     * Note: innerHTML usage is safe here because the HTML comes from our own
     * server-side Cwmlisting helper via an authenticated AJAX endpoint,
     * not from user-controlled input.
     *
     * @package  Proclaim.Site
     * @since    10.1.0
     */

    document.addEventListener('DOMContentLoaded', () => {

        var opts = Joomla.getOptions('com_proclaim.seriesScroll') || {};

        if (!opts.enabled || !opts.ajaxUrl || opts.paginationStyle === 'pagination') {
            return;
        }

        var listContainer = document.getElementById('proclaim-series-list');

        if (!listContainer) {
            return;
        }

        var abortController = null;
        var paginationStyle = opts.paginationStyle || 'pagination';
        var pageLimit       = opts.limit || 20;
        var currentOffset   = 0;
        var totalItems      = opts.totalItems || 0;
        var displayedCount  = Math.min(pageLimit, totalItems);
        var isLoadingMore   = false;
        var allItemsLoaded  = false;
        var scrollObserver  = null;

        // DOM elements
        var loadMoreContainer = document.getElementById('proclaim-load-more');
        var loadMoreBtn       = loadMoreContainer ? loadMoreContainer.querySelector('button') : null;
        var itemCounter       = document.getElementById('proclaim-item-counter');
        var scrollSentinel    = document.getElementById('proclaim-scroll-sentinel');

        /**
         * Helper to get translated text with fallback.
         */
        function txt(key, fallback) {
            var result = Joomla.Text._(key, fallback);
            return (result === key) ? fallback : result;
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
         * Hide the Load More button when all items are loaded.
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
         * Fetch the next page and append to the list.
         */
        async function fetchNextPage() {
            if (isLoadingMore || allItemsLoaded) {
                return;
            }

            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();
            isLoadingMore = true;
            showLoadMoreSpinner();

            currentOffset += pageLimit;

            var url = opts.ajaxUrl + '&limitstart=' + currentOffset;

            if (opts.csrfToken) {
                url += '&' + opts.csrfToken + '=1';
            }

            try {
                var response = await fetch(url, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: abortController.signal,
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                var result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Unknown error');
                }

                // Append new items (HTML from our own server-side listing helper)
                if (result.html) {
                    var temp = document.createElement('div');
                    temp.innerHTML = result.html;

                    // The listing helper renders a table inside a
                    // .table-responsive wrapper. Extract <tbody> rows from
                    // the response and append them to the existing <tbody>.
                    var newTbody = temp.querySelector('tbody');
                    var existingTbody = listContainer.querySelector('tbody');

                    if (newTbody && existingTbody) {
                        while (newTbody.firstChild) {
                            existingTbody.appendChild(newTbody.firstChild);
                        }
                    } else {
                        listContainer.insertAdjacentHTML('beforeend', result.html);
                    }
                }

                // Update tracking
                totalItems = result.total || 0;
                displayedCount = Math.min(currentOffset + pageLimit, totalItems);
                updateCounter(displayedCount, totalItems);

                // Check if we've loaded everything
                var currentPage = Math.floor(currentOffset / pageLimit) + 1;
                if (currentPage >= result.pagesTotal || !result.html) {
                    handleAllItemsLoaded();
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return;
                }

                console.warn('Proclaim series scroll error:', err.message);
                // Revert offset on error so user can retry
                currentOffset -= pageLimit;
            }

            hideLoadMoreSpinner();
            isLoadingMore = false;
        }

        // ─── Setup ──────────────────────────────────────────────────

        // Show initial counter
        if (totalItems > 0) {
            updateCounter(displayedCount, totalItems);

            // If all items fit on the first page, hide load-more controls
            if (displayedCount >= totalItems) {
                handleAllItemsLoaded();
            }
        }

        if (paginationStyle === 'loadmore' && loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                fetchNextPage();
            });
        }

        if (paginationStyle === 'infinite' && scrollSentinel) {
            scrollObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && !isLoadingMore && !allItemsLoaded) {
                        fetchNextPage();
                    }
                });
            }, {
                root: null,
                rootMargin: '200px',
                threshold: 0
            });

            scrollObserver.observe(scrollSentinel);
        }
    });

})();
