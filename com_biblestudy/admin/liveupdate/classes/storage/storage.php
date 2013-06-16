<?php
/**
 * Live Update Package
 *
 * @package    LiveUpdate
 * @copyright  Copyright (c)2010-2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license    GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 */

defined('_JEXEC') or die();

/**
 * Abstract class for the update parameters storage
 *
 * @package    LiveUpdate
 * @author     nicholas
 * @copyright  Copyright (c)2010-2013 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license    GNU LGPLv3 or later <http://www.gnu.org/copyleft/lesser.html>
 * @since      8.0.0
 */
class LiveUpdateStorage
{
	/**
	 * The update data registry
	 *
	 * @var JRegistry
	 */
	public static $registry = null;

	/**
	 * Instance
	 *
	 * @param   string $type    ?
	 * @param   array  $config  ?
	 *
	 * @return LiveUpdateStorage
	 */
	public static function getInstance($type, $config)
	{
		static $instances = array();

		$sig = md5($type, serialize($config));

		if (!array_key_exists($sig, $instances))
		{
			require_once dirname(__FILE__) . '/' . strtolower($type) . '.php';
			$className = 'LiveUpdateStorage' . ucfirst($type);
			$object    = new $className($config);
			$object->load($config);
			$newRegistry = clone(self::$registry);
			$object->setRegistry($newRegistry);
			$instances[$sig] = $object;
		}

		return $instances[$sig];
	}

	/**
	 * Returns the internally used registry
	 *
	 * @return JRegistry
	 */
	public function &getRegistry()
	{
		return self::$registry;
	}

	/**
	 * Replaces the internally used registry with the one supplied
	 *
	 * @param   JRegistry $registry  ?
	 *
	 * @return void
	 */
	public function setRegistry($registry)
	{
		self::$registry = $registry;
	}

	/**
	 * Set
	 *
	 * @param   string $key    ?
	 * @param   string $value  ?
	 *
	 * @return void
	 */
	public final function set($key, $value)
	{
		if ($key == 'updatedata')
		{
			if (function_exists('json_encode') && function_exists('json_decode'))
			{
				$value = json_encode($value);
			}
			elseif (function_exists('base64_encode') && function_exists('base64_decode'))
			{
				$value = base64_encode(serialize($value));
			}
			else
			{
				$value = serialize($value);
			}
		}
		self::$registry->set("update.$key", $value);
	}

	/**
	 * Get
	 *
	 * @param   string $key      ?
	 * @param   string $default  ?
	 *
	 * @return mixed
	 */
	public final function get($key, $default)
	{
		$value = self::$registry->get("update.$key", $default);

		if ($key == 'updatedata')
		{
			if (function_exists('json_encode') && function_exists('json_decode'))
			{
				$value = json_decode($value);
			}
			elseif (function_exists('base64_encode') && function_exists('base64_decode'))
			{
				$value = unserialize(base64_decode($value));
			}
			else
			{
				$value = unserialize($value);
			}
		}

		return $value;
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save()
	{
	}

	/**
	 * Load
	 *
	 * @param   string $config  ?
	 *
	 * @return void
	 */
	public function load($config)
	{
	}
}
