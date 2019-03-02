<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Always load JBSM API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_biblestudy/api.php';

if (file_exists($api))
{
	require_once $api;
}

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
function Admin_Postinstall_Template_condition()
{
	$results = null;

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
	catch (Exception $e)
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
 * @throws Exception
 *@since  3.2
 */
function Admin_Postinstall_Template_action()
{
	$url = 'index.php?option=com_biblestudy&view=templates';
	JFactory::getApplication()->redirect($url);
}
