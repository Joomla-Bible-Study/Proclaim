<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * YouTube Exclude Field - allows selecting upcoming videos to exclude
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class YoutubeExcludeField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.1.0
     */
    protected $type = 'YoutubeExclude';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function getInput(): string
    {
        // Load the module's language file for field strings
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('mod_proclaim_youtube', JPATH_SITE . '/modules/mod_proclaim_youtube');

        // Get the server field name from attribute or default
        $serverField = $this->element['server_field'] ?? 'server_id';

        // Current excluded video IDs (comma-separated)
        $excludedIds = $this->value ? explode(',', $this->value) : [];
        $excludedIds = array_map('trim', $excludedIds);
        $excludedIds = array_filter($excludedIds);

        // Build the HTML
        $html = [];

        // Hidden field to store the actual value
        $html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($this->value ?? '', ENT_QUOTES, 'UTF-8') . '">';

        // Container for the video list
        $html[] = '<div id="' . $this->id . '_container" class="youtube-exclude-container">';

        // Info text
        $html[] = '<div class="youtube-exclude-info alert alert-info mb-3">';
        $html[] = '<span class="icon-info-circle me-2" aria-hidden="true"></span>';
        $html[] = Text::_('JBS_FIELD_YOUTUBE_EXCLUDE_INFO');
        $html[] = '</div>';

        // Fetch button
        $html[] = '<button type="button" class="btn btn-secondary mb-3" id="' . $this->id . '_fetch">';
        $html[] = '<span class="icon-refresh me-2" aria-hidden="true"></span>';
        $html[] = Text::_('JBS_FIELD_YOUTUBE_EXCLUDE_FETCH');
        $html[] = '</button>';

        // Loading indicator
        $html[] = '<div id="' . $this->id . '_loading" class="youtube-exclude-loading d-none">';
        $html[] = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>';
        $html[] = Text::_('JBS_FIELD_YOUTUBE_EXCLUDE_LOADING');
        $html[] = '</div>';

        // Video list container
        $html[] = '<div id="' . $this->id . '_list" class="youtube-exclude-list">';

        // Show currently excluded IDs if any
        if (!empty($excludedIds)) {
            $html[] = '<div class="mb-2"><strong>' . Text::_('JBS_FIELD_YOUTUBE_EXCLUDE_CURRENT') . '</strong></div>';
            $html[] = '<ul class="list-group mb-3">';

            foreach ($excludedIds as $videoId) {
                $html[] = '<li class="list-group-item d-flex justify-content-between align-items-center">';
                $html[] = '<span class="youtube-exclude-videoid">' . htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8') . '</span>';
                $html[] = '<button type="button" class="btn btn-sm btn-danger youtube-exclude-remove" data-videoid="' . htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8') . '">';
                $html[] = '<span class="icon-times" aria-hidden="true"></span>';
                $html[] = '</button>';
                $html[] = '</li>';
            }

            $html[] = '</ul>';
        }

        $html[] = '</div>';

        // Error container
        $html[] = '<div id="' . $this->id . '_error" class="youtube-exclude-error alert alert-danger d-none"></div>';

        $html[] = '</div>';

        // Add JavaScript
        $html[] = $this->getJavaScript($serverField, $excludedIds);

        return implode("\n", $html);
    }

    /**
     * Get the JavaScript for the field
     *
     * @param   string  $serverField  The server field name
     * @param   array   $excludedIds  Currently excluded video IDs
     *
     * @return  string  JavaScript code
     *
     * @since   10.1.0
     */
    protected function getJavaScript(string $serverField, array $excludedIds): string
    {
        $token   = Factory::getApplication()->getSession()->getFormToken();
        $baseUrl = Uri::base() . 'index.php?option=com_proclaim&task=cwmserver.addonAjax&addon=youtube&action=fetchUpcoming&format=raw&' . $token . '=1';

        try {
            $excludedJson = json_encode($excludedIds, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $excludedJson = '[]';
        }

        $js = <<<JS
<script>
(function() {
    const fieldId = '{$this->id}';
    const serverFieldName = '{$serverField}';
    const baseUrl = '{$baseUrl}';
    let excludedIds = {$excludedJson};

    const hiddenInput = document.getElementById(fieldId);
    const fetchBtn = document.getElementById(fieldId + '_fetch');
    const loadingEl = document.getElementById(fieldId + '_loading');
    const listEl = document.getElementById(fieldId + '_list');
    const errorEl = document.getElementById(fieldId + '_error');

    function updateHiddenField() {
        hiddenInput.value = excludedIds.join(',');
    }

    function getServerId() {
        // Try to find the server field - could be in params or direct
        let serverEl = document.querySelector('[name*="' + serverFieldName + '"]');
        if (!serverEl) {
            serverEl = document.getElementById('jform_params_' + serverFieldName);
        }
        return serverEl ? serverEl.value : null;
    }

    function showError(message) {
        errorEl.textContent = message;
        errorEl.classList.remove('d-none');
    }

    function hideError() {
        errorEl.classList.add('d-none');
    }

    function renderVideoList(videos) {
        let html = '<div class="mb-2"><strong>Upcoming Videos:</strong></div>';

        if (videos.length === 0) {
            html += '<div class="alert alert-warning">No upcoming videos found.</div>';
            listEl.innerHTML = html;
            return;
        }

        html += '<div class="list-group">';
        videos.forEach(function(video) {
            const isExcluded = excludedIds.includes(video.videoId);
            const checkedAttr = isExcluded ? 'checked' : '';

            html += '<label class="list-group-item d-flex align-items-center">';
            html += '<input type="checkbox" class="form-check-input me-3 youtube-exclude-checkbox" ';
            html += 'data-videoid="' + video.videoId + '" ' + checkedAttr + '>';
            html += '<div class="flex-grow-1">';
            html += '<div class="fw-bold">' + escapeHtml(video.title) + '</div>';
            html += '<small class="text-muted">ID: ' + video.videoId + '</small>';
            if (video.thumbnail) {
                html += '<div class="mt-2"><img src="' + video.thumbnail + '" alt="" style="max-width:120px;"></div>';
            }
            html += '</div>';
            html += '</label>';
        });
        html += '</div>';

        listEl.innerHTML = html;

        // Add event listeners to checkboxes
        listEl.querySelectorAll('.youtube-exclude-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                const videoId = this.dataset.videoid;
                if (this.checked) {
                    if (!excludedIds.includes(videoId)) {
                        excludedIds.push(videoId);
                    }
                } else {
                    excludedIds = excludedIds.filter(id => id !== videoId);
                }
                updateHiddenField();
            });
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    fetchBtn.addEventListener('click', function() {
        const serverId = getServerId();

        if (!serverId) {
            showError('Please select a YouTube server first.');
            return;
        }

        hideError();
        loadingEl.classList.remove('d-none');
        fetchBtn.disabled = true;

        const url = baseUrl + '&server_id=' + serverId;

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingEl.classList.add('d-none');
            fetchBtn.disabled = false;

            if (data.success) {
                renderVideoList(data.videos || []);
            } else {
                showError(data.error || 'Failed to fetch videos');
            }
        })
        .catch(error => {
            loadingEl.classList.add('d-none');
            fetchBtn.disabled = false;
            showError('Error fetching videos: ' + error.message);
        });
    });

    // Handle remove buttons for currently excluded items
    listEl.querySelectorAll('.youtube-exclude-remove').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const videoId = this.dataset.videoid;
            excludedIds = excludedIds.filter(id => id !== videoId);
            updateHiddenField();
            this.closest('li').remove();
        });
    });
})();
</script>
JS;

        return $js;
    }
}
