<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 */

/**
* @version		$Id: submission.php 1284 2011-01-04 07:57:59Z genu $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementsubmission extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'submission';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
        if (JOOMLA_VERSION == '6')
        {
            $query = "SELECT id, title AS text FROM #__usergroups ORDER BY id ASC";
        }
        else
        {
            $query = "SELECT id, name AS text FROM #__core_acl_aro_groups ORDER BY id ASC";
        }
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_GROUP'), 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}
?>