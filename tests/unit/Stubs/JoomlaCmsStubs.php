<?php

/**
 * Joomla CMS class stubs for unit testing.
 *
 * These stubs provide the Joomla\CMS\* class hierarchy that is NOT available
 * as Composer packages. Framework packages (joomla/database, joomla/registry,
 * joomla/input, joomla/event, joomla/di, joomla/filesystem) are loaded from
 * real Composer dev dependencies — see composer.json require-dev.
 *
 * IMPORTANT: Method signatures MUST match the real Joomla CMS signatures.
 * If our source code has an incompatible override, fix the source — not these stubs.
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
// MVC Controller stubs
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
// Table stubs
// ============================================================================

namespace Joomla\CMS\Table {

    if (!class_exists('Joomla\CMS\Table\Table', false)) {
        class Table
        {
            protected string $_tbl = '';
            protected $_tbl_key = 'id';
            protected array $_tbl_keys = [];
            protected $_rules;

            public function __construct($table = '', $key = 'id', $db = null, $dispatcher = null)
            {
                $this->_tbl     = $table;
                $this->_tbl_key = $key;
            }

            public function bind($src, $ignore = ''): bool { return true; }
            public function check(): bool { return true; }
            public function store($updateNulls = false): bool { return true; }
            public function load($keys = null, $reset = true): bool { return true; }
            public function delete($pk = null): bool { return true; }
            public function checkIn($pk = null): bool { return true; }
            public function checkOut($userId, $pk = null): bool { return true; }
            public function publish($pks = null, $state = 1, $userId = 0): bool { return true; }
            public function getDatabase() { return null; }
            public function getDbo() { return null; }
            public function getKeyName($multiple = false) { return 'id'; }
            public function getTableName(): string { return ''; }
            public function reset(): void {}
            public function getRules() { return $this->_rules; }
            public function setRules($input): void { $this->_rules = $input; }
            protected function _getAssetName(): string { return ''; }
            protected function _getAssetTitle(): string { return ''; }
            protected function _getAssetParentId(?Table $table = null, $id = null): int { return 1; }
        }
    }
}

// ============================================================================
// Form stubs
// ============================================================================

namespace Joomla\CMS\Form {

    if (!class_exists('Joomla\CMS\Form\FormField', false)) {
        class FormField
        {
            protected $type;
            protected $name;
            protected $value;
            protected $element;

            public function __construct($form = null) {}
            protected function getInput(): string { return ''; }
            protected function getLabel(): string { return ''; }
            public function setup(\SimpleXMLElement $element, $value, $group = null): bool { return true; }
            protected function getOptions(): array { return []; }
            protected function getLayoutData(): array { return []; }
        }
    }
}

namespace Joomla\CMS\Form\Field {

    use Joomla\CMS\Form\FormField;

    if (!class_exists('Joomla\CMS\Form\Field\ListField', false)) {
        class ListField extends FormField
        {
            protected $type = 'List';
            protected function getOptions(): array { return []; }
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
            protected function getOptions(): array { return []; }
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
            public function __construct($dispatcher = null) {}
            public function getMVCFactory() { return null; }
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
// Service interface stubs
// ============================================================================

namespace Joomla\CMS\Fields {
    if (!interface_exists('Joomla\CMS\Fields\FieldsServiceInterface', false)) {
        interface FieldsServiceInterface {}
    }
}

namespace Joomla\CMS\Component\Router {
    if (!interface_exists('Joomla\CMS\Component\Router\RouterServiceInterface', false)) {
        interface RouterServiceInterface {}
    }
    if (!trait_exists('Joomla\CMS\Component\Router\RouterServiceTrait', false)) {
        trait RouterServiceTrait {}
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

namespace Joomla\CMS\Workflow {
    if (!interface_exists('Joomla\CMS\Workflow\WorkflowServiceInterface', false)) {
        interface WorkflowServiceInterface {}
    }
    if (!trait_exists('Joomla\CMS\Workflow\WorkflowServiceTrait', false)) {
        trait WorkflowServiceTrait {}
    }
}

namespace Joomla\CMS\HTML {
    if (!trait_exists('Joomla\CMS\HTML\HTMLRegistryAwareTrait', false)) {
        trait HTMLRegistryAwareTrait {}
    }
}

namespace Joomla\CMS\Versioning {
    if (!trait_exists('Joomla\CMS\Versioning\VersionableModelTrait', false)) {
        trait VersionableModelTrait {}
    }
}

// ============================================================================
// CMS utility stubs
// ============================================================================

namespace Joomla\CMS\Uri {
    if (!class_exists('Joomla\CMS\Uri\Uri', false)) {
        class Uri
        {
            public static function root($pathonly = false, $path = null): string { return $pathonly ? '/' : 'http://localhost/'; }
            public static function base($pathonly = false): string { return $pathonly ? '/' : 'http://localhost/'; }
            public static function current(): string { return 'http://localhost/'; }
            public static function isInternal($url): bool { return true; }
        }
    }
}

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

            public static function getContainer() { return self::$container; }
            public static function setContainer($container): void { self::$container = $container; }
        }
    }
}

namespace Joomla\CMS\Language {
    if (!class_exists('Joomla\CMS\Language\Text', false)) {
        class Text
        {
            public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false): string { return $string; }
            public static function sprintf($string, ...$args): string { return vsprintf($string, $args); }
            public static function plural($string, $n): string { return $string; }
            public static function script($string): string { return $string; }
        }
    }

    if (!class_exists('Joomla\CMS\Language\Multilanguage', false)) {
        class Multilanguage
        {
            public static function isEnabled(): bool { return false; }
        }
    }

    if (!class_exists('Joomla\CMS\Language\LanguageHelper', false)) {
        class LanguageHelper
        {
            public static function getLanguages($key = 'default'): array { return []; }
        }
    }
}

namespace Joomla\CMS\Application {
    if (!class_exists('Joomla\CMS\Application\CMSApplication', false)) {
        class CMSApplication
        {
            public function getInput() { return new \Joomla\Input\Input(); }
            public function getIdentity()
            {
                $user = new \Joomla\CMS\User\User();
                $user->id = 42;
                return $user;
            }
            public function isClient(string $identifier): bool { return false; }
            public function enqueueMessage(string $msg, string $type = 'message'): void {}
            public function getSession() { return null; }
            public function getMessageQueue(): array { return []; }
            public function get($name, $default = null) { return $default; }
            public function getDocument() { return null; }
            public function getUserState($key, $default = null) { return $default; }
            public function setUserState($key, $value) { return $value; }
            public function triggerEvent(string $event, array $args = []): array { return []; }
        }
    }

    if (!interface_exists('Joomla\CMS\Application\CMSApplicationInterface', false)) {
        interface CMSApplicationInterface {}
    }

    if (!class_exists('Joomla\CMS\Application\SiteApplication', false)) {
        class SiteApplication extends CMSApplication {}
    }

    if (!class_exists('Joomla\CMS\Application\ApplicationHelper', false)) {
        class ApplicationHelper
        {
            public static function stringURLSafe($string): string
            {
                return preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $string)));
            }
        }
    }
}

namespace Joomla\CMS\Plugin {
    if (!class_exists('Joomla\CMS\Plugin\CMSPlugin', false)) {
        class CMSPlugin
        {
            protected $params;
            public function __construct($subject = null, $config = []) {}
            protected function getApplication() { return null; }
        }
    }

    if (!class_exists('Joomla\CMS\Plugin\PluginHelper', false)) {
        class PluginHelper
        {
            public static function importPlugin($type, $plugin = null, $autocreate = true): void {}
            public static function isEnabled($type, $plugin = null): bool { return false; }
        }
    }
}

namespace Joomla\CMS\Component {
    if (!class_exists('Joomla\CMS\Component\ComponentHelper', false)) {
        class ComponentHelper
        {
            public static function getParams($option, $strict = false) { return new \Joomla\Registry\Registry(); }
        }
    }
}

namespace Joomla\CMS\Session {
    if (!class_exists('Joomla\CMS\Session\Session', false)) {
        class Session
        {
            public static function checkToken($method = 'post'): bool { return true; }
        }
    }
}

namespace Joomla\CMS\Response {
    if (!class_exists('Joomla\CMS\Response\JsonResponse', false)) {
        class JsonResponse
        {
            public function __construct($response = null, $message = null, $error = false, $ignoreMessages = false) {}
            public function __toString(): string { return '{}'; }
        }
    }
}

namespace Joomla\CMS\Router {
    if (!class_exists('Joomla\CMS\Router\Route', false)) {
        class Route
        {
            public static function _($url, $xhtml = true, $tls = null, $absolute = false): string { return $url; }
        }
    }
}

namespace Joomla\CMS\Toolbar {
    if (!class_exists('Joomla\CMS\Toolbar\ToolbarHelper', false)) {
        class ToolbarHelper
        {
            public static function title($title, $icon = 'generic.png'): void {}
            public static function addNew($task = '', $alt = 'JTOOLBAR_NEW'): void {}
            public static function save($task = '', $alt = 'JTOOLBAR_SAVE'): void {}
            public static function cancel($task = '', $alt = 'JTOOLBAR_CANCEL'): void {}
            public static function deleteList($msg = '', $task = '', $alt = 'JTOOLBAR_DELETE'): void {}
            public static function publish($task = '', $alt = 'JTOOLBAR_PUBLISH', $check = false): void {}
            public static function unpublish($task = '', $alt = 'JTOOLBAR_UNPUBLISH', $check = false): void {}
            public static function help($ref, $com = false, $override = null, $component = null): void {}
            public static function preferences($component, $height = '550', $width = '875', $alt = 'JToolbar_Options'): void {}
        }
    }
}

namespace Joomla\CMS\Access {
    if (!class_exists('Joomla\CMS\Access\Rules', false)) {
        class Rules
        {
            public function __construct($input = '') {}
        }
    }
}

namespace Joomla\CMS\Log {
    if (!class_exists('Joomla\CMS\Log\Log', false)) {
        class Log
        {
            public const int ALL     = 0;
            public const int ERROR   = 2;
            public const int WARNING = 4;
            public const int INFO    = 6;

            public static function add(string $entry, int $priority = self::INFO, string $category = '', ?string $date = null): void {}
            public static function addLogger(array $options, int $priorities = self::ALL, array $categories = []): void {}
        }
    }
}

namespace Joomla\CMS\Image {
    if (!class_exists('Joomla\CMS\Image\Image', false)) {
        class Image {}
    }
}

namespace Joomla\CMS\Date {
    if (!class_exists('Joomla\CMS\Date\Date', false)) {
        class Date extends \DateTime
        {
            public function toSql(): string { return $this->format('Y-m-d H:i:s'); }
        }
    }
}

namespace Joomla\CMS\User {
    if (!class_exists('Joomla\CMS\User\User', false)) {
        class User
        {
            public int $id          = 0;
            public string $name     = '';
            public string $username = '';
            public string $email    = '';

            public function authorise(string $action, ?string $assetname = null): bool { return true; }
            public function getAuthorisedViewLevels(): array { return [1]; }
        }
    }
}

// ============================================================================
// Core component stubs
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

namespace Joomla\Component\Content\Administrator\Helper {
    if (!class_exists('Joomla\Component\Content\Administrator\Helper\ContentHelper', false)) {
        class ContentHelper
        {
            public static function getActions(string $component, string $section = '', int $id = 0): object
            {
                return new class {
                    public function get(string $action, $default = null): bool { return true; }
                };
            }
        }
    }
}

namespace Joomla\Component\Actionlogs\Administrator\Helper {
    if (!class_exists('Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper', false)) {
        class ActionlogsHelper
        {
            public static function addLog($messages, $messageLanguageKey, $context, $userId = null): void {}
        }
    }
}

namespace Joomla\CMS\HTML {
    if (!class_exists('Joomla\CMS\HTML\HTMLHelper', false)) {
        class HTMLHelper
        {
            public static function cleanImageURL($url): object
            {
                $obj = new \stdClass();
                $obj->url = $url ?? '';
                $obj->attributes = ['width' => 0, 'height' => 0];
                return $obj;
            }
        }
    }
}

namespace Joomla\CMS\Filter {
    if (!class_exists('Joomla\CMS\Filter\InputFilter', false)) {
        class InputFilter
        {
            public static function getInstance($tagsArray = [], $attrArray = [], $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1): static
            {
                return new static();
            }

            public function clean($source, $type = 'string')
            {
                return $source;
            }
        }
    }
}
