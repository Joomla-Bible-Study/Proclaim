/**
 * JoomlaDialog bridge for Proclaim.
 *
 * Joomla 6/7 removes the Bootstrap-modal JS bridge and the synchronous
 * native confirm()/alert() flow we relied on. The first-class replacement is
 * JoomlaDialog, which is ESM-only (`export { JoomlaDialog as default }`, no
 * window global) and Promise-based. The only way to reach it is a real
 * `import` from a `type="module"` asset — which is why this file is a
 * `.es6.mjs` source: the cwm-build-tools rollup config emits it as an ES
 * module with `joomla.dialog` left external, so Joomla's import-map resolves
 * it in the browser. An IIFE bundle could not carry this import.
 *
 * It exposes async drop-in-ish replacements on `window` so the existing IIFE
 * modules (cwmadmin, admin-imagetools, layout-editor, …) can migrate one call
 * site at a time without each becoming a module:
 *
 *   // before (synchronous, breaks in J6/7):
 *   if (!confirm(msg)) return;
 *   // after (await the promise):
 *   if (!await window.cwmConfirm(msg)) return;
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */

import JoomlaDialog from 'joomla.dialog';

/**
 * Promise<boolean> confirmation dialog. Resolves true on Yes, false on No.
 *
 * @param {String} message
 * @param {String} [title]
 * @returns {Promise<boolean>}
 */
const cwmConfirm = (message, title) => JoomlaDialog.confirm(message, title);

/**
 * Promise<void> alert dialog. Resolves when dismissed.
 *
 * @param {String} message
 * @param {String} [title]
 * @returns {Promise<void>}
 */
const cwmAlert = (message, title) => JoomlaDialog.alert(message, title);

window.cwmConfirm = cwmConfirm;
window.cwmAlert = cwmAlert;

export { cwmConfirm, cwmAlert };