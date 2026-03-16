/**
 * @package    Proclaim.Media
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Reads AddToAny social share configuration from Joomla script options
 * and sets up the global a2a_config object.
 *
 * @since  10.2.0
 */
document.addEventListener('DOMContentLoaded', () => {
    const opts = Joomla.getOptions('com_proclaim.socialShare');
    if (!opts) {
        return;
    }

    window.a2a_config = window.a2a_config || {};

    a2a_config.onclick = 1;
    a2a_config.num_services = 8;
    a2a_config.thanks = { postShare: true, ad: false };

    a2a_config.templates = a2a_config.templates || {};
    a2a_config.templates.email = {
        subject: '${title}',
        body: (opts.description || 'Check out this message') + '\n\n${link}',
    };

    if (opts.linkUrl) {
        a2a_config.linkurl_default = opts.linkUrl;
    }
});
