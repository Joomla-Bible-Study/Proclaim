(function () {
    'use strict';

    /**
     * Scripture Autocomplete for Proclaim
     *
     * Attaches to .scripture-autocomplete inputs within the scripture subform.
     * Provides book name autocomplete and real-time reference validation.
     *
     * @package  Proclaim
     * @since    10.1.0
     */
    ((Joomla) => {

        if (!Joomla) {
            return;
        }

        // Reference regex matching the PHP parser
        const REFERENCE_REGEX = /^(.+?)\s+(\d+)(?::(\d+)(?:\s*-\s*(?:(\d+):)?(\d+))?)?$/;

        /**
         * Get book data from Joomla script options.
         * @returns {Array} Array of {booknumber, name, key}
         */
        function getBooks() {
            return Joomla.getOptions('com_proclaim.books') || [];
        }

        /**
         * Get default Bible version from script options.
         * @returns {string}
         */
        function getDefaultVersion() {
            return Joomla.getOptions('com_proclaim.defaultBibleVersion') || '';
        }

        /**
         * Test if a reference string is valid.
         * @param {string} text
         * @param {Array} books
         * @returns {boolean}
         */
        function isValidReference(text, books) {
            const match = text.match(REFERENCE_REGEX);
            if (!match) return false;

            const bookName = match[1].trim().toLowerCase();
            return books.some(b => b.name.toLowerCase() === bookName);
        }

        /**
         * Find matching books by prefix.
         * @param {string} query
         * @param {Array} books
         * @returns {Array}
         */
        function findMatches(query, books) {
            const lower = query.toLowerCase().trim();
            if (!lower) return [];

            return books.filter(book => {
                return book.name.toLowerCase().startsWith(lower);
            }).slice(0, 10);
        }

        /**
         * Create and manage the dropdown for a single input.
         * @param {HTMLInputElement} input
         */
        function initAutocomplete(input) {
            const books = getBooks();
            if (!books.length) return;

            let dropdown = null;
            let selectedIndex = -1;
            let matches = [];

            function createDropdown() {
                if (dropdown) return dropdown;

                dropdown = document.createElement('div');
                dropdown.className = 'scripture-autocomplete-dropdown';
                dropdown.style.cssText = 'position:absolute;z-index:9999;background:#fff;border:1px solid #ccc;'
                    + 'border-radius:4px;max-height:200px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,.15);'
                    + 'display:none;min-width:200px;';

                // Position relative to the input
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                wrapper.style.display = 'inline-block';
                wrapper.style.width = '100%';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                wrapper.appendChild(dropdown);

                return dropdown;
            }

            function showDropdown(items) {
                const dd = createDropdown();
                matches = items;
                selectedIndex = -1;

                dd.innerHTML = '';
                items.forEach((book, i) => {
                    const item = document.createElement('div');
                    item.className = 'scripture-autocomplete-item';
                    item.style.cssText = 'padding:6px 10px;cursor:pointer;font-size:14px;';
                    item.textContent = book.name;
                    item.dataset.index = i;

                    item.addEventListener('mouseenter', () => {
                        highlightItem(i);
                    });
                    item.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        selectItem(i);
                    });

                    dd.appendChild(item);
                });

                dd.style.display = items.length ? 'block' : 'none';
            }

            function hideDropdown() {
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
                matches = [];
                selectedIndex = -1;
            }

            function highlightItem(index) {
                if (!dropdown) return;
                const items = dropdown.querySelectorAll('.scripture-autocomplete-item');
                items.forEach((el, i) => {
                    el.style.backgroundColor = i === index ? '#e8f0fe' : '';
                });
                selectedIndex = index;
            }

            function selectItem(index) {
                if (index < 0 || index >= matches.length) return;

                const book = matches[index];
                input.value = book.name + ' ';
                hideDropdown();
                input.focus();
                updateValidation();
            }

            function updateValidation() {
                const val = input.value.trim();
                if (!val) {
                    input.style.borderColor = '';
                    return;
                }

                if (isValidReference(val, books)) {
                    input.style.borderColor = '#198754';
                } else {
                    // Could be mid-typing, only show red if they have chapter/verse parts
                    const hasNumbers = /\d/.test(val);
                    input.style.borderColor = hasNumbers ? '#dc3545' : '';
                }
            }

            function getBookPart(text) {
                // Extract what looks like the book part (everything before the first digit group separated by space)
                const match = text.match(/^(.+?)(?:\s+\d|$)/);
                return match ? match[1].trim() : text.trim();
            }

            // Event handlers
            input.addEventListener('input', () => {
                const val = input.value;
                const bookPart = getBookPart(val);

                // Only show autocomplete for the book part (before any numbers)
                if (bookPart && !/\d/.test(bookPart)) {
                    const items = findMatches(bookPart, books);
                    if (items.length > 0 && items.length < books.length) {
                        showDropdown(items);
                    } else {
                        hideDropdown();
                    }
                } else {
                    hideDropdown();
                }

                updateValidation();
            });

            input.addEventListener('keydown', (e) => {
                if (!dropdown || dropdown.style.display === 'none') return;

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        highlightItem(Math.min(selectedIndex + 1, matches.length - 1));
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        highlightItem(Math.max(selectedIndex - 1, 0));
                        break;
                    case 'Enter':
                        if (selectedIndex >= 0) {
                            e.preventDefault();
                            selectItem(selectedIndex);
                        }
                        break;
                    case 'Escape':
                        hideDropdown();
                        break;
                }
            });

            input.addEventListener('blur', () => {
                // Small delay to allow click events on dropdown items
                setTimeout(hideDropdown, 150);
                updateValidation();
            });

            input.addEventListener('focus', () => {
                updateValidation();
            });

            // Initial validation for pre-filled values
            updateValidation();
        }

        /**
         * Initialize autocomplete on all current and future .scripture-autocomplete inputs.
         */
        function initAll() {
            document.querySelectorAll('.scripture-autocomplete').forEach(input => {
                if (!input.dataset.scriptureInit) {
                    initAutocomplete(input);
                    input.dataset.scriptureInit = '1';
                }
            });
        }

        /**
         * Set default Bible version on newly added subform rows.
         * @param {HTMLElement} row
         */
        function setDefaultVersion(row) {
            const defaultVersion = getDefaultVersion();
            if (!defaultVersion) return;

            const versionSelect = row.querySelector('select[name*="bible_version"]');
            if (versionSelect && !versionSelect.value) {
                versionSelect.value = defaultVersion;

                // Trigger change event for Joomla chosen/select2
                versionSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            initAll();

            // Watch for new subform rows being added
            document.addEventListener('subform-row-add', (event) => {
                const row = event.detail?.row || event.target;
                if (row) {
                    // Small delay for DOM to be ready
                    setTimeout(() => {
                        row.querySelectorAll('.scripture-autocomplete').forEach(input => {
                            if (!input.dataset.scriptureInit) {
                                initAutocomplete(input);
                                input.dataset.scriptureInit = '1';
                            }
                        });
                        setDefaultVersion(row);
                    }, 50);
                }
            });
        });

        // Also re-init after AJAX updates (Joomla subform might reload)
        document.addEventListener('joomla:updated', initAll);
    })(Joomla);

})();
