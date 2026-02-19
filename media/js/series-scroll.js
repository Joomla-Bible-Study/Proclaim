(function () {
    'use strict';

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

        const opts = Joomla.getOptions('com_proclaim.seriesScroll') || {};

        if (!opts.enabled || !opts.ajaxUrl || opts.paginationStyle === 'pagination') {
            return;
        }

        const listContainer = document.getElementById('proclaim-series-list');

        if (!listContainer) {
            return;
        }

        let abortController = null;
        const paginationStyle = opts.paginationStyle || 'pagination';
        const pageLimit = opts.limit || 20;
        let currentOffset = 0;
        let totalItems = opts.totalItems || 0;
        let displayedCount = Math.min(pageLimit, totalItems);
        let isLoadingMore = false;
        let allItemsLoaded = false;
        let scrollObserver = null;

        // Infinite scroll threshold: after this many auto-loaded pages, pause and
        // require a manual "Load More" click.  0 = unlimited (never pause).
        const scrollThreshold = opts.scrollThreshold || 0;
        let autoLoadedPages = 0;
        let scrollPaused = false;

        // DOM elements
        const loadMoreContainer = document.getElementById('proclaim-load-more');
        const loadMoreBtn = loadMoreContainer ? loadMoreContainer.querySelector('button') : null;
        const itemCounter = document.getElementById('proclaim-item-counter');
        const scrollSentinel = document.getElementById('proclaim-scroll-sentinel');

        /**
         * Helper to get translated text with fallback.
         */
        function txt(key, fallback) {
            const result = Joomla.Text._(key, fallback);
            return (result === key) ? fallback : result;
        }

        /**
         * Show a small loading indicator on the Load More button.
         */
        function showLoadMoreSpinner() {
            if (loadMoreBtn) {
                loadMoreBtn.disabled = true;
                loadMoreBtn.dataset.originalText = loadMoreBtn.textContent;
                loadMoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" '
                    + `aria-hidden="true"></span>${txt('JBS_CMN_LOADING', 'Loading...')}`;
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
                const template = txt('JBS_CMN_SHOWING_X_OF_Y', 'Showing %s of %s');
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
         * Pause infinite scroll and show the Load More button instead.
         */
        function pauseInfiniteScroll() {
            scrollPaused = true;

            if (scrollObserver && scrollSentinel) {
                scrollObserver.unobserve(scrollSentinel);
            }

            if (loadMoreContainer) {
                loadMoreContainer.style.display = '';
            }
        }

        /**
         * Resume infinite scroll after a manual Load More click.
         */
        function resumeInfiniteScroll() {
            scrollPaused = false;
            autoLoadedPages = 0;

            if (loadMoreContainer && paginationStyle === 'infinite') {
                loadMoreContainer.style.display = 'none';
            }

            if (scrollObserver && scrollSentinel) {
                scrollObserver.observe(scrollSentinel);
            }
        }

        /**
         * Fetch the next page and append to the list.
         *
         * @param {boolean} manualClick  True when triggered by a Load More button click
         */
        async function fetchNextPage(manualClick) {
            if (isLoadingMore || allItemsLoaded) {
                return;
            }

            // If this was a manual click in infinite mode, resume auto-scrolling
            if (manualClick && scrollPaused) {
                resumeInfiniteScroll();
            }

            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();
            isLoadingMore = true;
            showLoadMoreSpinner();

            currentOffset += pageLimit;

            // Track auto-loaded pages for threshold
            let shouldPauseAfter = false;

            if (paginationStyle === 'infinite' && !manualClick) {
                autoLoadedPages += 1;

                if (scrollThreshold > 0 && autoLoadedPages >= scrollThreshold) {
                    shouldPauseAfter = true;
                }
            }

            let url = `${opts.ajaxUrl}&limitstart=${currentOffset}`;

            if (opts.csrfToken) {
                url += `&${opts.csrfToken}=1`;
            }

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    credentials: 'same-origin',
                    signal: abortController.signal,
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Unknown error');
                }

                // Append new items (server-side listing helper HTML, safe origin)
                if (result.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = result.html;

                    // The listing helper renders .proclaim-item divs inside a
                    // .proclaim-listing wrapper. Extract items and append them
                    // to the existing listing container.
                    const newListing = temp.querySelector('.proclaim-listing');
                    const existingListing = listContainer.querySelector('.proclaim-listing');

                    if (newListing && existingListing) {
                        const items = newListing.querySelectorAll(':scope > .proclaim-item');
                        items.forEach((item) => {
                            existingListing.appendChild(item);
                        });
                    } else {
                        listContainer.insertAdjacentHTML('beforeend', result.html);
                    }
                }

                // Update tracking
                totalItems = result.total || 0;
                displayedCount = Math.min(currentOffset + pageLimit, totalItems);
                updateCounter(displayedCount, totalItems);

                // Check if we've loaded everything
                const currentPage = Math.floor(currentOffset / pageLimit) + 1;
                if (currentPage >= result.pagesTotal || !result.html) {
                    handleAllItemsLoaded();
                } else if (shouldPauseAfter) {
                    pauseInfiniteScroll();
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

        if (loadMoreBtn) {
            // Load More button click handler (used in loadmore mode,
            // and in infinite mode after threshold is reached)
            loadMoreBtn.addEventListener('click', () => {
                fetchNextPage(true);
            });
        }

        if (paginationStyle === 'infinite' && scrollSentinel) {
            scrollObserver = new IntersectionObserver(((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && !isLoadingMore && !allItemsLoaded) {
                        fetchNextPage();
                    }
                });
            }), {
                root: null,
                rootMargin: '200px',
                threshold: 0,
            });

            scrollObserver.observe(scrollSentinel);
        }
    });

})();
