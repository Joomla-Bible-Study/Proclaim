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
     * Animated progress steps shown while the API call is in flight.
     * Each entry is [delay_ms, progress_percent, message].
     */
    const progressSteps = [
        [0,     5,  'Gathering sermon context...'],
        [1200,  15, 'Reading title and scripture references...'],
        [2500,  30, 'Analyzing attached media...'],
        [4000,  45, 'Sending to AI provider...'],
        [6000,  60, 'Generating topics...'],
        [8500,  75, 'Writing description and study text...'],
        [11000, 85, 'Formatting response...'],
        [14000, 90, 'Almost done...'],
    ];

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
     * @returns {Function}  Call to stop and clean up timers.
     */
    function startProgress() {
        const bar       = document.getElementById('ai-progress-bar');
        const text      = document.getElementById('ai-progress-text');
        const container = document.getElementById('ai-progress-steps');

        if (bar) {
            bar.style.transition = 'width 0.6s ease';
            bar.style.width      = '5%';
        }

        if (container) {
            container.innerHTML = '';
        }

        const timers = [];

        progressSteps.forEach(([delay, pct, msg]) => {
            const tid = setTimeout(() => {
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

        return function stop() {
            timers.forEach(clearTimeout);

            if (bar) {
                bar.style.width = '100%';
            }
        };
    }

    const btnAiAssist = document.getElementById('btn-ai-assist');

    if (btnAiAssist) {
        btnAiAssist.addEventListener('click', async () => {
            // Read toggle checkbox states
            const genTopics = document.getElementById('ai-gen-topics');
            const genIntro  = document.getElementById('ai-gen-intro');
            const genText   = document.getElementById('ai-gen-text');
            const wantTopics = genTopics ? genTopics.checked : true;
            const wantIntro  = genIntro ? genIntro.checked : true;
            const wantText   = genText ? genText.checked : true;

            // Validate at least one checked
            if (!wantTopics && !wantIntro && !wantText) {
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

            const stopProgress = startProgress();

            const titleEl  = document.getElementById('jform_studytitle');
            const formData = new FormData();
            formData.append('title', titleEl ? titleEl.value : '');
            formData.append('studyintro', getEditorValue('studyintro'));
            formData.append('studytext', getEditorValue('studytext'));
            formData.append('media_file_id', mediaId);

            // Append toggle flags
            formData.append('generate_topics', wantTopics ? '1' : '0');
            formData.append('generate_intro', wantIntro ? '1' : '0');
            formData.append('generate_text', wantText ? '1' : '0');

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

                // Populate AI description and study text
                document.getElementById('ai-studyintro').value = data.studyintro || '';
                document.getElementById('ai-studytext').value  = data.studytext || '';

                // Handle suggested chapters
                if (chaptersSection) {
                    if (data.chapters && data.chapters.length > 0) {
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
})();
