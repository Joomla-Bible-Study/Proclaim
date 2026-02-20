/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description Vimeo Video Browser for Proclaim Media Files
 */
(() => {
    'use strict';

    window.Proclaim = window.Proclaim || {};

    window.Proclaim.VimeoBrowser = {
        modal: null,
        serverId: null,
        currentPage: 1,
        totalPages: 1,
        searchQuery: '',
        selectedFolder: '',
        activeTab: 'browse',
        currentLookupId: null,
        currentLookupUrl: null,

        init() {
            this.createModal();
        },

        createModal() {
            if (document.getElementById('vimeoBrowserModal')) {
                return;
            }

            const modalHtml = '<div class="modal fade" id="vimeoBrowserModal" tabindex="-1" aria-labelledby="vimeoBrowserModalLabel">'
                + '<div class="modal-dialog modal-xl modal-dialog-scrollable">'
                + '<div class="modal-content">'
                + '<div class="modal-header">'
                + '<h5 class="modal-title" id="vimeoBrowserModalLabel"><span class="icon-video" aria-hidden="true"></span> Browse Vimeo Videos</h5>'
                + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
                + '</div>'
                + '<div class="modal-body">'
                // Bootstrap tabs nav
                + '<ul class="nav nav-tabs mb-3" id="vimeoBrowserTabs" role="tablist">'
                + '<li class="nav-item" role="presentation">'
                + '<button class="nav-link active" id="vimeoBrowseTab" data-bs-toggle="tab" data-bs-target="#vimeoBrowsePane" type="button" role="tab" aria-controls="vimeoBrowsePane" aria-selected="true">Browse My Videos</button>'
                + '</li>'
                + '<li class="nav-item" role="presentation">'
                + '<button class="nav-link" id="vimeoLookupTab" data-bs-toggle="tab" data-bs-target="#vimeoLookupPane" type="button" role="tab" aria-controls="vimeoLookupPane" aria-selected="false">Lookup by URL</button>'
                + '</li>'
                + '</ul>'
                // Tab panes
                + '<div class="tab-content" id="vimeoBrowserTabContent">'
                // Browse tab
                + '<div class="tab-pane fade show active" id="vimeoBrowsePane" role="tabpanel" aria-labelledby="vimeoBrowseTab">'
                + '<div class="vimeo-filters-container mb-3">'
                + '<div class="row g-2">'
                + '<div class="col-md-4">'
                + '<select id="vimeoFolderSelect" class="form-select"><option value="">All Videos</option></select>'
                + '</div>'
                + '<div class="col-md-5">'
                + '<div class="input-group">'
                + '<input type="text" id="vimeoSearchInput" class="form-control" placeholder="Search videos..." aria-label="Search videos">'
                + '<button class="btn btn-outline-secondary" type="button" id="vimeoSearchBtn"><span class="icon-search" aria-hidden="true"></span></button>'
                + '</div>'
                + '</div>'
                + '<div class="col-md-3">'
                + '<button class="btn btn-outline-secondary w-100" type="button" id="vimeoResetBtn"><span class="icon-refresh" aria-hidden="true"></span> Reset</button>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '<div id="vimeoNoToken" class="alert alert-warning d-none">'
                + 'No Vimeo access token configured. Please add one in the server settings, or use the <strong>Lookup by URL</strong> tab to paste a URL directly.'
                + '</div>'
                + '<div id="vimeoBrowseError" class="alert alert-danger d-none"></div>'
                + '<div id="vimeoBrowseLoading" class="text-center py-4 d-none">'
                + '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                + '<p class="mt-2">Loading videos...</p>'
                + '</div>'
                + '<div id="vimeoNoResults" class="alert alert-info d-none">No videos found.</div>'
                + '<div id="vimeoVideoGrid" class="row row-cols-1 row-cols-md-3 g-3"></div>'
                + '</div>'
                // Lookup tab
                + '<div class="tab-pane fade" id="vimeoLookupPane" role="tabpanel" aria-labelledby="vimeoLookupTab">'
                + '<p class="text-muted small">Paste a Vimeo URL or video ID to look up the video details and auto-fill the URL field.</p>'
                + '<div class="input-group mb-3">'
                + '<input type="text" id="vimeoLookupInput" class="form-control" placeholder="https://vimeo.com/123456789 or video ID" aria-label="Vimeo URL or ID">'
                + '<button class="btn btn-primary" type="button" id="vimeoLookupBtn"><span class="icon-search" aria-hidden="true"></span> Look Up</button>'
                + '</div>'
                + '<div id="vimeoLookupLoading" class="text-center py-3 d-none">'
                + '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>'
                + ' Fetching video details...'
                + '</div>'
                + '<div id="vimeoLookupError" class="alert alert-danger d-none"></div>'
                + '<div id="vimeoLookupResult" class="card d-none">'
                + '<div class="row g-0">'
                + '<div class="col-md-4">'
                + '<img id="vimeoResultThumbnail" src="" class="img-fluid rounded-start" alt="Video thumbnail">'
                + '</div>'
                + '<div class="col-md-8">'
                + '<div class="card-body">'
                + '<h6 class="card-title" id="vimeoResultTitle"></h6>'
                + '<p class="card-text small text-muted" id="vimeoResultAuthor"></p>'
                + '<p class="card-text small text-muted" id="vimeoResultDuration"></p>'
                + '<button type="button" class="btn btn-primary" id="vimeoUseBtn"><span class="icon-checkmark" aria-hidden="true"></span> Use This Video</button>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                // Footer with pagination
                + '<div class="modal-footer">'
                + '<div id="vimeoPaginationBar" class="me-auto d-flex align-items-center gap-2 d-none">'
                + '<button type="button" class="btn btn-secondary" id="vimeoPrevBtn" disabled><span class="icon-arrow-left" aria-hidden="true"></span> Previous</button>'
                + '<span id="vimeoPaginationInfo" class="text-muted small"></span>'
                + '<button type="button" class="btn btn-secondary" id="vimeoNextBtn" disabled>Next <span class="icon-arrow-right" aria-hidden="true"></span></button>'
                + '</div>'
                + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            const modalEl = document.getElementById('vimeoBrowserModal');
            modalEl.addEventListener('hidden.bs.modal', () => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach((backdrop) => { backdrop.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });

            this.bindEvents();
        },

        bindEvents() {
            const self = this;

            // Browse tab: search button
            const searchBtn = document.getElementById('vimeoSearchBtn');
            if (searchBtn) {
                const newBtn = searchBtn.cloneNode(true);
                searchBtn.parentNode.replaceChild(newBtn, searchBtn);
                newBtn.addEventListener('click', () => {
                    self.searchQuery = document.getElementById('vimeoSearchInput').value;
                    self.currentPage = 1;
                    self.loadVideos();
                });
            }

            // Browse tab: search input enter key
            const searchInput = document.getElementById('vimeoSearchInput');
            if (searchInput) {
                const newInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newInput, searchInput);
                newInput.addEventListener('keypress', function handleKeypress(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.searchQuery = this.value;
                        self.currentPage = 1;
                        self.loadVideos();
                    }
                });
            }

            // Browse tab: folder select
            const folderSelect = document.getElementById('vimeoFolderSelect');
            if (folderSelect) {
                const newSelect = folderSelect.cloneNode(true);
                folderSelect.parentNode.replaceChild(newSelect, folderSelect);
                newSelect.addEventListener('change', function handleFolderChange() {
                    self.selectedFolder = this.value;
                    self.currentPage = 1;
                    self.loadVideos();
                });
            }

            // Browse tab: reset button
            const resetBtn = document.getElementById('vimeoResetBtn');
            if (resetBtn) {
                const newResetBtn = resetBtn.cloneNode(true);
                resetBtn.parentNode.replaceChild(newResetBtn, resetBtn);
                newResetBtn.addEventListener('click', () => {
                    const si = document.getElementById('vimeoSearchInput');
                    const fs = document.getElementById('vimeoFolderSelect');
                    if (si) { si.value = ''; }
                    if (fs) { fs.value = ''; }
                    self.searchQuery = '';
                    self.selectedFolder = '';
                    self.currentPage = 1;
                    self.loadVideos();
                });
            }

            // Pagination: prev
            const prevBtn = document.getElementById('vimeoPrevBtn');
            if (prevBtn) {
                const newPrevBtn = prevBtn.cloneNode(true);
                prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
                newPrevBtn.addEventListener('click', () => {
                    if (self.currentPage > 1) {
                        self.currentPage -= 1;
                        self.loadVideos();
                    }
                });
            }

            // Pagination: next
            const nextBtn = document.getElementById('vimeoNextBtn');
            if (nextBtn) {
                const newNextBtn = nextBtn.cloneNode(true);
                nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
                newNextBtn.addEventListener('click', () => {
                    if (self.currentPage < self.totalPages) {
                        self.currentPage += 1;
                        self.loadVideos();
                    }
                });
            }

            // Lookup tab: look up button
            const lookupBtn = document.getElementById('vimeoLookupBtn');
            if (lookupBtn) {
                const newLookupBtn = lookupBtn.cloneNode(true);
                lookupBtn.parentNode.replaceChild(newLookupBtn, lookupBtn);
                newLookupBtn.addEventListener('click', () => { self.lookupVideo(); });
            }

            // Lookup tab: enter key
            const lookupInput = document.getElementById('vimeoLookupInput');
            if (lookupInput) {
                const newLookupInput = lookupInput.cloneNode(true);
                lookupInput.parentNode.replaceChild(newLookupInput, lookupInput);
                newLookupInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.lookupVideo();
                    }
                });
            }

            // Lookup tab: use this video button
            const useBtn = document.getElementById('vimeoUseBtn');
            if (useBtn) {
                const newUseBtn = useBtn.cloneNode(true);
                useBtn.parentNode.replaceChild(newUseBtn, useBtn);
                newUseBtn.addEventListener('click', () => { self.selectLookupVideo(); });
            }

            // Tab switch: hide pagination bar when switching to Lookup tab
            const lookupTabBtn = document.getElementById('vimeoLookupTab');
            if (lookupTabBtn) {
                lookupTabBtn.addEventListener('shown.bs.tab', () => {
                    self.activeTab = 'lookup';
                    const paginationBar = document.getElementById('vimeoPaginationBar');
                    if (paginationBar) {
                        paginationBar.classList.add('d-none');
                    }
                });
            }

            // Tab switch: show pagination bar on browse tab if we have pages
            const browseTabBtn = document.getElementById('vimeoBrowseTab');
            if (browseTabBtn) {
                browseTabBtn.addEventListener('shown.bs.tab', () => {
                    self.activeTab = 'browse';
                    if (self.totalPages > 1) {
                        const paginationBar = document.getElementById('vimeoPaginationBar');
                        if (paginationBar) {
                            paginationBar.classList.remove('d-none');
                        }
                    }
                });
            }
        },

        open() {
            const serverField = document.querySelector('[name="jform[server_id]"]');
            this.serverId = serverField ? serverField.value : null;

            this.init();

            // Auto-search from study title (same pattern as YouTube)
            const studyTitleField = document.getElementById('jform_study_id_name');
            if (studyTitleField && studyTitleField.value) {
                const studyTitle = studyTitleField.value;
                if (studyTitle.toLowerCase().indexOf('select') === -1) {
                    this.searchQuery = studyTitle;
                    const searchInput = document.getElementById('vimeoSearchInput');
                    if (searchInput) {
                        searchInput.value = studyTitle;
                    }
                }
            }

            // Pre-fill lookup input with existing URL if any
            const filenameField = document.querySelector('[name="jform[params][filename]"]');
            const lookupInput = document.getElementById('vimeoLookupInput');
            if (filenameField && filenameField.value && lookupInput) {
                lookupInput.value = filenameField.value;
            }

            // Reset browse state
            this.currentPage = 1;
            this.activeTab = 'browse';

            // Ensure browse tab is active
            const browseTabBtn = document.getElementById('vimeoBrowseTab');
            if (browseTabBtn && typeof bootstrap !== 'undefined') {
                const tab = new bootstrap.Tab(browseTabBtn);
                tab.show();
            }

            const modalEl = document.getElementById('vimeoBrowserModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                this.modal = new bootstrap.Modal(modalEl);
                this.modal.show();
                this.loadFolders();
                this.loadVideos();
            }
        },

        loadFolders() {
            if (!this.serverId) {
                return;
            }

            const folderSelect = document.getElementById('vimeoFolderSelect');
            if (!folderSelect) {
                return;
            }

            const url = `index.php?option=com_proclaim&task=cwmmediafile.xhr&type=vimeo&handler=fetchFolders&server_id=${encodeURIComponent(this.serverId)}&${Joomla.getOptions('csrf.token')}=1`;

            fetch(url)
                .then((response) => {
                    if (response.status === 403 || response.status === 401) {
                        const expiredMsg = Joomla.Text._('JLIB_ENVIRONMENT_SESSION_EXPIRED') || 'Your session has expired. Please log in again.';
                        Joomla.renderMessages({ error: [expiredMsg] });
                        setTimeout(() => { window.location.reload(); }, 3000);
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success && data.folders && data.folders.length > 0) {
                        // Remove all options except first (All Videos)
                        while (folderSelect.options.length > 1) {
                            folderSelect.remove(1);
                        }
                        data.folders.forEach((folder) => {
                            const option = document.createElement('option');
                            option.value = folder.folderId;
                            option.textContent = folder.title;
                            folderSelect.appendChild(option);
                        });
                    }
                })
                .catch((err) => {
                    console.error('Failed to load Vimeo folders:', err);
                });
        },

        loadVideos() {
            const grid = document.getElementById('vimeoVideoGrid');
            const loading = document.getElementById('vimeoBrowseLoading');
            const error = document.getElementById('vimeoBrowseError');
            const noResults = document.getElementById('vimeoNoResults');
            const noToken = document.getElementById('vimeoNoToken');
            const paginationBar = document.getElementById('vimeoPaginationBar');
            const self = this;

            if (!this.serverId) {
                if (noToken) { noToken.classList.remove('d-none'); }
                if (grid) { grid.innerHTML = ''; }
                return;
            }

            if (grid) { grid.innerHTML = ''; }
            if (loading) { loading.classList.remove('d-none'); }
            if (error) { error.classList.add('d-none'); }
            if (noResults) { noResults.classList.add('d-none'); }
            if (noToken) { noToken.classList.add('d-none'); }

            let url = `index.php?option=com_proclaim&task=cwmmediafile.xhr&type=vimeo&handler=fetchVideos&server_id=${encodeURIComponent(this.serverId)}&page=${this.currentPage}&${Joomla.getOptions('csrf.token')}=1`;

            if (this.searchQuery) {
                url += `&query=${encodeURIComponent(this.searchQuery)}`;
            }
            if (this.selectedFolder) {
                url += `&folder_id=${encodeURIComponent(this.selectedFolder)}`;
            }

            fetch(url, { signal: AbortSignal.timeout(30000) })
                .then((response) => {
                    if (response.status === 403 || response.status === 401) {
                        const expiredMsg = Joomla.Text._('JLIB_ENVIRONMENT_SESSION_EXPIRED') || 'Your session has expired. Please log in again.';
                        Joomla.renderMessages({ error: [expiredMsg] });
                        setTimeout(() => { window.location.reload(); }, 3000);
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then((data) => {
                    if (loading) { loading.classList.add('d-none'); }

                    if (!data.success) {
                        // Show no-token notice for missing access token
                        if (data.error === 'no access_token') {
                            if (noToken) { noToken.classList.remove('d-none'); }
                        } else if (error) {
                            error.textContent = data.error || 'Failed to load videos.';
                            error.classList.remove('d-none');
                        }
                        if (paginationBar) { paginationBar.classList.add('d-none'); }
                        return;
                    }

                    if (!data.videos || data.videos.length === 0) {
                        if (noResults) { noResults.classList.remove('d-none'); }
                        if (paginationBar) { paginationBar.classList.add('d-none'); }
                        return;
                    }

                    self.totalPages = data.totalPages || 1;
                    self.renderVideos(data.videos);

                    // Update pagination controls
                    const prevBtn = document.getElementById('vimeoPrevBtn');
                    const nextBtn = document.getElementById('vimeoNextBtn');
                    const infoEl = document.getElementById('vimeoPaginationInfo');

                    if (prevBtn) { prevBtn.disabled = self.currentPage <= 1; }
                    if (nextBtn) { nextBtn.disabled = self.currentPage >= self.totalPages; }
                    if (infoEl) {
                        infoEl.textContent = `Page ${self.currentPage} of ${self.totalPages} (${data.total} videos)`;
                    }

                    // Show pagination bar only when on browse tab
                    if (paginationBar && self.activeTab === 'browse') {
                        paginationBar.classList.remove('d-none');
                    }
                })
                .catch((err) => {
                    if (loading) { loading.classList.add('d-none'); }
                    if (error) {
                        error.textContent = `Error: ${err.message}`;
                        error.classList.remove('d-none');
                    }
                });
        },

        renderVideos(videos) {
            const grid = document.getElementById('vimeoVideoGrid');
            const self = this;

            videos.forEach((video) => {
                const card = document.createElement('div');
                card.className = 'col';

                const thumbHtml = video.thumbnail
                    ? `<img src="${self.escapeAttr(video.thumbnail)}" class="card-img-top" style="aspect-ratio:16/9;object-fit:cover" alt="${self.escapeAttr(video.title)}">`
                    : `<div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="aspect-ratio:16/9"><span class="icon-video text-white" style="font-size:2rem" aria-hidden="true"></span></div>`;

                card.innerHTML = `<div class="card h-100 vimeo-video-card" data-video-link="${self.escapeAttr(video.link)}" data-title="${self.escapeAttr(video.title)}">`
                    + '<div class="position-relative">'
                    + thumbHtml
                    + '</div>'
                    + '<div class="card-body p-2">'
                    + `<h6 class="card-title small mb-1">${self.escapeHtml(video.title)}</h6>`
                    + `<p class="card-text small text-muted mb-0">${self.formatDuration(video.duration)}</p>`
                    + '</div>'
                    + '<div class="card-footer p-2">'
                    + '<button type="button" class="btn btn-primary btn-sm w-100 select-video-btn"><span class="icon-checkmark" aria-hidden="true"></span> Select</button>'
                    + '</div>'
                    + '</div>';

                const selectBtn = card.querySelector('.select-video-btn');
                selectBtn.addEventListener('click', function selectVideoHandler() {
                    const cardEl = this.closest('.vimeo-video-card');
                    self.selectVideo(cardEl.dataset.videoLink, cardEl.dataset.title);
                });

                grid.appendChild(card);
            });
        },

        selectVideo(link, title) {
            const filenameField = document.querySelector('[name="jform[params][filename]"]');
            if (filenameField) {
                filenameField.value = link;
            }

            if (this.modal) {
                this.modal.hide();
            }

            if (Joomla.renderMessages) {
                Joomla.renderMessages({
                    success: [`Video "${title}" selected.`],
                });
            }
        },

        lookupVideo() {
            const input = document.getElementById('vimeoLookupInput');
            if (!input || !input.value.trim()) {
                return;
            }

            const query = input.value.trim();
            const videoId = this.extractVideoId(query);
            const errorEl = document.getElementById('vimeoLookupError');
            const loading = document.getElementById('vimeoLookupLoading');
            const result = document.getElementById('vimeoLookupResult');
            const self = this;

            if (!videoId) {
                if (errorEl) {
                    errorEl.textContent = 'Could not extract a Vimeo video ID from that input. Please paste a full Vimeo URL (e.g. https://vimeo.com/123456789) or a numeric video ID.';
                    errorEl.classList.remove('d-none');
                }
                if (result) { result.classList.add('d-none'); }
                return;
            }

            if (errorEl) { errorEl.classList.add('d-none'); }
            if (result) { result.classList.add('d-none'); }
            if (loading) { loading.classList.remove('d-none'); }

            const url = `index.php?option=com_proclaim&task=cwmmediafile.xhr&type=vimeo&handler=getMetadata&video_id=${encodeURIComponent(videoId)}&${Joomla.getOptions('csrf.token')}=1`;

            fetch(url)
                .then((response) => {
                    if (response.status === 403 || response.status === 401) {
                        const expiredMsg = Joomla.Text._('JLIB_ENVIRONMENT_SESSION_EXPIRED') || 'Your session has expired. Please log in again.';
                        Joomla.renderMessages({ error: [expiredMsg] });
                        setTimeout(() => { window.location.reload(); }, 3000);
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then((data) => {
                    if (loading) { loading.classList.add('d-none'); }

                    if (!data.success || !data.metadata) {
                        if (errorEl) {
                            errorEl.textContent = data.error || 'Failed to load video details. The video may be private or the ID may be incorrect.';
                            errorEl.classList.remove('d-none');
                        }
                        return;
                    }

                    self.currentLookupId = videoId;
                    self.currentLookupUrl = `https://vimeo.com/${videoId}`;
                    self.showLookupResult(data.metadata);
                })
                .catch((err) => {
                    if (loading) { loading.classList.add('d-none'); }
                    if (errorEl) {
                        errorEl.textContent = `Error: ${err.message}`;
                        errorEl.classList.remove('d-none');
                    }
                });
        },

        showLookupResult(metadata) {
            const result = document.getElementById('vimeoLookupResult');
            const title = document.getElementById('vimeoResultTitle');
            const thumbnail = document.getElementById('vimeoResultThumbnail');
            const author = document.getElementById('vimeoResultAuthor');
            const duration = document.getElementById('vimeoResultDuration');

            if (title) { title.textContent = metadata.title || 'Untitled'; }
            if (thumbnail && metadata.thumbnail) {
                thumbnail.src = metadata.thumbnail;
                thumbnail.alt = metadata.title || 'Video thumbnail';
            }
            if (author && metadata.author) { author.textContent = `By: ${metadata.author}`; }
            if (duration && metadata.duration) { duration.textContent = `Duration: ${this.formatDuration(metadata.duration)}`; }

            if (result) { result.classList.remove('d-none'); }
        },

        selectLookupVideo() {
            if (!this.currentLookupUrl) {
                return;
            }

            const filenameField = document.querySelector('[name="jform[params][filename]"]');
            if (filenameField) {
                filenameField.value = this.currentLookupUrl;
            }

            const title = document.getElementById('vimeoResultTitle');
            const videoTitle = (title && title.textContent) ? title.textContent : this.currentLookupUrl;

            if (this.modal) {
                this.modal.hide();
            }

            if (Joomla.renderMessages) {
                Joomla.renderMessages({
                    success: [`Video "${videoTitle}" selected.`],
                });
            }
        },

        extractVideoId(input) {
            // Numeric ID
            if (/^\d+$/.test(input)) {
                return input;
            }

            // Standard: vimeo.com/123456789
            let match = input.match(/vimeo\.com\/(\d+)/);
            if (match) { return match[1]; }

            // Player: player.vimeo.com/video/123456789
            match = input.match(/player\.vimeo\.com\/video\/(\d+)/);
            if (match) { return match[1]; }

            // Channels: vimeo.com/channels/name/123456789
            match = input.match(/vimeo\.com\/channels\/[^/]+\/(\d+)/);
            if (match) { return match[1]; }

            // Groups: vimeo.com/groups/name/videos/123456789
            match = input.match(/vimeo\.com\/groups\/[^/]+\/videos\/(\d+)/);
            if (match) { return match[1]; }

            return null;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        },

        escapeAttr(text) {
            return (text || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        },

        formatDuration(seconds) {
            if (!seconds) { return ''; }
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            if (hrs > 0) {
                return `${hrs}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }
            return `${mins}:${String(secs).padStart(2, '0')}`;
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        const browseBtn = document.getElementById('vimeo-browse-btn');
        if (browseBtn) {
            const newBrowseBtn = browseBtn.cloneNode(true);
            browseBtn.parentNode.replaceChild(newBrowseBtn, browseBtn);
            newBrowseBtn.addEventListener('click', () => {
                Proclaim.VimeoBrowser.open();
            });
        }
    });
})();
