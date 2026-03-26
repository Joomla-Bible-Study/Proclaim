(function (window, document) {
    'use strict';

    /**
     * Accessibility: Modal Focus Management
     * Traps focus within modal dialogs and restores focus on close
     */
    const ProclaimA11y = {
        /** Store the element that opened the modal */
        previousActiveElement: null,

        /**
         * Get all focusable elements within a container
         * @param {HTMLElement} container - The container element
         * @returns {Array} Array of focusable elements
         */
        getFocusableElements(container) {
            const focusableSelectors = [
                'a[href]',
                'button:not([disabled])',
                'input:not([disabled]):not([type="hidden"])',
                'select:not([disabled])',
                'textarea:not([disabled])',
                '[tabindex]:not([tabindex="-1"])',
                '[contenteditable="true"]',
            ].join(', ');

            return Array.from(container.querySelectorAll(focusableSelectors))
                .filter((el) => el.offsetParent !== null); // Only visible elements
        },

        /**
         * Initialize focus trap for a modal
         * @param {HTMLElement|string} modal - Modal element or selector
         */
        trapFocus(modal) {
            const modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
            if (!modalElement) { return; }

            // Store the currently focused element
            this.previousActiveElement = document.activeElement;

            // Set aria-modal for screen readers
            modalElement.setAttribute('aria-modal', 'true');
            modalElement.setAttribute('role', 'dialog');

            const focusableElements = this.getFocusableElements(modalElement);
            if (focusableElements.length === 0) { return; }

            // Focus the first focusable element
            focusableElements[0].focus();

            // Handle Tab key to trap focus
            const handleKeyDown = (e) => {
                if (e.key === 'Escape') {
                    this.releaseFocus(modalElement, handleKeyDown);
                    // Trigger close if modal has a close method
                    const closeBtn = modalElement.querySelector('[data-dismiss="modal"], .btn-close, .close');
                    if (closeBtn) { closeBtn.click(); }
                    return;
                }

                if (e.key !== 'Tab') { return; }

                // Update focusable elements (in case of dynamic content)
                const currentFocusable = this.getFocusableElements(modalElement);
                if (currentFocusable.length === 0) { return; }

                const [first] = currentFocusable;
                const last = currentFocusable[currentFocusable.length - 1];

                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else if (document.activeElement === last) {
                    // Tab
                    e.preventDefault();
                    first.focus();
                }
            };

            modalElement.addEventListener('keydown', handleKeyDown);
            modalElement._a11yKeyHandler = handleKeyDown;
        },

        /**
         * Release focus trap and restore previous focus
         * @param {HTMLElement|string} modal - Modal element or selector
         * @param {Function} handler - The keydown handler to remove (optional)
         */
        releaseFocus(modal, handler) {
            const modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
            if (!modalElement) { return; }

            // Remove the keydown handler
            if (handler) {
                modalElement.removeEventListener('keydown', handler);
            } else if (modalElement._a11yKeyHandler) {
                modalElement.removeEventListener('keydown', modalElement._a11yKeyHandler);
                delete modalElement._a11yKeyHandler;
            }

            // Restore focus to the previous element
            if (this.previousActiveElement && this.previousActiveElement.focus) {
                this.previousActiveElement.focus();
                this.previousActiveElement = null;
            }
        },

        /**
         * Announce message to screen readers
         * @param {string} message - Message to announce
         * @param {string} priority - 'polite' or 'assertive'
         */
        announce(message, priority = 'polite') {
            let announcer = document.getElementById('proclaim-a11y-announcer');
            if (!announcer) {
                announcer = document.createElement('div');
                announcer.id = 'proclaim-a11y-announcer';
                announcer.setAttribute('aria-live', priority);
                announcer.setAttribute('aria-atomic', 'true');
                announcer.className = 'visually-hidden';
                announcer.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;';
                document.body.appendChild(announcer);
            }
            announcer.setAttribute('aria-live', priority);
            announcer.textContent = '';
            // Use setTimeout to ensure screen readers pick up the change
            setTimeout(() => { announcer.textContent = message; }, 100);
        },
    };

    // Export to window
    window.ProclaimA11y = ProclaimA11y;

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btnPlay').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const mediaId = this.getAttribute('alt');
                const mediaEl = document.getElementById(`media-${mediaId}`);

                document.querySelectorAll('.inlinePlayer').forEach((el) => {
                    if (el.id !== `media-${mediaId}`) {
                        el.style.display = 'none';
                    }
                    el.innerHTML = '';
                });

                if (mediaEl) {
                    if (window.getComputedStyle(mediaEl).display === 'none') {
                        mediaEl.style.display = 'block';
                    } else {
                        mediaEl.style.display = 'none';
                    }

                    fetch('index.php?option=com_proclaim&view=cwmstudieslist&controller=cwmstudieslist&task=inlinePlayer&tmpl=component')
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then((html) => {
                            mediaEl.innerHTML = html;
                        })
                        .catch((error) => {
                            console.error('Error loading content:', error);
                        });
                }
            });
        });

        // Auto-initialize focus trap for Bootstrap modals
        document.addEventListener('shown.bs.modal', (e) => {
            ProclaimA11y.trapFocus(e.target);
        });

        document.addEventListener('hidden.bs.modal', (e) => {
            ProclaimA11y.releaseFocus(e.target);
        });

        // Print buttons: delegate click to window.print()
        document.addEventListener('click', (e) => {
            if (e.target.closest('.js-print-btn')) {
                e.preventDefault();
                window.print();
            }
        });

        // Submit-task buttons: delegate click to Joomla.submitbutton()
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-submit-task]');
            if (btn) {
                e.preventDefault();
                Joomla.submitbutton(btn.dataset.submitTask);
            }
        });
    });

}(window, window.document));
