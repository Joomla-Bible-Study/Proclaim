<?php
/**
 * @version		$Id: contact.php 8591 2007-08-27 21:09:32Z hackwar $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementmediafile extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'mediafile';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT mf.id, mf.filename AS text, mf.server, mf.path, s.id AS sid, s.server_path, f.id AS fid, f.folderpath"
		. "\n FROM #__bsms_mediafiles AS mf"
		. "\n LEFT JOIN #__bsms_servers AS s ON (s.id = mf.server)"
		. "\n LEFT JOIN #__bsms_folders AS f ON (f.id = mf.path)"
		. "\n WHERE mf.published = 1"
		. "\n ORDER BY mf.createdate DESC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}