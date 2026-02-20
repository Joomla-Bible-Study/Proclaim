/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description Vimeo Video Lookup for Proclaim Media Files
 */
(() => {
    'use strict';

    window.Proclaim = window.Proclaim || {};

    window.Proclaim.VimeoBrowser = {
        modal: null,

        init() {
            this.createModal();
        },

        createModal() {
            if (document.getElementById('vimeoBrowserModal')) {
                return;
            }

            const modalHtml = '<div class="modal fade" id="vimeoBrowserModal" tabindex="-1" aria-labelledby="vimeoBrowserModalLabel">'
                + '<div class="modal-dialog modal-lg">'
                + '<div class="modal-content">'
                + '<div class="modal-header">'
                + '<h5 class="modal-title" id="vimeoBrowserModalLabel"><i class="fab fa-vimeo me-2" aria-hidden="true"></i> Vimeo Video Lookup</h5>'
                + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
                + '</div>'
                + '<div class="modal-body">'
                + '<p class="text-muted small">Paste a Vimeo URL or video ID to look up the video details and auto-fill the URL field.</p>'
                + '<div class="input-group mb-3">'
                + '<input type="text" id="vimeoLookupInput" class="form-control" placeholder="https://vimeo.com/123456789 or video ID" aria-label="Vimeo URL or ID">'
                + '<button class="btn btn-primary" type="button" id="vimeoLookupBtn"><span class="icon-search" aria-hidden="true"></span> Look Up</button>'
                + '</div>'
                + '<div id="vimeoLoading" class="text-center py-3" style="display: none;">'
                + '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>'
                + ' Fetching video details...'
                + '</div>'
                + '<div id="vimeoError" class="alert alert-danger" style="display: none;"></div>'
                + '<div id="vimeoResult" class="card" style="display: none;">'
                + '<div class="row g-0">'
                + '<div class="col-md-4">'
                + '<img id="vimeoThumbnail" src="" class="img-fluid rounded-start" alt="Video thumbnail">'
                + '</div>'
                + '<div class="col-md-8">'
                + '<div class="card-body">'
                + '<h6 class="card-title" id="vimeoTitle"></h6>'
                + '<p class="card-text small text-muted" id="vimeoAuthor"></p>'
                + '<p class="card-text small text-muted" id="vimeoDuration"></p>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '<div class="modal-footer">'
                + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>'
                + '<button type="button" class="btn btn-primary" id="vimeoSelectBtn" style="display: none;"><span class="icon-checkmark" aria-hidden="true"></span> Use This Video</button>'
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

            const lookupBtn = document.getElementById('vimeoLookupBtn');
            if (lookupBtn) {
                lookupBtn.addEventListener('click', () => { self.lookupVideo(); });
            }

            const lookupInput = document.getElementById('vimeoLookupInput');
            if (lookupInput) {
                lookupInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        self.lookupVideo();
                    }
                });
            }

            const selectBtn = document.getElementById('vimeoSelectBtn');
            if (selectBtn) {
                selectBtn.addEventListener('click', () => { self.selectVideo(); });
            }
        },

        open() {
            this.init();

            const modalEl = document.getElementById('vimeoBrowserModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                // Pre-fill with existing URL if any
                const filenameField = document.querySelector('[name="jform[params][filename]"]');
                const lookupInput = document.getElementById('vimeoLookupInput');
                if (filenameField && filenameField.value && lookupInput) {
                    lookupInput.value = filenameField.value;
                }

                // Reset result state
                this.clearResult();

                this.modal = new bootstrap.Modal(modalEl);
                this.modal.show();
            }
        },

        lookupVideo() {
            const input = document.getElementById('vimeoLookupInput');
            if (!input || !input.value.trim()) {
                return;
            }

            const query = input.value.trim();
            const videoId = this.extractVideoId(query);

            if (!videoId) {
                const error = document.getElementById('vimeoError');
                if (error) {
                    error.textContent = 'Could not extract a Vimeo video ID from that input. Please paste a full Vimeo URL (e.g. https://vimeo.com/123456789) or a numeric video ID.';
                    error.style.display = 'block';
                }
                this.clearResult();
                return;
            }

            this.clearResult();

            const loading = document.getElementById('vimeoLoading');
            if (loading) {
                loading.style.display = 'block';
            }

            const self = this;
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
                    if (loading) {
                        loading.style.display = 'none';
                    }

                    if (!data.success || !data.metadata) {
                        const error = document.getElementById('vimeoError');
                        if (error) {
                            error.textContent = data.error || 'Failed to load video details. The video may be private or the ID may be incorrect.';
                            error.style.display = 'block';
                        }
                        return;
                    }

                    self.currentVideoId = videoId;
                    self.currentVideoUrl = `https://vimeo.com/${videoId}`;
                    self.showResult(data.metadata);
                })
                .catch((err) => {
                    if (loading) {
                        loading.style.display = 'none';
                    }
                    const error = document.getElementById('vimeoError');
                    if (error) {
                        error.textContent = `Error: ${err.message}`;
                        error.style.display = 'block';
                    }
                });
        },

        extractVideoId(input) {
            // Numeric ID
            if (/^\d+$/.test(input)) {
                return input;
            }

            // Standard: vimeo.com/123456789
            let match = input.match(/vimeo\.com\/(\d+)/);
            if (match) {
                return match[1];
            }

            // Player: player.vimeo.com/video/123456789
            match = input.match(/player\.vimeo\.com\/video\/(\d+)/);
            if (match) {
                return match[1];
            }

            // Channels: vimeo.com/channels/name/123456789
            match = input.match(/vimeo\.com\/channels\/[^/]+\/(\d+)/);
            if (match) {
                return match[1];
            }

            // Groups: vimeo.com/groups/name/videos/123456789
            match = input.match(/vimeo\.com\/groups\/[^/]+\/videos\/(\d+)/);
            if (match) {
                return match[1];
            }

            return null;
        },

        showResult(metadata) {
            const result = document.getElementById('vimeoResult');
            const title = document.getElementById('vimeoTitle');
            const thumbnail = document.getElementById('vimeoThumbnail');
            const author = document.getElementById('vimeoAuthor');
            const duration = document.getElementById('vimeoDuration');
            const selectBtn = document.getElementById('vimeoSelectBtn');

            if (title) {
                title.textContent = metadata.title || 'Untitled';
            }
            if (thumbnail && metadata.thumbnail) {
                thumbnail.src = metadata.thumbnail;
                thumbnail.alt = metadata.title || 'Video thumbnail';
            }
            if (author && metadata.author) {
                author.textContent = `By: ${metadata.author}`;
            }
            if (duration && metadata.duration) {
                duration.textContent = `Duration: ${this.formatDuration(metadata.duration)}`;
            }

            if (result) {
                result.style.display = 'block';
            }
            if (selectBtn) {
                selectBtn.style.display = 'inline-block';
            }
        },

        clearResult() {
            const result = document.getElementById('vimeoResult');
            const error = document.getElementById('vimeoError');
            const selectBtn = document.getElementById('vimeoSelectBtn');

            if (result) {
                result.style.display = 'none';
            }
            if (error) {
                error.style.display = 'none';
                error.textContent = '';
            }
            if (selectBtn) {
                selectBtn.style.display = 'none';
            }

            this.currentVideoId = null;
            this.currentVideoUrl = null;
        },

        selectVideo() {
            if (!this.currentVideoUrl) {
                return;
            }

            const filenameField = document.querySelector('[name="jform[params][filename]"]');
            if (filenameField) {
                filenameField.value = this.currentVideoUrl;
            }

            if (this.modal) {
                this.modal.hide();
            }

            const title = document.getElementById('vimeoTitle');
            const videoTitle = title ? title.textContent : this.currentVideoUrl;

            if (Joomla.renderMessages) {
                Joomla.renderMessages({
                    success: [`Video "${videoTitle}" selected.`],
                });
            }
        },

        formatDuration(seconds) {
            if (!seconds) {
                return '';
            }
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            if (hrs > 0) {
                return `${hrs}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }
            return `${mins}:${String(secs).padStart(2, '0')}`;
        },
    };
})();
