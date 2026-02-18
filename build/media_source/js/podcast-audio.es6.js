/**
 * Podcast audio player for Proclaim series podcast display
 *
 * @package  Proclaim.Site
 * @since    10.1.0
 */
window.loadVideo = function loadVideo(path) {
    const audio   = document.querySelector('audio');
    const loading = document.getElementById('audio-loading');
    let timeoutId;

    if (audio) {
        if (loading) {
            loading.style.display = 'block';
            loading.innerHTML     = (Joomla.Text._('JBS_CMN_LOADING') || 'Loading') + '...';
            loading.className     = 'alert alert-info';
        }

        audio.src = path;

        timeoutId = setTimeout(function () {
            if (loading) {
                loading.innerHTML = Joomla.Text._('JBS_CMN_LOADING_TIMEOUT') || 'Loading is taking longer than expected...';
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
                loading.innerHTML = Joomla.Text._('JBS_CMN_LOADING_ERROR') || 'Error loading audio file.';
                loading.className = 'alert alert-danger';
            }
        };

        audio.load();
    } else {
        const iframe = document.querySelector('iframe.playhit');
        if (iframe) {
            console.log('Video/Iframe update not fully supported in this view for ' + path);
        }
    }
};
