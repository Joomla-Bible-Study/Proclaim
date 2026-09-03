<?php

/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmserver\HtmlView $this */

// Create shortcut to parameters.
$app   = Factory::getApplication();
$input = $app->getInput();

$isNewRecord = ((int)$this->item->id === 0 && empty($this->item->type));

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        "
	/* Choosing a type must not submit the form: whatever was typed would ride
	   through a submit never meant to save, and every failure would become a
	   navigation. Instead the tab region is re-rendered server-side for the
	   chosen type and swapped in place, then the typed values are put back. */
	Joomla.cwmSwapServerType = function (type) {
		var form   = document.getElementById('item-form');
		var region = document.getElementById('server-tabset-region');

		/* The type field is a ModalSelectField: a readonly title input and a
		   hidden value input SHARE the name jform[type], so
		   elements['jform[type]'] is a RadioNodeList and assigning .value to
		   it does nothing at all. Write to the hidden value input by id, and
		   mirror it into the visible one so the choice shows immediately. */
		var typeValue = document.getElementById('jform_type_id');
		var typeTitle = document.getElementById('jform_type');
		if (typeValue) { typeValue.value = type; }
		if (typeTitle) { typeTitle.value = type; }

		if (!region) {
			/* No region to swap (unexpected markup): fall back to the old
			   round trip rather than doing nothing. */
			Joomla.submitform('cwmserver.setType', form);
			return;
		}

		var snapshot = new FormData(form);
		var recordId = (document.getElementById('jform_id') || { value: 0 }).value || 0;
		var url = 'index.php?option=com_proclaim&task=cwmserver.typeFields&tmpl=component'
			+ '&type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(recordId)
			+ '&' + " . json_encode(Session::getFormToken()) . " + '=1';

		/* A quick change of mind can start a second swap before the first
		   response lands; whichever arrives LAST would win regardless of
		   which was chosen last. Only the newest request may apply. */
		var seq = Joomla.cwmSwapServerType._seq = (Joomla.cwmSwapServerType._seq || 0) + 1;

		fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (response) {
				if (!response.ok) { throw new Error(String(response.status)); }
				return response.text();
			})
			.then(function (html) {
				if (seq !== Joomla.cwmSwapServerType._seq) { return; }
				var tpl = document.createElement('template');
				tpl.innerHTML = html.trim();
				var fresh = tpl.content.getElementById('server-tabset-region');
				if (!fresh) { throw new Error('fragment'); }
				/* Re-query rather than trust the closure: an earlier swap may
				   have replaced the node this call started from. */
				document.getElementById('server-tabset-region').replaceWith(fresh);

				/* Put back what was typed. The swap replaced empty fields with
				   empty fields; the person's work is the part that must not be
				   collateral. Old addon fields simply no longer exist. */
				snapshot.forEach(function (value, name) {
					if (name === 'task' || name === 'return' || name.indexOf('jform[type]') === 0) { return; }
					var el = form.elements[name];
					if (!el) { return; }
					if (el instanceof RadioNodeList) { el.value = value; return; }
					if (el.type === 'file') { return; }
					if (el.type === 'checkbox') { el.checked = value === '1' || value === 'on'; return; }
					el.value = value;
				});

				/* Joomla widgets in the fresh region bound their listeners to
				   the OLD nodes at page load. Core scripts (the modal-select
				   type field, chosen selects, etc.) re-initialise whatever is
				   inside the target of a joomla:updated event — the same signal
				   Joomla fires after its own AJAX form swaps. Without this the
				   type field's own Select/Clear buttons are dead after a swap. */
				fresh.dispatchEvent(new CustomEvent('joomla:updated', { bubbles: true }));

				/* The calendar and validator do not listen for joomla:updated,
				   so they are re-attached by hand. */
				if (window.JoomlaCalendar) {
					fresh.querySelectorAll('.field-calendar').forEach(function (el) {
						try { JoomlaCalendar.init(el); } catch (e) { /* cosmetic */ }
					});
				}
				if (document.formvalidator && document.formvalidator.attachToForm) {
					try { document.formvalidator.attachToForm(form); } catch (e) { /* cosmetic */ }
				}
			})
			.catch(function () {
				Joomla.renderMessages({ error: ['" . $this->escape(Text::_('JBS_SVR_TYPE_SWAP_FAILED')) . "'] });
			});
	};

	Joomla.submitbutton = function (task, type) {
		if (task == 'cwmserver.setType') {
			Joomla.cwmSwapServerType(type);
		} else if (task == 'cwmserver.cancel') {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else if (task == 'cwmserver.apply' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('" . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
		}
	}"
    );

if ($isNewRecord) {
    $wa->addInlineScript(
        "window.addEventListener('load', function () {
            var value = document.getElementById('jform_type_id');
            var wrap  = value && value.closest('.js-modal-content-select-field');
            var btn   = wrap && wrap.querySelector('[data-button-action=\"select\"]');
            if (btn) { btn.click(); }
        });"
    );
}
?>
<?php $currentLayout = $input->get('layout', 'edit'); ?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmserver&layout=' . $currentLayout . '&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="item-form"
      aria-label="<?php
        echo Text::_('JBS_CMN_' . ((int)$this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>"
      class="form-validate" enctype="multipart/form-data">
    <div class="main-card">
        <?php echo $this->loadTemplate('tabset'); ?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php
        echo $input->getBase64('return'); ?>"/>
        <?php
        echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
