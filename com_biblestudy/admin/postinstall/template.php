<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Checks if the template is setup right.
 *
 * This check returns true Templates is not setup yet, meaning
 * that the message concerning it should be displayed.
 *
 * @return  integer
 *
 * @since   3.2
 */
function admin_postinstall_template_condition()
{
	$results = null;

	/* Load language file out of administrator folder
	 * if phrase is not found in specific language file, load english language file:
	 */
	require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/defines.php';
	$language = JFactory::getLanguage();
	$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
	$language->load('com_biblestudy', BIBLESTUDY_PATH_ADMIN, null, true);

	$db = JFactory::getDbo();
	$qurey = $db->getQuery(true);
	$qurey->select('*')->from('#__bsms_templates');
	$db->setQuery($qurey);

	try
	{
		$tables = $db->loadObjectList();

		foreach ($tables as $table)
		{
			$registry = new Registry;
			$registry->loadString($table->params);

			if ($registry->get('playerresposive', false) != false)
			{
				$results = false;
			}
			else
			{
				$results = true;
			}
		}
	}
	catch (\Exception $e)
	{
		$results = null;
	}

	return $results;
}

/**
 * Redirect the view to the Templates view
 *
 * @return  void
 *
 * @since   3.2
 */
function admin_postinstall_template_action()
{
	$url = 'index.php?option=com_biblestudy&view=templates';
	JFactory::getApplication()->redirect($url);
}
