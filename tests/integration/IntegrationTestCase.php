<?php

/**
 * Base Integration Test Case for Proclaim Component
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Base test case for integration tests.
 *
 * Provides helpers for instantiating real classes without a database connection
 * and resetting static caches between tests.
 *
 * @since 10.1.0
 */
abstract class IntegrationTestCase extends ProclaimTestCase
{
    /**
     * Create a Table subclass instance without invoking the constructor.
     *
     * Sets the _tbl_key property via reflection so that check()/delete()
     * methods that reference it work correctly.
     *
     * @param   string  $fqcn    Fully-qualified class name
     * @param   string  $tblKey  Primary key column name (default: 'id')
     *
     * @return  object  Table instance
     */
    protected function createTableInstance(string $fqcn, string $tblKey = 'id'): object
    {
        $ref      = new \ReflectionClass($fqcn);
        $instance = $ref->newInstanceWithoutConstructor();

        // Set _tbl_key so delete() and other methods can reference it
        $prop = $ref->getProperty('_tbl_key');
        $prop->setValue($instance, $tblKey);

        // Provide a mock DatabaseInterface to satisfy non-nullable return types.
        // Real CMS Table::getDatabase() throws if no DB is set.
        $queryMock = $this->createMock(\Joomla\Database\QueryInterface::class);
        $queryMock->method('select')->willReturnSelf();
        $queryMock->method('from')->willReturnSelf();
        $queryMock->method('where')->willReturnSelf();
        $queryMock->method('whereIn')->willReturnSelf();

        // Use DatabaseDriver (abstract class) as mock base — it has both
        // getQuery() and createQuery() regardless of framework version.
        $dbMock = $this->getMockBuilder(\Joomla\Database\DatabaseDriver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbMock->method('getQuery')->willReturn($queryMock);
        $dbMock->method('createQuery')->willReturn($queryMock);
        $dbMock->method('quoteName')->willReturnCallback(
            static fn ($name) => \is_array($name)
                ? array_map(static fn ($n) => '`' . $n . '`', $name)
                : '`' . $name . '`'
        );
        $dbMock->method('quote')->willReturnCallback(
            static fn ($text) => "'" . $text . "'"
        );
        $dbMock->method('setQuery')->willReturnSelf();
        $dbMock->method('loadObject')->willReturn(null);

        // Inject DB via the real setter or reflection
        if ($ref->hasMethod('setDatabase')) {
            $instance->setDatabase($dbMock);
        } elseif ($ref->hasProperty('_db')) {
            $dbProp = $ref->getProperty('_db');
            $dbProp->setValue($instance, $dbMock);
        }

        // Provide a mock event dispatcher (required by real CMS Table::store/check)
        if ($ref->hasMethod('setDispatcher')) {
            $dispatcher = $this->createMock(\Joomla\Event\DispatcherInterface::class);
            $dispatcher->method('dispatch')->willReturn(new \Joomla\Event\Event('test'));
            $instance->setDispatcher($dispatcher);
        }

        return $instance;
    }

    /**
     * Reset a static property on a class to its default value.
     *
     * Useful for clearing static caches (e.g., Cwmstats::$cache) between tests.
     *
     * @param   string  $class     Fully-qualified class name
     * @param   string  $property  Static property name
     * @param   mixed   $value     Value to reset to
     *
     * @return  void
     */
    protected function resetStaticCache(string $class, string $property, mixed $value = []): void
    {
        $ref  = new \ReflectionClass($class);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue(null, $value);
    }
}
