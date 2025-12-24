/**
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description YouTube Video Browser for Proclaim Media Files
 */
(function() {
    'use strict';

    window.Proclaim = window.Proclaim || {};

    window.Proclaim.YoutubeBrowser = {
        modal: null,
        serverId: null,
        currentPageToken: '',
        searchQuery: '',

        init: function() {
            this.createModal();
            this.bindEvents();
        },

        createModal: function() {
            if (document.getElementById('youtubeBrowserModal')) {
                return;
            }

            var modalHtml = '<div class="modal fade" id="youtubeBrowserModal" tabindex="-1" aria-labelledby="youtubeBrowserModalLabel" aria-hidden="true">' +
                '<div class="modal-dialog modal-xl modal-dialog-scrollable">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h5 class="modal-title" id="youtubeBrowserModalLabel"><span class="icon-youtube"></span> Browse YouTube Videos</h5>' +
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
                '</div>' +
                '<div class="modal-body">' +
                '<div class="youtube-search-container mb-3">' +
                '<div class="input-group">' +
                '<input type="text" id="youtubeSearchInput" class="form-control" placeholder="Search videos..." aria-label="Search videos">' +
                '<button class="btn btn-outline-secondary" type="button" id="youtubeSearchBtn"><span class="icon-search"></span> Search</button>' +
                '<button class="btn btn-outline-secondary" type="button" id="youtubeResetBtn"><span class="icon-refresh"></span> Reset</button>' +
                '</div>' +
                '</div>' +
                '<div id="youtubeVideoGrid" class="row row-cols-1 row-cols-md-3 g-4"></div>' +
                '<div id="youtubeLoading" class="text-center py-4" style="display: none;">' +
                '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>' +
                '<p class="mt-2">Loading videos...</p>' +
                '</div>' +
                '<div id="youtubeError" class="alert alert-danger" style="display: none;"></div>' +
                '<div id="youtubeNoResults" class="alert alert-info" style="display: none;">No videos found.</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<div class="youtube-pagination me-auto">' +
                '<button type="button" class="btn btn-secondary" id="youtubePrevBtn" disabled><span class="icon-arrow-left"></span> Previous</button>' +
                '<button type="button" class="btn btn-secondary" id="youtubeNextBtn" disabled>Next <span class="icon-arrow-right"></span></button>' +
                '</div>' +
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            document.body.insertAdjacentHTML('beforeend', modalHtml);
        },

        bindEvents: function() {
            var self = this;

            var searchBtn = document.getElementById('youtubeSearchBtn');
            if (searchBtn) {
                searchBtn.addEventListener('click', function() {
                    self.searchQuery = document.getElementById('youtubeSearchInput').value;
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            var searchInput = document.getElementById('youtubeSearchInput');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.searchQuery = this.value;
                        self.currentPageToken = '';
                        self.loadVideos();
                    }
                });
            }

            var resetBtn = document.getElementById('youtubeResetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    document.getElementById('youtubeSearchInput').value = '';
                    self.searchQuery = '';
                    self.currentPageToken = '';
                    self.loadVideos();
                });
            }

            var prevBtn = document.getElementById('youtubePrevBtn');
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    if (this.dataset.pageToken) {
                        self.currentPageToken = this.dataset.pageToken;
                        self.loadVideos();
                    }
                });
            }

            var nextBtn = document.getElementById('youtubeNextBtn');
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    if (this.dataset.pageToken) {
                        self.currentPageToken = this.dataset.pageToken;
                        self.loadVideos();
                    }
                });
            }
        },

        open: function() {
            var serverField = document.querySelector('[name="jform[server_id]"]');
            if (serverField) {
                this.serverId = serverField.value;
            }

            if (!this.serverId) {
                alert('Please select a server first.');
                return;
            }

            this.init();

            // Get study title for auto-search
            var studyTitleField = document.getElementById('jform_study_id_name');
            var studyTitle = '';
            if (studyTitleField && studyTitleField.value) {
                studyTitle = studyTitleField.value;
                // Skip if it's the placeholder text
                if (studyTitle.indexOf('Select') === -1 && studyTitle.indexOf('select') === -1) {
                    this.searchQuery = studyTitle;
                    var searchInput = document.getElementById('youtubeSearchInput');
                    if (searchInput) {
                        searchInput.value = studyTitle;
                    }
                }
            }

            var modalEl = document.getElementById('youtubeBrowserModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                this.modal = new bootstrap.Modal(modalEl);
                this.modal.show();
                this.loadVideos();
            }
        },

        loadVideos: function() {
            var self = this;
            var grid = document.getElementById('youtubeVideoGrid');
            var loading = document.getElementById('youtubeLoading');
            var error = document.getElementById('youtubeError');
            var noResults = document.getElementById('youtubeNoResults');
            var prevBtn = document.getElementById('youtubePrevBtn');
            var nextBtn = document.getElementById('youtubeNextBtn');

            grid.innerHTML = '';
            loading.style.display = 'block';
            error.style.display = 'none';
            noResults.style.display = 'none';

            var handler = this.searchQuery ? 'searchChannelVideos' : 'fetchChannelVideos';
            var url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=youtube&handler=' + handler;
            url += '&server_id=' + this.serverId;
            url += '&' + Joomla.getOptions('csrf.token') + '=1';

            if (this.currentPageToken) {
                url += '&page_token=' + encodeURIComponent(this.currentPageToken);
            }

            if (this.searchQuery) {
                url += '&query=' + encodeURIComponent(this.searchQuery);
            }

            fetch(url)
                .then(function(response) { return response.json(); })
                .then(function(data) {
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
                .catch(function(err) {
                    loading.style.display = 'none';
                    error.textContent = 'Error: ' + err.message;
                    error.style.display = 'block';
                });
        },

        renderVideos: function(videos) {
            var grid = document.getElementById('youtubeVideoGrid');
            var self = this;

            videos.forEach(function(video) {
                var card = document.createElement('div');
                card.className = 'col';
                card.innerHTML = '<div class="card h-100 youtube-video-card" data-video-id="' + video.videoId + '" data-title="' + self.escapeHtml(video.title) + '" data-thumbnail="' + video.thumbnail + '">' +
                    '<img src="' + video.thumbnail + '" class="card-img-top" alt="' + self.escapeHtml(video.title) + '">' +
                    '<div class="card-body">' +
                    '<h6 class="card-title">' + self.escapeHtml(video.title) + '</h6>' +
                    '<p class="card-text small text-muted">' + self.formatDate(video.publishedAt) + '</p>' +
                    '</div>' +
                    '<div class="card-footer">' +
                    '<button type="button" class="btn btn-primary btn-sm w-100 select-video-btn"><span class="icon-checkmark"></span> Select</button>' +
                    '</div>' +
                    '</div>';

                var selectBtn = card.querySelector('.select-video-btn');
                selectBtn.addEventListener('click', function() {
                    var cardEl = this.closest('.youtube-video-card');
                    self.selectVideo(
                        cardEl.dataset.videoId,
                        cardEl.dataset.title,
                        cardEl.dataset.thumbnail
                    );
                });

                grid.appendChild(card);
            });
        },

        selectVideo: function(videoId, title, thumbnail) {
            var filenameField = document.querySelector('[name="jform[params][filename]"]');
            if (filenameField) {
                filenameField.value = 'https://www.youtube.com/watch?v=' + videoId;
            }

            if (this.modal) {
                this.modal.hide();
            }

            if (Joomla.renderMessages) {
                Joomla.renderMessages({
                    'success': ['Video "' + title + '" selected successfully.']
                });
            }
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        formatDate: function(dateString) {
            if (!dateString) {
                return '';
            }
            var date = new Date(dateString);
            return date.toLocaleDateString();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('youtube-browse-btn')) {
            Proclaim.YoutubeBrowser.init();
        }
    });
})();
