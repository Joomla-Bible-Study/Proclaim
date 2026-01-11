<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;

/**
 * Abstract Server class
 *
 * @since  9.0.0
 */
abstract class CWMAddon
{
    /**
     * Addon configuration
     *
     * @var     bool|null|\SimpleXMLElement
     * @since   9.0.0
     */
    protected bool|null|\SimpleXMLElement $xml = null;

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
    public function __construct(array $config = [])
    {
        if (empty($this->type)) {
            if (\array_key_exists('type', $config)) {
                $this->type = $config['type'];
            } else {
                $this->type = $this->getType();
            }
        }

        if (empty($this->xml)) {
            $this->xml = $this->getXml();

            if ($this->xml) {
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
     * @throws  \Exception
     * @since   9.0.0
     */
    public function getType(): string
    {
        if (empty($this->type)) {
            $r = null;

            if (!preg_match('/CWMAddon(.*)/i', \get_class($this), $r)) {
                throw new \RuntimeException(Text::sprintf('JBS_CMN_CANT_ADDON_CLASS_NAME', $this->type), 500);
            }

            $this->type = strtolower($r[1]);
        }

        return $this->type;
    }

    /**
     * Loads the addon configuration from the XML file
     *
     * @return  \SimpleXMLElement  The parsed XML configuration
     *
     * @throws  \RuntimeException  If the configuration file cannot be found or parsed
     * @since   9.0.0
     */
    public function getXml(): \SimpleXMLElement
    {
        $path = Path::find(BIBLESTUDY_PATH_ADMIN . '/src/Addons/Servers/' . ucfirst($this->type), $this->type . '.xml');

        if (!$path) {
            throw new \RuntimeException(Text::_('JBS_CMN_COULD_NOT_LOAD_ADDON_CONFIGURATION'), 404);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException(Text::sprintf('JBS_CMN_COULD_NOT_READ_ADDON_FILE', $path), 500);
        }

        $xml = simplexml_load_string($contents);

        if ($xml === false) {
            throw new \RuntimeException(Text::sprintf('JBS_CMN_COULD_NOT_PARSE_ADDON_XML', $path), 500);
        }

        return $xml;
    }

    /**
     * Returns an Addon object, always creating it
     *
     * @param   string  $type    The addon type to instantiate
     * @param   array   $config  Configuration options for the addon
     *
     * @return  static  The addon instance
     *
     * @throws  \RuntimeException  If the addon class does not exist
     * @since   9.0.0
     */
    public static function getInstance(string $type, array $config = []): static
    {
        $type  = ucfirst(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));
        $class = "CWM\\Component\\Proclaim\\Administrator\\Addons\\Servers\\" . ucfirst($type) . "\\CWMAddon" . ucfirst(
            $type
        );

        if (!class_exists($class)) {
            throw new \RuntimeException(Text::sprintf('JBS_CMN_ADDON_CLASS_NOT_FOUND', $type), 404);
        }

        return new $class($config);
    }

    /**
     * Render Fields for a general view.
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

    /**
     * Get available AJAX actions for this addon
     *
     * Override in child classes to register available AJAX actions.
     * Return an array of action names that map to handle{ActionName}Action methods.
     *
     * @return  array  List of available action names (e.g., ['testApi', 'fetchVideos'])
     *
     * @since   10.0.0
     */
    public function getAjaxActions(): array
    {
        return [];
    }

    /**
     * Handle an AJAX action request
     *
     * This method dispatches to the appropriate handler method based on the action name.
     * Handler methods should be named handle{ActionName}Action (e.g., handleTestApiAction).
     *
     * @param   string  $action  The action name to handle
     *
     * @return  array  Response data array with 'success' key and additional data
     *
     * @throws  \RuntimeException  If the action is not supported
     * @since   10.0.0
     */
    public function handleAjaxAction(string $action): array
    {
        $availableActions = $this->getAjaxActions();

        if (!\in_array($action, $availableActions, true)) {
            return [
                'success' => false,
                'error'   => Text::sprintf('JBS_CMN_ADDON_ACTION_NOT_SUPPORTED', $action, $this->type),
            ];
        }

        // Convert action name to method name (e.g., 'testApi' -> 'handleTestApiAction')
        $methodName = 'handle' . ucfirst($action) . 'Action';

        if (!method_exists($this, $methodName)) {
            return [
                'success' => false,
                'error'   => Text::sprintf('JBS_CMN_ADDON_ACTION_METHOD_NOT_FOUND', $methodName, $this->type),
            ];
        }

        return $this->$methodName();
    }

    /**
     * Prepare environment for AJAX response (suppress errors, clear buffers)
     *
     * Call this at the start of an AJAX handler to ensure clean JSON output.
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public static function prepareAjaxEnvironment(): void
    {
        // Suppress any error output that might corrupt JSON
        @ini_set('display_errors', '0');
        @error_reporting(0);

        // Clear any output buffers completely
        while (@ob_get_level()) {
            @ob_end_clean();
        }
    }

    /**
     * Output JSON response and terminate
     *
     * @param   array  $data  The data to encode as JSON
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public static function outputJson(array $data): void
    {
        // Clear all output buffers
        while (@ob_get_level()) {
            @ob_end_clean();
        }

        // Send headers before any output
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        // Encode and output JSON
        $json = json_encode($data);

        if ($json === false) {
            $json = '{"success":false,"error":"JSON encoding failed"}';
        }

        echo $json;

        // Force flush and terminate
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        exit;
    }

    /**
     * Handle a generic AJAX request for any addon
     *
     * This static method is the main entry point for addon AJAX requests from the controller.
     * It loads the appropriate addon, prepares the environment, and dispatches to the action handler.
     *
     * @param   string  $addonType  The addon type (e.g., 'youtube', 'vimeo')
     * @param   string  $action     The action to perform
     *
     * @return  void  Outputs JSON and exits
     *
     * @since   10.0.0
     */
    public static function handleAjaxRequest(string $addonType, string $action): void
    {
        self::prepareAjaxEnvironment();

        try {
            // Load the addon
            $addon = self::getInstance($addonType);

            // Start buffering to capture any deprecation warnings from vendor code
            ob_start();

            // Handle the action
            $result = @$addon->handleAjaxAction($action);

            // Discard any output from vendor code (deprecation warnings, etc.)
            ob_end_clean();

            self::outputJson($result);
        } catch (\Exception $e) {
            self::outputJson([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Load addon language file
     *
     * @return  void
     *
     * @since   10.0.0
     */
    protected function loadLanguage(): void
    {
        $lang = Factory::getApplication()->getLanguage();
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($this->type);
        $lang->load('jbs_addon_' . strtolower($this->type), $path);
    }
}
