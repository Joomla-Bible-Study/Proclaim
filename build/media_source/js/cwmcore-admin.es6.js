/* globals _ */
/**
 * Admin-only utilities for Proclaim backend forms.
 *
 * Handles reference management, image chooser previews, and the
 * file size converter modal on the media file edit screen.
 *
 * @package    Proclaim.Admin
 * @since      10.3.0
 */
(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        // --- Reference management ---
        const addReferenceBtn = document.getElementById('addReference');
        if (addReferenceBtn) {
            addReferenceBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const referenceTemplate = document.getElementById('reference');
                if (!referenceTemplate) { return; }

                const newReference = referenceTemplate.cloneNode(true);

                const deleteButton = document.createElement('a');
                deleteButton.href = '#';
                deleteButton.className = 'referenceDelete';
                deleteButton.textContent = 'Delete';

                const textInput = newReference.querySelector('#text');
                if (textInput) {
                    textInput.value = '';
                    textInput.setAttribute('value', '');
                }

                const scriptureSelect = newReference.querySelector('#scripture');
                if (scriptureSelect) {
                    scriptureSelect.value = '0';
                }

                newReference.appendChild(deleteButton);
                const referencesContainer = document.getElementById('references');
                if (referencesContainer) {
                    referencesContainer.appendChild(newReference);
                }
            });
        }

        // Use event delegation for dynamically added delete buttons
        const referencesContainer = document.getElementById('references');
        if (referencesContainer) {
            referencesContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('referenceDelete')) {
                    e.preventDefault();
                    const parent = e.target.closest('#reference');
                    if (parent) {
                        parent.remove();
                    }
                }
            });
        }

        // --- Image chooser preview ---
        document.querySelectorAll('.imgChoose').forEach((el) => {
            el.addEventListener('change', function () {
                const targetImage = document.getElementById(`img${this.id}`);
                if (!targetImage) { return; }

                const src = targetImage.getAttribute('src');
                let activeDir = [];
                if (src) {
                    activeDir = src.split('/');
                    activeDir.pop();
                }

                if (parseInt(this.value, 10) === 0) {
                    targetImage.style.display = 'none';
                } else {
                    targetImage.style.display = '';
                }

                // Safely escape the value
                const escapeHtml = (str) => {
                    if (typeof _ !== 'undefined' && typeof _.escape === 'function') {
                        return _.escape(str);
                    }
                    const entityMap = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;',
                    };
                    return String(str).replace(/[&<>"']/g, (s) => entityMap[s]);
                };

                if (activeDir.length > 0) {
                    targetImage.setAttribute('src', `${activeDir.join('/')}/${escapeHtml(this.value)}`);
                }
            });
        });

        // --- File size converter modal ---
        const converterInput = document.getElementById('Text1');
        if (converterInput) {
            converterInput.addEventListener('change', function () {
                decOnly(this);
            });
        }

        const transferBtn = document.getElementById('btn-transfer-filesize');
        if (transferBtn) {
            transferBtn.addEventListener('click', () => {
                transferFileSize();
            });
        }
    });

    function decOnly(i) {
        let t = i.value;
        if (t.length > 0) {
            t = t.replace(/[^\d.]+/g, '');
        }

        const s = t.split('.');
        if (s.length > 1) {
            s[1] = `${s[0]}.${s[1]}`;
            s.shift();
        }

        i.value = s.join('');
    }

    function bandwidth(bytees, type) {
        let value = bytees;
        if (!Number.isNaN(Number(value)) && (value !== '')) {
            switch (type.toUpperCase()) {
                case 'KB':
                    value *= 1024;
                    break;
                case 'MB':
                    value *= 1024 ** 2;
                    break;
                case 'GB':
                    value *= 1024 ** 3;
                    break;
                default:
                    return 'error';
            }

            return parseInt(value, 10);
        }
        return 'error';
    }

    function transferFileSize() {
        const size = document.getElementById('Text1').value;
        const ty = document.getElementById('Select1').value;
        const ss = bandwidth(size, ty);
        if (ss === 'error') {
            if (typeof Joomla !== 'undefined' && Joomla.renderMessages) {
                Joomla.renderMessages({ warning: ['Numbers only please.'] });
            }
            return false;
        }
        document.getElementById('jform_params_size').value = ss;
        return true;
    }

}(window, window.document));
