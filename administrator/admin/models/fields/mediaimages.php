<?php
/**
 * @version		$Id: filters.php 19730 2010-12-02 13:06:49Z chdemko $
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldMediaimages extends JFormField
{
	public $type = 'Mediaimages';

	protected function getInput()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id as value, media_image_name as text FROM #__bsms_media WHERE published=1 ORDER BY media_image_name ASC");
		$html[] = JHTML::_('select.option', '0', '- '.JText::_('JBS_MED_SELECT_MEDIA_TYPE').' -');
		$html = array_merge($html, $db->loadObjectList());
		return JHTML::_('select.genericlist', $html, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', 0);
	}
}


