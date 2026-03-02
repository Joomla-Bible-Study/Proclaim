/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description YouTube Video Browser for Proclaim Media Files
 */
(() => {
    'use strict';

    window.Proclaim = window.Proclaim || {};

    window.Proclaim.YoutubeBrowser = {
        modal: null,
        serverId: null,
        currentPageToken: '',
        searchQuery: '',
        selectedPlaylist: '',
        filterType: 'all', // all, live, upcoming, completed
        playlists: [],
        processedVideoIds: new Set(), // Track processed video IDs to prevent duplicates
        scopeChannel: true,
        phraseMatch: false,

        init() {
            this.createModal();
            this.bindEvents();
        },

        createModal() {
            if (document.getElementById('youtubeBrowserModal')) {
                return;
            }

            const modalHtml = '<div class="modal fade" id="youtubeBrowserModal" tabindex="-1" aria-labelledby="youtubeBrowserModalLabel">'
                + '<div class="modal-dialog modal-xl modal-dialog-scrollable">'
                + '<div class="modal-content">'
                + '<div class="modal-header">'
                + '<h5 class="modal-title" id="youtubeBrowserModalLabel"><span class="icon-youtube"></span> Browse YouTube Videos</h5>'
                + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
                + '</div>'
                + '<div class="modal-body">'
                + '<div class="youtube-filters-container mb-3">'
                + '<div class="row g-2">'
                + '<div class="col-md-4">'
                + '<select id="youtubeFilterType" class="form-select">'
                + '<option value="all">All Videos</option>'
                + '<option value="live">Live Now</option>'
                + '<option value="upcoming">Upcoming Live</option>'
                + '<option value="completed">Past Live Streams</option>'
                + '</select>'
                + '</div>'
                + '<div class="col-md-4">'
                + '<select id="youtubePlaylistSelect" class="form-select">'
                + '<option value="">All Uploads</option>'
                + '</select>'
                + '</div>'
                + '<div class="col-md-4">'
                + '<div class="input-group">'
                + '<input type="text" id="youtubeSearchInput" class="form-control" placeholder="Search videos..." aria-label="Search videos">'
                + '<button class="btn btn-outline-secondary" type="button" id="youtubeSearchBtn"><span class="icon-search"></span></button>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '<div class="mt-2 d-flex align-items-center gap-3">'
                + '<button class="btn btn-sm btn-outline-secondary" type="button" id="youtubeResetBtn"><span class="icon-refresh"></span> Reset Filters</button>'
                + '<div class="form-check form-check-inline mb-0">'
                + '<input class="form-check-input" type="checkbox" id="youtubeScopeChannel" checked>'
                + '<label class="form-check-label" for="youtubeScopeChannel">Channel Only</label>'
                + '</div>'
                + '<div class="form-check form-check-inline mb-0">'
                + '<input class="form-check-input" type="checkbox" id="youtubePhraseMatch">'
                + '<label class="form-check-label" for="youtubePhraseMatch">Exact Phrase</label>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '<div id="youtubeVideoGrid" class="row row-cols-1 row-cols-md-3 g-4"></div>'
                + '<div id="youtubeLoading" class="text-center py-4" style="display: none;">'
                + '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                + '<p class="mt-2">Loading videos...</p>'
                + '</div>'
                + '<div id="youtubeError" class="alert alert-danger" style="display: none;"></div>'
                + '<div id="youtubeNoResults" class="alert alert-info" style="display: none;">No videos found.</div>'
                + '</div>'
                + '<div class="modal-footer">'
                + '<div class="youtube-pagination me-auto">'
                + '<button type="button" class="btn btn-secondary" id="youtubePrevBtn" disabled><span class="icon-arrow-left"></span> Previous</button>'
                + '<button type="button" class="btn btn-secondary" id="youtubeNextBtn" disabled>Next <span class="icon-arrow-right"></span></button>'
                + '</div>'
                + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';

            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Add event listener for modal hidden event to clean up backdrop
            const modalEl = document.getElementById('youtubeBrowserModal');
            modalEl.addEventListener('hidden.bs.modal', () => {
                // Ensure backdrop is removed
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach((backdrop) => {
                    backdrop.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        },

        bindEvents() {
            const self = this;

            const searchBtn = document.getElementById('youtubeSearchBtn');
            if (searchBtn) {
                // Remove existing listeners to prevent duplicates (though cloning is safer)
                const newSearchBtn = searchBtn.cloneNode(true);
                searchBtn.parentNode.replaceChild(newSearchBtn, searchBtn);

                newSearchBtn.addEventListener('click', () => {
                    self.searchQuery = document.getElementById('youtubeSearchInput').value;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            const searchInput = document.getElementById('youtubeSearchInput');
            if (searchInput) {
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);

                newSearchInput.addEventListener('keypress', function handleKeypress(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.searchQuery = this.value;
                        self.currentPageToken = '';
                        self.loadVideos();
                    }
                });
            }

            // Filter type change
            const filterType = document.getElementById('youtubeFilterType');
            if (filterType) {
                const newFilterType = filterType.cloneNode(true);
                filterType.parentNode.replaceChild(newFilterType, filterType);

                newFilterType.addEventListener('change', function handleChange() {
                    self.filterType = this.value;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            // Playlist selection change
            const playlistSelect = document.getElementById('youtubePlaylistSelect');
            if (playlistSelect) {
                const newPlaylistSelect = playlistSelect.cloneNode(true);
                playlistSelect.parentNode.replaceChild(newPlaylistSelect, playlistSelect);

                newPlaylistSelect.addEventListener('change', function handlePlaylistChange() {
                    self.selectedPlaylist = this.value;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            // Scope channel checkbox
            const scopeChannel = document.getElementById('youtubeScopeChannel');
            if (scopeChannel) {
                const newScopeChannel = scopeChannel.cloneNode(true);
                scopeChannel.parentNode.replaceChild(newScopeChannel, scopeChannel);

                newScopeChannel.addEventListener('change', function handleScopeChange() {
                    self.scopeChannel = this.checked;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            // Phrase match checkbox
            const phraseMatch = document.getElementById('youtubePhraseMatch');
            if (phraseMatch) {
                const newPhraseMatch = phraseMatch.cloneNode(true);
                phraseMatch.parentNode.replaceChild(newPhraseMatch, phraseMatch);

                newPhraseMatch.addEventListener('change', function handlePhraseChange() {
                    self.phraseMatch = this.checked;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            const resetBtn = document.getElementById('youtubeResetBtn');
            if (resetBtn) {
                const newResetBtn = resetBtn.cloneNode(true);
                resetBtn.parentNode.replaceChild(newResetBtn, resetBtn);

                newResetBtn.addEventListener('click', () => {
                    document.getElementById('youtubeSearchInput').value = '';
                    document.getElementById('youtubeFilterType').value = 'all';
                    document.getElementById('youtubePlaylistSelect').value = '';
                    document.getElementById('youtubeScopeChannel').checked = true;
                    document.getElementById('youtubePhraseMatch').checked = false;
                    self.searchQuery = '';
                    self.filterType = 'all';
                    self.selectedPlaylist = '';
                    self.scopeChannel = true;
                    self.phraseMatch = false;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            const prevBtn = document.getElementById('youtubePrevBtn');
            if (prevBtn) {
                const newPrevBtn = prevBtn.cloneNode(true);
                prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);

                newPrevBtn.addEventListener('click', function handlePrevClick() {
                    if (this.dataset.pageToken) {
                        self.currentPageToken = this.dataset.pageToken;
                        self.loadVideos();
                    }
                });
            }

            const nextBtn = document.getElementById('youtubeNextBtn');
            if (nextBtn) {
                const newNextBtn = nextBtn.cloneNode(true);
                nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);

                newNextBtn.addEventListener('click', function () {
                    if (this.dataset.pageToken) {
                        self.currentPageToken = this.dataset.pageToken;
                        self.loadVideos();
                    }
                });
            }
        },

        open() {
            const serverField = document.querySelector('[name="jform[server_id]"]');
            if (serverField) {
                this.serverId = serverField.value;
            }

            if (!this.serverId) {
                alert('Please select a server first.');
                return;
            }

            this.init();

            // Get study title for auto-search
            const studyTitleField = document.getElementById('jform_study_id_name');
            let studyTitle = '';
            if (studyTitleField && studyTitleField.value) {
                studyTitle = studyTitleField.value;
                // Skip if it's the placeholder text
                if (studyTitle.indexOf('Select') === -1 && studyTitle.indexOf('select') === -1) {
                    this.searchQuery = studyTitle;
                    const searchInput = document.getElementById('youtubeSearchInput');
                    if (searchInput) {
                        searchInput.value = studyTitle;
                    }
                }
            }

            const modalEl = document.getElementById('youtubeBrowserModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                this.modal = new bootstrap.Modal(modalEl);
                this.modal.show();
                this.loadPlaylists();
                this.loadVideos();
            }
        },

        loadPlaylists() {
            const self = this;
            const playlistSelect = document.getElementById('youtubePlaylistSelect');

            if (!playlistSelect) {
                return;
            }

            let url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=youtube&handler=fetchChannelPlaylists';
            url += `&server_id=${this.serverId}`;
            url += `&${Joomla.getOptions('csrf.token')}=1`;

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
                    if (data.success && data.playlists) {
                        self.playlists = data.playlists;
                        // Clear existing options except first
                        while (playlistSelect.options.length > 1) {
                            playlistSelect.remove(1);
                        }
                        // Add playlist options
                        data.playlists.forEach((playlist) => {
                            const option = document.createElement('option');
                            option.value = playlist.playlistId;
                            option.textContent = playlist.title;
                            playlistSelect.appendChild(option);
                        });
                    }
                })
                .catch((err) => {
                    console.error('Failed to load playlists:', err);
                });
        },

        loadVideos() {
            const self = this;
            const grid = document.getElementById('youtubeVideoGrid');
            const loading = document.getElementById('youtubeLoading');
            const error = document.getElementById('youtubeError');
            const noResults = document.getElementById('youtubeNoResults');
            const prevBtn = document.getElementById('youtubePrevBtn');
            const nextBtn = document.getElementById('youtubeNextBtn');

            grid.innerHTML = '';
            loading.style.display = 'block';
            error.style.display = 'none';
            noResults.style.display = 'none';

            // Reset processed video IDs for new search
            this.processedVideoIds.clear();

            // Determine which handler to use based on filters
            let handler;
            if (this.searchQuery) {
                handler = 'searchChannelVideos';
            } else if (this.filterType !== 'all') {
                handler = 'fetchLiveVideos';
            } else if (this.selectedPlaylist) {
                handler = 'fetchPlaylistVideos';
            } else {
                handler = 'fetchChannelVideos';
            }

            let url = `index.php?option=com_proclaim&task=cwmmediafile.xhr&type=youtube&handler=${handler}`;
            url += `&server_id=${this.serverId}`;
            url += `&${Joomla.getOptions('csrf.token')}=1`;

            if (this.currentPageToken) {
                url += `&page_token=${encodeURIComponent(this.currentPageToken)}`;
            }

            if (this.searchQuery) {
                url += `&query=${encodeURIComponent(this.searchQuery)}`;
                url += `&scope_channel=${this.scopeChannel ? 1 : 0}`;
                url += `&phrase_match=${this.phraseMatch ? 1 : 0}`;
            }

            // Add filter type for live videos
            if (this.filterType !== 'all') {
                url += `&event_type=${encodeURIComponent(this.filterType)}`;
            }

            // Add playlist ID if selected
            if (this.selectedPlaylist && handler === 'fetchPlaylistVideos') {
                url += `&playlist_id=${encodeURIComponent(this.selectedPlaylist)}`;
            }

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
                    loading.style.display = 'none';

                    if (!data.success) {
                        error.textContent = data.error || 'Failed to load videos';
                        error.style.display = 'block';
                        return;
                    }

                    if (!data.videos || data.videos.length === 0) {
                        noResults.style.display = 'block';
                        return;
                    }

                    self.renderVideos(data.videos);

                    prevBtn.disabled = !data.prevPageToken;
                    prevBtn.dataset.pageToken = data.prevPageToken || '';
                    nextBtn.disabled = !data.nextPageToken;
                    nextBtn.dataset.pageToken = data.nextPageToken || '';
                })
                .catch((err) => {
                    loading.style.display = 'none';
                    error.textContent = `Error: ${err.message}`;
                    error.style.display = 'block';
                });
        },

        renderVideos(videos) {
            const grid = document.getElementById('youtubeVideoGrid');
            const self = this;

            videos.forEach((video) => {
                // Check for duplicates
                if (self.processedVideoIds.has(video.videoId)) {
                    return;
                }
                self.processedVideoIds.add(video.videoId);

                const card = document.createElement('div');
                card.className = 'col';

                // Build live badge HTML if present
                let badgeHtml = '';
                if (video.liveBadge) {
                    const badgeClass = video.liveBadge === 'LIVE' ? 'bg-danger' : 'bg-warning text-dark';
                    badgeHtml = `<span class="position-absolute top-0 start-0 badge ${badgeClass} m-2">${video.liveBadge}</span>`;
                }

                card.innerHTML = `<div class="card h-100 youtube-video-card" data-video-id="${video.videoId}" data-title="${self.escapeHtml(video.title)}" data-thumbnail="${video.thumbnail}">`
                    + '<div class="position-relative">'
                    + `<img src="${video.thumbnail}" class="card-img-top" alt="${self.escapeHtml(video.title)}">${
                        badgeHtml
                    }</div>`
                    + '<div class="card-body">'
                    + `<h6 class="card-title">${self.escapeHtml(video.title)}</h6>`
                    + `<p class="card-text small text-muted">${self.formatDate(video.publishedAt)}</p>`
                    + '</div>'
                    + '<div class="card-footer">'
                    + '<button type="button" class="btn btn-primary btn-sm w-100 select-video-btn"><span class="icon-checkmark"></span> Select</button>'
                    + '</div>'
                    + '</div>';

                const selectBtn = card.querySelector('.select-video-btn');
                selectBtn.addEventListener('click', function () {
                    const cardEl = this.closest('.youtube-video-card');
                    self.selectVideo(
                        cardEl.dataset.videoId,
                        cardEl.dataset.title,
                        cardEl.dataset.thumbnail,
                    );
                });

                grid.appendChild(card);
            });
        },

        selectVideo(videoId, title, thumbnail) {
            void thumbnail; // Parameter kept for API consistency with callers
            const filenameField = document.querySelector('[name="jform[params][filename]"]');
            if (filenameField) {
                filenameField.value = `https://www.youtube.com/watch?v=${videoId}`;
            }

            if (this.modal) {
                this.modal.hide();
            }

            if (Joomla.renderMessages) {
                Joomla.renderMessages({
                    success: [`Video "${title}" selected successfully.`],
                });
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        formatDate(dateString) {
            if (!dateString) {
                return '';
            }
            const date = new Date(dateString);
            return date.toLocaleDateString();
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        const browseBtn = document.getElementById('youtube-browse-btn');
        if (browseBtn) {
            // Use cloneNode to remove existing listeners to prevent duplicates
            const newBrowseBtn = browseBtn.cloneNode(true);
            browseBtn.parentNode.replaceChild(newBrowseBtn, browseBtn);

            newBrowseBtn.addEventListener('click', () => {
                Proclaim.YoutubeBrowser.open();
            });
        }
    });
})();
