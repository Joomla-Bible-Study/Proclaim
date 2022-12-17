<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
use Joomla\CMS\Filesystem\Folder;
use Joomla\Input\Input;
use Symfony\Component\Config\Loader\Loader;

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * Abstract Server class
 *
 * @since  9.0.0
 */
abstract class CWMServer
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
	public static function getServers(): array
	{
		$servers = array();

		$types = Folder::folders(__DIR__);

		foreach ($types as $type)
		{
			// Derive the class name from the type.
			$class = 'CWMServer' . ucfirst(trim($type));

			if (!class_exists($class))
			{
				$path = __DIR__ . '/' . $type . '.php';

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
	public static function getInstance(array $options = array())
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
				$path = __DIR__ . '/' . $options['type'] . '.php';

				if (file_exists($path))
				{
					Loader::register($class, $path);
				}
			}

			try
			{
				$instance = new $class($options);
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage("Error obtaining Class '" . $options['type'] . "'", Error);
			}

			self::$instances[$signature] = $instance;
		}

		return self::$instances[$signature];
	}

	/**
	 * Upload
	 *
	 * @param   Input|array  $data  Data for upload
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	abstract protected function upload($data);
}
