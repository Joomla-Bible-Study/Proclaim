/**
 * Playlist picker (phase 4)
 *
 * Opens a modal listing the selected server's live channel playlists and, on
 * pick, writes the remote playlist ID into the read-only remote_playlist_id
 * field and auto-fills the Title when it is still empty (e.g. on a new playlist).
 *
 * Reuses the existing fetchChannelPlaylists XHR endpoint. No build-time deps
 * beyond Bootstrap's modal and Joomla's core options.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla, document) => {
    'use strict';

    window.Proclaim = window.Proclaim || {};

    const MODAL_ID = 'playlistPickerModal';

    /**
   * Escape a value for safe interpolation into HTML (text or attribute).
   * Playlist titles/IDs come from the YouTube API and must never be trusted.
   */
    const esc = (value) => String(value === null || value === undefined ? '' : value)
        .replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));

    const PlaylistPicker = {
        modal: null,

        /**
     * Open the picker. Reads the chosen server from the form.
     */
        open() {
            const serverField = document.querySelector('[name="jform[server_id]"]');
            const serverId = serverField ? serverField.value : '';

            this.ensureModal();
            this.show();

            if (!serverId) {
                this.setBody(`<div class="alert alert-warning">${Joomla.Text._('JBS_PLAYLIST_PICK_NO_SERVER')}</div>`);
                return;
            }

            this.load(serverId);
        },

        /**
     * Build the modal markup once.
     */
        ensureModal() {
            if (document.getElementById(MODAL_ID)) {
                return;
            }

            const html = `<div class="modal fade" id="${MODAL_ID}" tabindex="-1" aria-labelledby="${MODAL_ID}Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="${MODAL_ID}Label"><span class="icon-list" aria-hidden="true"></span> ${Joomla.Text._('JBS_PLAYLIST_PICK_TITLE')}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="${MODAL_ID}Body"></div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${Joomla.Text._('JCANCEL')}</button>
            </div>
          </div>
        </div>
      </div>`;

            document.body.insertAdjacentHTML('beforeend', html);

            const modalEl = document.getElementById(MODAL_ID);

            // Clean up the backdrop reliably on close.
            modalEl.addEventListener('hidden.bs.modal', () => {
                document.querySelectorAll('.modal-backdrop').forEach((b) => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
            });
        },

        show() {
            const modalEl = document.getElementById(MODAL_ID);

            if (modalEl && typeof bootstrap !== 'undefined') {
                this.modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                this.modal.show();
            }
        },

        setBody(html) {
            const body = document.getElementById(`${MODAL_ID}Body`);

            if (body) {
                body.innerHTML = html;
            }
        },

        /**
     * Fetch and render the channel's playlists for the given server.
     */
        load(serverId) {
            this.setBody(`<div class="text-center p-4"><span class="icon-spinner icon-spin" aria-hidden="true"></span> ${Joomla.Text._('JGLOBAL_LOADING')}</div>`);

            // Resolve the platform type from the server select's data-server-types map
            // so the picker works for any playlist-capable platform, not just YouTube.
            const serverField = document.querySelector('[name="jform[server_id]"]');
            let serverType = 'youtube';

            if (serverField) {
                try {
                    const map = JSON.parse(serverField.getAttribute('data-server-types') || '{}');
                    serverType = map[serverId] || serverType;
                } catch {
                    // keep the default
                }
            }

            let url = `index.php?option=com_proclaim&task=cwmmediafile.xhr&type=${encodeURIComponent(serverType)}&handler=fetchRemotePlaylists`;
            url += `&server_id=${encodeURIComponent(serverId)}`;
            url += `&${Joomla.getOptions('csrf.token')}=1`;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => r.json())
                .then((data) => {
                    if (!data || !data.success) {
                        const err = (data && data.error) ? data.error : Joomla.Text._('JERROR_AN_ERROR_HAS_OCCURRED');
                        this.setBody(`<div class="alert alert-danger">${err}</div>`);
                        return;
                    }

                    if (!data.playlists || !data.playlists.length) {
                        this.setBody(`<div class="alert alert-info">${Joomla.Text._('JBS_PLAYLIST_PICK_EMPTY')}</div>`);
                        return;
                    }

                    this.render(data.playlists);
                })
                .catch(() => {
                    this.setBody(`<div class="alert alert-danger">${Joomla.Text._('JERROR_AN_ERROR_HAS_OCCURRED')}</div>`);
                });
        },

        /**
     * Render the playlist list and wire up selection.
     */
        render(playlists) {
            const items = playlists.map((pl) => {
                const id = esc(pl.playlistId || '');
                const title = esc(pl.title || pl.playlistId || '');
                const thumb = pl.thumbnail
                    ? `<img src="${esc(pl.thumbnail)}" alt="" width="80" height="45" class="me-3 rounded" loading="lazy">`
                    : '';

                return `<button type="button" class="list-group-item list-group-item-action d-flex align-items-center"
          data-playlist-id="${id}" data-playlist-title="${title}">
          ${thumb}
          <span class="flex-grow-1 text-start">
            <span class="d-block fw-bold">${title}</span>
            <small class="text-muted">${id}</small>
          </span>
        </button>`;
            }).join('');

            this.setBody(`<div class="list-group">${items}</div>`);

            document.querySelectorAll(`#${MODAL_ID}Body [data-playlist-id]`).forEach((btn) => {
                btn.addEventListener('click', () => {
                    this.pick(btn.getAttribute('data-playlist-id'), btn.getAttribute('data-playlist-title'));
                });
            });
        },

        /**
     * Apply the chosen playlist to the form and close.
     */
        pick(playlistId, title) {
            const idField = document.querySelector('[name="jform[remote_playlist_id]"]');

            if (idField) {
                idField.value = playlistId;
                idField.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Auto-fill the title only when it is still empty (e.g. on a new playlist).
            const titleField = document.querySelector('[name="jform[title]"]');

            if (titleField && titleField.value.trim() === '' && title) {
                titleField.value = title;
                titleField.dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (this.modal) {
                this.modal.hide();
            }
        },
    };

    window.Proclaim.PlaylistPicker = PlaylistPicker;
})(Joomla, document);
