/**
 * Enhanced QuickIcon display for Proclaim.
 *
 * Joomla's core quickicon.js only displays the `amount` (published count)
 * from AJAX responses. This script enhances the display by adding a subtitle
 * showing archived and total counts beneath each QuickIcon badge.
 *
 * @package  Proclaim
 * @since    10.1.0
 */
((document) => {
  'use strict';

  /**
   * Observe QuickIcon badges and enhance them with archived/total counts.
   *
   * We use a MutationObserver to watch for Joomla's quickicon.js inserting
   * badge elements, then make our own fetch to get the full data.
   */
  const enhanceQuickIcons = () => {
    const icons = document.querySelectorAll('.quick-icons [data-ajax-url]');

    if (!icons.length) {
      return;
    }

    icons.forEach((icon) => {
      const url = icon.dataset.ajaxUrl;

      if (!url || !url.includes('com_proclaim')) {
        return;
      }

      // Add CSRF token
      const separator = url.includes('?') ? '&' : '?';
      const tokenKey = Joomla.getOptions('csrf.token', '');
      const fullUrl = tokenKey
        ? `${url}${separator}${tokenKey}=1`
        : url;

      fetch(fullUrl)
        .then((response) => response.json())
        .then((result) => {
          if (!result.success || !result.data) {
            return;
          }

          const data = result.data;
          const archived = parseInt(data.archived, 10) || 0;
          const total = parseInt(data.total, 10) || 0;

          // Only add subtitle if there's something meaningful to show
          if (archived === 0 && total === data.amount) {
            return;
          }

          // Find the quickicon-info area (where Joomla puts the badge)
          const infoArea = icon.querySelector('.quickicon-info');

          if (!infoArea) {
            return;
          }

          // Check if we already enhanced this icon
          if (infoArea.querySelector('.quickicon-stat-detail')) {
            return;
          }

          const detail = document.createElement('div');
          detail.className = 'quickicon-stat-detail';
          detail.style.cssText = 'font-size: 0.75rem; opacity: 0.7; margin-top: 2px;';

          const parts = [];

          if (archived > 0) {
            parts.push(`${archived} archived`);
          }

          parts.push(`${total} total`);

          detail.textContent = parts.join(' / ');
          infoArea.appendChild(detail);
        })
        .catch(() => {
          // Silently ignore errors — the core badge still works
        });
    });
  };

  // Run after Joomla's quickicon.js has had time to process
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      // Delay slightly to run after Joomla's quickicon module
      setTimeout(enhanceQuickIcons, 500);
    });
  } else {
    setTimeout(enhanceQuickIcons, 500);
  }
})(document);
