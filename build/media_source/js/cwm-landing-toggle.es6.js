/**
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Show/hide toggle for landing page "show more" sections.
 * Called from inline onclick attributes in landing page templates.
 *
 * @since 10.2.0
 */

/**
 * Toggle visibility of a landing page section by ID or class name.
 *
 * @param {string} d  The element ID or class suffix to toggle
 */
window.ReverseDisplay2 = function ReverseDisplay2(d) {
  const el = document.getElementById(d);

  if (el) {
    // Legacy/Table layout support
    if (el.style.display === 'none') {
      el.style.display = 'contents';
    } else {
      el.style.display = 'none';
    }
  } else {
    // Grid layout support (class toggling)
    const elements = document.getElementsByClassName(`landing-hidden-${d}`);

    for (let i = 0; i < elements.length; i += 1) {
      if (elements[i].style.display === 'none') {
        elements[i].style.display = '';
      } else {
        elements[i].style.display = 'none';
      }
    }
  }
};
