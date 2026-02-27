/**
 * @package    Proclaim.Media
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Clipboard handler for the local/privacy share "Copy Link" button.
 * Uses navigator.clipboard with document.execCommand('copy') fallback.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.proclaim-share-copy');
    if (!btn) return;

    const url = btn.dataset.shareUrl;
    if (!url) return;

    let success = false;

    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(url);
        success = true;
      }
    } catch {
      // Clipboard API blocked — fall through to fallback
    }

    if (!success) {
      // execCommand fallback for older browsers / insecure contexts
      const ta = document.createElement('textarea');
      ta.value = url;
      ta.style.cssText = 'position:fixed;left:-9999px;top:-9999px';
      document.body.appendChild(ta);
      ta.select();

      try {
        document.execCommand('copy');
        success = true;
      } catch {
        // Ignore — button stays in default state
      }

      document.body.removeChild(ta);
    }

    if (success) {
      btn.classList.add('copied');
      const icon = btn.querySelector('i');
      const original = icon ? icon.className : '';

      if (icon) {
        icon.className = 'fa-solid fa-check';
      }

      setTimeout(() => {
        btn.classList.remove('copied');
        if (icon) {
          icon.className = original;
        }
      }, 2000);
    }
  });
});
