/**
 * AI Model Fetcher — Dynamic model dropdown for AI provider settings
 *
 * Fetches available models from the selected AI provider's API and
 * populates the model dropdown. Scopes models to the active provider.
 * Caches the model list in localStorage (1 hour TTL) to avoid repeated
 * API calls on every page load.
 *
 * @package  Proclaim
 * @since    10.1.0
 */
(() => {
    'use strict';

    const config       = document.getElementById('ai-models-config');
    const providerSel  = document.getElementById('jform_params_ai_provider');
    const modelSel     = document.getElementById('jform_params_ai_model');
    const fetchBtn     = document.getElementById('btn-fetch-ai-models');
    const statusEl     = document.getElementById('ai-models-status');

    if (!config || !providerSel || !modelSel) {
        return;
    }

    const token      = config.dataset.token || '';
    const savedModel = config.dataset.savedModel || '';
    const baseUrl    = `index.php?option=com_proclaim&format=raw&${token}=1`;

    /** Cache TTL: 1 hour in milliseconds */
    const CACHE_TTL = 60 * 60 * 1000;

    /**
     * Build a localStorage cache key for the given provider.
     *
     * @param {string} provider
     * @returns {string}
     */
    function cacheKey(provider) {
        return `cwm_ai_models_${provider}`;
    }

    /**
     * Read cached models from localStorage.
     *
     * @param {string} provider
     * @returns {Array<{id: string, name: string}>|null}  Models array or null if expired/missing
     */
    function getCachedModels(provider) {
        try {
            const raw = localStorage.getItem(cacheKey(provider));

            if (!raw) {
                return null;
            }

            const entry = JSON.parse(raw);

            if (!entry || !entry.ts || !Array.isArray(entry.models)) {
                return null;
            }

            if (Date.now() - entry.ts > CACHE_TTL) {
                localStorage.removeItem(cacheKey(provider));

                return null;
            }

            return entry.models;
        } catch {
            return null;
        }
    }

    /**
     * Write models to localStorage cache.
     *
     * @param {string} provider
     * @param {Array<{id: string, name: string}>} models
     */
    function setCachedModels(provider, models) {
        try {
            localStorage.setItem(cacheKey(provider), JSON.stringify({
                ts: Date.now(),
                models,
            }));
        } catch {
            // localStorage full or unavailable — ignore
        }
    }

    /**
     * Read the current API key value from the password input.
     *
     * @returns {string}
     */
    function getApiKey() {
        const field = document.getElementById('jform_params_ai_api_key');

        return field ? field.value : '';
    }

    /**
     * Replace the model <select> options with fetched models.
     *
     * @param {Array<{id: string, name: string}>} models
     * @param {string} restoreValue  Previously saved model ID to re-select
     */
    function setModelOptions(models, restoreValue) {
        const defaultOpt = modelSel.querySelector('option[value=""]');

        modelSel.innerHTML = '';

        if (defaultOpt) {
            modelSel.appendChild(defaultOpt);
        } else {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = (Joomla.Text._ && Joomla.Text._('JBS_ADM_AI_MODEL_DEFAULT'))
                || 'Provider Default';
            modelSel.appendChild(opt);
        }

        models.forEach((model) => {
            const opt = document.createElement('option');
            opt.value = model.id;
            opt.textContent = model.name;
            modelSel.appendChild(opt);
        });

        // Restore the previously saved model if it exists in the list
        if (restoreValue) {
            const exists = Array.from(modelSel.options).some((o) => o.value === restoreValue);

            if (exists) {
                modelSel.value = restoreValue;
            }
        }
    }

    /**
     * Fetch available models from the selected provider.
     *
     * @param {boolean} [bypassCache=false]  Skip localStorage cache (force fresh fetch)
     */
    async function fetchModels(bypassCache) {
        const provider = providerSel.value || 'claude';
        const apiKey   = getApiKey();

        if (!apiKey) {
            if (statusEl) {
                statusEl.textContent = (Joomla.Text._ && Joomla.Text._('JBS_CMN_AI_NO_API_KEY'))
                    || 'No API key configured.';
            }

            return;
        }

        // Try localStorage cache first (unless explicitly bypassed)
        if (!bypassCache) {
            const cached = getCachedModels(provider);

            if (cached) {
                setModelOptions(cached, savedModel);

                if (statusEl) {
                    const tpl = (Joomla.Text._ && Joomla.Text._('JBS_ADM_AI_MODELS_LOADED'))
                        || '%d models loaded';
                    statusEl.textContent = tpl.replace('%d', cached.length);
                }

                return;
            }
        }

        if (fetchBtn) {
            fetchBtn.disabled = true;
        }

        if (statusEl) {
            statusEl.textContent = (Joomla.Text._ && Joomla.Text._('JBS_ADM_AI_FETCHING_MODELS'))
                || 'Fetching models...';
        }

        const url = `${baseUrl}&task=cwmadmin.fetchAiModelsXHR`
            + `&provider=${encodeURIComponent(provider)}`
            + `&api_key=${encodeURIComponent(apiKey)}`;

        try {
            const data = await window.ProclaimFetch.fetchJson(url, { method: 'GET' }, { retries: 1 });

            if (data.success && data.models) {
                setCachedModels(provider, data.models);
                setModelOptions(data.models, savedModel);

                if (statusEl) {
                    const tpl = (Joomla.Text._ && Joomla.Text._('JBS_ADM_AI_MODELS_LOADED'))
                        || '%d models loaded';
                    statusEl.textContent = tpl.replace('%d', data.models.length);
                }
            } else if (statusEl) {
                statusEl.textContent = data.error || 'Failed to fetch models.';
            }
        } catch (err) {
            if (statusEl) {
                statusEl.textContent = err.message || 'Request failed.';
            }
        } finally {
            if (fetchBtn) {
                fetchBtn.disabled = false;
            }
        }
    }

    // Bind fetch button — always bypasses cache for a fresh list
    if (fetchBtn) {
        fetchBtn.addEventListener('click', () => fetchModels(true));
    }

    // Auto-restore models on page load from cache or API
    if (savedModel && getApiKey()) {
        fetchModels(false);
    }

    // Reset model dropdown and clear cache when provider changes
    providerSel.addEventListener('change', () => {
        const defaultOpt = modelSel.querySelector('option[value=""]');

        modelSel.innerHTML = '';

        if (defaultOpt) {
            modelSel.appendChild(defaultOpt);
        }

        modelSel.value = '';

        if (statusEl) {
            statusEl.textContent = (Joomla.Text._ && Joomla.Text._('JBS_ADM_AI_PROVIDER_CHANGED'))
                || 'Provider changed. Click "Fetch Models" to load available models.';
        }
    });
})();
