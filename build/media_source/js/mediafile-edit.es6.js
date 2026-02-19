/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description Media File Edit — AJAX server switching + server picker modal
 */
/* jshint esversion: 6 */
/* global Joomla, bootstrap, console */
(function() {
    'use strict';

    var config = {};
    var previousServerValue = '';
    var previousServerType = '';
    var isLoading = false;

    /**
     * Get configuration from Joomla script options
     */
    function getConfig() {
        config = Joomla.getOptions('com_proclaim.mediafile') || {};
        return config;
    }

    /**
     * Get the server type map from the select element
     */
    function getServerTypes() {
        var serverField = document.getElementById('jform_server_id');
        if (!serverField || !serverField.dataset.serverTypes) {
            return {};
        }
        try {
            return JSON.parse(serverField.dataset.serverTypes);
        } catch (e) {
            return {};
        }
    }

    /**
     * Get server type descriptions based on type name
     */
    function getTypeDescription(type) {
        var typeLower = (type || '').toLowerCase();
        if (typeLower === 'local') {
            return config.serverTypeLocalDesc || 'Files stored on your web server';
        }
        if (typeLower === 'youtube') {
            return config.serverTypeYoutubeDesc || 'YouTube video links';
        }
        return config.serverTypeLegacyDesc || 'External URLs and embed codes';
    }

    /**
     * Get icon class for a server type
     */
    function getTypeIcon(type) {
        var typeLower = (type || '').toLowerCase();
        if (typeLower === 'local') {
            return 'icon-folder';
        }
        if (typeLower === 'youtube') {
            return 'icon-play';
        }
        return 'icon-cloud';
    }

    /**
     * Get the display name for the currently selected server
     */
    function getSelectedServerName() {
        var serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return '';
        }
        var selected = serverField.options[serverField.selectedIndex];
        return (selected && selected.value) ? selected.text : '';
    }

    /**
     * Replace the <select> with an input-group matching the Modal_Study field pattern:
     * readonly text input + Select button + Clear button.
     * The hidden <select> keeps its value for form submission.
     */
    function convertSelectToInputGroup() {
        var serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return;
        }

        // If the input group already exists, just update the display
        if (document.getElementById('jform_server_id_name')) {
            updateServerDisplay();
            return;
        }

        // Hide the native <select>
        serverField.style.display = 'none';

        // Also hide any Joomla choices.js wrapper around the select
        var choicesWrapper = serverField.closest('.choices');
        if (choicesWrapper) {
            choicesWrapper.style.display = 'none';
        }

        var value = serverField.value;
        var name = getSelectedServerName();
        var hasValue = !!(value && name);
        var displayText = hasValue ? name : (config.selectServerTitle || 'Select a Server');

        // Build the input-group (same pattern as StudyField / Modal_Study)
        var group = document.createElement('span');
        group.className = 'input-group';
        group.id = 'server-picker-group';

        // Readonly text input showing server name
        var input = document.createElement('input');
        input.className = 'form-control';
        input.id = 'jform_server_id_name';
        input.type = 'text';
        input.value = displayText;
        input.readOnly = true;
        input.style.cursor = 'pointer';
        input.addEventListener('click', function() {
            showServerPickerModal();
        });

        // Select button (hidden when a value is set)
        var selectBtn = document.createElement('button');
        selectBtn.type = 'button';
        selectBtn.id = 'jform_server_id_select';
        selectBtn.className = 'btn btn-primary' + (hasValue ? ' hidden' : '');
        selectBtn.innerHTML = '<span class="icon-file" aria-hidden="true"></span> ' +
            (config.selectLabel || 'Select');
        selectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showServerPickerModal();
        });

        // Clear button (hidden when no value)
        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.id = 'jform_server_id_clear';
        clearBtn.className = 'btn btn-secondary' + (hasValue ? '' : ' hidden');
        clearBtn.innerHTML = '<span class="icon-times" aria-hidden="true"></span> ' +
            (config.clearLabel || 'Clear');
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearServer();
        });

        group.appendChild(input);
        group.appendChild(selectBtn);
        group.appendChild(clearBtn);

        // Insert the group after the select (or its wrapper)
        var insertAfter = choicesWrapper || serverField;
        insertAfter.parentNode.insertBefore(group, insertAfter.nextSibling);
    }

    /**
     * Update the display input and button visibility to match the current selection
     */
    function updateServerDisplay() {
        var serverField = document.getElementById('jform_server_id');
        var nameInput = document.getElementById('jform_server_id_name');
        var selectBtn = document.getElementById('jform_server_id_select');
        var clearBtn = document.getElementById('jform_server_id_clear');

        if (!serverField || !nameInput) {
            return;
        }

        var value = serverField.value;
        var name = getSelectedServerName();
        var hasValue = !!(value && name);

        nameInput.value = hasValue ? name : (config.selectServerTitle || 'Select a Server');

        if (selectBtn) {
            selectBtn.classList.toggle('hidden', hasValue);
        }

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', !hasValue);
        }
    }

    /**
     * Clear the server selection and reset the addon containers
     */
    function clearServer() {
        var serverField = document.getElementById('jform_server_id');
        if (serverField) {
            serverField.value = '';
        }

        previousServerValue = '';
        previousServerType = '';

        updateServerDisplay();

        // Clear addon containers
        var generalContainer = document.getElementById('addon-general-container');
        if (generalContainer) {
            generalContainer.innerHTML = '';
        }
        var optionsContent = document.getElementById('addon-options-content');
        if (optionsContent) {
            optionsContent.innerHTML = '';
        }

        // Show the picker modal so user can select a new server
        showServerPickerModal();
    }

    /**
     * Show loading spinner in the addon containers
     */
    function showLoading() {
        var generalContainer = document.getElementById('addon-general-container');
        if (generalContainer) {
            generalContainer.innerHTML = '<div class="text-center p-4"><span class="spinner-border" role="status"></span>' +
                '<p class="mt-2 text-muted">' + (config.switchLoading || 'Switching server...') + '</p></div>';
        }
        var optionsContent = document.getElementById('addon-options-content');
        if (optionsContent) {
            optionsContent.innerHTML = '';
        }
    }

    /**
     * Load addon HTML via AJAX for the given server_id
     */
    function loadAddonHtml(serverId) {
        if (isLoading) {
            return;
        }
        isLoading = true;
        showLoading();

        var url = 'index.php?option=com_proclaim&task=cwmmediafile.getAddonHtml&server_id=' +
            encodeURIComponent(serverId) + '&' + encodeURIComponent(config.token) + '=1';

        Joomla.request({
            url: url,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            onSuccess: function(response) {
                isLoading = false;
                try {
                    var data = JSON.parse(response);
                    if (data.success) {
                        var generalContainer = document.getElementById('addon-general-container');
                        if (generalContainer) {
                            generalContainer.innerHTML = data.generalHtml || '';
                        }
                        var optionsContent = document.getElementById('addon-options-content');
                        if (optionsContent) {
                            optionsContent.innerHTML = data.optionsHtml || '';
                        }

                        // Update tracked server type
                        var serverTypes = getServerTypes();
                        previousServerType = serverTypes[serverId] || '';
                        previousServerValue = serverId;

                        // Re-initialize custom fields in the updated containers.
                        // Dispatch on each container (not the whole form) to avoid
                        // showon.js errors when controlling fields aren't in scope.
                        [generalContainer, optionsContent].forEach(function(el) {
                            if (el && el.innerHTML) {
                                el.dispatchEvent(new CustomEvent('joomla:updated', {
                                    bubbles: true,
                                    cancelable: true,
                                    detail: { container: el }
                                }));
                            }
                        });
                    } else {
                        showError(data.error || 'Failed to load server configuration');
                    }
                } catch (e) {
                    showError('Invalid response from server');
                }
            },
            onError: function() {
                isLoading = false;
                showError('Network error. The server change will use a page reload instead.');
                // Fallback to setServer
                Joomla.submitbutton('cwmmediafile.setServer');
            }
        });
    }

    /**
     * Show error in the addon general container
     */
    function showError(message) {
        var generalContainer = document.getElementById('addon-general-container');
        if (generalContainer) {
            generalContainer.innerHTML = '<div class="alert alert-danger">' +
                '<span class="icon-warning" aria-hidden="true"></span> ' + message + '</div>';
        }
    }

    /**
     * Create and show the server picker modal
     */
    function showServerPickerModal() {
        var serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return;
        }

        var serverTypes = getServerTypes();
        var options = serverField.options;
        var currentValue = serverField.value;

        // Remove existing modal if present
        var existingModal = document.getElementById('serverPickerModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Determine if this is first selection (no addon loaded) — use static backdrop
        var hasAddon = previousServerValue && previousServerType;
        var backdropAttr = hasAddon ? '' : ' data-bs-backdrop="static" data-bs-keyboard="false"';

        // Insert static modal shell (no user content) then populate via DOM to prevent XSS
        document.body.insertAdjacentHTML('beforeend',
            '<div class="modal fade" id="serverPickerModal" tabindex="-1"' + backdropAttr + '>' +
            '<div class="modal-dialog modal-lg modal-dialog-centered">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title" id="serverPickerModalTitle"></h5>' +
            (hasAddon ? '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' : '') +
            '</div>' +
            '<div class="modal-body">' +
            '<p class="text-muted" id="serverPickerModalDesc"></p>' +
            '<div class="row" id="serverPickerModalCards"></div>' +
            '</div></div></div></div>');

        var modalEl = document.getElementById('serverPickerModal');

        // Populate text content safely
        var titleEl = modalEl.querySelector('#serverPickerModalTitle');
        var titleIcon = document.createElement('span');
        titleIcon.className = 'icon-server';
        titleIcon.setAttribute('aria-hidden', 'true');
        titleEl.appendChild(titleIcon);
        titleEl.appendChild(document.createTextNode(' ' + (config.selectServerTitle || 'Select a Server')));
        modalEl.querySelector('#serverPickerModalDesc').textContent =
            config.selectServerDesc || 'Choose which server to use for this media file.';

        // Build server cards using DOM methods so opt.text is never parsed as HTML
        var cardsContainer = modalEl.querySelector('#serverPickerModalCards');
        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            if (!opt.value) { continue; }
            var type = serverTypes[opt.value] || 'legacy';
            var icon = getTypeIcon(type);
            var desc = getTypeDescription(type);
            var typeBadge = type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();
            var isSelected = (opt.value === currentValue);

            var col = document.createElement('div');
            col.className = 'col-md-4 mb-3';

            var card = document.createElement('div');
            card.className = 'card h-100 server-picker-card' + (isSelected ? ' border-primary' : '');
            card.setAttribute('role', 'button');
            card.dataset.serverId = opt.value;
            card.style.cssText = 'cursor:pointer;transition:border-color 0.2s,box-shadow 0.2s;';

            var cardBody = document.createElement('div');
            cardBody.className = 'card-body text-center';

            var iconSpan = document.createElement('span');
            iconSpan.className = icon;
            iconSpan.style.fontSize = '2.5rem';
            iconSpan.setAttribute('aria-hidden', 'true');
            cardBody.appendChild(iconSpan);

            var title = document.createElement('h5');
            title.className = 'card-title mt-2';
            title.textContent = opt.text;
            if (isSelected) {
                var badge = document.createElement('span');
                badge.className = 'badge bg-primary ms-1';
                badge.textContent = 'Current';
                title.appendChild(badge);
            }
            cardBody.appendChild(title);

            var typeBadgeEl = document.createElement('span');
            typeBadgeEl.className = 'badge bg-secondary';
            typeBadgeEl.textContent = typeBadge;
            cardBody.appendChild(typeBadgeEl);

            var descEl = document.createElement('p');
            descEl.className = 'card-text text-muted small mt-2';
            descEl.textContent = desc;
            cardBody.appendChild(descEl);

            card.appendChild(cardBody);
            col.appendChild(card);
            cardsContainer.appendChild(col);
        }
        var bsModal = new bootstrap.Modal(modalEl);

        // Bind card click events
        var cards = modalEl.querySelectorAll('.server-picker-card');
        cards.forEach(function(card) {
            function selectServer() {
                var serverId = card.dataset.serverId;

                // Check if changing type — confirm if addon already loaded
                var sTypes = getServerTypes();
                var newType = sTypes[serverId] || '';

                if (previousServerValue && previousServerType &&
                    previousServerType.toLowerCase() !== newType.toLowerCase()) {
                    var warning = config.switchWarning || 'Changing server type will reset the media options. Continue?';
                    if (!window.confirm(warning)) {
                        return;
                    }
                }

                serverField.value = serverId;
                previousServerValue = serverId;
                previousServerType = newType;

                // Update the display input and buttons
                updateServerDisplay();

                // Dispose the modal immediately (skip hide animation) to avoid
                // Bootstrap's aria-hidden focus conflict during the hide transition
                closeModal(bsModal, modalEl);

                // Same server — no need to reload
                if (serverId === currentValue) {
                    return;
                }

                loadAddonHtml(serverId);
            }

            card.addEventListener('click', selectServer);

            // Hover effects
            card.addEventListener('mouseenter', function() {
                this.style.borderColor = '#0d6efd';
                this.style.boxShadow = '0 0 0 0.2rem rgba(13,110,253,.25)';
            });
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('border-primary')) {
                    this.style.borderColor = '';
                }
                this.style.boxShadow = '';
            });
        });

        bsModal.show();
    }

    /**
     * Close and remove a Bootstrap modal without the hide animation.
     * Bootstrap's animated hide sets aria-hidden while the modal still holds
     * focus, triggering a browser a11y warning. Disposing directly avoids this.
     */
    function closeModal(bsModal, modalEl) {
        // Move focus out first
        var focusTarget = document.getElementById('jform_server_id_name') || document.body;
        focusTarget.focus();

        // Dispose removes event listeners and Bootstrap data
        bsModal.dispose();

        // Clean up DOM: modal element and any remaining backdrop/body class
        modalEl.remove();
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        var backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    /**
     * Override Joomla.submitbutton for form validation
     */
    Joomla.submitbutton = function(task) {
        if (task === 'cwmmediafile.setServer') {
            Joomla.submitform(task, document.getElementById('adminForm'));
        } else if (task === 'cwmmediafile.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        } else {
            Joomla.renderMessages({ error: [config.validationFailed || 'Please complete the form correctly.'] });
        }
    };

    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        getConfig();

        var serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return;
        }

        // Track initial state
        var serverTypes = getServerTypes();
        previousServerValue = serverField.value;
        previousServerType = serverTypes[serverField.value] || '';

        // Replace the <select> with an input-group (readonly input + Select/Clear buttons)
        convertSelectToInputGroup();

        // Show the modal immediately for new items with no server
        var form = document.getElementById('adminForm');
        if (form && form.dataset.showServerPicker === 'true') {
            setTimeout(function() {
                showServerPickerModal();
            }, 300);
        }
    });
})();
