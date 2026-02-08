(function () {
    'use strict';

    /**
     * Scripture Version Switcher
     *
     * Handles AJAX-based Bible version switching on the frontend.
     * Listens for change events on .scripture-version-select dropdowns
     * and fetches new passage text from the server.
     *
     * @package  Proclaim.Site
     * @since    10.1.0
     */

    document.addEventListener('DOMContentLoaded', () => {
        const selects = document.querySelectorAll('.scripture-version-select');

        selects.forEach((select) => {
            select.addEventListener('change', async (event) => {
                const version = event.target.value;
                const reference = event.target.dataset.reference;
                const container = event.target.closest('.scripture-version-switcher');

                if (!container) {
                    return;
                }

                // Find the scripture container that follows the switcher
                const scriptureContainer = container.nextElementSibling;

                if (!scriptureContainer) {
                    return;
                }

                const body = scriptureContainer.querySelector('.scripture-body');
                const copyright = scriptureContainer.querySelector('.scripture-copyright');

                if (!body) {
                    return;
                }

                // Show loading state
                const originalText = body.innerHTML;
                body.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                select.disabled = true;

                try {
                    const token = Joomla.getOptions('csrf.token') || '';
                    const url = `index.php?option=com_proclaim&task=cwmscripture.getPassageXHR`
                        + `&reference=${encodeURIComponent(reference)}`
                        + `&version=${encodeURIComponent(version)}`
                        + `&${token}=1`;

                    const response = await fetch(url);
                    const data = await response.json();

                    if (data.success && data.text) {
                        body.innerHTML = data.text;

                        if (copyright) {
                            copyright.textContent = data.copyright || '';
                            copyright.style.display = data.copyright ? '' : 'none';
                        }
                    } else if (data.success && data.isIframe && data.iframeUrl) {
                        body.innerHTML = `<iframe src="${data.iframeUrl}" width="100%" height="400" `
                            + `style="border:0;" title="Bible Passage"></iframe>`;

                        if (copyright) {
                            copyright.style.display = 'none';
                        }
                    } else {
                        body.innerHTML = originalText;
                    }
                } catch (error) {
                    body.innerHTML = originalText;
                } finally {
                    select.disabled = false;
                }
            });
        });
    });

})();
