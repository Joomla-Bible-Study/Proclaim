(function () {
    'use strict';

    /**
     * @copyright  (C) 2026 CWM Team All rights reserved
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
     * @description Local File Browser for Proclaim Media Files
     */
    /* jshint esversion: 6 */
    (() => {

        window.Proclaim = window.Proclaim || {};

        /**
         * Helper to get a Joomla translation key, returning the fallback
         * when the key is unregistered (Joomla.Text._() returns the raw key).
         */
        function txt(key, fallback) {
            const val = Joomla.Text._(key);
            return (val && val !== key) ? val : (fallback || key);
        }

        window.Proclaim.LocalBrowser = {
            modal: null,
            serverId: null,
            currentPath: '',
            basePath: '',
            currentFilter: 'all',
            searchTerm: '',

            /**
             * Image file extensions that trigger the duplicate/copy prompt
             */
            imageExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],

            /**
             * Initialize and open the browser modal
             */
            open() {
                // Get server_id from the form
                const serverField = document.getElementById('jform_server_id');
                if (!serverField || !serverField.value) {
                    Joomla.renderMessages({ warning: ['Please select a server first.'] });
                    return;
                }
                this.serverId = serverField.value;
                this.currentPath = '';
                this.currentFilter = 'all';
                this.searchTerm = '';

                if (!this.modal) {
                    this.createModal();
                }

                this.modal.show();
                this.loadFiles('', 'all');
            },

            /**
             * Create the Bootstrap 5 modal
             */
            createModal() {
                if (document.getElementById('localBrowserModal')) {
                    document.getElementById('localBrowserModal').remove();
                }

                // Build modal using DOM API to avoid innerHTML with dynamic content
                const modalEl = document.createElement('div');
                modalEl.className = 'modal fade';
                modalEl.id = 'localBrowserModal';
                modalEl.setAttribute('tabindex', '-1');
                modalEl.setAttribute('aria-labelledby', 'localBrowserModalLabel');

                const dialog = document.createElement('div');
                dialog.className = 'modal-dialog modal-xl modal-dialog-scrollable';

                const content = document.createElement('div');
                content.className = 'modal-content';

                // Header
                const header = document.createElement('div');
                header.className = 'modal-header';
                const title = document.createElement('h5');
                title.className = 'modal-title';
                title.id = 'localBrowserModalLabel';
                const titleIcon = document.createElement('span');
                titleIcon.className = 'icon-folder';
                titleIcon.setAttribute('aria-hidden', 'true');
                title.appendChild(titleIcon);
                title.appendChild(document.createTextNode(` ${txt('JBS_MED_LOCAL_BROWSER_TITLE', 'Browse Media Files')}`));
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn-close';
                closeBtn.setAttribute('data-bs-dismiss', 'modal');
                closeBtn.setAttribute('aria-label', 'Close');
                header.appendChild(title);
                header.appendChild(closeBtn);

                // Body
                const body = document.createElement('div');
                body.className = 'modal-body';

                // Breadcrumb container
                const breadcrumbDiv = document.createElement('div');
                breadcrumbDiv.id = 'localBrowserBreadcrumb';
                breadcrumbDiv.className = 'mb-3';
                body.appendChild(breadcrumbDiv);

                // Filter bar + search (static translated labels, safe to set via textContent)
                const filterBar = document.createElement('div');
                filterBar.className = 'd-flex gap-2 mb-3 flex-wrap align-items-center';

                const btnGroup = document.createElement('div');
                btnGroup.className = 'btn-group';
                btnGroup.setAttribute('role', 'group');
                btnGroup.setAttribute('aria-label', 'File type filter');

                const filters = [
                    {
                        filter: 'all', label: txt('JBS_MED_LOCAL_BROWSER_FILTER_ALL', 'All Files'), icon: null, active: true,
                    },
                    {
                        filter: 'audio', label: txt('JBS_MED_LOCAL_BROWSER_FILTER_AUDIO', 'Audio'), icon: 'icon-headphones', active: false,
                    },
                    {
                        filter: 'video', label: txt('JBS_MED_LOCAL_BROWSER_FILTER_VIDEO', 'Video'), icon: 'icon-play', active: false,
                    },
                    {
                        filter: 'document', label: txt('JBS_MED_LOCAL_BROWSER_FILTER_DOCS', 'Documents'), icon: 'icon-file', active: false,
                    },
                ];

                filters.forEach((f) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `btn btn-outline-secondary btn-sm${f.active ? ' active' : ''}`;
                    btn.setAttribute('data-filter', f.filter);
                    if (f.icon) {
                        const ico = document.createElement('span');
                        ico.className = f.icon;
                        ico.setAttribute('aria-hidden', 'true');
                        btn.appendChild(ico);
                        btn.appendChild(document.createTextNode(' '));
                    }
                    btn.appendChild(document.createTextNode(f.label));
                    btnGroup.appendChild(btn);
                });

                filterBar.appendChild(btnGroup);

                const searchWrap = document.createElement('div');
                searchWrap.className = 'ms-auto';
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.id = 'localBrowserSearch';
                searchInput.className = 'form-control form-control-sm';
                searchInput.placeholder = 'Filter by name...';
                searchInput.style.width = '200px';
                searchWrap.appendChild(searchInput);
                filterBar.appendChild(searchWrap);

                body.appendChild(filterBar);

                // File grid container
                const grid = document.createElement('div');
                grid.id = 'localBrowserGrid';
                body.appendChild(grid);

                content.appendChild(header);
                content.appendChild(body);
                dialog.appendChild(content);
                modalEl.appendChild(dialog);
                document.body.appendChild(modalEl);

                this.modal = new bootstrap.Modal(modalEl);

                // Bind filter buttons
                const self = this;
                const filterBtns = modalEl.querySelectorAll('[data-filter]');
                filterBtns.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        filterBtns.forEach((b) => { b.classList.remove('active'); });
                        btn.classList.add('active');
                        self.currentFilter = btn.dataset.filter;
                        self.loadFiles(self.currentPath, self.currentFilter);
                    });
                });

                // Bind search input
                if (searchInput) {
                    let searchTimer = null;
                    searchInput.addEventListener('input', () => {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(() => {
                            self.searchTerm = searchInput.value.toLowerCase();
                            self.filterVisibleFiles();
                        }, 200);
                    });
                }

                // Clean up search on modal hide
                modalEl.addEventListener('hidden.bs.modal', () => {
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    self.searchTerm = '';
                });
            },

            /**
             * Load files via AJAX
             */
            loadFiles(path, filter) {
                const self = this;
                const grid = document.getElementById('localBrowserGrid');
                if (!grid) {
                    return;
                }

                grid.textContent = '';
                const spinner = document.createElement('div');
                spinner.className = 'text-center p-4';
                const spinnerIcon = document.createElement('span');
                spinnerIcon.className = 'spinner-border';
                spinnerIcon.setAttribute('role', 'status');
                spinner.appendChild(spinnerIcon);
                const loadingText = document.createElement('p');
                loadingText.className = 'mt-2 text-muted';
                loadingText.textContent = txt('JBS_MED_LOCAL_BROWSER_LOADING', 'Loading files...');
                spinner.appendChild(loadingText);
                grid.appendChild(spinner);

                const config = Joomla.getOptions('com_proclaim.mediafile') || {};
                const token = config.token || '';

                const url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=local&handler=browseFiles'
                    + `&server_id=${encodeURIComponent(this.serverId)
                }&path=${encodeURIComponent(path || '')
                }&filter=${encodeURIComponent(filter || 'all')
                }&${encodeURIComponent(token)}=1`;

                Joomla.request({
                    url,
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    onSuccess(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                self.currentPath = path || '';
                                self.basePath = data.basePath || '';
                                self.renderBreadcrumb(data.currentPath, data.parentPath, data.basePath);
                                self.renderGrid(data.folders, data.files);
                            } else {
                                self.showGridMessage(grid, 'alert-danger', data.error || 'Error loading files');
                            }
                        } catch {
                            self.showGridMessage(grid, 'alert-danger', 'Invalid response from server');
                        }
                    },
                    onError() {
                        self.showGridMessage(grid, 'alert-danger', 'Network error loading files');
                    },
                });
            },

            /**
             * Show a message inside the grid area using safe DOM methods
             */
            showGridMessage(grid, alertClass, message) {
                grid.textContent = '';
                const alert = document.createElement('div');
                alert.className = `alert ${alertClass}`;
                alert.textContent = message;
                grid.appendChild(alert);
            },

            /**
             * Render breadcrumb navigation using safe DOM methods
             */
            renderBreadcrumb(currentPath, parentPath, basePath) {
                const breadcrumb = document.getElementById('localBrowserBreadcrumb');
                if (!breadcrumb) {
                    return;
                }

                const self = this;
                const parts = (currentPath || '').split('/').filter((p) => p);
                const baseParts = (basePath || '').split('/').filter((p) => p);

                breadcrumb.textContent = '';
                const nav = document.createElement('nav');
                nav.setAttribute('aria-label', 'File browser breadcrumb');
                const ol = document.createElement('ol');
                ol.className = 'breadcrumb mb-0';

                // Root link
                const rootLi = document.createElement('li');
                rootLi.className = 'breadcrumb-item';
                const rootLink = document.createElement('a');
                rootLink.href = '#';
                rootLink.className = 'local-browser-nav';
                rootLink.dataset.path = '';
                const homeIcon = document.createElement('span');
                homeIcon.className = 'icon-home';
                homeIcon.setAttribute('aria-hidden', 'true');
                rootLink.appendChild(homeIcon);
                rootLi.appendChild(rootLink);
                ol.appendChild(rootLi);

                // Build path segments after the base path
                const relativeParts = parts.slice(baseParts.length);
                for (let i = 0; i < relativeParts.length; i += 1) {
                    const segmentPath = relativeParts.slice(0, i + 1).join('/');
                    const isLast = (i === relativeParts.length - 1);
                    const li = document.createElement('li');
                    li.className = `breadcrumb-item${isLast ? ' active' : ''}`;

                    if (isLast) {
                        li.textContent = relativeParts[i];
                    } else {
                        const link = document.createElement('a');
                        link.href = '#';
                        link.className = 'local-browser-nav';
                        link.dataset.path = segmentPath;
                        link.textContent = relativeParts[i];
                        li.appendChild(link);
                    }
                    ol.appendChild(li);
                }

                nav.appendChild(ol);
                breadcrumb.appendChild(nav);

                // Bind navigation links
                breadcrumb.querySelectorAll('.local-browser-nav').forEach((navLink) => {
                    navLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        self.loadFiles(navLink.dataset.path, self.currentFilter);
                    });
                });
            },

            /**
             * Render the file/folder grid using safe DOM methods
             */
            renderGrid(folders, files) {
                const grid = document.getElementById('localBrowserGrid');
                if (!grid) {
                    return;
                }

                const self = this;
                grid.textContent = '';

                if ((!folders || !folders.length) && (!files || !files.length)) {
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'text-center p-4 text-muted';
                    const emptyIcon = document.createElement('span');
                    emptyIcon.className = 'icon-info-circle';
                    emptyIcon.style.fontSize = '2rem';
                    emptyIcon.setAttribute('aria-hidden', 'true');
                    emptyDiv.appendChild(emptyIcon);
                    const emptyText = document.createElement('p');
                    emptyText.className = 'mt-2';
                    emptyText.textContent = txt('JBS_MED_LOCAL_BROWSER_EMPTY', 'No files found in this directory.');
                    emptyDiv.appendChild(emptyText);
                    grid.appendChild(emptyDiv);
                    return;
                }

                const row = document.createElement('div');
                row.className = 'row g-2';

                // Helper to create a card column
                function createCol() {
                    const col = document.createElement('div');
                    col.className = 'col-lg-2 col-md-3 col-sm-4 col-6';
                    return col;
                }

                // Parent folder link
                if (this.currentPath) {
                    const upCol = createCol();
                    const upCard = document.createElement('div');
                    upCard.className = 'card h-100 local-browser-item local-browser-folder';
                    upCard.setAttribute('role', 'button');
                    upCard.setAttribute('tabindex', '0');
                    upCard.dataset.pathUp = 'true';
                    const upBody = document.createElement('div');
                    upBody.className = 'card-body text-center p-2';
                    const upIcon = document.createElement('span');
                    upIcon.className = 'icon-arrow-up';
                    upIcon.style.fontSize = '1.5rem';
                    upIcon.style.color = '#6c757d';
                    upIcon.setAttribute('aria-hidden', 'true');
                    upBody.appendChild(upIcon);
                    const upLabel = document.createElement('p');
                    upLabel.className = 'card-text small text-truncate mt-1 mb-0';
                    upLabel.textContent = '..';
                    upBody.appendChild(upLabel);
                    upCard.appendChild(upBody);
                    upCol.appendChild(upCard);
                    row.appendChild(upCol);
                }

                // Folders
                if (folders && folders.length) {
                    for (let i = 0; i < folders.length; i += 1) {
                        const folder = folders[i];
                        const fCol = createCol();
                        const fCard = document.createElement('div');
                        fCard.className = 'card h-100 local-browser-item local-browser-folder';
                        fCard.setAttribute('role', 'button');
                        fCard.setAttribute('tabindex', '0');
                        fCard.dataset.folderPath = folder.path;
                        const fBody = document.createElement('div');
                        fBody.className = 'card-body text-center p-2';
                        const fIcon = document.createElement('span');
                        fIcon.className = 'icon-folder';
                        fIcon.style.fontSize = '1.5rem';
                        fIcon.style.color = '#ffc107';
                        fIcon.setAttribute('aria-hidden', 'true');
                        fBody.appendChild(fIcon);
                        const fName = document.createElement('p');
                        fName.className = 'card-text small text-truncate mt-1 mb-0';
                        fName.title = folder.name;
                        fName.textContent = folder.name;
                        fBody.appendChild(fName);
                        fCard.appendChild(fBody);
                        fCol.appendChild(fCard);
                        row.appendChild(fCol);
                    }
                }

                // Files
                if (files && files.length) {
                    for (let j = 0; j < files.length; j += 1) {
                        const file = files[j];
                        const fileCol = createCol();
                        fileCol.className += ' local-browser-file-item';
                        fileCol.dataset.filename = file.name.toLowerCase();

                        const fileCard = document.createElement('div');
                        fileCard.className = 'card h-100 local-browser-item local-browser-file';
                        fileCard.setAttribute('role', 'button');
                        fileCard.setAttribute('tabindex', '0');
                        fileCard.dataset.filePath = file.path;
                        fileCard.dataset.fileSize = file.size;

                        const fileBody = document.createElement('div');
                        fileBody.className = 'card-body text-center p-2';

                        const fileIcon = document.createElement('span');
                        fileIcon.className = self.getFileIcon(file.category);
                        fileIcon.style.fontSize = '1.5rem';
                        fileIcon.style.color = self.getFileIconColor(file.category);
                        fileIcon.setAttribute('aria-hidden', 'true');
                        fileBody.appendChild(fileIcon);

                        const fileName = document.createElement('p');
                        fileName.className = 'card-text small text-truncate mt-1 mb-0';
                        fileName.title = file.name;
                        fileName.textContent = file.name;
                        fileBody.appendChild(fileName);

                        const metaDiv = document.createElement('div');
                        metaDiv.className = 'mt-1';
                        if (file.extension) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-light text-dark';
                            badge.textContent = file.extension.toUpperCase();
                            metaDiv.appendChild(badge);
                            metaDiv.appendChild(document.createTextNode(' '));
                        }
                        const sizeSmall = document.createElement('small');
                        sizeSmall.className = 'text-muted';
                        sizeSmall.textContent = self.formatFileSize(file.size);
                        metaDiv.appendChild(sizeSmall);
                        fileBody.appendChild(metaDiv);

                        fileCard.appendChild(fileBody);
                        fileCol.appendChild(fileCard);
                        row.appendChild(fileCol);
                    }
                }

                grid.appendChild(row);

                // Bind folder navigation
                grid.querySelectorAll('.local-browser-folder').forEach((card) => {
                    function navigate() {
                        if (card.dataset.pathUp) {
                            self.navigateUp();
                        } else {
                            self.loadFiles(card.dataset.folderPath, self.currentFilter);
                        }
                    }
                    card.addEventListener('click', navigate);
                    card.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); navigate(); }
                    });
                });

                // Bind file selection
                grid.querySelectorAll('.local-browser-file').forEach((card) => {
                    function doSelect() {
                        self.selectFile(card.dataset.filePath, parseInt(card.dataset.fileSize, 10) || 0);
                    }
                    card.addEventListener('click', doSelect);
                    card.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); doSelect(); }
                    });
                });

                // Apply any current search filter
                if (this.searchTerm) {
                    this.filterVisibleFiles();
                }
            },

            /**
             * Navigate up one directory
             */
            navigateUp() {
                if (!this.currentPath) {
                    return;
                }
                const parts = this.currentPath.split('/').filter((p) => p);
                parts.pop();
                const parentPath = parts.length > 0 ? parts.join('/').replace(`${this.basePath}/`, '').replace(this.basePath, '') : '';
                this.loadFiles(parentPath, this.currentFilter);
            },

            /**
             * Check if a file path is an image based on its extension
             */
            isImageFile(filePath) {
                const ext = (filePath || '').split('.').pop().toLowerCase();
                return this.imageExtensions.indexOf(ext) !== -1;
            },

            /**
             * Set the filename and size form fields (extracted for reuse)
             */
            setFileFields(filePath, fileSize) {
                const filenameField = document.getElementById('jform_params_filename');
                if (filenameField) {
                    filenameField.value = filePath;
                    filenameField.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (fileSize > 0) {
                    const sizeField = document.getElementById('jform_params_size');
                    if (sizeField && (!sizeField.value || sizeField.value === '0')) {
                        sizeField.value = fileSize;
                        sizeField.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            },

            /**
             * Select a file — shows image prompt for images, direct set for others
             */
            selectFile(filePath, fileSize) {
                const self = this;

                if (this.isImageFile(filePath)) {
                    // Close the file browser modal first
                    if (this.modal) {
                        this.modal.hide();
                    }

                    // Show the image prompt modal
                    this.showImagePrompt(
                        filePath,
                        fileSize,
                        () => {
                            // "Use Existing" — reference in place
                            self.setFileFields(filePath, fileSize);
                        },
                        () => {
                            // "Copy for This Record" — server-side copy
                            self.copyFileAndSelect(filePath, fileSize);
                        },
                    );
                } else {
                    // Non-image: set fields directly and close modal
                    this.setFileFields(filePath, fileSize);
                    if (this.modal) {
                        this.modal.hide();
                    }
                }
            },

            /**
             * Show a Bootstrap 5 modal prompting how to handle the selected image
             * Built using safe DOM methods to avoid XSS via innerHTML.
             */
            showImagePrompt(filePath, fileSize, onUseExisting, onCopyForRecord) {
                const modalId = 'imagePromptModal';
                const existing = document.getElementById(modalId);
                if (existing) {
                    existing.remove();
                }

                const fileNameStr = filePath.split('/').pop();

                // Build modal DOM
                const modalEl = document.createElement('div');
                modalEl.className = 'modal fade';
                modalEl.id = modalId;
                modalEl.setAttribute('tabindex', '-1');
                modalEl.setAttribute('aria-labelledby', 'imagePromptLabel');

                const dialog = document.createElement('div');
                dialog.className = 'modal-dialog modal-dialog-centered';
                const content = document.createElement('div');
                content.className = 'modal-content';

                // Header
                const header = document.createElement('div');
                header.className = 'modal-header';
                const title = document.createElement('h5');
                title.className = 'modal-title';
                title.id = 'imagePromptLabel';
                const titleIcon = document.createElement('span');
                titleIcon.className = 'icon-image';
                titleIcon.setAttribute('aria-hidden', 'true');
                title.appendChild(titleIcon);
                title.appendChild(document.createTextNode(` ${txt('JBS_MED_LOCAL_BROWSER_IMAGE_PROMPT_TITLE', 'Image File Selected')}`));
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn-close';
                closeBtn.setAttribute('data-bs-dismiss', 'modal');
                closeBtn.setAttribute('aria-label', 'Close');
                header.appendChild(title);
                header.appendChild(closeBtn);

                // Body
                const body = document.createElement('div');
                body.className = 'modal-body';

                const desc = document.createElement('p');
                desc.className = 'text-muted mb-3';
                desc.textContent = txt('JBS_MED_LOCAL_BROWSER_IMAGE_PROMPT_DESC', 'How would you like to use this image file?');
                body.appendChild(desc);

                const fileLabel = document.createElement('p');
                fileLabel.className = 'fw-bold text-truncate mb-4';
                fileLabel.title = fileNameStr;
                const fileLabelIcon = document.createElement('span');
                fileLabelIcon.className = 'icon-file';
                fileLabelIcon.setAttribute('aria-hidden', 'true');
                fileLabel.appendChild(fileLabelIcon);
                fileLabel.appendChild(document.createTextNode(` ${fileNameStr}`));
                body.appendChild(fileLabel);

                const btnGrid = document.createElement('div');
                btnGrid.className = 'd-grid gap-2';

                // "Use Existing" button
                const useExistingBtn = document.createElement('button');
                useExistingBtn.type = 'button';
                useExistingBtn.className = 'btn btn-outline-primary btn-lg text-start';
                useExistingBtn.id = 'imagePromptUseExisting';
                const ueIcon = document.createElement('span');
                ueIcon.className = 'icon-link';
                ueIcon.setAttribute('aria-hidden', 'true');
                useExistingBtn.appendChild(ueIcon);
                useExistingBtn.appendChild(document.createTextNode(' '));
                const ueStrong = document.createElement('strong');
                ueStrong.textContent = txt('JBS_MED_LOCAL_BROWSER_USE_EXISTING', 'Use Existing');
                useExistingBtn.appendChild(ueStrong);
                useExistingBtn.appendChild(document.createElement('br'));
                const ueSmall = document.createElement('small');
                ueSmall.className = 'text-muted';
                ueSmall.textContent = txt('JBS_MED_LOCAL_BROWSER_USE_EXISTING_DESC', 'Reference the original file in place (no copy)');
                useExistingBtn.appendChild(ueSmall);
                btnGrid.appendChild(useExistingBtn);

                // "Copy for This Record" button
                const copyBtn = document.createElement('button');
                copyBtn.type = 'button';
                copyBtn.className = 'btn btn-outline-success btn-lg text-start';
                copyBtn.id = 'imagePromptCopyForRecord';
                const cpIcon = document.createElement('span');
                cpIcon.className = 'icon-copy';
                cpIcon.setAttribute('aria-hidden', 'true');
                copyBtn.appendChild(cpIcon);
                copyBtn.appendChild(document.createTextNode(' '));
                const cpStrong = document.createElement('strong');
                cpStrong.textContent = txt('JBS_MED_LOCAL_BROWSER_COPY_FOR_RECORD', 'Copy for This Record');
                copyBtn.appendChild(cpStrong);
                copyBtn.appendChild(document.createElement('br'));
                const cpSmall = document.createElement('small');
                cpSmall.className = 'text-muted';
                cpSmall.textContent = txt('JBS_MED_LOCAL_BROWSER_COPY_FOR_RECORD_DESC', 'Create a unique copy so the original stays untouched');
                copyBtn.appendChild(cpSmall);
                btnGrid.appendChild(copyBtn);

                body.appendChild(btnGrid);

                content.appendChild(header);
                content.appendChild(body);
                dialog.appendChild(content);
                modalEl.appendChild(dialog);
                document.body.appendChild(modalEl);

                const bsModal = new bootstrap.Modal(modalEl);

                // Clean up on hide
                modalEl.addEventListener('hidden.bs.modal', () => {
                    bsModal.dispose();
                    modalEl.remove();
                });

                // Bind "Use Existing"
                useExistingBtn.addEventListener('click', () => {
                    bsModal.hide();
                    if (onUseExisting) {
                        onUseExisting();
                    }
                });

                // Bind "Copy for This Record"
                copyBtn.addEventListener('click', () => {
                    bsModal.hide();
                    if (onCopyForRecord) {
                        onCopyForRecord();
                    }
                });

                bsModal.show();
            },

            /**
             * Copy a file server-side via AJAX, then set the new path in the form
             */
            copyFileAndSelect(filePath, fileSize) {
                const self = this;
                const config = Joomla.getOptions('com_proclaim.mediafile') || {};
                const token = config.token || '';

                Joomla.renderMessages({ info: [txt('JBS_MED_LOCAL_BROWSER_COPYING', 'Copying file...')] });

                const url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=local&handler=copyFile'
                    + `&server_id=${encodeURIComponent(this.serverId)
                }&file=${encodeURIComponent(filePath)
                }&${encodeURIComponent(token)}=1`;

                Joomla.request({
                    url,
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    onSuccess(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success && data.newPath) {
                                self.setFileFields(data.newPath, data.newSize || fileSize);
                                Joomla.renderMessages({ message: ['File copied successfully.'] });
                            } else {
                                console.warn('Copy failed, using original:', data.error);
                                Joomla.renderMessages({ warning: [txt('JBS_MED_LOCAL_BROWSER_COPY_FAILED', 'Failed to copy file. Using original.')] });
                                self.setFileFields(filePath, fileSize);
                            }
                        } catch (e) {
                            console.error('Copy response parse error:', e);
                            Joomla.renderMessages({ warning: [txt('JBS_MED_LOCAL_BROWSER_COPY_FAILED', 'Failed to copy file. Using original.')] });
                            self.setFileFields(filePath, fileSize);
                        }
                    },
                    onError() {
                        console.error('Copy request failed');
                        Joomla.renderMessages({ warning: [txt('JBS_MED_LOCAL_BROWSER_COPY_FAILED', 'Failed to copy file. Using original.')] });
                        self.setFileFields(filePath, fileSize);
                    },
                });
            },

            /**
             * Filter visible files by search term
             */
            filterVisibleFiles() {
                const items = document.querySelectorAll('.local-browser-file-item');
                const term = this.searchTerm;

                items.forEach((item) => {
                    const filename = item.dataset.filename || '';
                    if (!term || filename.indexOf(term) !== -1) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            },

            /**
             * Get icon class for file category
             */
            getFileIcon(category) {
                if (category === 'audio') {
                    return 'icon-headphones';
                }
                if (category === 'video') {
                    return 'icon-play';
                }
                return 'icon-file';
            },

            /**
             * Get icon color for file category
             */
            getFileIconColor(category) {
                if (category === 'audio') {
                    return '#198754';
                }
                if (category === 'video') {
                    return '#0d6efd';
                }
                return '#6c757d';
            },

            /**
             * Format file size to human-readable string
             */
            formatFileSize(bytes) {
                if (!bytes || bytes === 0) {
                    return '0 B';
                }
                const units = ['B', 'KB', 'MB', 'GB'];
                let i = 0;
                let size = bytes;
                while (size >= 1024 && i < units.length - 1) {
                    size /= 1024;
                    i += 1;
                }
                return `${size.toFixed(i > 0 ? 1 : 0)} ${units[i]}`;
            },
        };
    })();

})();
