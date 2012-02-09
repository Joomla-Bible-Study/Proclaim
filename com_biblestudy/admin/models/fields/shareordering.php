<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */


/**
 * @version		$Id: messagetypeordering.php 2086 2011-11-11 21:18:05Z bcordis $
 * @package		Joomla.Administrator
 * @subpackage	com_biblestudy
 * @copyright	Copyright (C) 2005 - 2011 All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @package		Joomla.Administrator
 * @subpackage	com_biblestudy
 * @since		1.6
 */
class JFormFieldShareOrdering extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ShareOrdering';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get some field values from the form.
		$messagetypeId	= (int) $this->form->getValue('id');


		// Build the query for the ordering list.
		$query = 'SELECT ordering AS value, filename AS text' .
				' FROM #__bsms_share' .
				' WHERE id = ' .(int) $messagetypeId .
				' ORDER BY ordering';

		// Create a read-only list (no name) with a hidden input to store the value.

		if ((string) $this->element['readonly'] == 'true') {
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $messagetypeId ? 0 : 1);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $messagetypeId ? 0 : 1);
		}

		return implode($html);
	}
}