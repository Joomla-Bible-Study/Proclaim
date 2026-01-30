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
        getFocusableElements: function(container) {
            const focusableSelectors = [
                'a[href]',
                'button:not([disabled])',
                'input:not([disabled]):not([type="hidden"])',
                'select:not([disabled])',
                'textarea:not([disabled])',
                '[tabindex]:not([tabindex="-1"])',
                '[contenteditable="true"]'
            ].join(', ');

            return Array.from(container.querySelectorAll(focusableSelectors))
                .filter(el => el.offsetParent !== null); // Only visible elements
        },

        /**
         * Initialize focus trap for a modal
         * @param {HTMLElement|string} modal - Modal element or selector
         */
        trapFocus: function(modal) {
            const modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
            if (!modalElement) return;

            // Store the currently focused element
            this.previousActiveElement = document.activeElement;

            // Set aria-modal for screen readers
            modalElement.setAttribute('aria-modal', 'true');
            modalElement.setAttribute('role', 'dialog');

            const focusableElements = this.getFocusableElements(modalElement);
            if (focusableElements.length === 0) return;

            const firstFocusable = focusableElements[0];
            const lastFocusable = focusableElements[focusableElements.length - 1];

            // Focus the first focusable element
            firstFocusable.focus();

            // Handle Tab key to trap focus
            const handleKeyDown = (e) => {
                if (e.key === 'Escape') {
                    this.releaseFocus(modalElement, handleKeyDown);
                    // Trigger close if modal has a close method
                    const closeBtn = modalElement.querySelector('[data-dismiss="modal"], .btn-close, .close');
                    if (closeBtn) closeBtn.click();
                    return;
                }

                if (e.key !== 'Tab') return;

                // Update focusable elements (in case of dynamic content)
                const currentFocusable = this.getFocusableElements(modalElement);
                if (currentFocusable.length === 0) return;

                const first = currentFocusable[0];
                const last = currentFocusable[currentFocusable.length - 1];

                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
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
        releaseFocus: function(modal, handler) {
            const modalElement = typeof modal === 'string' ? document.querySelector(modal) : modal;
            if (!modalElement) return;

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
        announce: function(message, priority = 'polite') {
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
        }
    };

    // Export to window
    window.ProclaimA11y = ProclaimA11y;

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btnPlay').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const mediaId = this.getAttribute('alt');
                const mediaEl = document.getElementById('media-' + mediaId);

                document.querySelectorAll('.inlinePlayer').forEach(function (el) {
                    if (el.id !== 'media-' + mediaId) {
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
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(html => {
                            mediaEl.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error loading content:', error);
                        });
                }
            });
        });

        // Check for touch support using native browser APIs
        const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
        if (isTouchDevice) {
            const jbsmcloseoverlay = document.querySelectorAll('.jbsmclose-overlay');

            jbsmcloseoverlay.forEach(function (el) {
                el.classList.remove('hidden');
            });

            document.querySelectorAll('.jbsmimg').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (!this.classList.contains('hover')) {
                        this.classList.add('hover');
                    }
                });
            });

            jbsmcloseoverlay.forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const closestImg = this.closest('.jbsmimg');
                    if (closestImg && closestImg.classList.contains('hover')) {
                        closestImg.classList.remove('hover');
                    }
                });
            });
        } else {
            document.querySelectorAll('.jbsmimg').forEach(function (el) {
                el.addEventListener('mouseenter', function () {
                    this.classList.add('hover');
                });
                el.addEventListener('mouseleave', function () {
                    this.classList.remove('hover');
                });
            });
        }

        const addReferenceBtn = document.getElementById('addReference');
        if (addReferenceBtn) {
            addReferenceBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const referenceTemplate = document.getElementById('reference');
                if (!referenceTemplate) return;

                const newReference = referenceTemplate.cloneNode(true);

                const deleteButton = document.createElement('a');
                deleteButton.href = '#';
                deleteButton.className = 'referenceDelete';
                deleteButton.textContent = 'Delete';

                const textInput = newReference.querySelector('#text');
                if (textInput) {
                    textInput.value = '';
                    textInput.setAttribute('value', '');
                }

                const scriptureSelect = newReference.querySelector('#scripture');
                if (scriptureSelect) {
                    scriptureSelect.value = '0';
                }

                newReference.appendChild(deleteButton);
                const referencesContainer = document.getElementById('references');
                if (referencesContainer) {
                    referencesContainer.appendChild(newReference);
                }
            });
        }

        // Use event delegation for dynamically added delete buttons
        const referencesContainer = document.getElementById('references');
        if (referencesContainer) {
            referencesContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('referenceDelete')) {
                    e.preventDefault();
                    const parent = e.target.closest('#reference');
                    if (parent) {
                        parent.remove();
                    }
                }
            });
        }

        document.querySelectorAll('.imgChoose').forEach(function (el) {
            el.addEventListener('change', function () {
                const targetImage = document.getElementById('img' + this.id);
                if (!targetImage) return;

                const src = targetImage.getAttribute('src');
                let activeDir = [];
                if (src) {
                    activeDir = src.split('/');
                    activeDir.pop();
                }

                if (parseInt(this.value) === 0) {
                    targetImage.style.display = 'none';
                } else {
                    targetImage.style.display = '';
                }

                // Safely escape the value - use underscore if available, otherwise basic HTML escape
                const escapeHtml = (str) => {
                    if (typeof _ !== 'undefined' && typeof _.escape === 'function') {
                        return _.escape(str);
                    }
                    const entityMap = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return String(str).replace(/[&<>"']/g, function (s) {
                        return entityMap[s];
                    });
                };

                if (activeDir.length > 0) {
                    targetImage.setAttribute('src', activeDir.join('/') + '/' + escapeHtml(this.value));
                }
            });
        });

        // Auto-initialize focus trap for Bootstrap modals
        // Listen for Bootstrap modal events
        document.addEventListener('shown.bs.modal', function(e) {
            ProclaimA11y.trapFocus(e.target);
        });

        document.addEventListener('hidden.bs.modal', function(e) {
            ProclaimA11y.releaseFocus(e.target);
        });
    });

    function decOnly(i)
    {
        let t = i.value;
        if (t.length > 0) {
            t = t.replace(/[^\d.]+/g, '');
        }

        const s = t.split('.');
        if (s.length > 1) {
            s[1] = s[0] + '.' + s[1];
            s.shift();
        }

        i.value = s.join('');
    }

    function bandwidth(bytees, type)
    {
        let value = bytees;
        if (!isNaN(value) && (value !== '')) {
            switch (type.toUpperCase()) {
                case 'KB':
                    value *= 1024;
                    break;
                case 'MB':
                    value *= Math.pow(1024, 2);
                    break;
                case 'GB':
                    value *= Math.pow(1024, 3);
                    break;
                default:
                    return 'error';
            }

            return parseInt(value);
        } else {
            return 'error';
        }
    }

    function transferFileSize()
    {
        const size = document.getElementById('Text1').value;
        const ty = document.getElementById('Select1').value;
        const ss = bandwidth(size, ty);
        if (ss === 'error') {
            alert('Numbers only please.');
            return false;
        } else {
            document.getElementById('jform_params_size').value = ss;
            return true;
        }
    }

    // Export utilities to window
    window.decOnly = decOnly;
    window.bandwidth = bandwidth;
    window.transferFileSize = transferFileSize;

})(window, window.document);