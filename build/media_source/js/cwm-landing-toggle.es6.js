/**
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Landing page show/hide toggle.
 *
 * New approach (10.3.0): data-attribute delegation with ARIA support.
 * Legacy approach preserved for backward compatibility with template overrides.
 *
 * @since 10.2.0
 */

/**
 * New data-attribute based toggle (10.3.0+)
 *
 * Button:  <button data-proclaim-toggle="sectionId" aria-expanded="false">
 * Hidden:  <div data-proclaim-section="sectionId" data-proclaim-hidden>
 */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-proclaim-toggle]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const sectionId = btn.dataset.proclaimToggle;
      const expanded = btn.getAttribute('aria-expanded') === 'true';

      // Toggle all hidden items in this section
      document.querySelectorAll(
        `[data-proclaim-section="${sectionId}"][data-proclaim-hidden]`
      ).forEach((el) => {
        el.classList.toggle('proclaim-landing--hidden');
      });

      // Update ARIA state
      btn.setAttribute('aria-expanded', String(!expanded));

      // Update button text
      const showText = btn.dataset.proclaimShowText;
      const hideText = btn.dataset.proclaimHideText;
      const label = btn.querySelector('.proclaim-landing__toggle-label');

      if (showText && hideText && label) {
        label.textContent = expanded ? showText : hideText;
      }
    });
  });
});

/**
 * Legacy toggle (pre-10.3.0) — kept for backward compatibility
 * with custom template overrides using inline onclick attributes.
 *
 * @param {string} d  The element ID or class suffix to toggle
 * @deprecated 10.3.0 Use data-proclaim-toggle attributes instead
 */
window.ReverseDisplay2 = function ReverseDisplay2(d) {
  const el = document.getElementById(d);

  if (el) {
    if (el.style.display === 'none') {
      el.style.display = 'contents';
    } else {
      el.style.display = 'none';
    }
  } else {
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
