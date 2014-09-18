<?php
/* Add proper header */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * Abstract Server class
 *
 * @since   8.1.0
 */
abstract class JBSMAddon
{

    /**
     * Addon configuration
     *
     * @var     object
     * @since   8.1.0
     */
    protected $_xml = null;

    protected $name = null;

    protected $description = null;

    protected $config = null;

    /**
     * The type of server
     *
     * @var     string
     * @since   8.1.0
     */
    protected $type;

    public function __construct($config = array())
    {

        if (empty($this->type)) {
            if (array_key_exists('type', $config)) {
                $this->type = $config['type'];
            } else {
                $this->type = $this->getType();
            }
        }
        if (empty($this->_xml)) {
            $this->_xml = $this->getXml();

            if($this->_xml) {
                $this->name = $this->_xml->name->__toString();
                $this->description = $this->_xml->description->__toString();
                $this->config = $this->_xml->config;
            }
        }
    }

    /**
     * Gets the type of addon loaded based on the class name
     *
     * @return  string
     * @throws  Exception
     * @since   8.1.0
     */
    public function getType()
    {
        if (empty($this->type)) {
            $r = null;
            if (!preg_match('/JBSMAddon(.*)/i', get_class($this), $r)) {
                //@TODO Changed to a localized exception
                throw new Exception(JText::sprintf('CANT ADDON CLASS NAME'), 500);
            }
            $this->type = strtolower($r[1]);
        }

        return $this->type;
    }

    /**
     * Loads the addon configuration from the xml file
     *
     * @return  bool|SimpleXMLElement
     * @throws  Exception
     * @since   8.1.0
     */
    public function getXml()
    {
        $path = JPath::find(BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . $this->type, $this->type . '.xml');
        if ($path) {
            $xml = simplexml_load_file($path);
        } else {
            //@TODO Need to properly translate this string
            throw new Exception(JText::sprintf('COULD NOT LOAD ADDON CONFIGURATION'), 404);

            return false;
        }

        return $xml;
    }

    /**
     * Returns a Addon object, always creating it
     *
     * @param $type
     * @param array $config
     * @return bool
     * @since   8.1.0
     */
    public static function getInstance($type, $config = array())
    {
        $type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
        $addonClass = "JBSMAddon" . ucfirst($type);

        if (!class_exists($addonClass)) {
            jimport('joomla.filesystem.path');
            $path = JPath::find(BIBLESTUDY_PATH_ADMIN . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . $type, $type . '.php');
            if ($path) {
                require_once $path;

                if (!class_exists($addonClass)) {
                    //@TODO Need to properly translate this string
                    JLog::add(JText::sprintf('COULD NOT LOAD ADDON CLASS', $addonClass), JLog::WARNING, 'jerror');
                    return false;
                }
            } else {
                return false;
            }
        }

        return new $addonClass($config);
    }

    abstract protected function upload($target);

}