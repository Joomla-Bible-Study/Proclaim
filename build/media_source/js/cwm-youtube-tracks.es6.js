/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description YouTube Tracks — Import chapters and download captions from YouTube
 */
(function () {
    'use strict';

    let config = {};

    function getConfig() {
        config = Joomla.getOptions('com_proclaim.youtubeTracks') || {};
        return config;
    }

    /**
     * Show a status message in the given container
     */
    function showMessage(container, success, message) {
        container.innerHTML = '<div class="alert alert-'
            + (success ? 'success' : 'danger') + ' mt-2">' + message + '</div>';
    }

    /**
     * Build the AJAX URL for addon actions
     */
    function ajaxUrl(action, extraParams) {
        const cfg = getConfig();
        let url = cfg.baseUrl + 'index.php?option=com_proclaim&task=cwmserver.addonAjax'
            + '&addon=youtube&action=' + action
            + '&media_id=' + cfg.mediaId
            + '&format=raw&' + cfg.token + '=1';
        if (extraParams) {
            url += '&' + extraParams;
        }
        return url;
    }

    /**
     * Populate the chapters subform with imported chapter data.
     *
     * Uses Joomla's subform API to add rows dynamically.
     */
    function populateChaptersSubform(chapters) {
        const subformField = document.querySelector('[name="jform[params][chapters]"]');
        if (!subformField) {
            // Try the subform container approach
            const container = document.getElementById('jform_params_chapters');
            if (!container) {
                return false;
            }
        }

        // Find the subform element (joomla-field-subform)
        const subform = document.querySelector('joomla-field-subform[name="jform[params][chapters]"]')
            || document.querySelector('[data-base-name="jform[params][chapters]"]');

        if (!subform) {
            return false;
        }

        // Clear existing rows
        const existingRows = subform.querySelectorAll('.subform-repeatable-group');
        existingRows.forEach(function (row) {
            // Use subform's remove method if available
            const removeBtn = row.querySelector('[class*="group-remove"], .group-remove');
            if (removeBtn) {
                removeBtn.click();
            }
        });

        // Add chapter rows
        chapters.forEach(function (chapter, index) {
            // Click the add button to create a new row
            const addBtn = subform.querySelector('[class*="group-add"], .group-add');
            if (addBtn) {
                addBtn.click();
            }

            // Wait for DOM update then fill fields
            setTimeout(function () {
                const rows = subform.querySelectorAll('.subform-repeatable-group');
                const row = rows[rows.length - 1];
                if (!row) {
                    return;
                }

                const timeField = row.querySelector('[name*="[time]"]');
                const labelField = row.querySelector('[name*="[label]"]');

                if (timeField) {
                    timeField.value = chapter.time || '';
                }
                if (labelField) {
                    labelField.value = chapter.label || '';
                }
            }, 50 * (index + 1));
        });

        return true;
    }

    /**
     * Add a row to the subtitle tracks subform
     */
    function addSubtitleTrackRow(trackData) {
        const subform = document.querySelector('joomla-field-subform[name="jform[params][subtitle_tracks]"]')
            || document.querySelector('[data-base-name="jform[params][subtitle_tracks]"]');

        if (!subform) {
            return false;
        }

        const addBtn = subform.querySelector('[class*="group-add"], .group-add');
        if (addBtn) {
            addBtn.click();
        }

        setTimeout(function () {
            const rows = subform.querySelectorAll('.subform-repeatable-group');
            const row = rows[rows.length - 1];
            if (!row) {
                return;
            }

            const labelField = row.querySelector('[name*="[label]"]');
            const srclangField = row.querySelector('[name*="[srclang]"]');
            const srcField = row.querySelector('[name*="[src]"]');
            const kindField = row.querySelector('[name*="[kind]"]');

            if (labelField) {
                labelField.value = trackData.label || '';
            }
            if (srclangField) {
                srclangField.value = trackData.srclang || '';
            }
            if (srcField) {
                srcField.value = trackData.src || '';
            }
            if (kindField) {
                kindField.value = trackData.kind || 'captions';
            }
        }, 100);

        return true;
    }

    /**
     * Handle Import Chapters button click
     */
    function handleImportChapters(resultContainer) {
        resultContainer.innerHTML = '<div class="alert alert-info mt-2">'
            + '<span class="spinner-border spinner-border-sm me-2"></span>'
            + (config.importing || 'Importing chapters...') + '</div>';

        fetch(ajaxUrl('importChapters'), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.chapters && data.chapters.length) {
                    const populated = populateChaptersSubform(data.chapters);
                    if (populated !== false) {
                        showMessage(resultContainer, true,
                            (config.importSuccess || 'Imported {count} chapters from YouTube.')
                                .replace('{count}', data.chapters.length));
                    } else {
                        showMessage(resultContainer, true,
                            (config.importSuccess || 'Found {count} chapters.')
                                .replace('{count}', data.chapters.length)
                            + ' <strong>' + (config.importManual || 'Please save to apply.') + '</strong>');
                    }
                } else {
                    showMessage(resultContainer, false, data.error || config.importFailed || 'No chapters found.');
                }
            })
            .catch(function () {
                showMessage(resultContainer, false, config.importError || 'Error importing chapters.');
            });
    }

    /**
     * Handle List Captions button click — shows available tracks with download buttons
     */
    function handleListCaptions(resultContainer) {
        resultContainer.innerHTML = '<div class="alert alert-info mt-2">'
            + '<span class="spinner-border spinner-border-sm me-2"></span>'
            + (config.loadingCaptions || 'Loading caption tracks...') + '</div>';

        fetch(ajaxUrl('listCaptions'), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.tracks && data.tracks.length) {
                    let html = '<div class="list-group mt-2">';
                    data.tracks.forEach(function (track) {
                        const label = track.name || track.language;
                        const kind = track.trackKind === 'ASR' ? ' (Auto-generated)' : '';
                        html += '<div class="list-group-item d-flex justify-content-between align-items-center">'
                            + '<span><strong>' + Joomla.sanitizeHtml(label) + '</strong>'
                            + ' <code>' + Joomla.sanitizeHtml(track.language) + '</code>' + kind + '</span>'
                            + '<button type="button" class="btn btn-sm btn-success cwm-download-caption"'
                            + ' data-caption-id="' + Joomla.sanitizeHtml(track.id) + '"'
                            + ' data-srclang="' + Joomla.sanitizeHtml(track.language) + '"'
                            + ' data-label="' + Joomla.sanitizeHtml(label) + '">'
                            + '<span class="icon-download me-1"></span>'
                            + (config.downloadBtn || 'Download VTT') + '</button>'
                            + '</div>';
                    });
                    html += '</div>';
                    resultContainer.innerHTML = html;

                    // Attach download handlers
                    resultContainer.querySelectorAll('.cwm-download-caption').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            handleDownloadCaption(btn, resultContainer);
                        });
                    });
                } else if (data.success && (!data.tracks || !data.tracks.length)) {
                    showMessage(resultContainer, false, config.noCaptions || 'No caption tracks found on this video.');
                } else {
                    showMessage(resultContainer, false, data.error || config.captionError || 'Error loading captions.');
                }
            })
            .catch(function () {
                showMessage(resultContainer, false, config.captionError || 'Error loading captions.');
            });
    }

    /**
     * Handle download of a specific caption track
     */
    function handleDownloadCaption(btn, resultContainer) {
        const captionId = btn.dataset.captionId;
        const srclang = btn.dataset.srclang;
        const label = btn.dataset.label;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const params = 'caption_id=' + encodeURIComponent(captionId)
            + '&srclang=' + encodeURIComponent(srclang)
            + '&label=' + encodeURIComponent(label);

        fetch(ajaxUrl('downloadCaption', params), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    btn.innerHTML = '<span class="icon-check me-1"></span>' + (config.downloaded || 'Added');
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-secondary');

                    // Add to subtitle tracks subform
                    addSubtitleTrackRow({
                        label: data.label || label,
                        srclang: data.srclang || srclang,
                        src: data.src,
                        kind: data.kind || 'captions',
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="icon-download me-1"></span>' + (config.downloadBtn || 'Download VTT');
                    showMessage(resultContainer, false, data.error || 'Download failed.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = '<span class="icon-download me-1"></span>' + (config.downloadBtn || 'Download VTT');
                showMessage(resultContainer, false, config.captionError || 'Error downloading caption.');
            });
    }

    /**
     * Render the platform integration toolbar on the Chapters & Tracks tab.
     * Buttons are driven by addon capability flags (supportsChapters, supportsCaptions).
     */
    function renderPlatformToolbar() {
        const cfg = getConfig();
        if (!cfg.mediaId || (!cfg.supportsChapters && !cfg.supportsCaptions)) {
            return;
        }

        const tracksContent = document.getElementById('tracks-content');
        if (!tracksContent) {
            return;
        }

        // Platform icon (YouTube gets its brand icon, others get generic)
        const iconClass = cfg.isYouTube ? 'icon-youtube' : 'icon-cloud-download';
        const addonName = cfg.addonName || 'Platform';

        // Create toolbar container
        const toolbar = document.createElement('div');
        toolbar.className = 'card card-body bg-body-tertiary mb-3';

        let buttonsHtml = '';

        if (cfg.supportsChapters) {
            buttonsHtml += '<button type="button" class="btn btn-primary" id="cwm-import-chapters-btn">'
                + '<span class="icon-download me-1"></span>'
                + (cfg.importChaptersBtn || 'Import Chapters from ' + addonName) + '</button>';
        }

        if (cfg.supportsCaptions) {
            buttonsHtml += '<button type="button" class="btn btn-primary" id="cwm-list-captions-btn">'
                + '<span class="icon-comments me-1"></span>'
                + (cfg.listCaptionsBtn || 'Download Captions from ' + addonName) + '</button>';
        }

        toolbar.innerHTML = '<h4 class="card-title mb-3">'
            + '<span class="' + iconClass + ' me-2" aria-hidden="true"></span>'
            + (cfg.toolbarTitle || addonName + ' Integration') + '</h4>'
            + '<div class="d-flex flex-wrap gap-2 mb-2">' + buttonsHtml + '</div>'
            + '<div id="cwm-youtube-tracks-result"></div>';

        tracksContent.insertBefore(toolbar, tracksContent.firstChild);

        const resultContainer = document.getElementById('cwm-youtube-tracks-result');

        var chaptersBtn = document.getElementById('cwm-import-chapters-btn');
        if (chaptersBtn) {
            chaptersBtn.addEventListener('click', function () {
                handleImportChapters(resultContainer);
            });
        }

        var captionsBtn = document.getElementById('cwm-list-captions-btn');
        if (captionsBtn) {
            captionsBtn.addEventListener('click', function () {
                handleListCaptions(resultContainer);
            });

            // Show OAuth warning if not connected
            if (!cfg.oauthConnected) {
                captionsBtn.title = cfg.oauthRequired || 'OAuth required — connect in server settings';
                captionsBtn.classList.add('disabled');
                captionsBtn.setAttribute('aria-disabled', 'true');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', renderPlatformToolbar);
})();
