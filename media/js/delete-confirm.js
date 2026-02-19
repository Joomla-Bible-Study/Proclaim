(function () {
   'use strict';

   /**
    * @package     Proclaim.Admin
    * @copyright   (C) 2026 CWM Team All rights reserved
    * @license     GNU General Public License version 2 or later; see LICENSE.txt
    *
    * Delete confirmation dialog for physical files.
    * Intercepts Joomla.submitbutton on messages/mediafiles list views when the
    * task is a permanent delete.  If selected items have physical files on
    * servers with delete_files=1 a Bootstrap modal lets the user choose:
    *   Delete Everything  – records + physical files
    *   Records Only       – keep files on disk
    *   Cancel
    *
    * @since 10.1.0
    */

   (() => {

       /* ── helpers ─────────────────────────────────────────────────── */

       /**
      * Return a translated string, falling back to the key itself when the
      * Joomla language registry does not contain the key.
      */
       const txt = (key, fallback) => {
           const v = Joomla.Text._(key);
           return (v && v !== key) ? v : (fallback || key);
       };

       /** Create an element with optional classes and text content */
       const el = (tag, classes, text) => {
           const node = document.createElement(tag);
           if (classes) node.className = classes;
           if (text) node.textContent = text;
           return node;
       };

       /* ── context detection ───────────────────────────────────────── */

       const form = document.getElementById('adminForm');
       if (!form) return;

       const action = form.getAttribute('action') || '';
       let context = '';
       let deleteTask = '';

       if (action.indexOf('view=cwmmessages') !== -1) {
           context = 'cwmmessages';
           deleteTask = 'cwmmessages.delete';
       } else if (action.indexOf('view=cwmmediafiles') !== -1) {
           context = 'cwmmediafiles';
           deleteTask = 'cwmmediafiles.delete';
       }

       if (!context) return;

       /* ── wrap Joomla.submitbutton ─────────────────────────────────── */

       const origSubmitbutton = Joomla.submitbutton;

       Joomla.submitbutton = (task) => {
           if (task !== deleteTask) {
               origSubmitbutton(task);
               return;
           }

           // Collect selected IDs
           const checkboxes = form.querySelectorAll('input[name="cid[]"]:checked');
           if (!checkboxes.length) {
               origSubmitbutton(task);
               return;
           }

           const ids = Array.from(checkboxes).map((cb) => cb.value);

           // Find the CSRF token input in the form
           const tokenInput = form.querySelector('input[type="hidden"][value="1"]');
           let tokenParam = '';
           if (tokenInput) {
               tokenParam = `&${encodeURIComponent(tokenInput.name)}=1`;
           }

           const url = `index.php?option=com_proclaim&task=${context}.checkDeleteFiles`
         + `&cid[]=${ids.join('&cid[]=')
      }${tokenParam
      }&format=json`;

           // AJAX check
           fetch(url, {
               method: 'GET',
               headers: { 'X-Requested-With': 'XMLHttpRequest' },
           })
               .then((r) => r.json())
               .then((data) => {
                   if (!data.success || !data.hasFiles) {
                       origSubmitbutton(task);
                       return;
                   }
                   showDeleteModal(data.files || [], task);
               })
               .catch(() => {
                   // On AJAX failure — never block deletion, fall through
                   origSubmitbutton(task);
               });
       };

       /* ── modal rendering (DOM-only, no innerHTML) ─────────────────── */

       function showDeleteModal(files, task) {
           const modalId = 'proclaimDeleteConfirmModal';
           const fileCount = files.length;
           const countText = txt('JBS_DEL_PHYSICAL_FILES_COUNT', '%d file(s) will be affected:')
               .replace('%d', String(fileCount));

           // ── outer modal shell
           const modalEl = el('div', 'modal fade');
           modalEl.id = modalId;
           modalEl.tabIndex = -1;
           modalEl.setAttribute('aria-labelledby', `${modalId}Label`);
           modalEl.setAttribute('aria-hidden', 'true');

           const dialog = el('div', 'modal-dialog modal-dialog-centered');
           const content = el('div', 'modal-content');

           // ── header (bg-warning)
           const header = el('div', 'modal-header bg-warning text-dark');
           const title = el('h5', 'modal-title');
           title.id = `${modalId}Label`;
           const icon = el('span', 'icon-warning');
           icon.setAttribute('aria-hidden', 'true');
           title.appendChild(icon);
           title.appendChild(document.createTextNode(` ${txt('JBS_DEL_PHYSICAL_FILES_TITLE', 'Physical Files Will Be Affected')}`));
           const closeBtn = el('button', 'btn-close');
           closeBtn.type = 'button';
           closeBtn.setAttribute('data-bs-dismiss', 'modal');
           closeBtn.setAttribute('aria-label', 'Close');
           header.appendChild(title);
           header.appendChild(closeBtn);

           // ── body
           const body = el('div', 'modal-body');
           body.appendChild(el('p', '', txt('JBS_DEL_PHYSICAL_FILES_WARNING', 'The following physical files exist on servers with file deletion enabled. You can choose to delete both the database records and the physical files, or only the database records.')));
           body.appendChild(el('p', 'fw-bold', countText));

           // scrollable file list
           const ul = el('ul', 'list-group list-group-flush');
           ul.style.maxHeight = '200px';
           ul.style.overflowY = 'auto';
           files.forEach((f) => {
               const li = el('li', 'list-group-item py-1 px-2');
               const strong = el('strong', '', f.filename);
               const span = el('span', 'text-muted small', `(${f.server})`);
               li.appendChild(strong);
               li.appendChild(document.createTextNode(' '));
               li.appendChild(span);
               ul.appendChild(li);
           });
           body.appendChild(ul);

           // ── footer
           const footer = el('div', 'modal-footer');

           const btnCancel = el('button', 'btn btn-secondary', Joomla.Text._('JCANCEL') || 'Cancel');
           btnCancel.type = 'button';
           btnCancel.setAttribute('data-bs-dismiss', 'modal');

           const btnRecords = el('button', 'btn btn-warning', txt('JBS_DEL_RECORDS_ONLY', 'Records Only'));
           btnRecords.type = 'button';
           btnRecords.id = `${modalId}RecordsOnly`;

           const btnAll = el('button', 'btn btn-danger', txt('JBS_DEL_DELETE_EVERYTHING', 'Delete Everything'));
           btnAll.type = 'button';
           btnAll.id = `${modalId}DeleteAll`;

           footer.appendChild(btnCancel);
           footer.appendChild(btnRecords);
           footer.appendChild(btnAll);

           // ── assemble
           content.appendChild(header);
           content.appendChild(body);
           content.appendChild(footer);
           dialog.appendChild(content);
           modalEl.appendChild(dialog);
           document.body.appendChild(modalEl);

           // ── Bootstrap modal
           const bsModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });

           const cleanup = () => {
               document.body.focus();
               bsModal.dispose();
               modalEl.remove();
               document.querySelectorAll('.modal-backdrop').forEach((n) => n.remove());
               document.body.classList.remove('modal-open');
               document.body.style.removeProperty('overflow');
               document.body.style.removeProperty('padding-right');
           };

           const hiddenField = form.querySelector('input[name="delete_physical_files"]');

           btnAll.addEventListener('click', () => {
               if (hiddenField) hiddenField.value = '1';
               cleanup();
               origSubmitbutton(task);
           });

           btnRecords.addEventListener('click', () => {
               if (hiddenField) hiddenField.value = '0';
               cleanup();
               origSubmitbutton(task);
           });

           modalEl.addEventListener('hidden.bs.modal', () => {
               cleanup();
           });

           bsModal.show();
       }
   })();

})();
