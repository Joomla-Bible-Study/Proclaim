/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description Local File Browser for Proclaim Media Files
 */
/* jshint esversion: 6 */
/* global Joomla, bootstrap, Proclaim, console */
(function() {
    'use strict';

    window.Proclaim = window.Proclaim || {};

    window.Proclaim.LocalBrowser = {
        modal: null,
        serverId: null,
        currentPath: '',
        basePath: '',
        currentFilter: 'all',
        searchTerm: '',

        /**
         * Initialize and open the browser modal
         */
        open: function() {
            // Get server_id from the form
            var serverField = document.getElementById('jform_server_id');
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
        createModal: function() {
            if (document.getElementById('localBrowserModal')) {
                document.getElementById('localBrowserModal').remove();
            }

            var modalHtml = '<div class="modal fade" id="localBrowserModal" tabindex="-1" aria-labelledby="localBrowserModalLabel">' +
                '<div class="modal-dialog modal-xl modal-dialog-scrollable">' +
                '<div class="modal-content">' +

                // Header
                '<div class="modal-header">' +
                '<h5 class="modal-title" id="localBrowserModalLabel"><span class="icon-folder" aria-hidden="true"></span> ' +
                (Joomla.Text._('JBS_MED_LOCAL_BROWSER_TITLE') || 'Browse Media Files') + '</h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                '</div>' +

                // Body
                '<div class="modal-body">' +

                // Breadcrumb
                '<div id="localBrowserBreadcrumb" class="mb-3"></div>' +

                // Filter bar + search
                '<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">' +
                '<div class="btn-group" role="group" aria-label="File type filter">' +
                '<button type="button" class="btn btn-outline-secondary btn-sm active" data-filter="all">' +
                (Joomla.Text._('JBS_MED_LOCAL_BROWSER_FILTER_ALL') || 'All Files') + '</button>' +
                '<button type="button" class="btn btn-outline-secondary btn-sm" data-filter="audio">' +
                '<span class="icon-headphones" aria-hidden="true"></span> ' +
                (Joomla.Text._('JBS_MED_LOCAL_BROWSER_FILTER_AUDIO') || 'Audio') + '</button>' +
                '<button type="button" class="btn btn-outline-secondary btn-sm" data-filter="video">' +
                '<span class="icon-play" aria-hidden="true"></span> ' +
                (Joomla.Text._('JBS_MED_LOCAL_BROWSER_FILTER_VIDEO') || 'Video') + '</button>' +
                '<button type="button" class="btn btn-outline-secondary btn-sm" data-filter="document">' +
                '<span class="icon-file" aria-hidden="true"></span> ' +
                (Joomla.Text._('JBS_MED_LOCAL_BROWSER_FILTER_DOCS') || 'Documents') + '</button>' +
                '</div>' +
                '<div class="ms-auto">' +
                '<input type="text" id="localBrowserSearch" class="form-control form-control-sm" ' +
                'placeholder="Filter by name..." style="width:200px;">' +
                '</div>' +
                '</div>' +

                // File grid container
                '<div id="localBrowserGrid"></div>' +

                '</div>' + // modal-body
                '</div></div></div>';

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            var modalEl = document.getElementById('localBrowserModal');
            this.modal = new bootstrap.Modal(modalEl);

            // Bind filter buttons
            var self = this;
            var filterBtns = modalEl.querySelectorAll('[data-filter]');
            filterBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(function(b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    self.currentFilter = btn.dataset.filter;
                    self.loadFiles(self.currentPath, self.currentFilter);
                });
            });

            // Bind search input
            var searchInput = document.getElementById('localBrowserSearch');
            if (searchInput) {
                var searchTimer = null;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() {
                        self.searchTerm = searchInput.value.toLowerCase();
                        self.filterVisibleFiles();
                    }, 200);
                });
            }

            // Clean up search on modal hide
            modalEl.addEventListener('hidden.bs.modal', function() {
                if (searchInput) {
                    searchInput.value = '';
                }
                self.searchTerm = '';
            });
        },

        /**
         * Load files via AJAX
         */
        loadFiles: function(path, filter) {
            var self = this;
            var grid = document.getElementById('localBrowserGrid');
            if (!grid) {
                return;
            }

            grid.innerHTML = '<div class="text-center p-4"><span class="spinner-border" role="status"></span>' +
                '<p class="mt-2 text-muted">' + (Joomla.Text._('JBS_MED_LOCAL_BROWSER_LOADING') || 'Loading files...') + '</p></div>';

            var config = Joomla.getOptions('com_proclaim.mediafile') || {};
            var token = config.token || '';

            var url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=local&handler=browseFiles' +
                '&server_id=' + encodeURIComponent(this.serverId) +
                '&path=' + encodeURIComponent(path || '') +
                '&filter=' + encodeURIComponent(filter || 'all') +
                '&' + encodeURIComponent(token) + '=1';

            Joomla.request({
                url: url,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                onSuccess: function(response) {
                    try {
                        var data = JSON.parse(response);
                        if (data.success) {
                            self.currentPath = path || '';
                            self.basePath = data.basePath || '';
                            self.renderBreadcrumb(data.currentPath, data.parentPath, data.basePath);
                            self.renderGrid(data.folders, data.files);
                        } else {
                            grid.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Error loading files') + '</div>';
                        }
                    } catch (e) {
                        grid.innerHTML = '<div class="alert alert-danger">Invalid response from server</div>';
                    }
                },
                onError: function() {
                    grid.innerHTML = '<div class="alert alert-danger">Network error loading files</div>';
                }
            });
        },

        /**
         * Render breadcrumb navigation
         */
        renderBreadcrumb: function(currentPath, parentPath, basePath) {
            var breadcrumb = document.getElementById('localBrowserBreadcrumb');
            if (!breadcrumb) {
                return;
            }

            var self = this;
            var parts = (currentPath || '').split('/').filter(function(p) { return p; });
            var baseParts = (basePath || '').split('/').filter(function(p) { return p; });

            var html = '<nav aria-label="File browser breadcrumb"><ol class="breadcrumb mb-0">';

            // Root link
            html += '<li class="breadcrumb-item"><a href="#" class="local-browser-nav" data-path="">' +
                '<span class="icon-home" aria-hidden="true"></span></a></li>';

            // Build path segments after the base path
            var relativeParts = parts.slice(baseParts.length);
            for (var i = 0; i < relativeParts.length; i++) {
                var segmentPath = relativeParts.slice(0, i + 1).join('/');
                var isLast = (i === relativeParts.length - 1);

                if (isLast) {
                    html += '<li class="breadcrumb-item active">' + relativeParts[i] + '</li>';
                } else {
                    html += '<li class="breadcrumb-item"><a href="#" class="local-browser-nav" data-path="' + segmentPath + '">' +
                        relativeParts[i] + '</a></li>';
                }
            }

            html += '</ol></nav>';
            breadcrumb.innerHTML = html;

            // Bind navigation links
            breadcrumb.querySelectorAll('.local-browser-nav').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.loadFiles(link.dataset.path, self.currentFilter);
                });
            });
        },

        /**
         * Render the file/folder grid
         */
        renderGrid: function(folders, files) {
            var grid = document.getElementById('localBrowserGrid');
            if (!grid) {
                return;
            }

            var self = this;

            if ((!folders || !folders.length) && (!files || !files.length)) {
                grid.innerHTML = '<div class="text-center p-4 text-muted">' +
                    '<span class="icon-info-circle" style="font-size:2rem;" aria-hidden="true"></span>' +
                    '<p class="mt-2">' + (Joomla.Text._('JBS_MED_LOCAL_BROWSER_EMPTY') || 'No files found in this directory.') + '</p></div>';
                return;
            }

            var html = '<div class="row g-2">';

            // Parent folder link
            if (this.currentPath) {
                html += '<div class="col-lg-2 col-md-3 col-sm-4 col-6">' +
                    '<div class="card h-100 local-browser-item local-browser-folder" role="button" tabindex="0" data-path-up="true">' +
                    '<div class="card-body text-center p-2">' +
                    '<span class="icon-arrow-up" style="font-size:1.5rem;color:#6c757d;" aria-hidden="true"></span>' +
                    '<p class="card-text small text-truncate mt-1 mb-0">..</p>' +
                    '</div></div></div>';
            }

            // Folders
            if (folders && folders.length) {
                for (var i = 0; i < folders.length; i++) {
                    var folder = folders[i];
                    html += '<div class="col-lg-2 col-md-3 col-sm-4 col-6">' +
                        '<div class="card h-100 local-browser-item local-browser-folder" role="button" tabindex="0" data-folder-path="' + folder.path + '">' +
                        '<div class="card-body text-center p-2">' +
                        '<span class="icon-folder" style="font-size:1.5rem;color:#ffc107;" aria-hidden="true"></span>' +
                        '<p class="card-text small text-truncate mt-1 mb-0" title="' + folder.name + '">' + folder.name + '</p>' +
                        '</div></div></div>';
                }
            }

            // Files
            if (files && files.length) {
                for (var j = 0; j < files.length; j++) {
                    var file = files[j];
                    var icon = self.getFileIcon(file.category);
                    var iconColor = self.getFileIconColor(file.category);
                    var sizeStr = self.formatFileSize(file.size);
                    var extBadge = file.extension ? '<span class="badge bg-light text-dark">' + file.extension.toUpperCase() + '</span>' : '';

                    html += '<div class="col-lg-2 col-md-3 col-sm-4 col-6 local-browser-file-item" data-filename="' + file.name.toLowerCase() + '">' +
                        '<div class="card h-100 local-browser-item local-browser-file" role="button" tabindex="0" ' +
                        'data-file-path="' + file.path + '" data-file-size="' + file.size + '">' +
                        '<div class="card-body text-center p-2">' +
                        '<span class="' + icon + '" style="font-size:1.5rem;color:' + iconColor + ';" aria-hidden="true"></span>' +
                        '<p class="card-text small text-truncate mt-1 mb-0" title="' + file.name + '">' + file.name + '</p>' +
                        '<div class="mt-1">' + extBadge + ' <small class="text-muted">' + sizeStr + '</small></div>' +
                        '</div></div></div>';
                }
            }

            html += '</div>';
            grid.innerHTML = html;

            // Bind folder navigation
            grid.querySelectorAll('.local-browser-folder').forEach(function(card) {
                function navigate() {
                    if (card.dataset.pathUp) {
                        self.navigateUp();
                    } else {
                        self.loadFiles(card.dataset.folderPath, self.currentFilter);
                    }
                }
                card.addEventListener('click', navigate);
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); navigate(); }
                });
            });

            // Bind file selection
            grid.querySelectorAll('.local-browser-file').forEach(function(card) {
                function selectFile() {
                    self.selectFile(card.dataset.filePath, parseInt(card.dataset.fileSize, 10) || 0);
                }
                card.addEventListener('click', selectFile);
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectFile(); }
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
        navigateUp: function() {
            if (!this.currentPath) {
                return;
            }
            var parts = this.currentPath.split('/').filter(function(p) { return p; });
            parts.pop();
            var parentPath = parts.length > 0 ? parts.join('/').replace(this.basePath + '/', '').replace(this.basePath, '') : '';
            this.loadFiles(parentPath, this.currentFilter);
        },

        /**
         * Select a file and populate the filename field
         */
        selectFile: function(filePath, fileSize) {
            // Set the filename field value
            var filenameField = document.getElementById('jform_params_filename');
            if (filenameField) {
                filenameField.value = filePath;
                // Trigger change event for any listeners
                filenameField.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Pre-fill size if available and field is empty/zero
            if (fileSize > 0) {
                var sizeField = document.getElementById('jform_params_size');
                if (sizeField && (!sizeField.value || sizeField.value === '0')) {
                    sizeField.value = fileSize;
                    sizeField.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Close the modal
            if (this.modal) {
                this.modal.hide();
            }
        },

        /**
         * Filter visible files by search term
         */
        filterVisibleFiles: function() {
            var items = document.querySelectorAll('.local-browser-file-item');
            var term = this.searchTerm;

            items.forEach(function(item) {
                var filename = item.dataset.filename || '';
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
        getFileIcon: function(category) {
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
        getFileIconColor: function(category) {
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
        formatFileSize: function(bytes) {
            if (!bytes || bytes === 0) {
                return '0 B';
            }
            var units = ['B', 'KB', 'MB', 'GB'];
            var i = 0;
            var size = bytes;
            while (size >= 1024 && i < units.length - 1) {
                size /= 1024;
                i++;
            }
            return size.toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
        }
    };
})();
