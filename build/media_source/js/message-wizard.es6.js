/**
 * Message Wizard — Step navigation, validation, and review summary.
 *
 * @package  Proclaim
 * @since    10.3.0
 */
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const TOTAL_STEPS = 6;
    let currentStep = 1;

    const steps = document.querySelectorAll('.wizard-step');
    const indicators = document.querySelectorAll('.wizard-step-indicator');
    const progressBar = document.getElementById('wizard-progress-bar');
    const prevBtn = document.getElementById('wizard-prev-btn');
    const nextBtn = document.getElementById('wizard-next-btn');
    const saveBtn = document.getElementById('wizard-save-btn');

    if (!steps.length || !prevBtn || !nextBtn || !saveBtn) {
        return;
    }

    /**
     * Show the given step and update UI state.
     *
     * @param {number} step  Step number (1-based)
     */
    function goToStep(step) {
        if (step < 1 || step > TOTAL_STEPS) {
            return;
        }

        currentStep = step;

        // Show/hide step panels
        steps.forEach((el) => {
            el.classList.toggle('active', parseInt(el.dataset.step, 10) === step);
        });

        // Update step indicators
        indicators.forEach((el) => {
            const s = parseInt(el.dataset.step, 10);
            el.classList.toggle('active', s === step);
            el.classList.toggle('completed', s < step);
        });

        // Update progress bar
        const pct = (step / TOTAL_STEPS) * 100;
        progressBar.style.width = pct + '%';
        progressBar.setAttribute('aria-valuenow', step);

        // Button states
        prevBtn.disabled = (step === 1);

        if (step === TOTAL_STEPS) {
            nextBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
            buildReviewSummary();
        } else {
            nextBtn.classList.remove('d-none');
            saveBtn.classList.add('d-none');
        }

        // Lazy-init TinyMCE editor when step 5 becomes visible
        if (step === 5) {
            initEditorIfNeeded();
        }

        // Scroll to top of form
        document.getElementById('message-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Validate the current step before advancing.
     *
     * @returns {boolean}
     */
    function validateCurrentStep() {
        if (currentStep === 1) {
            const title = document.getElementById('jform_studytitle');

            if (!title || !title.value.trim()) {
                title.classList.add('invalid');
                title.focus();

                return false;
            }

            title.classList.remove('invalid');
        }

        return true;
    }

    /**
     * Trigger TinyMCE initialization if the editor hasn't loaded yet.
     */
    function initEditorIfNeeded() {
        const editorEl = document.getElementById('jform_studyintro');

        if (!editorEl) {
            return;
        }

        // Joomla 6: JoomlaEditor API
        if (typeof JoomlaEditor !== 'undefined') {
            const editor = JoomlaEditor.get('jform_studyintro');

            if (editor) {
                return;
            }
        }

        // Trigger TinyMCE init via Joomla's custom event
        editorEl.dispatchEvent(new CustomEvent('joomla:updated', { bubbles: true }));
    }

    /**
     * Build the review summary from current form values.
     */
    function buildReviewSummary() {
        const container = document.getElementById('wizard-review-summary');

        if (!container) {
            return;
        }

        const getValue = (id) => {
            const el = document.getElementById(id);

            return el ? el.value.trim() : '';
        };

        const getSelectedText = (id) => {
            const el = document.getElementById(id);

            if (!el) {
                return '';
            }

            if (el.selectedIndex >= 0 && el.options[el.selectedIndex]) {
                return el.options[el.selectedIndex].text;
            }

            return el.value || '';
        };

        // Gather teachers from subform
        const teacherNames = [];
        document.querySelectorAll('[name*="[teacher_id]"]').forEach((sel) => {
            if (sel.value && sel.selectedIndex >= 0 && sel.options[sel.selectedIndex]) {
                const text = sel.options[sel.selectedIndex].text;

                if (text && !text.startsWith('-')) {
                    teacherNames.push(text);
                }
            }
        });

        // Gather scriptures from subform
        const scriptureRefs = [];
        document.querySelectorAll('.scripture-autocomplete-input').forEach((input) => {
            if (input.value.trim()) {
                scriptureRefs.push(input.value.trim());
            }
        });

        // Get editor content for description
        let introText = '';

        if (typeof JoomlaEditor !== 'undefined') {
            const editor = JoomlaEditor.get('jform_studyintro');

            if (editor && typeof editor.getValue === 'function') {
                introText = editor.getValue();
            }
        }

        if (!introText) {
            introText = getValue('jform_studyintro');
        }

        // Strip HTML tags for preview using regex (no DOM parsing needed)
        let introPreview = introText.replace(/<[^>]*>/g, '');

        // Decode HTML entities using DOM to avoid double-unescaping issues
        const decodeHtml = (str) => {
            const d = document.createElement('textarea');
            d.innerHTML = str;

            return d.value;
        };

        introPreview = decodeHtml(introPreview);
        introPreview = introPreview.substring(0, 200);

        const escHtml = (str) => {
            const d = document.createElement('div');
            d.textContent = str;

            return d.innerHTML;
        };

        let html = '<dl class="row mb-0">';
        html += '<dt class="col-sm-3">' + (Joomla.Text._('JBS_CMN_TITLE') || 'Title') + '</dt>';
        html += '<dd class="col-sm-9">' + escHtml(getValue('jform_studytitle') || '—') + '</dd>';

        html += '<dt class="col-sm-3">' + (Joomla.Text._('JBS_CMN_STUDY_DATE') || 'Date') + '</dt>';
        html += '<dd class="col-sm-9">' + escHtml(getValue('jform_studydate') || '—') + '</dd>';

        html += '<dt class="col-sm-3">' + (Joomla.Text._('JBS_CMN_TEACHERS') || 'Teachers') + '</dt>';
        html += '<dd class="col-sm-9">' + escHtml(teacherNames.join(', ') || '—') + '</dd>';

        // Series — get text from the modal field display
        const seriesDisplay = document.getElementById('jform_series_id_name');
        const seriesText = seriesDisplay ? seriesDisplay.value.trim() : '';
        html += '<dt class="col-sm-3">' + (Joomla.Text._('JBS_CMN_SERIES') || 'Series') + '</dt>';
        html += '<dd class="col-sm-9">' + escHtml(seriesText || '—') + '</dd>';

        html += '<dt class="col-sm-3">' + (Joomla.Text._('JBS_CMN_SCRIPTURE') || 'Scripture') + '</dt>';
        html += '<dd class="col-sm-9">' + escHtml(scriptureRefs.join('; ') || '—') + '</dd>';

        if (introPreview) {
            html += '<dt class="col-sm-3">' + (Joomla.Text._('JBS_CMN_STUDYINTRO') || 'Description') + '</dt>';
            html += '<dd class="col-sm-9">' + escHtml(introPreview) + (introPreview.length >= 200 ? '...' : '') + '</dd>';
        }

        html += '</dl>';

        container.innerHTML = html;
    }

    // Event listeners
    prevBtn.addEventListener('click', () => {
        goToStep(currentStep - 1);
    });

    nextBtn.addEventListener('click', () => {
        if (validateCurrentStep()) {
            goToStep(currentStep + 1);
        }
    });

    saveBtn.addEventListener('click', () => {
        document.querySelector('[name="task"]').value = 'cwmmessage.save';
        document.getElementById('message-form').submit();
    });

    // Keyboard: Enter advances to next step (not submit)
    document.getElementById('message-form').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && currentStep < TOTAL_STEPS) {
            e.preventDefault();
            if (validateCurrentStep()) {
                goToStep(currentStep + 1);
            }
        }
    });
});
