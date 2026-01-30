(function () {
    'use strict';

    /**
     * @copyright  (C) 2026 CWM Team All rights reserved
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
     * @description YouTube Video Browser for Proclaim Media Files
     */
    (function () {

      window.Proclaim = window.Proclaim || {};
      window.Proclaim.YoutubeBrowser = {
        modal: null,
        serverId: null,
        currentPageToken: '',
        searchQuery: '',
        selectedPlaylist: '',
        filterType: 'all',
        playlists: [],
        init: function init() {
          this.createModal();
          this.bindEvents();
        },
        createModal: function createModal() {
          if (document.getElementById('youtubeBrowserModal')) {
            return;
          }
          var modalHtml = '<div class="modal fade" id="youtubeBrowserModal" tabindex="-1" aria-labelledby="youtubeBrowserModalLabel" aria-hidden="true">' + '<div class="modal-dialog modal-xl modal-dialog-scrollable">' + '<div class="modal-content">' + '<div class="modal-header">' + '<h5 class="modal-title" id="youtubeBrowserModalLabel"><span class="icon-youtube"></span> Browse YouTube Videos</h5>' + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' + '</div>' + '<div class="modal-body">' + '<div class="youtube-filters-container mb-3">' + '<div class="row g-2">' + '<div class="col-md-4">' + '<select id="youtubeFilterType" class="form-select">' + '<option value="all">All Videos</option>' + '<option value="live">Live Now</option>' + '<option value="upcoming">Upcoming Live</option>' + '<option value="completed">Past Live Streams</option>' + '</select>' + '</div>' + '<div class="col-md-4">' + '<select id="youtubePlaylistSelect" class="form-select">' + '<option value="">All Uploads</option>' + '</select>' + '</div>' + '<div class="col-md-4">' + '<div class="input-group">' + '<input type="text" id="youtubeSearchInput" class="form-control" placeholder="Search videos..." aria-label="Search videos">' + '<button class="btn btn-outline-secondary" type="button" id="youtubeSearchBtn"><span class="icon-search"></span></button>' + '</div>' + '</div>' + '</div>' + '<div class="mt-2">' + '<button class="btn btn-sm btn-outline-secondary" type="button" id="youtubeResetBtn"><span class="icon-refresh"></span> Reset Filters</button>' + '</div>' + '</div>' + '<div id="youtubeVideoGrid" class="row row-cols-1 row-cols-md-3 g-4"></div>' + '<div id="youtubeLoading" class="text-center py-4" style="display: none;">' + '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>' + '<p class="mt-2">Loading videos...</p>' + '</div>' + '<div id="youtubeError" class="alert alert-danger" style="display: none;"></div>' + '<div id="youtubeNoResults" class="alert alert-info" style="display: none;">No videos found.</div>' + '</div>' + '<div class="modal-footer">' + '<div class="youtube-pagination me-auto">' + '<button type="button" class="btn btn-secondary" id="youtubePrevBtn" disabled><span class="icon-arrow-left"></span> Previous</button>' + '<button type="button" class="btn btn-secondary" id="youtubeNextBtn" disabled>Next <span class="icon-arrow-right"></span></button>' + '</div>' + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' + '</div>' + '</div>' + '</div>' + '</div>';
          document.body.insertAdjacentHTML('beforeend', modalHtml);
        },
        bindEvents: function bindEvents() {
          var self = this;
          var searchBtn = document.getElementById('youtubeSearchBtn');
          if (searchBtn) {
            searchBtn.addEventListener('click', function () {
              self.searchQuery = document.getElementById('youtubeSearchInput').value;
              self.currentPageToken = '';
              self.loadVideos();
            });
          }
          var searchInput = document.getElementById('youtubeSearchInput');
          if (searchInput) {
            searchInput.addEventListener('keypress', function (e) {
              if (e.key === 'Enter') {
                e.preventDefault();
                self.searchQuery = this.value;
                self.currentPageToken = '';
                self.loadVideos();
              }
            });
          }
          var filterType = document.getElementById('youtubeFilterType');
          if (filterType) {
            filterType.addEventListener('change', function () {
              self.filterType = this.value;
              self.currentPageToken = '';
              self.loadVideos();
            });
          }
          var playlistSelect = document.getElementById('youtubePlaylistSelect');
          if (playlistSelect) {
            playlistSelect.addEventListener('change', function () {
              self.selectedPlaylist = this.value;
              self.currentPageToken = '';
              self.loadVideos();
            });
          }
          var resetBtn = document.getElementById('youtubeResetBtn');
          if (resetBtn) {
            resetBtn.addEventListener('click', function () {
              document.getElementById('youtubeSearchInput').value = '';
              document.getElementById('youtubeFilterType').value = 'all';
              document.getElementById('youtubePlaylistSelect').value = '';
              self.searchQuery = '';
              self.filterType = 'all';
              self.selectedPlaylist = '';
              self.currentPageToken = '';
              self.loadVideos();
            });
          }
          var prevBtn = document.getElementById('youtubePrevBtn');
          if (prevBtn) {
            prevBtn.addEventListener('click', function () {
              if (this.dataset.pageToken) {
                self.currentPageToken = this.dataset.pageToken;
                self.loadVideos();
              }
            });
          }
          var nextBtn = document.getElementById('youtubeNextBtn');
          if (nextBtn) {
            nextBtn.addEventListener('click', function () {
              if (this.dataset.pageToken) {
                self.currentPageToken = this.dataset.pageToken;
                self.loadVideos();
              }
            });
          }
        },
        open: function open() {
          var serverField = document.querySelector('[name="jform[server_id]"]');
          if (serverField) {
            this.serverId = serverField.value;
          }
          if (!this.serverId) {
            alert('Please select a server first.');
            return;
          }
          this.init();
          var studyTitleField = document.getElementById('jform_study_id_name');
          var studyTitle = '';
          if (studyTitleField && studyTitleField.value) {
            studyTitle = studyTitleField.value;
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
            this.loadPlaylists();
            this.loadVideos();
          }
        },
        loadPlaylists: function loadPlaylists() {
          var self = this;
          var playlistSelect = document.getElementById('youtubePlaylistSelect');
          if (!playlistSelect) {
            return;
          }
          var url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=youtube&handler=fetchChannelPlaylists';
          url += '&server_id=' + this.serverId;
          url += '&' + Joomla.getOptions('csrf.token') + '=1';
          fetch(url).then(function (response) {
            return response.json();
          }).then(function (data) {
            if (data.success && data.playlists) {
              self.playlists = data.playlists;
              while (playlistSelect.options.length > 1) {
                playlistSelect.remove(1);
              }
              data.playlists.forEach(function (playlist) {
                var option = document.createElement('option');
                option.value = playlist.playlistId;
                option.textContent = playlist.title;
                playlistSelect.appendChild(option);
              });
            }
          }).catch(function (err) {
            console.error('Failed to load playlists:', err);
          });
        },
        loadVideos: function loadVideos() {
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
          var handler;
          if (this.searchQuery) {
            handler = 'searchChannelVideos';
          } else if (this.filterType !== 'all') {
            handler = 'fetchLiveVideos';
          } else if (this.selectedPlaylist) {
            handler = 'fetchPlaylistVideos';
          } else {
            handler = 'fetchChannelVideos';
          }
          var url = 'index.php?option=com_proclaim&task=cwmmediafile.xhr&type=youtube&handler=' + handler;
          url += '&server_id=' + this.serverId;
          url += '&' + Joomla.getOptions('csrf.token') + '=1';
          if (this.currentPageToken) {
            url += '&page_token=' + encodeURIComponent(this.currentPageToken);
          }
          if (this.searchQuery) {
            url += '&query=' + encodeURIComponent(this.searchQuery);
          }
          if (this.filterType !== 'all') {
            url += '&event_type=' + encodeURIComponent(this.filterType);
          }
          if (this.selectedPlaylist && handler === 'fetchPlaylistVideos') {
            url += '&playlist_id=' + encodeURIComponent(this.selectedPlaylist);
          }
          fetch(url).then(function (response) {
            return response.json();
          }).then(function (data) {
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
          }).catch(function (err) {
            loading.style.display = 'none';
            error.textContent = 'Error: ' + err.message;
            error.style.display = 'block';
          });
        },
        renderVideos: function renderVideos(videos) {
          var grid = document.getElementById('youtubeVideoGrid');
          var self = this;
          videos.forEach(function (video) {
            var card = document.createElement('div');
            card.className = 'col';
            var badgeHtml = '';
            if (video.liveBadge) {
              var badgeClass = video.liveBadge === 'LIVE' ? 'bg-danger' : 'bg-warning text-dark';
              badgeHtml = '<span class="position-absolute top-0 start-0 badge ' + badgeClass + ' m-2">' + video.liveBadge + '</span>';
            }
            card.innerHTML = '<div class="card h-100 youtube-video-card" data-video-id="' + video.videoId + '" data-title="' + self.escapeHtml(video.title) + '" data-thumbnail="' + video.thumbnail + '">' + '<div class="position-relative">' + '<img src="' + video.thumbnail + '" class="card-img-top" alt="' + self.escapeHtml(video.title) + '">' + badgeHtml + '</div>' + '<div class="card-body">' + '<h6 class="card-title">' + self.escapeHtml(video.title) + '</h6>' + '<p class="card-text small text-muted">' + self.formatDate(video.publishedAt) + '</p>' + '</div>' + '<div class="card-footer">' + '<button type="button" class="btn btn-primary btn-sm w-100 select-video-btn"><span class="icon-checkmark"></span> Select</button>' + '</div>' + '</div>';
            var selectBtn = card.querySelector('.select-video-btn');
            selectBtn.addEventListener('click', function () {
              var cardEl = this.closest('.youtube-video-card');
              self.selectVideo(cardEl.dataset.videoId, cardEl.dataset.title, cardEl.dataset.thumbnail);
            });
            grid.appendChild(card);
          });
        },
        selectVideo: function selectVideo(videoId, title, thumbnail) {
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
        escapeHtml: function escapeHtml(text) {
          var div = document.createElement('div');
          div.textContent = text;
          return div.innerHTML;
        },
        formatDate: function formatDate(dateString) {
          if (!dateString) {
            return '';
          }
          var date = new Date(dateString);
          return date.toLocaleDateString();
        }
      };
      document.addEventListener('DOMContentLoaded', function () {
        var browseBtn = document.getElementById('youtube-browse-btn');
        if (browseBtn) {
          browseBtn.addEventListener('click', function () {
            Proclaim.YoutubeBrowser.open();
          });
        }
      });
    })();

})();
