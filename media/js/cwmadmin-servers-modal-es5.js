// noinspection JSAnnotator

(function () {
	'use strict';

	/**
	 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
	 * @license    GNU General Public License version 2 or later; see LICENSE.txt
	 */
	(function () {
		/**
		 * Javascript to insert the link
		 * View element calls jSelectServer when a server is clicked
		 * jSelectServer creates the link tag, sends it to the editor,
		 * and closes the select frame.
		 * */
		window.jSelectSeries = function (id, title, object, link, lang) {
			var hreflang = ''

			if (!Joomla.getOptions('xtd-servers'))
			{
				// Something went wrong!
				// @TODO Close the modal
				return false
			}

			var _Joomla$getOptions = Joomla.getOptions('xtd-servers'),
				editor = _Joomla$getOptions.editor

			if (lang !== '')
			{
				hreflang = 'hreflang="' + lang + '"'
			}

			var tag = '<a ' + hreflang + ' href="' + link + '">' + title + '</a>'
			window.parent.Joomla.editors.instances[editor].replaceSelection(tag)
			window.parent.Joomla.submitForm();

			if (window.parent.Joomla.Modal)
			{
				window.parent.Joomla.Modal.getCurrent().close()
			}

			return true
		}

		document.addEventListener('DOMContentLoaded', function () {
			// Get the elements
			var elements = document.querySelectorAll('.select-link')

			for (var i = 0, l = elements.length; l > i; i += 1)
			{
				// Listen for click event
				elements[i].addEventListener('click', function (event) {
					event.preventDefault()
					var target = event.target
					var functionName = target.getAttribute('data-function')

					if (functionName === 'jSelectServer')
					{
						// Used in xtd_contacts
						window[functionName](target.getAttribute('data-id'),
							target.getAttribute('data-title'),
							target.getAttribute('data-uri'),
							target.getAttribute('data-language')
						)
					}
					else
					{
						// Used in com_menus
						window.parent[functionName](target.getAttribute('data-id'),
							target.getAttribute('data-title'),
							target.getAttribute('data-uri'),
							target.getAttribute('data-language')
						)
					}

					if (window.parent.Joomla.Modal)
					{
						window.parent.Joomla.Modal.getCurrent().close()
						const doc = window.parent.document,
							theForm = doc.getElementById("adminForm"),
							task = doc.getElementsByName('task');
						for (let i = 0; i < task.length; i++) {
							task[i].value = "cwmmediafile.setServer";
						}

						theForm.submit();
					}
				})
			}
		})
	})()
})()
