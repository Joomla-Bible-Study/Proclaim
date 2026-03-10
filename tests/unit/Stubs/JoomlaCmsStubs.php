<?php

/**
 * Joomla CMS class stubs for unit testing.
 *
 * These minimal stubs allow our source classes to be loaded by PHP's autoloader
 * without requiring the full Joomla CMS framework. They provide the class hierarchy
 * (extends/implements/traits) so that ReflectionClass/ReflectionMethod works on
 * our Proclaim classes.
 *
 * IMPORTANT: Method signatures here MUST match the real Joomla CMS signatures.
 * If our source code has an incompatible override, fix the source code — not these stubs.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.1.0
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR2.Classes.PropertyDeclaration
// phpcs:disable PSR12.Files.FileHeader

// ============================================================================
// MVC Controller stubs — matches Joomla 5.x signatures
// ============================================================================

namespace Joomla\CMS\MVC\Controller {

    if (!class_exists('Joomla\CMS\MVC\Controller\BaseController', false)) {
        class BaseController
        {
            protected $input;

            public function __construct($config = [], $factory = null, $app = null, $input = null)
            {
            }

            public function display($cachable = false, $urlparams = [])
            {
                return $this;
            }

            public function getModel($name = '', $prefix = '', $config = [])
            {
                return null;
            }

            public function execute($task): mixed
            {
                return null;
            }

            public function getTask(): string
            {
                return '';
            }

            public function redirect(): bool
            {
                return true;
            }

            public function setRedirect($url, $msg = null, $type = null)
            {
                return $this;
            }

            public function setMessage($text, $type = 'message')
            {
                return $this;
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Controller\FormController', false)) {
        class FormController extends BaseController
        {
            public function add(): bool
            {
                return true;
            }

            public function cancel($key = null): bool
            {
                return true;
            }

            public function edit($key = null, $urlVar = null): bool
            {
                return true;
            }

            public function save($key = null, $urlVar = null): bool
            {
                return true;
            }

            protected function allowAdd($data = []): bool
            {
                return true;
            }

            protected function allowEdit($data = [], $key = 'id'): bool
            {
                return true;
            }

            protected function postSaveHook(\Joomla\CMS\MVC\Model\BaseDatabaseModel $model, $validData = []): void
            {
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Controller\AdminController', false)) {
        class AdminController extends BaseController
        {
            public function delete(): bool
            {
                return true;
            }

            public function publish(): void
            {
            }

            public function getModel($name = '', $prefix = '', $config = [])
            {
                return null;
            }
        }
    }
}

// ============================================================================
// MVC Model stubs
// ============================================================================

namespace Joomla\CMS\MVC\Model {

    if (!class_exists('Joomla\CMS\MVC\Model\BaseModel', false)) {
        class BaseModel
        {
            public function __construct($config = [])
            {
            }

            public function getState($property = '', $default = null)
            {
                return $default;
            }

            public function setState($property, $value = null)
            {
                return $value;
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Model\BaseDatabaseModel', false)) {
        class BaseDatabaseModel extends BaseModel
        {
            public function getDatabase()
            {
                return null;
            }

            public function getDbo()
            {
                return null;
            }

            public function getTable($name = '', $prefix = '', $options = [])
            {
                return null;
            }

            protected function getCurrentUser()
            {
                return null;
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Model\ListModel', false)) {
        class ListModel extends BaseDatabaseModel
        {
            protected $filter_fields = [];
            protected $context       = '';

            public function getItems()
            {
                return [];
            }

            public function getPagination()
            {
                return null;
            }

            public function getFilterForm($data = [], $loadData = true)
            {
                return null;
            }

            public function getActiveFilters(): array
            {
                return [];
            }

            protected function getListQuery()
            {
                return null;
            }

            protected function getStoreId($id = ''): string
            {
                return md5($id);
            }

            protected function populateState($ordering = null, $direction = null): void
            {
            }

            protected function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
            {
                return $default;
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Model\AdminModel', false)) {
        class AdminModel extends BaseDatabaseModel
        {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }

            public function save($data): bool
            {
                return true;
            }

            public function getItem($pk = null)
            {
                return null;
            }

            public function validate($form, $data, $group = null)
            {
                return $data;
            }

            protected function prepareTable($table): void
            {
            }

            protected function loadFormData()
            {
                return [];
            }

            protected function canDelete($record): bool
            {
                return true;
            }

            protected function canEditState($record): bool
            {
                return true;
            }

            protected function populateState(): void
            {
            }

            public function batch($commands, $pks, $contexts): bool
            {
                return true;
            }

            protected function generateNewTitle($categoryId, $alias, $title): array
            {
                return [$title, $alias];
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Model\FormModel', false)) {
        class FormModel extends BaseDatabaseModel
        {
            public function getForm($data = [], $loadData = true)
            {
                return null;
            }

            public function validate($form, $data, $group = null)
            {
                return $data;
            }

            protected function loadFormData()
            {
                return [];
            }

            protected function populateState(): void
            {
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\Model\ItemModel', false)) {
        class ItemModel extends BaseDatabaseModel
        {
            public function getItem($pk = null)
            {
                return null;
            }

            protected function getStoreId($id = ''): string
            {
                return md5($id);
            }

            protected function populateState(): void
            {
            }
        }
    }
}

// ============================================================================
// MVC View stubs
// ============================================================================

namespace Joomla\CMS\MVC\View {

    if (!class_exists('Joomla\CMS\MVC\View\HtmlView', false)) {
        class HtmlView
        {
            protected $document;

            public function __construct($config = [])
            {
            }

            public function display($tpl = null): void
            {
            }

            public function get($property, $default = null)
            {
                return $default;
            }

            public function getModel($name = null)
            {
                return null;
            }

            public function getLayout(): string
            {
                return 'default';
            }

            public function setLayout($layout): string
            {
                return $layout;
            }
        }
    }

    if (!class_exists('Joomla\CMS\MVC\View\GenericDataException', false)) {
        class GenericDataException extends \RuntimeException
        {
        }
    }
}

// ============================================================================
// MVC Factory stubs
// ============================================================================

namespace Joomla\CMS\MVC\Factory {

    if (!interface_exists('Joomla\CMS\MVC\Factory\MVCFactoryInterface', false)) {
        interface MVCFactoryInterface
        {
            public function createController($name, $prefix, array $config, $app, $input);
            public function createModel($name, $prefix = '', array $config = []);
            public function createView($name, $prefix = '', $type = '', array $config = []);
            public function createTable($name, $prefix = '', array $config = []);
        }
    }
}

// ============================================================================
// Table stubs — matches Joomla 5.x Table class
// ============================================================================

namespace Joomla\CMS\Table {

    if (!class_exists('Joomla\CMS\Table\Table', false)) {
        class Table
        {
            /**
             * @var string Name of the database table
             */
            protected string $_tbl = '';

            /**
             * @var string|array Name of the primary key field(s)
             */
            protected $_tbl_key = 'id';

            /**
             * @var array Array of primary key field names
             */
            protected array $_tbl_keys = [];

            /**
             * @var \Joomla\CMS\Access\Rules|null ACL rules
             */
            protected $_rules;

            public function __construct($table = '', $key = 'id', $db = null, $dispatcher = null)
            {
                $this->_tbl     = $table;
                $this->_tbl_key = $key;
            }

            public function bind($src, $ignore = ''): bool
            {
                return true;
            }

            public function check(): bool
            {
                return true;
            }

            public function store($updateNulls = false): bool
            {
                return true;
            }

            public function load($keys = null, $reset = true): bool
            {
                return true;
            }

            public function delete($pk = null): bool
            {
                return true;
            }

            public function checkIn($pk = null): bool
            {
                return true;
            }

            public function checkOut($userId, $pk = null): bool
            {
                return true;
            }

            public function publish($pks = null, $state = 1, $userId = 0): bool
            {
                return true;
            }

            public function getDatabase()
            {
                return null;
            }

            public function getDbo()
            {
                return null;
            }

            public function getKeyName($multiple = false)
            {
                return 'id';
            }

            public function getTableName(): string
            {
                return '';
            }

            public function reset(): void
            {
            }

            protected function _getAssetName(): string
            {
                return '';
            }

            protected function _getAssetTitle(): string
            {
                return '';
            }

            protected function _getAssetParentId(?Table $table = null, $id = null): int
            {
                return 1;
            }
        }
    }
}

// ============================================================================
// Form stubs — matches Joomla 5.x FormField
// ============================================================================

namespace Joomla\CMS\Form {

    if (!class_exists('Joomla\CMS\Form\FormField', false)) {
        class FormField
        {
            protected $type;
            protected $name;
            protected $value;
            protected $element;

            public function __construct($form = null)
            {
            }

            protected function getInput(): string
            {
                return '';
            }

            protected function getLabel(): string
            {
                return '';
            }

            public function setup(\SimpleXMLElement $element, $value, $group = null): bool
            {
                return true;
            }

            protected function getOptions(): array
            {
                return [];
            }

            protected function getLayoutData(): array
            {
                return [];
            }
        }
    }
}

namespace Joomla\CMS\Form\Field {

    use Joomla\CMS\Form\FormField;

    if (!class_exists('Joomla\CMS\Form\Field\ListField', false)) {
        class ListField extends FormField
        {
            protected $type = 'List';

            protected function getOptions(): array
            {
                return [];
            }
        }
    }

    if (!class_exists('Joomla\CMS\Form\Field\TextField', false)) {
        class TextField extends FormField
        {
            protected $type = 'Text';
        }
    }

    if (!class_exists('Joomla\CMS\Form\Field\RadioField', false)) {
        class RadioField extends FormField
        {
            protected $type = 'Radio';

            protected function getOptions(): array
            {
                return [];
            }
        }
    }

    if (!class_exists('Joomla\CMS\Form\Field\MediaField', false)) {
        class MediaField extends FormField
        {
            protected $type = 'Media';
        }
    }
}

// ============================================================================
// Extension stubs
// ============================================================================

namespace Joomla\CMS\Extension {

    if (!class_exists('Joomla\CMS\Extension\MVCComponent', false)) {
        class MVCComponent
        {
            public function __construct($dispatcher = null)
            {
            }

            public function getMVCFactory()
            {
                return null;
            }
        }
    }

    if (!interface_exists('Joomla\CMS\Extension\BootableExtensionInterface', false)) {
        interface BootableExtensionInterface
        {
            public function boot(\Joomla\DI\Container $container): void;
        }
    }
}

// ============================================================================
// Fields service stubs
// ============================================================================

namespace Joomla\CMS\Fields {

    if (!interface_exists('Joomla\CMS\Fields\FieldsServiceInterface', false)) {
        interface FieldsServiceInterface
        {
        }
    }
}

// ============================================================================
// Router stubs
// ============================================================================

namespace Joomla\CMS\Component\Router {

    if (!interface_exists('Joomla\CMS\Component\Router\RouterServiceInterface', false)) {
        interface RouterServiceInterface
        {
        }
    }

    if (!trait_exists('Joomla\CMS\Component\Router\RouterServiceTrait', false)) {
        trait RouterServiceTrait
        {
        }
    }
}

namespace Joomla\CMS\Component\Router\Rules {

    if (!interface_exists('Joomla\CMS\Component\Router\Rules\RulesInterface', false)) {
        interface RulesInterface
        {
            public function preprocess(&$query): void;
            public function build(&$segments, &$query): void;
            public function parse(&$segments, &$vars): void;
        }
    }
}

// ============================================================================
// Workflow stubs
// ============================================================================

namespace Joomla\CMS\Workflow {

    if (!interface_exists('Joomla\CMS\Workflow\WorkflowServiceInterface', false)) {
        interface WorkflowServiceInterface
        {
        }
    }

    if (!trait_exists('Joomla\CMS\Workflow\WorkflowServiceTrait', false)) {
        trait WorkflowServiceTrait
        {
        }
    }
}

// ============================================================================
// HTML stubs
// ============================================================================

namespace Joomla\CMS\HTML {

    if (!trait_exists('Joomla\CMS\HTML\HTMLRegistryAwareTrait', false)) {
        trait HTMLRegistryAwareTrait
        {
        }
    }
}

// ============================================================================
// Versioning stubs
// ============================================================================

namespace Joomla\CMS\Versioning {

    if (!trait_exists('Joomla\CMS\Versioning\VersionableModelTrait', false)) {
        trait VersionableModelTrait
        {
        }
    }
}

// ============================================================================
// URI stubs
// ============================================================================

namespace Joomla\CMS\Uri {

    if (!class_exists('Joomla\CMS\Uri\Uri', false)) {
        class Uri
        {
            public static function root($pathonly = false, $path = null): string
            {
                return $pathonly ? '/' : 'http://localhost/';
            }

            public static function base($pathonly = false): string
            {
                return $pathonly ? '/' : 'http://localhost/';
            }

            public static function current(): string
            {
                return 'http://localhost/';
            }

            public static function isInternal($url): bool
            {
                return true;
            }
        }
    }
}

// ============================================================================
// Factory stubs
// ============================================================================

namespace Joomla\CMS {

    if (!class_exists('Joomla\CMS\Factory', false)) {
        class Factory
        {
            private static $application;
            private static $container;

            public static function getApplication()
            {
                if (self::$application === null) {
                    self::$application = new \Joomla\CMS\Application\CMSApplication();
                }

                return self::$application;
            }

            public static function getContainer()
            {
                return self::$container;
            }
        }
    }
}

// ============================================================================
// Language stubs
// ============================================================================

namespace Joomla\CMS\Language {

    if (!class_exists('Joomla\CMS\Language\Text', false)) {
        class Text
        {
            public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false): string
            {
                return $string;
            }

            public static function sprintf($string, ...$args): string
            {
                return vsprintf($string, $args);
            }

            public static function plural($string, $n): string
            {
                return $string;
            }
        }
    }

    if (!class_exists('Joomla\CMS\Language\Multilanguage', false)) {
        class Multilanguage
        {
            public static function isEnabled(): bool
            {
                return false;
            }
        }
    }

    if (!class_exists('Joomla\CMS\Language\LanguageHelper', false)) {
        class LanguageHelper
        {
            public static function getLanguages($key = 'default'): array
            {
                return [];
            }
        }
    }
}

// ============================================================================
// Plugin stubs
// ============================================================================

namespace Joomla\CMS\Plugin {

    if (!class_exists('Joomla\CMS\Plugin\CMSPlugin', false)) {
        class CMSPlugin
        {
            protected $params;

            public function __construct($subject = null, $config = [])
            {
            }

            protected function getApplication()
            {
                return null;
            }
        }
    }
}

// ============================================================================
// Component stubs
// ============================================================================

namespace Joomla\CMS\Component {

    if (!class_exists('Joomla\CMS\Component\ComponentHelper', false)) {
        class ComponentHelper
        {
            public static function getParams($option, $strict = false)
            {
                return new \Joomla\Registry\Registry();
            }
        }
    }
}

// ============================================================================
// Content stubs (CONDITION_PUBLISHED constants)
// ============================================================================

namespace Joomla\Component\Content\Administrator\Extension {

    if (!class_exists('Joomla\Component\Content\Administrator\Extension\ContentComponent', false)) {
        class ContentComponent
        {
            public const CONDITION_PUBLISHED   = 1;
            public const CONDITION_UNPUBLISHED = 0;
            public const CONDITION_ARCHIVED    = 2;
            public const CONDITION_TRASHED     = -2;
        }
    }
}

// ============================================================================
// Session stubs
// ============================================================================

namespace Joomla\CMS\Session {

    if (!class_exists('Joomla\CMS\Session\Session', false)) {
        class Session
        {
            public static function checkToken($method = 'post'): bool
            {
                return true;
            }
        }
    }
}

// ============================================================================
// Response stubs
// ============================================================================

namespace Joomla\CMS\Response {

    if (!class_exists('Joomla\CMS\Response\JsonResponse', false)) {
        class JsonResponse
        {
            public function __construct($response = null, $message = null, $error = false, $ignoreMessages = false)
            {
            }

            public function __toString(): string
            {
                return '{}';
            }
        }
    }
}

// ============================================================================
// Router stubs
// ============================================================================

namespace Joomla\CMS\Router {

    if (!class_exists('Joomla\CMS\Router\Route', false)) {
        class Route
        {
            public static function _($url, $xhtml = true, $tls = null, $absolute = false): string
            {
                return $url;
            }
        }
    }
}

// ============================================================================
// Toolbar stubs
// ============================================================================

namespace Joomla\CMS\Toolbar {

    if (!class_exists('Joomla\CMS\Toolbar\ToolbarHelper', false)) {
        class ToolbarHelper
        {
            public static function title($title, $icon = 'generic.png'): void
            {
            }

            public static function addNew($task = '', $alt = 'JTOOLBAR_NEW'): void
            {
            }

            public static function save($task = '', $alt = 'JTOOLBAR_SAVE'): void
            {
            }

            public static function cancel($task = '', $alt = 'JTOOLBAR_CANCEL'): void
            {
            }

            public static function deleteList($msg = '', $task = '', $alt = 'JTOOLBAR_DELETE'): void
            {
            }

            public static function publish($task = '', $alt = 'JTOOLBAR_PUBLISH', $check = false): void
            {
            }

            public static function unpublish($task = '', $alt = 'JTOOLBAR_UNPUBLISH', $check = false): void
            {
            }

            public static function help($ref, $com = false, $override = null, $component = null): void
            {
            }

            public static function preferences($component, $height = '550', $width = '875', $alt = 'JToolbar_Options'): void
            {
            }
        }
    }
}

// ============================================================================
// DI Container stub
// ============================================================================

namespace Joomla\DI {

    if (!class_exists('Joomla\DI\Container', false)) {
        class Container
        {
            public function get($id)
            {
                return null;
            }

            public function has($id): bool
            {
                return false;
            }
        }
    }
}

// ============================================================================
// Database stubs
// ============================================================================

namespace Joomla\Database {

    if (!class_exists('Joomla\Database\DatabaseDriver', false)) {
        abstract class DatabaseDriver
        {
            public function getQuery($new = false)
            {
                return null;
            }

            public function setQuery($query, $offset = 0, $limit = 0)
            {
                return $this;
            }

            public function loadObjectList($key = '', $class = \stdClass::class): array
            {
                return [];
            }

            public function loadObject($class = \stdClass::class)
            {
                return null;
            }

            public function loadResult()
            {
                return null;
            }

            public function execute()
            {
                return false;
            }

            public function quote($text, $escape = true): string
            {
                return "'" . $text . "'";
            }

            public function quoteName($name, $as = null): string|array
            {
                if (\is_array($name)) {
                    return array_map(static fn ($n) => '`' . $n . '`', $name);
                }

                return '`' . $name . '`';
            }

            public function getPrefix(): string
            {
                return '';
            }
        }
    }

    if (!class_exists('Joomla\Database\DatabaseInterface', false)) {
        interface DatabaseInterface
        {
        }
    }
}

// ============================================================================
// Event stubs
// ============================================================================

namespace Joomla\Event {

    if (!interface_exists('Joomla\Event\SubscriberInterface', false)) {
        interface SubscriberInterface
        {
            public static function getSubscribedEvents(): array;
        }
    }
}

// ============================================================================
// Registry stubs
// ============================================================================

namespace Joomla\Registry {

    if (!class_exists('Joomla\Registry\Registry', false)) {
        class Registry
        {
            private array $data = [];

            public function __construct($data = null)
            {
                if (\is_array($data)) {
                    $this->data = $data;
                }
            }

            public function get($path, $default = null)
            {
                return $this->data[$path] ?? $default;
            }

            public function set($path, $value)
            {
                $this->data[$path] = $value;

                return $this;
            }

            public function loadString($data, $format = 'JSON')
            {
                if (\is_string($data) && $data !== '') {
                    $decoded = json_decode($data, true);

                    if (\is_array($decoded)) {
                        $this->data = $decoded;
                    }
                }

                return $this;
            }

            public function loadArray(array $array)
            {
                $this->data = $array;

                return $this;
            }

            public function toArray(): array
            {
                return $this->data;
            }

            public function toString($format = 'JSON', $options = 0): string
            {
                return json_encode($this->data, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) ?: '{}';
            }

            public function remove($path)
            {
                unset($this->data[$path]);

                return $this;
            }

            public function __toString(): string
            {
                return $this->toString();
            }
        }
    }
}

// ============================================================================
// Input stubs
// ============================================================================

namespace Joomla\Input {

    if (!class_exists('Joomla\Input\Input', false)) {
        class Input
        {
            private array $data;

            public function __construct(array $source = [])
            {
                $this->data = $source;
            }

            public function get($name, $default = null, $filter = 'cmd')
            {
                return $this->data[$name] ?? $default;
            }

            public function getInt($name, $default = 0): int
            {
                return (int) ($this->data[$name] ?? $default);
            }

            public function getCmd($name, $default = ''): string
            {
                return (string) ($this->data[$name] ?? $default);
            }

            public function getString($name, $default = ''): string
            {
                return (string) ($this->data[$name] ?? $default);
            }

            public function set($name, $value): void
            {
                $this->data[$name] = $value;
            }

            public function getMethod(): string
            {
                return $_SERVER['REQUEST_METHOD'] ?? 'GET';
            }
        }
    }
}

// ============================================================================
// Application stubs
// ============================================================================

namespace Joomla\CMS\Application {

    if (!class_exists('Joomla\CMS\Application\CMSApplication', false)) {
        class CMSApplication
        {
            public function getInput()
            {
                return new \Joomla\Input\Input();
            }

            public function getIdentity()
            {
                $user     = new \Joomla\CMS\User\User();
                $user->id = 42;

                return $user;
            }

            public function isClient(string $identifier): bool
            {
                return false;
            }

            public function enqueueMessage(string $msg, string $type = 'message'): void
            {
            }

            public function getSession()
            {
                return null;
            }

            public function getMessageQueue(): array
            {
                return [];
            }

            public function get($name, $default = null)
            {
                return $default;
            }

            public function getDocument()
            {
                return null;
            }

            public function triggerEvent(string $event, array $args = []): array
            {
                return [];
            }
        }
    }

    if (!interface_exists('Joomla\CMS\Application\CMSApplicationInterface', false)) {
        interface CMSApplicationInterface
        {
        }
    }
}

// ============================================================================
// Plugin helper stubs
// ============================================================================

namespace Joomla\CMS\Plugin {

    if (!class_exists('Joomla\CMS\Plugin\PluginHelper', false)) {
        class PluginHelper
        {
            public static function importPlugin($type, $plugin = null, $autocreate = true): void
            {
            }

            public static function isEnabled($type, $plugin = null): bool
            {
                return false;
            }
        }
    }
}

// ============================================================================
// Action Log stubs
// ============================================================================

namespace Joomla\Component\Actionlogs\Administrator\Helper {

    if (!class_exists('Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper', false)) {
        class ActionlogsHelper
        {
            public static function addLog($messages, $messageLanguageKey, $context, $userId = null): void
            {
            }
        }
    }
}

// ============================================================================
// Filesystem stubs — Joomla\Filesystem (for Cwmthumbnail integration tests)
// ============================================================================

namespace Joomla\Filesystem {

    if (!class_exists('Joomla\Filesystem\Path', false)) {
        class Path
        {
            public static function clean(string $path, string $ds = DIRECTORY_SEPARATOR): string
            {
                return str_replace(['/', '\\'], $ds, $path);
            }

            /**
             * @param string|array $paths  Directory or array of directories to search
             * @param string       $file   Filename to find
             *
             * @return string|false  Full path if found, false otherwise
             */
            public static function find($paths, string $file)
            {
                if (\is_string($paths)) {
                    $paths = [$paths];
                }

                foreach ($paths as $path) {
                    $fullPath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $file;

                    if (is_file($fullPath)) {
                        return $fullPath;
                    }
                }

                return false;
            }
        }
    }

    if (!class_exists('Joomla\Filesystem\Folder', false)) {
        class Folder
        {
            public static function delete(string $path): bool
            {
                return true;
            }

            public static function create(string $path, int $mode = 0755): bool
            {
                return true;
            }
        }
    }

    if (!class_exists('Joomla\Filesystem\File', false)) {
        class File
        {
            public static function delete(string $file): bool
            {
                return true;
            }
        }
    }
}

// ============================================================================
// Log stub — Joomla\CMS\Log
// ============================================================================

namespace Joomla\CMS\Log {

    if (!class_exists('Joomla\CMS\Log\Log', false)) {
        class Log
        {
            public const int ALL     = 0;
            public const int ERROR   = 2;
            public const int WARNING = 4;
            public const int INFO    = 6;

            public static function add(string $entry, int $priority = self::INFO, string $category = '', ?string $date = null): void
            {
            }

            public static function addLogger(array $options, int $priorities = self::ALL, array $categories = []): void
            {
            }
        }
    }
}

// ============================================================================
// Image stub — Joomla\CMS\Image
// ============================================================================

namespace Joomla\CMS\Image {

    if (!class_exists('Joomla\CMS\Image\Image', false)) {
        class Image
        {
        }
    }
}

// ============================================================================
// User stub — Joomla\CMS\User
// ============================================================================

namespace Joomla\CMS\User {

    if (!class_exists('Joomla\CMS\User\User', false)) {
        class User
        {
            public int $id          = 0;
            public string $name     = '';
            public string $username = '';
            public string $email    = '';
        }
    }
}
