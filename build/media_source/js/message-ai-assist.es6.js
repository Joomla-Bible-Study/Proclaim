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

    const token          = config.dataset.token || '';
    const mediaId        = parseInt(config.dataset.mediaId, 10) || 0;
    const youtubeMediaId = parseInt(config.dataset.youtubeMediaId, 10) || 0;
    const ajaxBase       = `index.php?option=com_proclaim&format=raw&${token}=1`;

    // ---- Helpers ----

    /**
     * Get content from a Joomla editor field (TinyMCE, etc.).
     *
     * @param {string} fieldName  Form field name (e.g. 'studyintro')
     * @returns {string}
     */
    function getEditorValue(fieldName) {
        const editorId = `jform_${fieldName}`;

        // Joomla 6+: JoomlaEditor API (non-deprecated)
        if (typeof JoomlaEditor !== 'undefined') {
            const editor = JoomlaEditor.get(editorId);

            if (editor && typeof editor.getValue === 'function') {
                return editor.getValue();
            }
        }

        const el = document.getElementById(editorId);

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

        // Joomla 6+: JoomlaEditor API (non-deprecated)
        if (typeof JoomlaEditor !== 'undefined') {
            const editor = JoomlaEditor.get(editorId);

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
    /**
     * Sync the hidden topics input from current Choices.js selections.
     */
    function syncTopicsHiddenInput() {
        const selectEl = document.getElementById('jform_topics');

        if (!selectEl) {
            return;
        }

        const fancySelect = selectEl.closest('joomla-field-fancy-select');

        if (!fancySelect || !fancySelect.choicesInstance) {
            return;
        }

        const hiddenInput = document.getElementById('jform_topics_input');

        if (hiddenInput) {
            const items = fancySelect.choicesInstance.getValue();
            const vals  = Array.isArray(items) ? items.map((i) => i.value) : [];

            hiddenInput.value = vals.join(',');
        }
    }

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

        // Try to match by label against existing options (case-insensitive)
        // so we use the numeric topic ID instead of the text name
        const allChoices   = choices._store.choices || [];
        const labelLower   = String(label).toLowerCase();
        const matchedChoice = allChoices.find(
            (c) => c.label && c.label.toLowerCase() === labelLower,
        );

        const resolvedValue = matchedChoice ? String(matchedChoice.value) : String(value);

        // Check if already selected
        const existing = choices.getValue();
        const ids      = Array.isArray(existing) ? existing.map((i) => String(i.value)) : [];

        if (ids.indexOf(resolvedValue) !== -1) {
            return;
        }

        if (matchedChoice) {
            // Select the existing option by its numeric ID
            choices.setValue([{ value: resolvedValue, label: matchedChoice.label }]);
        } else {
            // New topic — add as text value (backend will create it)
            choices.setChoices(
                [{ value: resolvedValue, label, selected: true }],
                'value',
                'label',
                false,
            );
        }
    }

    // Safety net: sync hidden input before form submission
    const topicsSelect = document.getElementById('jform_topics');

    if (topicsSelect) {
        const form = topicsSelect.closest('form');

        if (form) {
            form.addEventListener('submit', () => syncTopicsHiddenInput(), true);
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

    /**
     * Flash a success badge next to a button to confirm the action.
     *
     * @param {HTMLElement} btn  The button element
     * @param {string} message   Feedback text
     */
    function showAppliedFeedback(btn, message) {
        const existing = btn.parentNode.querySelector('.ai-applied-badge');

        if (existing) {
            existing.remove();
        }

        const badge = document.createElement('span');
        badge.className = 'ai-applied-badge badge bg-success ms-2';
        badge.innerHTML = `<span class="icon-check" aria-hidden="true"></span> ${message}`;
        btn.insertAdjacentElement('afterend', badge);

        setTimeout(() => badge.remove(), 3000);
    }

    /**
     * Parse chapter text lines (e.g. "0:00 Introduction") into structured array.
     *
     * @param {string} text  Multi-line chapter text
     * @returns {Array<{time: string, label: string}>}
     */
    function parseChaptersText(text) {
        if (!text) {
            return [];
        }

        const chapters = [];

        text.split('\n').forEach((line) => {
            const match = line.trim().match(/^(\d{1,2}:\d{2}(?::\d{2})?)\s+(.+)$/);

            if (match) {
                chapters.push({ time: match[1], label: match[2].trim() });
            }
        });

        return chapters;
    }

    /**
     * Save chapters to the media file via AJAX and show feedback.
     *
     * @param {HTMLElement} btn       The button that was clicked
     * @param {HTMLTextAreaElement} textarea  The chapters textarea
     */
    function applyChaptersToMedia(btn, textarea) {
        // Use YouTube media ID when available (chapters from YouTube apply to that file)
        const targetMediaId = youtubeMediaId || mediaId;

        if (!textarea || !textarea.value || !targetMediaId) {
            return;
        }

        const chapters = parseChaptersText(textarea.value);

        if (!chapters.length) {
            return;
        }

        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        fetch(`${ajaxBase}&task=cwmmediafile.saveChapters&media_id=${targetMediaId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ chapters }),
        })
            .then((r) => r.json())
            .then((data) => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                if (data.success) {
                    showAppliedFeedback(
                        btn,
                        (Joomla.Text._('JBS_CMN_AI_CHAPTERS_APPLIED') || '{count} chapters saved')
                            .replace('{count}', data.count),
                    );
                } else {
                    showAppliedFeedback(btn, data.error || 'Error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
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

    /**
     * Build progress steps based on which fields are being generated.
     * Each entry is [delay_ms, progress_percent, message].
     *
     * @param {boolean} topics    Generating topics
     * @param {boolean} intro     Generating intro
     * @param {boolean} text      Generating study text
     * @param {boolean} chapters  Generating chapters
     * @returns {Array}
     */
    function buildProgressSteps(topics, intro, text, chapters) {
        const steps = [
            [0,    5,  'Gathering sermon context...'],
            [1200, 15, 'Reading title and scripture references...'],
            [2500, 25, 'Analyzing attached media...'],
            [4000, 35, 'Sending to AI provider...'],
        ];

        let delay = 6000;
        let pct   = 40;

        if (topics) {
            steps.push([delay, pct, 'Generating topics...']);
            delay += 2500;
            pct   += 10;
        }

        if (intro) {
            steps.push([delay, pct, 'Writing description...']);
            delay += 3000;
            pct   += 10;
        }

        if (text) {
            steps.push([delay, pct, 'Writing study text (this may take a moment)...']);
            delay += 8000;
            pct   += 15;
            steps.push([delay, pct, 'Expanding key points and application...']);
            delay += 6000;
            pct   += 5;
        }

        if (chapters) {
            steps.push([delay, pct, 'Suggesting chapter timestamps...']);
            delay += 3000;
            pct   += 5;
        }

        steps.push([delay, Math.min(pct + 5, 90), 'Formatting response...']);
        steps.push([delay + 4000, 92, 'Almost done...']);
        steps.push([delay + 10000, 94, 'Still working...']);
        steps.push([delay + 18000, 96, 'Wrapping up...']);

        return steps;
    }

    /**
     * Add a completed step to the progress list.
     *
     * @param {HTMLElement} container  The steps container element
     * @param {string} msg            Step message
     */
    function addStep(container, msg) {
        const div = document.createElement('div');
        div.className = 'text-body-secondary small mb-1';
        div.style.opacity = '0';
        div.style.transition = 'opacity 0.3s ease-in';
        div.innerHTML = '<span class="icon-check text-success me-1" aria-hidden="true"></span>' + msg;
        container.appendChild(div);

        // Trigger fade-in on next frame
        requestAnimationFrame(() => {
            div.style.opacity = '1';
        });
    }

    /**
     * Start the progress animation. Returns a stop function.
     *
     * @param {Array} steps  Progress steps from buildProgressSteps()
     * @returns {Function}  Call to stop and clean up timers.
     */
    function startProgress(steps) {
        const bar       = document.getElementById('ai-progress-bar');
        const text      = document.getElementById('ai-progress-text');
        const container = document.getElementById('ai-progress-steps');
        let stopped     = false;

        if (bar) {
            bar.style.transition = 'width 0.6s ease';
            bar.style.width      = '5%';
        }

        if (container) {
            container.innerHTML = '';
        }

        const timers = [];

        steps.forEach(([delay, pct, msg]) => {
            const tid = setTimeout(() => {
                if (stopped) {
                    return;
                }

                if (bar) {
                    bar.style.width = `${pct}%`;
                }

                if (text) {
                    text.textContent = msg;
                }

                if (container) {
                    addStep(container, msg);
                }
            }, delay);
            timers.push(tid);
        });

        // After all scripted steps finish, start a repeating "pulse" so the
        // UI never looks frozen — the bar creeps toward 99% and the message
        // cycles through reassuring phrases.
        const lastDelay = steps.length > 0 ? steps[steps.length - 1][0] : 0;
        const pulseMessages = [
            'AI is composing a detailed response...',
            'Reviewing scripture references...',
            'Crafting key points and application...',
            'Polishing the final text...',
            'Almost there, just a moment longer...',
        ];
        let pulseIndex = 0;
        let pulsePct   = 96;

        // Start the pulse loop after all scripted steps are done
        let pulseInterval = null;
        const pulseStart = setTimeout(() => {
            if (stopped) {
                return;
            }

            pulseInterval = setInterval(() => {
                if (stopped) {
                    clearInterval(pulseInterval);

                    return;
                }

                pulsePct = Math.min(pulsePct + 0.5, 99);

                if (bar) {
                    bar.style.width = `${pulsePct}%`;
                }

                if (text) {
                    text.textContent = pulseMessages[pulseIndex % pulseMessages.length];
                }

                pulseIndex++;
            }, 8000);
        }, lastDelay + 5000);
        timers.push(pulseStart);

        return function stop() {
            stopped = true;
            timers.forEach(clearTimeout);
            clearInterval(pulseInterval);

            if (bar) {
                bar.style.width = '100%';
            }
        };
    }

    const btnAiAssist = document.getElementById('btn-ai-assist');

    if (btnAiAssist) {
        btnAiAssist.addEventListener('click', async () => {
            // Read toggle checkbox states
            const genTopics   = document.getElementById('ai-gen-topics');
            const genIntro    = document.getElementById('ai-gen-intro');
            const genText     = document.getElementById('ai-gen-text');
            const genChapters = document.getElementById('ai-gen-chapters');
            const wantTopics   = genTopics ? genTopics.checked : true;
            const wantIntro    = genIntro ? genIntro.checked : true;
            const wantText     = genText ? genText.checked : true;
            const wantChapters = genChapters ? genChapters.checked : true;

            // Validate at least one checked
            if (!wantTopics && !wantIntro && !wantText && !wantChapters) {
                alert(Joomla.Text._('JBS_CMN_AI_SELECT_ONE') || 'Select at least one field to generate.');

                return;
            }

            const modalEl  = document.getElementById('aiAssistModal');
            const modal    = new bootstrap.Modal(modalEl);
            const aiLoad   = document.getElementById('ai-loading');
            const aiErr    = document.getElementById('ai-error');
            const aiRes    = document.getElementById('ai-results');

            aiLoad.style.display = 'block';
            aiErr.style.display  = 'none';
            aiRes.style.display  = 'none';
            modal.show();

            const stopProgress = startProgress(
                buildProgressSteps(wantTopics, wantIntro, wantText, wantChapters),
            );

            const titleEl  = document.getElementById('jform_studytitle');
            const formData = new FormData();
            formData.append('title', titleEl ? titleEl.value : '');
            formData.append('studyintro', getEditorValue('studyintro'));
            formData.append('studytext', getEditorValue('studytext'));
            // Prefer YouTube media file for video context (chapters, tags, description)
            formData.append('media_file_id', youtubeMediaId || mediaId);

            // Send teacher ID so the server can look up the teacher name for AI voice
            const teacherEl = document.getElementById('jform_teacher_id');
            formData.append('teacher_id', teacherEl ? teacherEl.value : '0');

            // Append toggle flags
            formData.append('generate_topics', wantTopics ? '1' : '0');
            formData.append('generate_intro', wantIntro ? '1' : '0');
            formData.append('generate_text', wantText ? '1' : '0');
            formData.append('generate_chapters', wantChapters ? '1' : '0');

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

                stopProgress();
                aiLoad.style.display = 'none';

                if (data.error) {
                    aiErr.textContent    = data.error;
                    aiErr.style.display  = 'block';

                    return;
                }

                aiRes.style.display = 'block';

                // Show/hide sections based on toggle state
                const topicsSection  = document.getElementById('ai-topics-section');
                const introSection   = document.getElementById('ai-intro-section');
                const textSection    = document.getElementById('ai-text-section');
                const chaptersSection = document.getElementById('ai-chapters-section');

                if (topicsSection) {
                    topicsSection.style.display = wantTopics ? '' : 'none';
                }

                if (introSection) {
                    introSection.style.display = wantIntro ? '' : 'none';
                }

                if (textSection) {
                    textSection.style.display = wantText ? '' : 'none';
                }

                // Populate AI topics
                const aiTopicsList = document.getElementById('ai-topics-list');
                aiTopicsList.innerHTML = '';

                if (wantTopics && data.topics && data.topics.length > 0) {
                    data.topics.forEach((topic, idx) => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        div.innerHTML = `<input class="form-check-input ai-topic-cb" type="checkbox" checked `
                            + `value="${escAttr(topic)}" id="ait_${idx}">`
                            + `<label class="form-check-label" for="ait_${idx}">${escAttr(topic)}</label>`;
                        aiTopicsList.appendChild(div);
                    });
                }

                // Populate AI description and study text (hidden inputs + rendered previews)
                document.getElementById('ai-studyintro').value = data.studyintro || '';
                document.getElementById('ai-studytext').value  = data.studytext || '';

                const introPreview = document.getElementById('ai-studyintro-preview');
                const textPreview  = document.getElementById('ai-studytext-preview');

                if (introPreview) {
                    introPreview.innerHTML = data.studyintro || '';
                }

                if (textPreview) {
                    textPreview.innerHTML = data.studytext || '';
                }

                // Handle suggested chapters (only show if user opted in)
                if (chaptersSection) {
                    if (wantChapters && data.chapters && data.chapters.length > 0) {
                        const chapterLines = data.chapters.map(
                            (ch) => `${ch.time} ${ch.label}`,
                        );
                        const chaptersTextarea = document.getElementById('ai-chapters-text');

                        if (chaptersTextarea) {
                            chaptersTextarea.value = chapterLines.join('\n');
                        }

                        chaptersSection.style.display = '';
                    } else {
                        chaptersSection.style.display = 'none';
                    }
                }
            } catch (err) {
                stopProgress();
                aiLoad.style.display = 'none';
                aiErr.textContent    = err.message || 'Request failed';
                aiErr.style.display  = 'block';
            }
        });

        // AI: Add topics button
        const btnAddTopics = document.getElementById('btn-ai-add-topics');
        btnAddTopics.addEventListener('click', () => {
            const checked = document.querySelectorAll('.ai-topic-cb:checked');
            let count = 0;

            checked.forEach((cb) => {
                addTopicToField(cb.value, cb.value);
                count += 1;
            });

            if (count > 0) {
                // Allow Choices.js addItem events to settle, then sync hidden input
                setTimeout(() => syncTopicsHiddenInput(), 150);
                showAppliedFeedback(btnAddTopics, `${count} topic${count > 1 ? 's' : ''} added`);
            }
        });

        // AI: Apply intro (only when user clicks — never auto-applied)
        const btnApplyIntro = document.getElementById('btn-ai-apply-intro');
        btnApplyIntro.addEventListener('click', () => {
            const aiIntro = document.getElementById('ai-studyintro').value;

            if (aiIntro) {
                setEditorValue('studyintro', aiIntro);
                showAppliedFeedback(btnApplyIntro, 'Applied');
            }
        });

        // AI: Apply study text (only when user clicks — never auto-applied)
        const btnApplyText = document.getElementById('btn-ai-apply-text');
        btnApplyText.addEventListener('click', () => {
            const aiText = document.getElementById('ai-studytext').value;

            if (aiText) {
                setEditorValue('studytext', aiText);
                showAppliedFeedback(btnApplyText, 'Applied');
            }
        });

        // Apply chapters to media file
        const btnApplyChapters = document.getElementById('btn-apply-chapters');

        if (btnApplyChapters) {
            btnApplyChapters.addEventListener('click', () => {
                const textarea = document.getElementById('ai-chapters-text');
                applyChaptersToMedia(btnApplyChapters, textarea);
            });
        }

        // Copy chapters to clipboard
        const btnCopyChapters = document.getElementById('btn-copy-chapters');

        if (btnCopyChapters) {
            btnCopyChapters.addEventListener('click', () => {
                const textarea = document.getElementById('ai-chapters-text');

                if (textarea && textarea.value) {
                    navigator.clipboard.writeText(textarea.value).then(() => {
                        showAppliedFeedback(
                            btnCopyChapters,
                            Joomla.Text._('JBS_CMN_AI_CHAPTERS_COPIED') || 'Copied',
                        );
                    });
                }
            });
        }
    }

    // ---- YouTube Sync ----

    const hasYouTube = config.dataset.hasYoutube === '1';
    const btnYtSync  = document.getElementById('btn-yt-sync');

    if (hasYouTube && btnYtSync) {
        btnYtSync.addEventListener('click', async () => {
            const modalEl = document.getElementById('ytSyncModal');
            const modal   = new bootstrap.Modal(modalEl);
            const ytLoad  = document.getElementById('yt-sync-loading');
            const ytErr   = document.getElementById('yt-sync-error');
            const ytRes   = document.getElementById('yt-sync-results');

            ytLoad.style.display = 'block';
            ytErr.style.display  = 'none';
            ytRes.style.display  = 'none';
            modal.show();

            const formData = new FormData();
            formData.append('media_file_id', mediaId);

            try {
                const response = await fetch(`${ajaxBase}&task=cwmmessage.syncFromYouTube`, {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json();

                ytLoad.style.display = 'none';

                if (data.error) {
                    ytErr.innerHTML = escAttr(data.error);

                    if (data.quota_error) {
                        ytErr.innerHTML += '<br><small class="mt-1 d-block">'
                            + (Joomla.Text._('JBS_CMN_YT_QUOTA_HELP') || 'You can increase your quota in the YouTube server settings, or request a higher quota from Google.')
                            + ' <a href="https://console.cloud.google.com/apis/api/youtube.googleapis.com/quotas" '
                            + 'target="_blank" rel="noopener">Google API Console</a></small>';
                    }

                    ytErr.style.display = 'block';

                    return;
                }

                ytRes.style.display = 'block';

                // --- Tags → Topics ---
                const matchedSection = document.getElementById('yt-matched-section');
                const matchedList    = document.getElementById('yt-matched-list');
                const newSection     = document.getElementById('yt-new-section');
                const newList        = document.getElementById('yt-new-list');
                const noTags         = document.getElementById('yt-no-tags');
                const btnAddTopics   = document.getElementById('btn-yt-add-topics');

                matchedList.innerHTML = '';
                newList.innerHTML     = '';

                const tags            = data.video_tags || [];
                const matchedTopics   = data.matched_topics || [];
                const matchedTextsLc  = matchedTopics.map((m) => m.text.toLowerCase());

                // Matched existing topics (pre-checked)
                if (matchedTopics.length > 0) {
                    matchedSection.style.display = 'block';
                    matchedTopics.forEach((topic) => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        div.innerHTML = `<input class="form-check-input yt-topic-cb" type="checkbox" checked `
                            + `value="${escAttr(topic.id)}" data-label="${escAttr(topic.text)}" `
                            + `data-is-existing="1" id="ytm_${escAttr(topic.id)}">`
                            + `<label class="form-check-label" for="ytm_${escAttr(topic.id)}">`
                            + `${escAttr(topic.text)}</label>`;
                        matchedList.appendChild(div);
                    });
                } else {
                    matchedSection.style.display = 'none';
                }

                // Unmatched tags (unchecked, shown as new)
                const unmatched = tags.filter(
                    (tag) => !matchedTextsLc.includes(tag.toLowerCase()),
                );

                if (unmatched.length > 0) {
                    newSection.style.display = 'block';
                    unmatched.forEach((tag, idx) => {
                        const div = document.createElement('div');
                        div.className = 'form-check form-check-inline';
                        div.innerHTML = `<input class="form-check-input yt-topic-cb" type="checkbox" `
                            + `value="${escAttr(tag)}" data-label="${escAttr(tag)}" `
                            + `data-is-existing="0" id="ytn_${idx}">`
                            + `<label class="form-check-label" for="ytn_${idx}">${escAttr(tag)}</label>`;
                        newList.appendChild(div);
                    });
                } else {
                    newSection.style.display = 'none';
                }

                const hasTags = matchedTopics.length > 0 || unmatched.length > 0;
                noTags.style.display     = hasTags ? 'none' : 'block';
                btnAddTopics.style.display = hasTags ? '' : 'none';

                // --- Description ---
                const descSection = document.getElementById('yt-desc-section');
                const descText    = document.getElementById('yt-description-text');

                if (data.video_description) {
                    descSection.style.display = 'block';
                    descText.value = data.video_description;
                } else {
                    descSection.style.display = 'none';
                }

                // --- Chapters ---
                const chaptersSection = document.getElementById('yt-chapters-section');
                const chaptersText    = document.getElementById('yt-chapters-text');

                if (data.video_chapters && data.video_chapters.length > 0) {
                    const lines = data.video_chapters.map(
                        (ch) => `${ch.time} ${ch.label}`,
                    );
                    chaptersText.value = lines.join('\n');
                    chaptersSection.style.display = 'block';
                } else {
                    chaptersSection.style.display = 'none';
                }
            } catch (err) {
                ytLoad.style.display = 'none';
                ytErr.textContent    = err.message || 'Request failed';
                ytErr.style.display  = 'block';
            }
        });

        // Add selected topics
        const btnYtAddTopics = document.getElementById('btn-yt-add-topics');

        if (btnYtAddTopics) {
            btnYtAddTopics.addEventListener('click', () => {
                const checked = document.querySelectorAll('.yt-topic-cb:checked');
                let count = 0;

                checked.forEach((cb) => {
                    addTopicToField(cb.value, cb.dataset.label);
                    count += 1;
                });

                if (count > 0) {
                    showAppliedFeedback(btnYtAddTopics, `${count} topic${count > 1 ? 's' : ''} added`);
                }
            });
        }

        // Apply description → studyintro
        const btnYtApplyDesc = document.getElementById('btn-yt-apply-desc');

        if (btnYtApplyDesc) {
            btnYtApplyDesc.addEventListener('click', () => {
                const desc = document.getElementById('yt-description-text').value;

                if (desc) {
                    setEditorValue('studyintro', desc);
                    showAppliedFeedback(btnYtApplyDesc, 'Applied');
                }
            });
        }

        // Apply YouTube chapters to media file
        const btnYtApplyChapters = document.getElementById('btn-yt-apply-chapters');

        if (btnYtApplyChapters) {
            btnYtApplyChapters.addEventListener('click', () => {
                const textarea = document.getElementById('yt-chapters-text');
                applyChaptersToMedia(btnYtApplyChapters, textarea);
            });
        }

        // Copy chapters to clipboard
        const btnYtCopyChapters = document.getElementById('btn-yt-copy-chapters');

        if (btnYtCopyChapters) {
            btnYtCopyChapters.addEventListener('click', () => {
                const textarea = document.getElementById('yt-chapters-text');

                if (textarea && textarea.value) {
                    navigator.clipboard.writeText(textarea.value).then(() => {
                        showAppliedFeedback(
                            btnYtCopyChapters,
                            Joomla.Text._('JBS_CMN_YT_SYNC_CHAPTERS_COPIED') || 'Copied',
                        );
                    });
                }
            });
        }
    }

    // ---- Timestamp seek handler (admin preview) ----
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.cwm-timestamp');

        if (!link) {
            return;
        }

        e.preventDefault();
        const seconds = parseInt(link.dataset.seconds, 10);

        if (Number.isNaN(seconds)) {
            return;
        }

        // Find YouTube iframe on the page and seek
        const iframe = document.querySelector('iframe[src*="youtube.com"]');

        if (iframe) {
            iframe.contentWindow.postMessage(JSON.stringify({
                event: 'command',
                func: 'seekTo',
                args: [seconds, true],
            }), '*');
        }
    });

    // ---- Per-media Copy Description modal ----

    // Lazily initialise the modal on first use to avoid bootstrap timing issues
    let copyDescModal   = null;
    let copyDescText    = null;
    let copyDescLoading = null;
    let copyDescCopyBtn = null;

    function ensureCopyDescModal() {
        if (copyDescModal) {
            return;
        }

        const T = (key, fb) => {
            const v = Joomla.Text._(key);

            return (v && v !== key) ? v : fb;
        };

        const html = '<div class="modal fade" id="copyDescModal" tabindex="-1" aria-hidden="true">'
            + '<div class="modal-dialog modal-lg"><div class="modal-content">'
            + '<div class="modal-header"><h5 class="modal-title">'
            + T('JBS_MED_COPY_DESC', 'Copy Description')
            + '</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'
            + '<div class="modal-body">'
            + '<p class="text-muted small mb-2">'
            + T('JBS_MED_COPY_DESC_TIP', 'Copy this description and paste it into your video platform.')
            + '</p>'
            + '<div id="copyDescLoading" class="text-center py-4" style="display:none;">'
            + '<span class="spinner-border"></span></div>'
            + '<textarea id="copyDescText" class="form-control" rows="12" readonly'
            + ' style="display:none; font-family:monospace; font-size:0.85rem;"></textarea>'
            + '</div>'
            + '<div class="modal-footer">'
            + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
            + T('JCLOSE', 'Close') + '</button>'
            + '<button type="button" class="btn btn-primary" id="copyDescCopyBtn" disabled>'
            + '<span class="icon-copy" aria-hidden="true"></span> '
            + T('JBS_MED_COPY_DESC', 'Copy Description')
            + '</button></div></div></div></div>';

        document.body.insertAdjacentHTML('beforeend', html);

        const modalEl = document.getElementById('copyDescModal');
        copyDescModal   = new bootstrap.Modal(modalEl);
        copyDescText    = document.getElementById('copyDescText');
        copyDescLoading = document.getElementById('copyDescLoading');
        copyDescCopyBtn = document.getElementById('copyDescCopyBtn');

        copyDescCopyBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(copyDescText.value);
                copyDescCopyBtn.classList.remove('btn-primary');
                copyDescCopyBtn.classList.add('btn-success');
                copyDescCopyBtn.innerHTML = '<span class="icon-checkmark" aria-hidden="true"></span> '
                    + T('JBS_MED_COPY_DESC_COPIED', 'Copied!');
                setTimeout(() => {
                    copyDescCopyBtn.classList.remove('btn-success');
                    copyDescCopyBtn.classList.add('btn-primary');
                    copyDescCopyBtn.innerHTML = '<span class="icon-copy" aria-hidden="true"></span> '
                        + T('JBS_MED_COPY_DESC', 'Copy Description');
                }, 2000);
            } catch {
                copyDescText.select();
            }
        });
    }

    document.querySelectorAll('.cwm-copy-desc-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const descMediaId = parseInt(btn.dataset.mediaId, 10) || 0;
            const studyId     = parseInt(btn.dataset.studyId, 10) || 0;

            if (!studyId) {
                return;
            }

            // Initialise modal on first click
            ensureCopyDescModal();

            // Reset modal state and show
            copyDescText.value            = '';
            copyDescText.style.display    = 'none';
            copyDescLoading.style.display = 'block';
            copyDescCopyBtn.disabled      = true;
            copyDescModal.show();

            try {
                const url = `${ajaxBase}&task=cwmadmin.getVideoDescriptionXHR`
                    + `&study_id=${studyId}&media_id=${descMediaId}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 'X-CSRF-Token': '1' },
                });
                const data = await response.json();

                copyDescLoading.style.display = 'none';

                if (!data.success || !data.description) {
                    copyDescText.value        = data.error || 'Failed to generate description';
                    copyDescText.style.display = 'block';

                    return;
                }

                copyDescText.value        = data.description;
                copyDescText.style.display = 'block';
                copyDescCopyBtn.disabled  = false;
            } catch (e) {
                copyDescLoading.style.display = 'none';
                copyDescText.value        = e.message;
                copyDescText.style.display = 'block';
            }
        });
    });
})();
