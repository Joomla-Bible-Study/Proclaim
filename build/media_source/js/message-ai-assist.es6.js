/**
 * Message AI Assist — Topic suggestion and AI content generation
 *
 * Handles "Suggest Topics" (text matching) and "AI Assist" (API-generated
 * content) buttons on the sermon message edit form.
 *
 * Reads configuration from #message-ai-config data attributes:
 *   data-token     — Joomla session form token
 *   data-media-id  — First attached media file ID (for video context)
 *
 * @package  Proclaim
 * @since    10.1.0
 */
(() => {
    'use strict';

    const config = document.getElementById('message-ai-config');

    if (!config) {
        return;
    }

    const token      = config.dataset.token || '';
    const mediaId    = parseInt(config.dataset.mediaId, 10) || 0;
    const ajaxBase   = `index.php?option=com_proclaim&format=raw&${token}=1`;

    // ---- Helpers ----

    /**
     * Get content from a Joomla editor field (TinyMCE, etc.).
     *
     * @param {string} fieldName  Form field name (e.g. 'studyintro')
     * @returns {string}
     */
    function getEditorValue(fieldName) {
        if (window.Joomla && Joomla.editors && Joomla.editors.instances) {
            const editorId = `jform_${fieldName}`;
            const editor   = Joomla.editors.instances[editorId];

            if (editor && typeof editor.getValue === 'function') {
                return editor.getValue();
            }
        }

        const el = document.getElementById(`jform_${fieldName}`);

        return el ? el.value : '';
    }

    /**
     * Set content in a Joomla editor field.
     *
     * @param {string} fieldName  Form field name
     * @param {string} value      Content to set
     */
    function setEditorValue(fieldName, value) {
        const editorId = `jform_${fieldName}`;

        if (window.Joomla && Joomla.editors && Joomla.editors.instances) {
            const editor = Joomla.editors.instances[editorId];

            if (editor && typeof editor.setValue === 'function') {
                editor.setValue(value);

                return;
            }
        }

        const el = document.getElementById(editorId);

        if (el) {
            el.value = value;
        }
    }

    /**
     * Add a topic to the Choices.js fancy-select field.
     *
     * @param {string|number} value  Topic ID or new topic text
     * @param {string} label         Display label
     */
    function addTopicToField(value, label) {
        const selectEl = document.getElementById('jform_topics');

        if (!selectEl) {
            return;
        }

        const fancySelect = selectEl.closest('joomla-field-fancy-select');

        if (!fancySelect || !fancySelect.choicesInstance) {
            return;
        }

        const choices  = fancySelect.choicesInstance;
        const existing = choices.getValue();
        const ids      = Array.isArray(existing) ? existing.map((i) => String(i.value)) : [];

        if (ids.indexOf(String(value)) !== -1) {
            return;
        }

        choices.setChoices(
            [{ value: String(value), label, selected: true }],
            'value',
            'label',
            false,
        );

        // Sync hidden input
        const hiddenInput = document.getElementById('jform_topics_input');

        if (hiddenInput) {
            const items = choices.getValue();
            const vals  = Array.isArray(items) ? items.map((i) => i.value) : [];

            hiddenInput.value = vals.join(',');
        }
    }

    /**
     * Escape a string for safe insertion in HTML attributes.
     *
     * @param {string} str
     * @returns {string}
     */
    function escAttr(str) {
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // ---- Suggest Topics ----

    const btnSuggest = document.getElementById('btn-suggest-topics');

    if (btnSuggest) {
        btnSuggest.addEventListener('click', async () => {
            const panel   = document.getElementById('topic-suggestions-panel');
            const loading = document.getElementById('topic-suggestions-loading');
            const results = document.getElementById('topic-suggestions-results');

            panel.style.display   = 'block';
            loading.style.display = 'block';
            results.style.display = 'none';

            const formData = new FormData();
            formData.append('studyintro', getEditorValue('studyintro'));
            formData.append('studytext', getEditorValue('studytext'));

            try {
                const response = await fetch(`${ajaxBase}&task=cwmmessage.suggestTopics`, {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json();

                loading.style.display = 'none';
                results.style.display = 'block';

                const matchedSection  = document.getElementById('matched-topics-section');
                const matchedList     = document.getElementById('matched-topics-list');
                const keywordsSection = document.getElementById('suggested-keywords-section');
                const keywordsList    = document.getElementById('suggested-keywords-list');
                const noSuggestions   = document.getElementById('no-suggestions');

                matchedList.innerHTML  = '';
                keywordsList.innerHTML = '';

                if (data.existing && data.existing.length > 0) {
                    matchedSection.style.display = 'block';
                    data.existing.forEach((topic) => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        div.innerHTML = `<input class="form-check-input matched-topic-cb" type="checkbox" checked `
                            + `value="${escAttr(topic.id)}" data-label="${escAttr(topic.text)}" id="mt_${escAttr(topic.id)}">`
                            + `<label class="form-check-label" for="mt_${escAttr(topic.id)}">${escAttr(topic.text)}</label>`;
                        matchedList.appendChild(div);
                    });
                } else {
                    matchedSection.style.display = 'none';
                }

                if (data.suggested && data.suggested.length > 0) {
                    keywordsSection.style.display = 'block';
                    data.suggested.forEach((kw, idx) => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        div.innerHTML = `<input class="form-check-input keyword-cb" type="checkbox" `
                            + `value="${escAttr(kw.word)}" id="kw_${idx}">`
                            + `<label class="form-check-label" for="kw_${idx}">`
                            + `${escAttr(kw.word)} <span class="badge bg-secondary">${kw.count}</span></label>`;
                        keywordsList.appendChild(div);
                    });
                } else {
                    keywordsSection.style.display = 'none';
                }

                const hasResults = (data.existing && data.existing.length > 0)
                    || (data.suggested && data.suggested.length > 0);
                noSuggestions.style.display = hasResults ? 'none' : 'block';
            } catch {
                loading.style.display = 'none';
                results.style.display = 'block';
                document.getElementById('no-suggestions').style.display = 'block';
            }
        });
    }

    // Add matched topics button
    const btnAddMatched = document.getElementById('btn-add-matched');

    if (btnAddMatched) {
        btnAddMatched.addEventListener('click', () => {
            document.querySelectorAll('.matched-topic-cb:checked').forEach((cb) => {
                addTopicToField(cb.value, cb.dataset.label);
            });
        });
    }

    // Add keyword topics button
    const btnAddKeywords = document.getElementById('btn-add-keywords');

    if (btnAddKeywords) {
        btnAddKeywords.addEventListener('click', () => {
            document.querySelectorAll('.keyword-cb:checked').forEach((cb) => {
                addTopicToField(cb.value, cb.value);
            });
        });
    }

    // ---- AI Assist ----

    const btnAiAssist = document.getElementById('btn-ai-assist');

    if (btnAiAssist) {
        btnAiAssist.addEventListener('click', async () => {
            const modalEl  = document.getElementById('aiAssistModal');
            const modal    = new bootstrap.Modal(modalEl);
            const aiLoad   = document.getElementById('ai-loading');
            const aiErr    = document.getElementById('ai-error');
            const aiRes    = document.getElementById('ai-results');

            aiLoad.style.display = 'block';
            aiErr.style.display  = 'none';
            aiRes.style.display  = 'none';
            modal.show();

            const titleEl  = document.getElementById('jform_studytitle');
            const formData = new FormData();
            formData.append('title', titleEl ? titleEl.value : '');
            formData.append('studyintro', getEditorValue('studyintro'));
            formData.append('studytext', getEditorValue('studytext'));
            formData.append('media_file_id', mediaId);

            // Gather scripture text
            const scriptureEls = document.querySelectorAll('[id^="jform_scripture"]');
            const scriptureText = [];

            scriptureEls.forEach((el) => {
                if (el.value) {
                    scriptureText.push(el.value);
                }
            });
            formData.append('scripture', scriptureText.join('; '));

            // Gather current topics
            const hiddenTopics = document.getElementById('jform_topics_input');
            formData.append('topics', hiddenTopics ? hiddenTopics.value : '');

            try {
                const response = await fetch(`${ajaxBase}&task=cwmmessage.aiAssist`, {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json();

                aiLoad.style.display = 'none';

                if (data.error) {
                    aiErr.textContent    = data.error;
                    aiErr.style.display  = 'block';

                    return;
                }

                aiRes.style.display = 'block';

                // Populate AI topics
                const aiTopicsList = document.getElementById('ai-topics-list');
                aiTopicsList.innerHTML = '';

                if (data.topics && data.topics.length > 0) {
                    data.topics.forEach((topic, idx) => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        div.innerHTML = `<input class="form-check-input ai-topic-cb" type="checkbox" checked `
                            + `value="${escAttr(topic)}" id="ait_${idx}">`
                            + `<label class="form-check-label" for="ait_${idx}">${escAttr(topic)}</label>`;
                        aiTopicsList.appendChild(div);
                    });
                }

                // Populate AI description and study text
                document.getElementById('ai-studyintro').value = data.studyintro || '';
                document.getElementById('ai-studytext').value  = data.studytext || '';
            } catch (err) {
                aiLoad.style.display = 'none';
                aiErr.textContent    = err.message || 'Request failed';
                aiErr.style.display  = 'block';
            }
        });

        // AI: Add topics button
        document.getElementById('btn-ai-add-topics').addEventListener('click', () => {
            document.querySelectorAll('.ai-topic-cb:checked').forEach((cb) => {
                addTopicToField(cb.value, cb.value);
            });
        });

        // AI: Apply intro (only when user clicks — never auto-applied)
        document.getElementById('btn-ai-apply-intro').addEventListener('click', () => {
            const aiIntro = document.getElementById('ai-studyintro').value;

            if (aiIntro) {
                setEditorValue('studyintro', aiIntro);
            }
        });

        // AI: Apply study text (only when user clicks — never auto-applied)
        document.getElementById('btn-ai-apply-text').addEventListener('click', () => {
            const aiText = document.getElementById('ai-studytext').value;

            if (aiText) {
                setEditorValue('studytext', aiText);
            }
        });
    }
})();
