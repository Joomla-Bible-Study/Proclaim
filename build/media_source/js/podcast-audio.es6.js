/**
 * Podcast audio player for Proclaim series podcast display
 *
 * @package  Proclaim.Site
 * @since    10.1.0
 */

// Local helper: Joomla.Text._() returns the raw key string when a key
// is unregistered (truthy), so `Joomla.Text._('K') || 'fallback'` never
// fires the fallback. Compare against the key itself to detect misses.
const t = (key, fallback) => {
    const v = Joomla.Text._(key);
    return v === key ? fallback : v;
};

window.loadVideo = function loadVideo(path) {
    const audio = document.querySelector('audio');
    const loading = document.getElementById('audio-loading');
    let timeoutId;

    if (audio) {
        if (loading) {
            loading.style.display = 'block';
            loading.innerHTML = `${t('JBS_CMN_LOADING', 'Loading')}...`;
            loading.className = 'alert alert-info';
        }

        audio.src = path;

        timeoutId = setTimeout(() => {
            if (loading) {
                loading.innerHTML = t('JBS_CMN_LOADING_TIMEOUT', 'Loading is taking longer than expected...');
                loading.className = 'alert alert-warning';
            }
        }, 10000);

        audio.oncanplay = function () {
            clearTimeout(timeoutId);
            if (loading) {
                loading.style.display = 'none';
            }
            audio.play();
        };

        audio.onerror = function () {
            clearTimeout(timeoutId);
            if (loading) {
                loading.innerHTML = t('JBS_CMN_LOADING_ERROR', 'Error loading audio file.');
                loading.className = 'alert alert-danger';
            }
        };

        audio.load();
    } else {
        const iframe = document.querySelector('iframe.playhit');
        if (iframe) {
            console.log(`Video/Iframe update not fully supported in this view for ${path}`);
        }
    }
};

/**
 * Bind the episode "Listen" controls.
 *
 * They used to be `<a href="javascript:loadVideo(…)">`, which no
 * Content-Security-Policy allows and which is announced as a link to a
 * destination that does not exist. They are now buttons carrying the path in a
 * data attribute, bound here — in the file that already owns loadVideo and is
 * already loaded on this view (#1814).
 *
 * Delegated, so it does not care whether the rows were present at parse time.
 *
 * @since __DEPLOY_VERSION__
 */
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-proclaim-audio]');

    if (!trigger) {
        return;
    }

    const path = trigger.getAttribute('data-proclaim-audio');

    if (path) {
        window.loadVideo(path);
    }
});
