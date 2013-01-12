<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die;

/**
 * Content Component HTML Helper
 *
 * @static
 * @package     BibleStudy.Site
 * @since       7.0.0
 *
 * @deprecated  7.0.0
 */
class JHtmlIcon
{

	/**
	 * Print Popup
	 *
	 * @todo need to verify what the $request is coming from error out now. TOM
	 * @return string
	 */
	public static function print_popup()
	{
		$url = '&tmpl=component&print=1&layout=default';

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// Checks template image directory for image, if non found default are loaded
		$text = JHtml::_('image', 'system/printButton.png', JText::_('JBS_CMN_PRINT'), null, true);

		$attribs['title']   = JText::_('JBS_CMN_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}

}
