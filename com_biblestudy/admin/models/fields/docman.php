<?php
/**
 * @version		$Id: docman.php 1284 2011-01-04 07:57:59Z genu $
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
class JFormFieldDocman extends JFormField
{
	public $type = 'Docman';

	protected function getInput()
	{
		//Check to see if Docman is installed
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_docman')){
			return "DOCman is not installed";
		}
		$db = JFactory::getDBO();
		//@todo Tom needs to look into the Query to be modified to reflect the DOCman database.
		$db->setQuery("SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%Y-%m-%d'), ' - ', studynumber) AS text FROM #__bsms_studies WHERE published = 1 ORDER BY studydate DESC");
		$html[] = JHTML::_('select.option', '0', '- '. JText::_( 'JBS_MED_SELECT_DOCMAN' ) .' -' );
		$html = array_merge($html, $db->loadObjectList());
		return JHTML::_('select.genericlist', $html, 'study_id', 'class="inputbox" size="1" ', 'value', 'text', $this->mediafilesedit->study_id);
	}
}


