<?php
/**
 * @version		$Id: icon.php 21706 2011-06-28 21:28:56Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Content Component HTML Helper
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class JHtmlIcon
{
	static function print_popup()
	{
		//$url  = BibleStudyHelperRoute::getArticleRoute($id->slug);
		$url = '&tmpl=component&print=1&layout=default&page='.@ $request->limitstart;

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
			$text = JHtml::_('image','system/printButton.png', JText::_('JBS_CMN_PRINT'), NULL, true);

		$attribs['title']	= JText::_('JBS_CMN_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']		= 'nofollow';

		return JHtml::_('link',JRoute::_($url), $text, $attribs);
	}

}
