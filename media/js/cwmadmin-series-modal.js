 /**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
	/**
	 * Javascript to insert the link
	 * View element calls jSelectSeries when a series is clicked
	 * jSelectSeries creates the link tag, sends it to the editor,
	 * and closes the select frame.
	 * */

	window.jSelectSeries = (id, title, catid, object, link, lang) => {
		let hreflang = ''

		if (!Joomla.getOptions('xtd-series'))
		{
			// Something went wrong!
			// @TODO Close the modal
			return false
		}

		const {
			editor,
		} = Joomla.getOptions('xtd-series')

		if (lang !== '')
		{
			hreflang = `hreflang="${lang}"`
		}

		const tag = `<a ${hreflang} href="${link}">${title}</a>`
		window.parent.Joomla.editors.instances[editor].replaceSelection(tag)

		if (window.parent.Joomla.Modal)
		{
			window.parent.Joomla.Modal.getCurrent().close()
		}

		return true
	}

	document.addEventListener('DOMContentLoaded', () => {
		// Get the elements
		const elements = document.querySelectorAll('.select-link')

		for (let i = 0, l = elements.length; l > i; i += 1)
		{
			// Listen for click event
			elements[i].addEventListener('click', event => {
				event.preventDefault()
				const {
					target,
				} = event
				const functionName = target.getAttribute('data-function')

				if (functionName === 'jSelectSeries')
				{
					// Used in xtd_contacts
					window[functionName](target.getAttribute('data-id'),
						target.getAttribute('data-title'),
						target.getAttribute('data-cat-id'), null,
						target.getAttribute('data-uri'),
						target.getAttribute('data-language'),
					)
				}
				else
				{
					// Used in com_menus
					window.parent[functionName](target.getAttribute('data-id'),
						target.getAttribute('data-title'),
						target.getAttribute('data-cat-id'), null,
						target.getAttribute('data-uri'),
						target.getAttribute('data-language'),
					)
				}

				if (window.parent.Joomla.Modal)
				{
					window.parent.Joomla.Modal.getCurrent().close()
				}
			})
		}
	})
})()
