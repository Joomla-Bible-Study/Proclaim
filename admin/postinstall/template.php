<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007-2022 (C) CWM Team All rights reserved
 * @license    https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (file_exists($api))
{
	require_once $api;
}

/**
 * Checks if the template is set up right.
 *
 * This check returns true Templates is not setup yet, meaning
 * that the message concerning it should be displayed.
 *
 * @return  boolean
 *
 * @since   3.2
 */
function admin_postinstall_template_condition(): bool
{
	$results = null;

	$db = Factory::getContainer()->get('DatabaseDriver');
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

			if ($registry->get('playerresposive', false))
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
 * @since  3.2
 */
function admin_postinstall_template_action(): void
{
	$url = 'index.php?option=com_proclaim&view=templates';
	Factory::getApplication()->redirect($url);
}
