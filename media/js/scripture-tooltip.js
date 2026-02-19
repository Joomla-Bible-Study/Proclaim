(function () {
    'use strict';

    /**
     * Scripture Verse Tooltips
     *
     * Shows Bible verse text in Bootstrap 5 popovers on hover/tap
     * over scripture reference links. Fetches passage text via the
     * existing getPassageXHR AJAX endpoint and caches in sessionStorage.
     *
     * @package  Proclaim.Site
     * @since    10.1.0
     */

    document.addEventListener('DOMContentLoaded', () => {
        const scriptureOpts = Joomla.getOptions('com_proclaim.scripture') || {};
        const ajaxBaseUrl = scriptureOpts.ajaxUrl || '';

        if (!ajaxBaseUrl) {
            return;
        }

        const HOVER_DELAY = 300;
        const HIDE_DELAY = 250;
        const CACHE_PREFIX = 'proclaim:passage:';
        const popoverMap = new WeakMap();
        let hoverTimer = null;
        let hideTimer = null;
        let activeRef = null;

        /**
         * Get cached passage from sessionStorage.
         *
         * @param {string} ref     Scripture reference
         * @param {string} version Bible version
         * @returns {object|null}  Cached data or null
         */
        const getCached = (ref, version) => {
            try {
                const key = `${CACHE_PREFIX + ref}:${version}`;
                const raw = sessionStorage.getItem(key);

                if (raw) {
                    return JSON.parse(raw);
                }
            } catch {
                // sessionStorage unavailable or parse error
            }

            return null;
        };

        /**
         * Store passage in sessionStorage cache.
         *
         * @param {string} ref     Scripture reference
         * @param {string} version Bible version
         * @param {object} data    Response data
         */
        const setCache = (ref, version, data) => {
            try {
                const key = `${CACHE_PREFIX + ref}:${version}`;

                sessionStorage.setItem(key, JSON.stringify(data));
            } catch {
                // Storage full or unavailable
            }
        };

        /**
         * Build popover HTML content.
         *
         * @param {object} data Passage response data
         * @returns {string} HTML content
         */
        const buildContent = (data) => {
            if (!data.success || !data.text) {
                return `<div class="proclaim-tooltip-error">${
                Joomla.Text._('JBS_CMN_TOOLTIP_UNAVAILABLE', 'Passage unavailable')
            }</div>`;
            }

            let html = `<div class="proclaim-tooltip-body">${data.text}</div>`;

            if (data.copyright) {
                html += `<div class="proclaim-tooltip-copyright">${data.copyright}</div>`;
            }

            return html;
        };

        /**
         * Get or create a Bootstrap Popover for the element.
         *
         * @param {HTMLElement} el Scripture reference link
         * @returns {bootstrap.Popover}
         */
        const getPopover = (el) => {
            if (popoverMap.has(el)) {
                return popoverMap.get(el);
            }

            const loading = '<div class="proclaim-tooltip-loading">'
                + '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                + '</div>';

            const pop = new bootstrap.Popover(el, {
                trigger: 'manual',
                html: true,
                sanitize: false,
                content: loading,
                placement: 'top',
                customClass: 'proclaim-scripture-popover',
                container: 'body',
            });

            popoverMap.set(el, pop);

            return pop;
        };

        /**
         * Fetch passage and update popover content.
         *
         * @param {HTMLElement} el Scripture reference link
         */
        /**
         * Update the visible popover body without calling setContent(),
         * which internally disposes and re-shows the popover — creating
         * competing animation callbacks that hide it unexpectedly.
         *
         * @param {bootstrap.Popover} pop  Popover instance
         * @param {string}            html New body HTML
         */
        const updatePopoverBody = (pop, html) => {
            const tip = pop.getTipElement ? pop.getTipElement() : pop.tip;

            if (tip) {
                const body = tip.querySelector('.popover-body');

                if (body) {
                    body.innerHTML = html;
                }
            }
        };

        const fetchAndShow = async (el) => {
            const ref = el.dataset.scriptureRef;
            const version = el.dataset.bibleVersion || 'kjv';

            const pop = getPopover(el);

            // Bootstrap 5.3 checks _isHovered in the show-animation-complete callback;
            // if false it calls _leave() → hide(). With trigger:'manual', _enter() is
            // never called so _isHovered stays false. Set it true to prevent auto-hide.
            pop._isHovered = true;
            pop.show();

            // Check cache
            const cached = getCached(ref, version);

            if (cached) {
                updatePopoverBody(pop, buildContent(cached));

                return;
            }

            try {
                const token = Joomla.getOptions('csrf.token') || '';
                const url = `${ajaxBaseUrl
            }&reference=${encodeURIComponent(ref)
            }&version=${encodeURIComponent(version)
            }&${token}=1`;

                const response = await fetch(url);
                const data = await response.json();

                setCache(ref, version, data);
                updatePopoverBody(pop, buildContent(data));
            } catch {
                updatePopoverBody(pop, `<div class="proclaim-tooltip-error">${
                Joomla.Text._('JBS_CMN_TOOLTIP_UNAVAILABLE', 'Passage unavailable')
            }</div>`);
            }
        };

        /**
         * Hide popover for element.
         *
         * @param {HTMLElement} el Scripture reference link
         */
        const hidePopover = (el) => {
            if (popoverMap.has(el)) {
                const pop = popoverMap.get(el);

                pop._isHovered = false;
                pop.hide();
            }
        };

        /**
         * Schedule a delayed hide of the active popover.
         * The delay allows the mouse to cross the gap between
         * the scripture ref and the popover without dismissing.
         */
        const scheduleHide = () => {
            clearTimeout(hideTimer);
            hideTimer = setTimeout(() => {
                if (activeRef) {
                    hidePopover(activeRef);
                    activeRef = null;
                }
            }, HIDE_DELAY);
        };

        /**
         * Check whether an element belongs to the tooltip system
         * (scripture ref or its popover).
         *
         * @param {Element|null} el Element to test
         * @returns {boolean}
         */
        const isTooltipElement = (el) => {
            if (!el) {
                return false;
            }

            return !!(el.closest('.proclaim-scripture-ref') || el.closest('.proclaim-scripture-popover'));
        };

        // mouseover bubbles (unlike mouseenter) — show popover on ref hover
        document.body.addEventListener('mouseover', (e) => {
            const pop = e.target.closest('.proclaim-scripture-popover');

            if (pop) {
                // Mouse entered the popover — cancel any pending hide
                clearTimeout(hideTimer);

                return;
            }

            const ref = e.target.closest('.proclaim-scripture-ref');

            if (!ref) {
                return;
            }

            clearTimeout(hideTimer);
            clearTimeout(hoverTimer);

            if (activeRef && activeRef !== ref) {
                hidePopover(activeRef);
                activeRef = null;
            }

            hoverTimer = setTimeout(() => {
                activeRef = ref;
                fetchAndShow(ref);
            }, HOVER_DELAY);
        });

        // mouseout bubbles — hide when leaving both ref and popover
        document.body.addEventListener('mouseout', (e) => {
            const ref = e.target.closest('.proclaim-scripture-ref');
            const pop = e.target.closest('.proclaim-scripture-popover');

            if (!ref && !pop) {
                return;
            }

            // If the mouse is moving to the other tooltip element
            // (ref → popover or popover → ref), stay open.
            if (isTooltipElement(e.relatedTarget)) {
                return;
            }

            // Moving away from the tooltip system entirely
            clearTimeout(hoverTimer);
            scheduleHide();
        });

        // Mobile: touchstart toggle
        document.body.addEventListener('touchstart', (e) => {
            const ref = e.target.closest('.proclaim-scripture-ref');

            if (!ref) {
                // Tap outside - dismiss active popover
                if (activeRef) {
                    hidePopover(activeRef);
                    activeRef = null;
                }

                return;
            }

            e.preventDefault();

            if (activeRef === ref) {
                hidePopover(ref);
                activeRef = null;
            } else {
                if (activeRef) {
                    hidePopover(activeRef);
                }

                activeRef = ref;
                fetchAndShow(ref);
            }
        }, { passive: false });

        // Keyboard: Enter/Space to show, Escape to dismiss
        document.body.addEventListener('keydown', (e) => {
            const ref = e.target.closest('.proclaim-scripture-ref');

            if (ref && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();

                if (activeRef === ref) {
                    hidePopover(ref);
                    activeRef = null;
                } else {
                    if (activeRef) {
                        hidePopover(activeRef);
                    }

                    activeRef = ref;
                    fetchAndShow(ref);
                }
            }

            if (e.key === 'Escape' && activeRef) {
                hidePopover(activeRef);
                activeRef = null;
            }
        });
    });

})();
