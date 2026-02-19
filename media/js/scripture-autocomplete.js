(function () {
    'use strict';

    /**
     * Scripture Autocomplete for Proclaim
     *
     * Attaches to .scripture-autocomplete inputs within the scripture subform.
     * Provides book name autocomplete with progressive chapter/verse hints
     * based on KJV verse-count data. Hints are informational only — the user
     * is never prevented from entering any value (Bible versions differ).
     *
     * Also enhances Bible version <select> elements with a search filter.
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

        // Partial regex: book + chapter (no verse yet)
        const BOOK_CHAPTER_REGEX = /^(.+?)\s+(\d+)$/;

        // Partial regex: book + chapter + colon (waiting for verse)
        const BOOK_CHAPTER_COLON_REGEX = /^(.+?)\s+(\d+):$/;

        // Partial regex: book + chapter:verse + dash (waiting for end)
        const RANGE_START_REGEX = /^(.+?)\s+(\d+):(\d+)\s*-\s*$/;

        /**
         * Get book data from Joomla script options.
         * @returns {Array} Array of {booknumber, name, key}
         */
        function getBooks() {
            return Joomla.getOptions('com_proclaim.books') || [];
        }

        /**
         * Get Bible structure data (verse counts per chapter, keyed by booknumber).
         * @returns {Object} { booknumber: [ch1Verses, ch2Verses, ...], ... }
         */
        function getStructure() {
            return Joomla.getOptions('com_proclaim.bibleStructure') || {};
        }

        /**
         * Get default Bible version from script options.
         * @returns {string}
         */
        function getDefaultVersion() {
            return Joomla.getOptions('com_proclaim.defaultBibleVersion') || '';
        }

        /**
         * Look up a book by name (case-insensitive exact match).
         * @param {string} name
         * @param {Array} books
         * @returns {Object|null}
         */
        function findBookByName(name, books) {
            const lower = name.toLowerCase().trim();
            return books.find((b) => b.name.toLowerCase() === lower) || null;
        }

        /**
         * Get chapter count for a book.
         * @param {number} booknumber
         * @param {Object} structure
         * @returns {number}
         */
        function getChapterCount(booknumber, structure) {
            const verses = structure[booknumber];
            return verses ? verses.length : 0;
        }

        /**
         * Get verse count for a specific chapter in a book.
         * @param {number} booknumber
         * @param {number} chapter  1-based
         * @param {Object} structure
         * @returns {number}
         */
        function getVerseCount(booknumber, chapter, structure) {
            const verses = structure[booknumber];
            if (!verses || chapter < 1 || chapter > verses.length) {
                return 0;
            }
            return verses[chapter - 1];
        }

        /**
         * Test if a reference string matches a complete pattern.
         * @param {string} text
         * @param {Array} books
         * @returns {boolean}
         */
        function isCompleteReference(text, books) {
            const match = text.match(REFERENCE_REGEX);
            if (!match) return false;
            return !!findBookByName(match[1], books);
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

            return books.filter((book) => book.name.toLowerCase().startsWith(lower)).slice(0, 10);
        }

        /**
         * Extract the book-name portion from a partial reference string.
         * For "Genesis 3:16" returns "Genesis", for "1 John" returns "1 John".
         * @param {string} text
         * @returns {string}
         */
        function getBookPart(text) {
            const match = text.match(/^(.+?)(?:\s+\d|$)/);
            if (!match) return text.trim();

            const candidate = match[1].trim();

            // Handle numbered books: if the extracted part is just a digit (e.g. "1"),
            // the user is likely starting a numbered book name. Return the full text
            // so autocomplete can match "1 John", "1 Kings", etc.
            if (/^\d+$/.test(candidate)) {
                return text.trim();
            }

            return candidate;
        }

        /**
         * Create and manage the dropdown + hint for a single input.
         * @param {HTMLInputElement} input
         */
        function initAutocomplete(input) {
            const books = getBooks();
            const structure = getStructure();
            if (!books.length) return;

            let dropdown = null;
            let hintEl = null;
            let selectedIndex = -1;
            let matches = [];

            function createDropdown() {
                if (dropdown) return dropdown;

                dropdown = document.createElement('div');
                dropdown.className = 'scripture-autocomplete-dropdown';
                dropdown.style.cssText = 'position:absolute;z-index:9999;background:#fff;border:1px solid #ccc;'
                    + 'border-radius:4px;max-height:200px;overflow-y:auto;box-shadow:0 2px 8px rgba(0,0,0,.15);'
                    + 'display:none;min-width:220px;';

                // Hint element below input
                hintEl = document.createElement('div');
                hintEl.className = 'scripture-hint text-muted';
                hintEl.style.cssText = 'font-size:12px;margin-top:2px;min-height:1.2em;';

                // Position relative to the input
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                wrapper.style.display = 'inline-block';
                wrapper.style.width = '100%';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                wrapper.appendChild(dropdown);
                wrapper.appendChild(hintEl);

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
                    item.style.cssText = 'padding:6px 10px;cursor:pointer;font-size:14px;'
                        + 'display:flex;justify-content:space-between;align-items:center;';

                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = book.name;

                    const chapterCount = getChapterCount(book.booknumber, structure);
                    const infoSpan = document.createElement('span');
                    infoSpan.style.cssText = 'font-size:11px;color:#6c757d;margin-left:12px;';
                    if (chapterCount > 0) {
                        infoSpan.textContent = `${chapterCount} ch`;
                    }

                    item.appendChild(nameSpan);
                    item.appendChild(infoSpan);

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
                input.value = `${book.name} `;
                hideDropdown();
                input.focus();
                updateState();
            }

            /**
             * Update the hint text and input border based on current value.
             * Green border = complete valid reference pattern.
             * No red border ever — just neutral or green.
             */
            function updateState() {
                createDropdown();
                const val = input.value.trim();

                if (!val) {
                    input.style.borderColor = '';
                    hintEl.textContent = '';
                    return;
                }

                // Complete reference — green border, clear hint
                if (isCompleteReference(val, books)) {
                    input.style.borderColor = '#198754';
                    hintEl.textContent = '';
                    return;
                }

                // Reset border to default when not a complete reference
                input.style.borderColor = '';

                // Check for partial states and show progressive hints

                // State: "Book Ch:Vs -" waiting for end verse/chapter
                let m = val.match(RANGE_START_REGEX);
                if (m) {
                    const book = findBookByName(m[1], books);
                    if (book) {
                        const ch = parseInt(m[2], 10);
                        const maxV = getVerseCount(book.booknumber, ch, structure);
                        const maxCh = getChapterCount(book.booknumber, structure);
                        if (maxV > 0) {
                            hintEl.textContent = `End verse (ch ${ch} has ${maxV
                        } verses) or chapter:verse (book has ${maxCh} chapters)`;
                        } else {
                            hintEl.textContent = 'Enter end verse or chapter:verse';
                        }
                    }
                    return;
                }

                // State: "Book Ch:" waiting for verse
                m = val.match(BOOK_CHAPTER_COLON_REGEX);
                if (m) {
                    const book = findBookByName(m[1], books);
                    if (book) {
                        const ch = parseInt(m[2], 10);
                        const maxV = getVerseCount(book.booknumber, ch, structure);
                        if (maxV > 0) {
                            hintEl.textContent = `Chapter ${ch} has ${maxV} verses (KJV)`;
                        } else {
                            hintEl.textContent = '';
                        }
                    }
                    return;
                }

                // State: "Book Ch" — book selected, chapter entered, waiting for colon
                m = val.match(BOOK_CHAPTER_REGEX);
                if (m) {
                    const book = findBookByName(m[1], books);
                    if (book) {
                        const ch = parseInt(m[2], 10);
                        const maxCh = getChapterCount(book.booknumber, structure);
                        const maxV = getVerseCount(book.booknumber, ch, structure);
                        if (maxCh > 0 && maxV > 0) {
                            hintEl.textContent = `Type : for verses (ch ${ch} has ${
                            maxV} verses, book has ${maxCh} chapters)`;
                        } else if (maxCh > 0) {
                            hintEl.textContent = `Book has ${maxCh} chapters — type : for verses`;
                        } else {
                            hintEl.textContent = '';
                        }
                    }
                    return;
                }

                // State: typing book name — show book hint if exact match found
                const bookPart = getBookPart(val);
                if (bookPart) {
                    const exactBook = findBookByName(bookPart, books);
                    if (exactBook) {
                        const maxCh = getChapterCount(exactBook.booknumber, structure);
                        if (maxCh > 0) {
                            hintEl.textContent = `${exactBook.name} has ${maxCh} chapters`;
                        }
                    } else {
                        hintEl.textContent = '';
                    }
                } else {
                    hintEl.textContent = '';
                }
            }

            // Event handlers
            input.addEventListener('input', () => {
                const val = input.value;
                const bookPart = getBookPart(val);

                if (bookPart) {
                    const items = findMatches(bookPart, books);
                    // Check if the book part is an exact match for a known book.
                    // If so, the user has already selected the book and is now typing
                    // chapter/verse — don't re-show the dropdown.
                    const isExactBookMatch = books.some(
                        (b) => b.name.toLowerCase() === bookPart.toLowerCase(),
                    );

                    if (items.length > 0 && items.length < books.length && !isExactBookMatch) {
                        showDropdown(items);
                    } else {
                        hideDropdown();
                    }
                } else {
                    hideDropdown();
                }

                updateState();
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
                updateState();
            });

            input.addEventListener('focus', () => {
                updateState();
            });

            // Initial state for pre-filled values
            updateState();
        }

        // ─── Bible Version Search Enhancement ─────────────────────────────

        /**
         * Enhance a <select> with a search filter input.
         * Adds a text input above the select that filters visible <option> elements.
         * If the select has no value, applies the global default Bible version.
         *
         * @param {HTMLSelectElement} select
         */
        function initVersionSearch(select) {
            // If no value selected, apply the global default
            if (!select.value) {
                const defaultVersion = getDefaultVersion();
                if (defaultVersion) {
                    select.value = defaultVersion;
                }
            }

            // Collect all options
            const allOptions = [];
            for (let i = 0; i < select.options.length; i++) {
                allOptions.push({
                    value: select.options[i].value,
                    text: select.options[i].textContent,
                    element: select.options[i],
                });
            }

            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'bible-version-search-wrapper';
            wrapper.style.cssText = 'position:relative;width:100%;';
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);

            // Create search input
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control form-control-sm';
            searchInput.placeholder = Joomla.Text._('JBS_STY_SEARCH_VERSIONS') || 'Search versions...';
            searchInput.style.cssText = 'margin-bottom:4px;font-size:12px;';
            wrapper.insertBefore(searchInput, select);

            // Expand the select to show multiple options for easier browsing
            select.size = 6;
            select.style.cssText = 'width:100%;max-height:160px;overflow-y:auto;';

            /**
             * Highlight the selected option with explicit colors and a checkmark.
             * Native <select>/<option> styling is inconsistent across dark admin
             * themes, so we paint every option explicitly using Bootstrap CSS
             * variables so the whole list looks uniform.
             */
            function highlightSelected() {
                const cs = getComputedStyle(document.documentElement);
                const selectCs = getComputedStyle(select);
                const activeBg = cs.getPropertyValue('--bs-primary-bg-subtle').trim() || '#cfe2ff';
                const activeColor = cs.getPropertyValue('--bs-emphasis-color').trim() || '#000';
                // Read normal colors from the select element itself (inherits the admin theme correctly)
                const normalBg = selectCs.backgroundColor || '#fff';
                const normalColor = selectCs.color || '#212529';

                for (let i = 0; i < select.options.length; i++) {
                    const original = allOptions[i] ? allOptions[i].text : select.options[i].textContent;

                    if (i === select.selectedIndex && select.options[i].value) {
                        select.options[i].textContent = `\u2713 ${original}`;
                        select.options[i].style.backgroundColor = activeBg;
                        select.options[i].style.color = activeColor;
                        select.options[i].style.fontWeight = '600';
                    } else {
                        select.options[i].textContent = `   ${original}`;
                        select.options[i].style.backgroundColor = normalBg;
                        select.options[i].style.color = normalColor;
                        select.options[i].style.fontWeight = '';
                    }
                }
            }

            // Scroll the currently selected option into view
            function scrollToSelected() {
                const idx = select.selectedIndex;
                if (idx >= 0 && select.options[idx] && select.options[idx].value) {
                    select.options[idx].scrollIntoView({ block: 'nearest' });
                }
            }

            // Apply highlight + scroll on init (delay for DOM settle)
            setTimeout(() => {
                highlightSelected();
                scrollToSelected();
            }, 100);

            // Filter options on search input
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();

                allOptions.forEach((opt) => {
                    if (!opt.value) {
                        opt.element.style.display = '';
                        return;
                    }

                    if (!query || opt.text.toLowerCase().includes(query)) {
                        opt.element.style.display = '';
                    } else {
                        opt.element.style.display = 'none';
                    }
                });
            });

            // When an option is selected, update highlight, clear search, restore all options
            select.addEventListener('change', () => {
                highlightSelected();
                const selected = select.options[select.selectedIndex];
                if (selected && selected.value) {
                    searchInput.value = '';
                    allOptions.forEach((opt) => {
                        opt.element.style.display = '';
                    });
                }
            });
        }

        /**
         * Initialize search on all bible version selects.
         */
        function initAllVersionSearches() {
            document.querySelectorAll('.bible-version-searchable').forEach((select) => {
                if (!select.dataset.versionSearchInit) {
                    initVersionSearch(select);
                    select.dataset.versionSearchInit = '1';
                }
            });
        }

        // ─── Initialization ───────────────────────────────────────────────

        /**
         * Initialize autocomplete on all current and future .scripture-autocomplete inputs.
         */
        function initAll() {
            document.querySelectorAll('.scripture-autocomplete').forEach((input) => {
                if (!input.dataset.scriptureInit) {
                    initAutocomplete(input);
                    input.dataset.scriptureInit = '1';
                }
            });
            initAllVersionSearches();
        }

        /**
         * Set default Bible version on newly added subform rows.
         * Always resets to the global default — cloned rows may carry a stale value.
         * @param {HTMLElement} row
         */
        function setDefaultVersion(row) {
            const defaultVersion = getDefaultVersion();
            if (!defaultVersion) return;

            const versionSelect = row.querySelector('select[name*="bible_version"]');
            if (versionSelect) {
                versionSelect.value = defaultVersion;

                // Trigger change event so the search wrapper stays in sync
                versionSelect.dispatchEvent(new Event('change', { bubbles: true }));

                // Scroll the newly selected option into view in the listbox
                const idx = versionSelect.selectedIndex;
                if (idx >= 0 && versionSelect.options[idx]) {
                    versionSelect.options[idx].scrollIntoView({ block: 'nearest' });
                }
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
                        row.querySelectorAll('.scripture-autocomplete').forEach((input) => {
                            if (!input.dataset.scriptureInit) {
                                initAutocomplete(input);
                                input.dataset.scriptureInit = '1';
                            }
                        });
                        row.querySelectorAll('.bible-version-searchable').forEach((select) => {
                            if (!select.dataset.versionSearchInit) {
                                initVersionSearch(select);
                                select.dataset.versionSearchInit = '1';
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
