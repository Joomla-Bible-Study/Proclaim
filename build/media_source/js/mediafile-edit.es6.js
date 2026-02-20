/**
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @description Media File Edit — AJAX server switching + server picker modal
 */
/* jshint esversion: 6 */
(function () {
    'use strict';

    let config = {};
    let previousServerValue = '';
    let previousServerType = '';
    let isLoading = false;

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
        const serverField = document.getElementById('jform_server_id');
        if (!serverField || !serverField.dataset.serverTypes) {
            return {};
        }
        try {
            return JSON.parse(serverField.dataset.serverTypes);
        } catch {
            return {};
        }
    }

    /**
     * Get server type descriptions based on type name
     */
    function getTypeDescription(type) {
        const typeLower = (type || '').toLowerCase();
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
        const icons = {
            local:   'fas fa-server',
            youtube: 'fab fa-youtube',
            vimeo:   'fab fa-vimeo',
            wistia:  'fas fa-play-circle',
            resi:    'fas fa-signal',
            legacy:  'fas fa-archive',
        };
        return icons[(type || '').toLowerCase()] || 'fas fa-cloud';
    }

    /**
     * Get the display name for the currently selected server
     */
    function getSelectedServerName() {
        const serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return '';
        }
        const selected = serverField.options[serverField.selectedIndex];
        return (selected && selected.value) ? selected.text : '';
    }

    /**
     * Replace the <select> with an input-group matching the Modal_Study field pattern:
     * readonly text input + Select button + Clear button.
     * The hidden <select> keeps its value for form submission.
     */
    function convertSelectToInputGroup() {
        const serverField = document.getElementById('jform_server_id');
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
        const choicesWrapper = serverField.closest('.choices');
        if (choicesWrapper) {
            choicesWrapper.style.display = 'none';
        }

        const { value } = serverField;
        const name = getSelectedServerName();
        const hasValue = !!(value && name);
        const displayText = hasValue ? name : (config.selectServerTitle || 'Select a Server');

        // Build the input-group (same pattern as StudyField / Modal_Study)
        const group = document.createElement('span');
        group.className = 'input-group';
        group.id = 'server-picker-group';

        // Readonly text input showing server name
        const input = document.createElement('input');
        input.className = 'form-control';
        input.id = 'jform_server_id_name';
        input.type = 'text';
        input.value = displayText;
        input.readOnly = true;
        input.style.cursor = 'pointer';
        input.addEventListener('click', () => {
            showServerPickerModal();
        });

        // Select button (hidden when a value is set)
        const selectBtn = document.createElement('button');
        selectBtn.type = 'button';
        selectBtn.id = 'jform_server_id_select';
        selectBtn.className = `btn btn-primary${hasValue ? ' hidden' : ''}`;
        selectBtn.innerHTML = `<span class="icon-file" aria-hidden="true"></span> ${
            config.selectLabel || 'Select'}`;
        selectBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showServerPickerModal();
        });

        // Clear button (hidden when no value)
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.id = 'jform_server_id_clear';
        clearBtn.className = `btn btn-secondary${hasValue ? '' : ' hidden'}`;
        clearBtn.innerHTML = `<span class="icon-times" aria-hidden="true"></span> ${
            config.clearLabel || 'Clear'}`;
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            clearServer();
        });

        group.appendChild(input);
        group.appendChild(selectBtn);
        group.appendChild(clearBtn);

        // Insert the group after the select (or its wrapper)
        const insertAfter = choicesWrapper || serverField;
        insertAfter.parentNode.insertBefore(group, insertAfter.nextSibling);
    }

    /**
     * Update the display input and button visibility to match the current selection
     */
    function updateServerDisplay() {
        const serverField = document.getElementById('jform_server_id');
        const nameInput = document.getElementById('jform_server_id_name');
        const selectBtn = document.getElementById('jform_server_id_select');
        const clearBtn = document.getElementById('jform_server_id_clear');

        if (!serverField || !nameInput) {
            return;
        }

        const { value } = serverField;
        const name = getSelectedServerName();
        const hasValue = !!(value && name);

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
        const serverField = document.getElementById('jform_server_id');
        if (serverField) {
            serverField.value = '';
        }

        previousServerValue = '';
        previousServerType = '';

        updateServerDisplay();

        // Clear addon containers
        const generalContainer = document.getElementById('addon-general-container');
        if (generalContainer) {
            generalContainer.innerHTML = '';
        }
        const optionsContent = document.getElementById('addon-options-content');
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
        const generalContainer = document.getElementById('addon-general-container');
        if (generalContainer) {
            generalContainer.innerHTML = '<div class="text-center p-4"><span class="spinner-border" role="status"></span>'
                + `<p class="mt-2 text-muted">${config.switchLoading || 'Switching server...'}</p></div>`;
        }
        const optionsContent = document.getElementById('addon-options-content');
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

        const url = `index.php?option=com_proclaim&task=cwmmediafile.getAddonHtml&server_id=${
            encodeURIComponent(serverId)}&${encodeURIComponent(config.token)}=1`;

        Joomla.request({
            url,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            onSuccess(response) {
                isLoading = false;
                try {
                    const data = JSON.parse(response);
                    if (data.success) {
                        const generalContainer = document.getElementById('addon-general-container');
                        if (generalContainer) {
                            generalContainer.innerHTML = data.generalHtml || '';
                        }
                        const optionsContent = document.getElementById('addon-options-content');
                        if (optionsContent) {
                            optionsContent.innerHTML = data.optionsHtml || '';
                        }

                        // Update tracked server type
                        const serverTypes = getServerTypes();
                        previousServerType = serverTypes[serverId] || '';
                        previousServerValue = serverId;

                        // Re-initialize custom fields in the updated containers.
                        // Dispatch on each container (not the whole form) to avoid
                        // showon.js errors when controlling fields aren't in scope.
                        [generalContainer, optionsContent].forEach((el) => {
                            if (el && el.innerHTML) {
                                el.dispatchEvent(new CustomEvent('joomla:updated', {
                                    bubbles: true,
                                    cancelable: true,
                                    detail: { container: el },
                                }));
                            }
                        });
                    } else {
                        showError(data.error || 'Failed to load server configuration');
                    }
                } catch {
                    showError('Invalid response from server');
                }
            },
            onError() {
                isLoading = false;
                showError('Network error. The server change will use a page reload instead.');
                // Fallback to setServer
                Joomla.submitbutton('cwmmediafile.setServer');
            },
        });
    }

    /**
     * Show error in the addon general container
     */
    function showError(message) {
        const generalContainer = document.getElementById('addon-general-container');
        if (generalContainer) {
            generalContainer.innerHTML = '<div class="alert alert-danger">'
                + `<span class="icon-warning" aria-hidden="true"></span> ${message}</div>`;
        }
    }

    /**
     * Create and show the server picker modal
     */
    function showServerPickerModal() {
        const serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return;
        }

        const serverTypes = getServerTypes();
        const { options } = serverField;
        const currentValue = serverField.value;

        // Remove existing modal if present
        const existingModal = document.getElementById('serverPickerModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Determine if this is first selection (no addon loaded) — use static backdrop
        const hasAddon = previousServerValue && previousServerType;
        const backdropAttr = hasAddon ? '' : ' data-bs-backdrop="static" data-bs-keyboard="false"';

        // Insert static modal shell (no user content) then populate via DOM to prevent XSS
        document.body.insertAdjacentHTML(
            'beforeend',
            `<div class="modal fade" id="serverPickerModal" tabindex="-1"${backdropAttr}>`
            + '<div class="modal-dialog modal-lg modal-dialog-centered">'
            + '<div class="modal-content">'
            + '<div class="modal-header">'
            + `<h5 class="modal-title" id="serverPickerModalTitle"></h5>${
                hasAddon ? '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' : ''
            }</div>`
            + '<div class="modal-body">'
            + '<p class="text-muted" id="serverPickerModalDesc"></p>'
            + '<div class="row" id="serverPickerModalCards"></div>'
            + '</div></div></div></div>',
        );

        const modalEl = document.getElementById('serverPickerModal');

        // Populate text content safely
        const titleEl = modalEl.querySelector('#serverPickerModalTitle');
        const titleIcon = document.createElement('span');
        titleIcon.className = 'icon-server';
        titleIcon.setAttribute('aria-hidden', 'true');
        titleEl.appendChild(titleIcon);
        titleEl.appendChild(document.createTextNode(` ${config.selectServerTitle || 'Select a Server'}`));
        modalEl.querySelector('#serverPickerModalDesc').textContent = config.selectServerDesc || 'Choose which server to use for this media file.';

        // Build server cards using DOM methods so opt.text is never parsed as HTML
        const cardsContainer = modalEl.querySelector('#serverPickerModalCards');
        for (let i = 0; i < options.length; i++) {
            const opt = options[i];
            if (!opt.value) { continue; }
            const type = serverTypes[opt.value] || 'legacy';
            const icon = getTypeIcon(type);
            const desc = getTypeDescription(type);
            const typeBadge = type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();
            const isSelected = (opt.value === currentValue);

            const col = document.createElement('div');
            col.className = 'col-md-4 mb-3';

            const card = document.createElement('div');
            card.className = `card h-100 server-picker-card${isSelected ? ' border-primary' : ''}`;
            card.setAttribute('role', 'button');
            card.dataset.serverId = opt.value;
            card.style.cssText = 'cursor:pointer;transition:border-color 0.2s,box-shadow 0.2s;';

            const cardBody = document.createElement('div');
            cardBody.className = 'card-body text-center';

            const iconSpan = document.createElement('span');
            iconSpan.className = icon;
            iconSpan.style.fontSize = '2.5rem';
            iconSpan.setAttribute('aria-hidden', 'true');
            cardBody.appendChild(iconSpan);

            const title = document.createElement('h5');
            title.className = 'card-title mt-2';
            title.textContent = opt.text;
            if (isSelected) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary ms-1';
                badge.textContent = 'Current';
                title.appendChild(badge);
            }
            cardBody.appendChild(title);

            const typeBadgeEl = document.createElement('span');
            typeBadgeEl.className = 'badge bg-secondary';
            typeBadgeEl.textContent = typeBadge;
            cardBody.appendChild(typeBadgeEl);

            const descEl = document.createElement('p');
            descEl.className = 'card-text text-muted small mt-2';
            descEl.textContent = desc;
            cardBody.appendChild(descEl);

            card.appendChild(cardBody);
            col.appendChild(card);
            cardsContainer.appendChild(col);
        }
        const bsModal = new bootstrap.Modal(modalEl);

        // Bind card click events
        const cards = modalEl.querySelectorAll('.server-picker-card');
        cards.forEach((card) => {
            function selectServer() {
                const { serverId } = card.dataset;

                // Check if changing type — confirm if addon already loaded
                const sTypes = getServerTypes();
                const newType = sTypes[serverId] || '';

                if (previousServerValue && previousServerType
                    && previousServerType.toLowerCase() !== newType.toLowerCase()) {
                    const warning = config.switchWarning || 'Changing server type will reset the media options. Continue?';
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
            card.addEventListener('mouseenter', function () {
                this.style.borderColor = '#0d6efd';
                this.style.boxShadow = '0 0 0 0.2rem rgba(13,110,253,.25)';
            });
            card.addEventListener('mouseleave', function () {
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
        const focusTarget = document.getElementById('jform_server_id_name') || document.body;
        focusTarget.focus();

        // Dispose removes event listeners and Bootstrap data
        bsModal.dispose();

        // Clean up DOM: modal element and any remaining backdrop/body class
        modalEl.remove();
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    /**
     * Override Joomla.submitbutton for form validation
     */
    Joomla.submitbutton = function (task) {
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
    document.addEventListener('DOMContentLoaded', () => {
        getConfig();

        const serverField = document.getElementById('jform_server_id');
        if (!serverField) {
            return;
        }

        // Track initial state
        const serverTypes = getServerTypes();
        previousServerValue = serverField.value;
        previousServerType = serverTypes[serverField.value] || '';

        // Replace the <select> with an input-group (readonly input + Select/Clear buttons)
        convertSelectToInputGroup();

        // Show the modal immediately for new items with no server
        const form = document.getElementById('adminForm');
        if (form && form.dataset.showServerPicker === 'true') {
            setTimeout(() => {
                showServerPickerModal();
            }, 300);
        }
    });
}());
