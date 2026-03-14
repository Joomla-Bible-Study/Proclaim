<?php

/**
 * PHPUnit Bootstrap for Proclaim Component Unit Tests
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// Define _JEXEC to prevent "restricted access" errors
\define('_JEXEC', 1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define Joomla path constants for testing
if (!\defined('JPATH_BASE')) {
    \define('JPATH_BASE', \dirname(__DIR__, 2));
}

if (!\defined('JPATH_ROOT')) {
    \define('JPATH_ROOT', JPATH_BASE);
}

if (!\defined('JPATH_SITE')) {
    \define('JPATH_SITE', JPATH_ROOT);
}

if (!\defined('JPATH_ADMINISTRATOR')) {
    \define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
}

if (!\defined('BIBLESTUDY_PATH_ADMIN')) {
    \define('BIBLESTUDY_PATH_ADMIN', JPATH_ROOT . '/admin');
}

if (!\defined('JPATH_TESTS')) {
    \define('JPATH_TESTS', \dirname(__DIR__));
}

if (!\defined('JPATH_LIBRARIES')) {
    \define('JPATH_LIBRARIES', JPATH_ROOT . '/libraries');
}

if (!\defined('JPATH_CACHE')) {
    \define('JPATH_CACHE', JPATH_BASE . '/cache');
}

if (!\defined('JPATH_CONFIGURATION')) {
    \define('JPATH_CONFIGURATION', JPATH_BASE);
}

if (!\defined('JPATH_PLUGINS')) {
    \define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
}

if (!\defined('JPATH_THEMES')) {
    \define('JPATH_THEMES', JPATH_BASE . '/templates');
}

if (!\defined('JDEBUG')) {
    \define('JDEBUG', false);
}

// Load Joomla CMS stubs BEFORE Composer autoloader so they are available
// when our source classes (which extend Joomla base classes) are loaded.
// These stubs provide minimal class/interface/trait definitions that satisfy
// PHP's autoloader without requiring the full Joomla CMS framework.
require_once __DIR__ . '/Stubs/JoomlaCmsStubs.php';

// Load Composer autoloader
$composerAutoload = JPATH_ROOT . '/libraries/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Register PSR-4 autoloader for the Proclaim component
spl_autoload_register(function ($class) {
    $prefix  = 'CWM\\Component\\Proclaim\\Administrator\\';
    $baseDir = JPATH_ROOT . '/admin/src/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    $prefix  = 'CWM\\Component\\Proclaim\\Site\\';
    $baseDir = JPATH_ROOT . '/site/src/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // Autoload test classes
    $prefix  = 'CWM\\Component\\Proclaim\\Tests\\';
    $baseDir = JPATH_ROOT . '/tests/unit/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    // Autoload integration test classes
    $prefix  = 'CWM\\Component\\Proclaim\\Tests\\Integration\\';
    $baseDir = JPATH_ROOT . '/tests/integration/';

    $len = \strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});

// Load the base test case
require_once __DIR__ . '/ProclaimTestCase.php';

// ---------------------------------------------------------------------------
// Optional: Bootstrap a real database connection from build.properties
// When available, this allows integration tests to use Factory::getContainer()
// to obtain a DatabaseInterface. No-op when build.properties is missing.
// ---------------------------------------------------------------------------

(function () {
    $root      = \dirname(__DIR__, 2);
    $propsFile = $root . '/build.properties';

    if (!file_exists($propsFile)) {
        return;
    }

    // Parse build.properties
    $props = [];
    $lines = file($propsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $eq = strpos($trimmed, '=');

        if ($eq === false) {
            continue;
        }

        $props[trim(substr($trimmed, 0, $eq))] = trim(substr($trimmed, $eq + 1));
    }

    // Find the first Joomla installation path
    $joomlaPath = '';

    if (!empty($props['builder.joomla_paths'])) {
        $paths      = array_map('trim', explode(',', $props['builder.joomla_paths']));
        $joomlaPath = $paths[0] ?? '';
    } elseif (!empty($props['builder.joomla_path'])) {
        $joomlaPath = $props['builder.joomla_path'];
    }

    // Append subdirectory if configured (only relative paths, not absolute)
    $joomlaDir = $props['builder.joomla_dir'] ?? '';

    if ($joomlaDir !== '' && !str_starts_with($joomlaDir, '/')) {
        $joomlaPath = rtrim($joomlaPath, '/') . '/' . ltrim($joomlaDir, '/');
    }

    if ($joomlaPath === '' || !is_dir($joomlaPath)) {
        return;
    }

    $configFile = rtrim($joomlaPath, '/') . '/configuration.php';

    if (!file_exists($configFile)) {
        return;
    }

    // Load Joomla's configuration
    require_once $configFile;

    if (!class_exists('JConfig', false)) {
        return;
    }

    $config = new \JConfig();

    // Create a real database connection via PDO
    $host   = $config->host ?? 'localhost';
    $dbName = $config->db ?? '';
    $user   = $config->user ?? '';
    $pass   = $config->password ?? '';
    $prefix = $config->dbprefix ?? '';

    if ($dbName === '' || $user === '') {
        return;
    }

    // Parse host:port
    $port = 3306;

    if (str_contains($host, ':')) {
        [$host, $port] = explode(':', $host, 2);
        $port = (int) $port;
    }

    try {
        $pdo = new \PDO(
            "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4",
            $user,
            $pass,
            [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            ]
        );
    } catch (\PDOException $e) {
        // Database not available — integration tests will skip gracefully
        return;
    }

    // Create a minimal PSR-11 container that provides DatabaseInterface
    $container = new class ($pdo, $prefix) {
        private \PDO $pdo;
        private string $prefix;
        private ?object $db = null;

        public function __construct(\PDO $pdo, string $prefix)
        {
            $this->pdo    = $pdo;
            $this->prefix = $prefix;
        }

        public function has(string $id): bool
        {
            return $id === 'Joomla\\Database\\DatabaseInterface'
                || $id === \Joomla\Database\DatabaseInterface::class;
        }

        public function get(string $id): object
        {
            if (!$this->has($id)) {
                throw new \RuntimeException("Service not found: $id");
            }

            if ($this->db === null) {
                $this->db = new class ($this->pdo, $this->prefix) extends \Joomla\Database\DatabaseDriver {
                    private \PDO $pdo;
                    private string $dbPrefix;

                    public function __construct(\PDO $pdo, string $prefix)
                    {
                        $this->pdo      = $pdo;
                        $this->dbPrefix = $prefix;
                    }

                    public function getPrefix(): string
                    {
                        return $this->dbPrefix;
                    }

                    public function getQuery($new = false)
                    {
                        if ($new) {
                            return new class ($this) {
                                private object $db;
                                private string $type = '';
                                private array $select = [];
                                private array $from = [];
                                private array $where = [];
                                private ?int $limit = null;
                                private array $order = [];

                                public function __construct(object $db)
                                {
                                    $this->db = $db;
                                }

                                public function select($columns): static
                                {
                                    $this->type     = 'SELECT';
                                    $this->select[] = $columns;

                                    return $this;
                                }

                                public function from($table): static
                                {
                                    $this->from[] = $table;

                                    return $this;
                                }

                                public function where($condition): static
                                {
                                    $this->where[] = $condition;

                                    return $this;
                                }

                                public function order($column): static
                                {
                                    $this->order[] = $column;

                                    return $this;
                                }

                                public function setLimit(?int $limit, int $offset = 0): static
                                {
                                    $this->limit = $limit;

                                    return $this;
                                }

                                public function __toString(): string
                                {
                                    $flatten = static fn ($arr) => implode(', ', array_map(
                                        static fn ($v) => \is_array($v) ? implode(', ', $v) : (string) $v,
                                        $arr
                                    ));
                                    $sql = $this->type . ' ' . $flatten($this->select);
                                    $sql .= ' FROM ' . $flatten($this->from);

                                    if ($this->where) {
                                        $sql .= ' WHERE ' . implode(' AND ', $this->where);
                                    }

                                    if ($this->order) {
                                        $sql .= ' ORDER BY ' . implode(', ', $this->order);
                                    }

                                    if ($this->limit !== null) {
                                        $sql .= ' LIMIT ' . $this->limit;
                                    }

                                    return $sql;
                                }
                            };
                        }

                        return null;
                    }

                    public function quoteName($name, $as = null): string|array
                    {
                        if (\is_array($name)) {
                            return array_map(fn ($n) => '`' . str_replace('`', '``', $n) . '`', $name);
                        }

                        $quoted = '`' . str_replace('`', '``', $name) . '`';

                        if ($as !== null) {
                            $quoted .= ' AS `' . str_replace('`', '``', $as) . '`';
                        }

                        return $quoted;
                    }

                    public function quote($text, $escape = true): string
                    {
                        if (\is_array($text)) {
                            return implode(', ', array_map(fn ($t) => $this->quote($t, $escape), $text));
                        }

                        return $this->pdo->quote((string) $text);
                    }

                    // Joomla shorthand alias for quote()
                    public function q($text, $escape = true): string
                    {
                        return $this->quote($text, $escape);
                    }

                    public function setQuery($query, $offset = 0, $limit = 0): static
                    {
                        $this->sql = (string) $query;

                        return $this;
                    }

                    /**
                     * Replace #__ table prefix placeholder with the real prefix.
                     */
                    private function prepareSql(): string
                    {
                        return str_replace('#__', $this->dbPrefix, $this->sql);
                    }

                    public function loadResult(): mixed
                    {
                        $stmt = $this->pdo->query($this->prepareSql());

                        return $stmt ? $stmt->fetchColumn() : null;
                    }

                    public function loadObject($class = \stdClass::class)
                    {
                        $stmt = $this->pdo->query($this->prepareSql());

                        return $stmt ? ($stmt->fetch(\PDO::FETCH_OBJ) ?: null) : null;
                    }

                    public function loadObjectList($key = '', $class = \stdClass::class): array
                    {
                        $stmt = $this->pdo->query($this->prepareSql());

                        return $stmt ? $stmt->fetchAll(\PDO::FETCH_OBJ) : [];
                    }

                    public function loadColumn(): array
                    {
                        $stmt = $this->pdo->query($this->prepareSql());

                        return $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
                    }

                    public function execute(): bool
                    {
                        return (bool) $this->pdo->exec($this->prepareSql());
                    }

                    public function getTableList(): array
                    {
                        $stmt = $this->pdo->query('SHOW TABLES');

                        return $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
                    }

                    private string $sql = '';
                };
            }

            return $this->db;
        }
    };

    \Joomla\CMS\Factory::setContainer($container);
})();
