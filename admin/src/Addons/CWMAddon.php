<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;
use SimpleXMLElement;

/**
 * Abstract Server class
 *
 * @since  9.0.0
 */
abstract class CWMAddon
{
    /**
     * Path for the class to load
     *
     * @var bool|string
     * @since 10.0.0
     */
    private static string|bool $path;

    /**
     * Addon configuration
     *
     * @var     bool|null|SimpleXMLElement
     * @since   9.0.0
     */
    protected bool|null|SimpleXMLElement $xml = null;

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
     * @param array $config Array of Obtains
     *
     * @throws \Exception
     *
     * @since 9.0.0
     */
    public function __construct(array $config = array())
    {
        if (empty($this->type)) {
            if (array_key_exists('type', $config)) {
                $this->type = $config['type'];
            } else {
                $this->type = $this->getType();
            }
        }

        if (empty($this->xml)) {
            $this->xml = $this->getXml();

            if ($this->xml) {
                $this->name = $this->xml->name->__toString();
                $this->description = $this->xml->description->__toString();
                $this->config = $this->xml->config;
            }
        }
    }

    /**
     * Gets the type of addon loaded based on the class name
     *
     * @return  string
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function getType(): string
    {
        if (empty($this->type)) {
            $r = null;

            if (!preg_match('/CWMAddon(.*)/i', get_class($this), $r)) {
                throw new \RuntimeException(Text::sprintf('JBS_CMN_CANT_ADDON_CLASS_NAME', $this->type), 500);
            }

            $this->type = strtolower($r[1]);
        }

        return $this->type;
    }

    /**
     * Loads the addon configuration from the xml file
     *
     * @return  bool|SimpleXMLElement
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function getXml(): SimpleXMLElement|bool
    {
        $path = Path::find(BIBLESTUDY_PATH_ADMIN . '/src/Addons/Servers/' . ucfirst($this->type), $this->type . '.xml');

        if ($path) {
            $xml = simplexml_load_string(file_get_contents($path));
        } else {
            throw new \RuntimeException(Text::_('JBS_CMN_COULD_NOT_LOAD_ADDON_CONFIGURATION'), 404);
        }

        return $xml;
    }

    /**
     * Returns a Addon object, always creating it
     *
     * @param string $type ?
     * @param array $config ?
     *
     * @return mixed
     *
     * @since   9.0.0
     */
    public static function getInstance(string $type, array $config = array()): mixed
    {
        $type = ucfirst(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));
        $class = "CWM\Component\Proclaim\Administrator\Addons\Servers\\" . ucfirst($type) . "\CWMAddon" . ucfirst(
            $type
        );

        return new $class($config);
    }

    /**
     * Render Fields for general view.
     *
     * @param object $media_form Media files form
     * @param bool $new If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    abstract protected function renderGeneral($media_form, bool $new): string;

    /**
     * Render Layout and fields
     *
     * @param object $media_form Media files form
     * @param bool $new If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    abstract protected function render($media_form, bool $new): string;

    /**
     * Upload
     *
     * @param ?array $data Data to upload
     *
     * @return mixed
     *
     * @since 9.0.0
     */
    abstract protected function upload(?array $data): mixed;
}
