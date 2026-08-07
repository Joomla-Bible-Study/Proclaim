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

        // Inject DB and dispatcher: prefer real services from the CMS container
        // when available so check()/store() exercise the same code paths as in
        // production. Fall back to stubs when the test harness has no live DB —
        // that path satisfies non-nullable return types but won't run real
        // queries, so tests that depend on real query results should guard with
        // `if (!PROCLAIM_TEST_DB_AVAILABLE) { $this->markTestSkipped(...); }`.
        $db         = $this->resolveDatabase();
        $dispatcher = $this->resolveDispatcher();

        if ($ref->hasMethod('setDatabase')) {
            $instance->setDatabase($db);
        } elseif ($ref->hasProperty('_db')) {
            $dbProp = $ref->getProperty('_db');
            $dbProp->setValue($instance, $db);
        }

        if ($ref->hasMethod('setDispatcher')) {
            $instance->setDispatcher($dispatcher);
        }

        return $instance;
    }

    /**
     * Get a DatabaseInterface for tables: real driver from the CMS container
     * when PROCLAIM_TEST_DB_AVAILABLE, otherwise a typed stub.
     *
     * @return  \Joomla\Database\DatabaseInterface
     */
    private function resolveDatabase(): \Joomla\Database\DatabaseInterface
    {
        if (\defined('PROCLAIM_TEST_DB_AVAILABLE') && PROCLAIM_TEST_DB_AVAILABLE) {
            return \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        }

        $queryStub = $this->createStub(\Joomla\Database\QueryInterface::class);
        $queryStub->method('select')->willReturnSelf();
        $queryStub->method('from')->willReturnSelf();
        $queryStub->method('where')->willReturnSelf();
        $queryStub->method('whereIn')->willReturnSelf();

        // DatabaseDriver is abstract — createStub() handles that the same as
        // createMock() but flags the result so PHPUnit 12 doesn't emit a
        // "no expectations configured" notice.
        $dbStub = $this->createStub(\Joomla\Database\DatabaseDriver::class);
        $dbStub->method('getQuery')->willReturn($queryStub);
        $dbStub->method('createQuery')->willReturn($queryStub);
        $dbStub->method('quoteName')->willReturnCallback(
            static fn ($name) => \is_array($name)
                ? array_map(static fn ($n) => '`' . $n . '`', $name)
                : '`' . $name . '`'
        );
        $dbStub->method('quote')->willReturnCallback(
            static fn ($text) => "'" . $text . "'"
        );
        $dbStub->method('setQuery')->willReturnSelf();
        $dbStub->method('loadObject')->willReturn(null);

        return $dbStub;
    }

    /**
     * Get a DispatcherInterface for tables: real CMS dispatcher when
     * available, otherwise a stub that returns an empty Event.
     *
     * @return  \Joomla\Event\DispatcherInterface
     */
    private function resolveDispatcher(): \Joomla\Event\DispatcherInterface
    {
        if (\defined('PROCLAIM_TEST_DB_AVAILABLE') && PROCLAIM_TEST_DB_AVAILABLE) {
            try {
                return \Joomla\CMS\Factory::getContainer()->get(\Joomla\Event\DispatcherInterface::class);
            } catch (\Throwable) {
                // Container has no dispatcher registered — fall through to stub.
            }
        }

        $dispatcher = $this->createStub(\Joomla\Event\DispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturn(new \Joomla\Event\Event('test'));

        return $dispatcher;
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
        $prop->setValue(null, $value);
    }

    /**
     * Give Factory::getDate() a language tag to read, and return the previous
     * language so the caller can restore it.
     *
     * Any code under test that calls Factory::getDate() reaches
     * Factory::$language->getTag(), i.e. the language's metadata['tag']. A test
     * Joomla root carrying no langmetadata files leaves every Language instance
     * with metadata = null, so getTag() reads an offset on null and the date path
     * prints warnings that trip beStrictAboutOutputDuringTests. There is no public
     * tag setter, so force one onto the metadata via reflection.
     *
     * Restore the returned value in tearDown(): leaving the forced tag in place
     * leaks into every later test in the run and masks exactly this warning class
     * for suites that would otherwise surface it.
     *
     * @return  mixed  The previous Factory::$language.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function silenceDateLanguageWarnings(): mixed
    {
        $previous = \Joomla\CMS\Factory::$language;
        $lang     = \Joomla\CMS\Factory::getApplication()->getLanguage();

        try {
            $prop = new \ReflectionProperty($lang, 'metadata');
            $meta = $prop->getValue($lang);

            if (!\is_array($meta) || ($meta['tag'] ?? null) === null) {
                $prop->setValue($lang, array_merge(\is_array($meta) ? $meta : [], ['tag' => 'en-GB']));
            }
        } catch (\ReflectionException) {
            // Property absent on this Joomla version -- leave the language as-is.
        }

        \Joomla\CMS\Factory::$language = $lang;

        return $previous;
    }
}
