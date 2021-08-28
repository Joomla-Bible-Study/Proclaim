<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * Abstract Server class
 *
 * @since  9.0.0
 */
abstract class JBSServer
{
	/**
	 * The type of server
	 *
	 * @var     string
	 * @since   9.0.0
	 */
	protected $type = '';

	/**
	 * @var     resource    The server connection resource
	 * @since   9.0.0
	 */
	protected $connection;

	/**
	 * @var array
	 *
	 * @since 9.0.0
	 */
	protected static array $instances = array();

	/**
	 * @var string
	 *
	 * @since 9.0.0
	 */
	protected string $file;

	/**
	 * Get a list of available servers
	 *
	 * @return      array   An Array of available servers
	 *
	 * @since       9.0.0
	 */
	public static function getServers()
	{
		$servers = array();

		$types = JFolder::folders(__DIR__);

		foreach ($types as $type)
		{
			// Derive the class name from the type.
			$class = 'JBSServer' . ucfirst(trim($type));

			if (!class_exists($class))
			{
				$path = __DIR__ . '/' . $type . '/' . $type . '.php';

				// If the file exists register the class
				if (file_exists($path))
				{
					JLoader::register($class, $path);
				}
				// If it doesn't exist, skip it
				else
				{
					continue;
				}
			}

			// If the class still doesn't exist we move on to next server type
			if (!class_exists($class))
			{
				continue;
			}

			// Add server to our list of available servers
			$servers[] = $type;
		}

		return $servers;
	}

	/**
	 * Instance
	 *
	 * @param   array  $options  Options to be set
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	public static function getInstance($options = array())
	{
		$options['type'] = $options['type'] ?? 'amazons3';
		$instance = null;

		// Get the options signature for this server type
		$signature = md5(serialize($options));

		if (empty(self::$instances[$signature]))
		{
			$class = 'JBSServer' . ucfirst($options['type']);

			if (!class_exists($class))
			{
				$path = __DIR__ . '/' . $options['type'] . '/' . $options['type'] . '.php';

				if (file_exists($path))
				{
					JLoader::register($class, $path);
				}
			}

			try
			{
				$instance = new $class($options);
			}
			catch (Exception $e)
			{
			}

			self::$instances[$signature] = $instance;
		}

		return self::$instances[$signature];
	}

	/**
	 * Upload
	 *
	 * @param   JInput|array  $data  Data for upload
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	abstract protected function upload($data);
}
