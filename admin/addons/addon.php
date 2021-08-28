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

JLoader::discover('JBSM', BIBLESTUDY_PATH_ADMIN_ADDON . '/servers/', 'true', 'true');

/**
 * Abstract Server class
 *
 * @since  9.0.0
 */
abstract class JBSMAddon
{
	/**
	 * Addon configuration
	 *
	 * @var     object
	 * @since   9.0.0
	 */
	protected $xml = null;

	/**
	 * Name of Add-on
	 *
	 * @var     string
	 * @since   9.0.0
	 */
	protected $name = '';

	/**
	 * Description of add-on
	 *
	 * @var     string
	 * @since   9.0.0
	 */
	protected $description = '';

	/**
	 * Config information
	 *
	 * @var     string
	 * @since   9.0.0
	 */
	protected $config = '';

	/**
	 * The type of server
	 *
	 * @var     string
	 * @since   9.0.0
	 */
	protected $type = '';

	/**
	 * Construct
	 *
	 * @param   array  $config  Array of Obtains
	 *
	 * @throws \Exception
	 *
	 * @since 9.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($this->type))
		{
			if (array_key_exists('type', $config))
			{
				$this->type = $config['type'];
			}
			else
			{
				$this->type = $this->getType();
			}
		}

		if (empty($this->xml))
		{
			$this->xml = $this->getXml();

			if ($this->xml)
			{
				$this->name        = $this->xml->name->__toString();
				$this->description = $this->xml->description->__toString();
				$this->config      = $this->xml->config;
			}
		}
	}

	/**
	 * Gets the type of addon loaded based on the class name
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 * @since   9.0.0
	 */
	public function getType()
	{
		if (empty($this->type))
		{
			$r = null;

			if (!preg_match('/JBSMAddon(.*)/i', get_class($this), $r))
			{
				throw new \RuntimeException(JText::sprintf('JBS_CMN_CANT_ADDON_CLASS_NAME', $this->type), 500);
			}

			$this->type = strtolower($r[1]);
		}

		return $this->type;
	}

	/**
	 * Loads the addon configuration from the xml file
	 *
	 * @return  boolean|SimpleXMLElement
	 *
	 * @throws  Exception
	 * @since   9.0.0
	 */
	public function getXml()
	{
		$path = JPath::find(BIBLESTUDY_PATH_ADMIN . '/addons/servers/' . $this->type, $this->type . '.xml');

		if ($path)
		{
			$xml = simplexml_load_string(file_get_contents($path));
		}
		else
		{
			throw new Exception(JText::_('JBS_CMN_COULD_NOT_LOAD_ADDON_CONFIGURATION'), 404);
		}

		return $xml;
	}

	/**
	 * Returns a Addon object, always creating it
	 *
	 * @param   string  $type    ?
	 * @param   array   $config  ?
	 *
	 * @return boolean
	 *
	 * @since   9.0.0
	 */
	public static function getInstance($type, $config = array())
	{
		$type       = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));
		$addonClass = "JBSMAddon" . ucfirst($type);

		if (!class_exists($addonClass))
		{
			jimport('joomla.filesystem.path');
			$path = JPath::find(BIBLESTUDY_PATH_ADMIN . '/addons/servers/' . $type . '/', $type . '.php');

			// Try and load missing class
			JLoader::register($addonClass, $path);

			if (!$path)
			{
				JLog::add(JText::sprintf('JBS_CMN_CANT_ADDON_LOAD_CLASS_NAME', $addonClass), JLog::WARNING, 'jerror');

				return false;
			}
		}

		return new $addonClass($config);
	}

	/**
	 * Render Fields for general view.
	 *
	 * @param   object  $media_form  Media files form
	 * @param   bool    $new         If media is new
	 *
	 * @return string
	 *
	 * @since 9.1.3
	 */
	abstract protected function renderGeneral($media_form, $new);

	/**
	 * Render Layout and fields
	 *
	 * @param   object  $media_form  Media files form
	 * @param   bool    $new         If media is new
	 *
	 * @return string
	 *
	 * @since 9.1.3
	 */
	abstract protected function render($media_form, $new);

	/**
	 * Upload
	 *
	 * @param   JInput|array  $data  Data to upload
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	abstract protected function upload($data);
}
