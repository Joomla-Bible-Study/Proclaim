<?php

/**
 * Minimal runtime shims for Joomla CMS classes that tests call.
 *
 * These provide simple return values for Factory, Text, Uri, and Route
 * so tests can run without a full Joomla CMS runtime. The real CMS source
 * provides all OTHER class signatures via the PSR-4 autoloader in bootstrap.
 *
 * These MUST be loaded BEFORE the CMS source autoloader so they take priority
 * over the real classes (which have heavy dependency chains).
 *
 * @package    Proclaim.UnitTest
 * @since      10.3.0
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// phpcs:disable PSR1.Files.SideEffects

namespace Joomla\CMS\Event {

    /**
     * Shim for AbstractImmutableEvent — Joomla 5.4.x CMS source has offsetSet()
     * without : void return type, but joomla/event v4.0 requires it.
     * This shim provides a compatible definition that satisfies both.
     */
    class AbstractImmutableEvent extends \Joomla\Event\AbstractEvent
    {
        public function offsetSet($name, $value): void
        {
            throw new \BadMethodCallException('Immutable event');
        }

        public function offsetUnset($name): void
        {
            throw new \BadMethodCallException('Immutable event');
        }
    }

    class AbstractEvent extends AbstractImmutableEvent
    {
        public static function create(string $eventName, array $arguments = [])
        {
            return new static($eventName, $arguments);
        }
    }
}

namespace Joomla\CMS {

    class Factory
    {
        public static $application;
        private static $container;

        public static function getApplication()
        {
            if (self::$application === null) {
                self::$application = new Application\CMSApplication();
            }

            return self::$application;
        }

        public static function getContainer()
        {
            return self::$container;
        }
        public static function setContainer($container): void
        {
            self::$container = $container;
        }
    }
}

namespace Joomla\CMS\Application {

    class CMSApplication
    {
        protected $input;
        protected $config;

        public function __construct()
        {
            $this->input  = new \Joomla\Input\Input();
            $this->config = new \Joomla\Registry\Registry();
        }

        public function getInput()
        {
            return $this->input;
        }

        public function getIdentity()
        {
            $user     = new \Joomla\CMS\User\User();
            $user->id = 42;

            return $user;
        }

        public function isClient($identifier): bool
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
        public function getUserState($key, $default = null)
        {
            return $default;
        }
        public function setUserState($key, $value)
        {
            return $value;
        }
        public function triggerEvent(string $event, array $args = []): array
        {
            return [];
        }
        public function getLanguage()
        {
            return new \Joomla\CMS\Language\Language();
        }
        public function bootComponent(string $component)
        {
            return null;
        }
    }

    class SiteApplication extends CMSApplication
    {
    }

    class ApplicationHelper
    {
        public static function stringURLSafe($string): string
        {
            return preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $string)));
        }
    }

    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\CMS\Language {

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
        public static function script($string): string
        {
            return $string;
        }
    }

    class Language
    {
        public function getTag(): string
        {
            return 'en-GB';
        }
        public function load($extension = '', $basePath = ''): bool
        {
            return true;
        }
    }

    class Multilanguage
    {
        public static function isEnabled(): bool
        {
            return false;
        }
    }

    class LanguageHelper
    {
        public static function getLanguages($key = 'default'): array
        {
            return [];
        }
    }
}

namespace Joomla\CMS\Uri {

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
        public static function getInstance($uri = 'SERVER'): self
        {
            return new self();
        }
        public function toString(array $parts = []): string
        {
            return 'http://localhost/';
        }
    }
}

namespace Joomla\CMS\Router {

    class Route
    {
        public static function _($url, $xhtml = true, $tls = null, $absolute = false): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\User {

    class User
    {
        public int $id          = 0;
        public string $name     = '';
        public string $username = '';
        public string $email    = '';

        public function authorise(string $action, ?string $assetname = null): bool
        {
            return true;
        }
        public function getAuthorisedViewLevels(): array
        {
            return [1];
        }
    }
}

namespace Joomla\CMS\Session {

    class Session
    {
        public static function checkToken($method = 'post'): bool
        {
            return true;
        }
    }
}

namespace Joomla\CMS\Component {

    class ComponentHelper
    {
        public static function getParams($option, $strict = false)
        {
            return new \Joomla\Registry\Registry();
        }
    }
}

namespace Joomla\CMS\Plugin {

    class PluginHelper
    {
        public static function importPlugin($type, $plugin = null, $autocreate = true, $dispatcher = null): void
        {
        }
        public static function isEnabled($type, $plugin = null): bool
        {
            return false;
        }
    }
}

namespace Joomla\CMS\Log {

    class Log
    {
        public const int ALL     = 0;
        public const int ERROR   = 2;
        public const int WARNING = 4;
        public const int INFO    = 6;

        public static function add(string $entry, int $priority = self::INFO, string $category = '', ?string $date = null): void
        {
        }
    }
}

namespace Joomla\CMS\Date {

    class Date extends \DateTime
    {
        public function toSql(): string
        {
            return $this->format('Y-m-d H:i:s');
        }
    }
}

namespace Joomla\CMS\HTML {

    class HTMLHelper
    {
        public static function cleanImageURL($url): object
        {
            $obj             = new \stdClass();
            $obj->url        = $url ?? '';
            $obj->attributes = ['width' => 0, 'height' => 0];

            return $obj;
        }
    }
}

namespace Joomla\CMS\Filter {

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
