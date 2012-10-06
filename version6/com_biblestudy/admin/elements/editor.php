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

class JElementeditor extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'editor';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes('rows');
		if ($rows == '') $rows = 20;
		$cols = $node->attributes('cols');
		if ($cols == '') $colss = 60;
		$width = $node->attributes('width');
		if ($width == '') $width = '100%';
		$height = $node->attributes('height');
		if ($height == '') $height = '100%';
		$buttons = $node->attributes('buttons');
		if ($buttons == 'false') $buttons = false;
		else $buttons = true;
		$editor = & JFactory::getEditor(); //dump ($editor, 'editor: ');
		return '<div style="text-align: left;">'.$editor->display($control_name .'['. $name .']', $value, $width, $height, $cols,
		$rows, $buttons).'</div>';
	}


}