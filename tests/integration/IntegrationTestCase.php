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
     * An alias no other row will have, for seeding #__bsms_studies and
     * #__bsms_series.
     *
     * Both tables carry a unique index on alias, and the column defaults to
     * '', so two seeded rows without an alias collide. Seed every row with one
     * of these: a row left behind by a test whose writes were committed (a
     * Table::store() that creates an asset commits the transaction) would
     * otherwise make every later alias-less insert fail, depending on order.
     *
     * @param   string  $label  A readable part, to tell rows apart
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function uniqueAlias(string $label = 'row'): string
    {
        return 'zz-' . $label . '-' . str_replace('.', '-', uniqid('', true));
    }

    /**
     * Run code under a stand-in application, putting the real one back after.
     *
     * The suite runs under the console application, which is never the
     * administrator and never clears its message queue; a stub can be both.
     * Factory::$language is pinned to the real application's language first:
     * with none set, Text builds one from the application's config, which a
     * stub does not have.
     *
     * @param   \Joomla\CMS\Application\CMSApplicationInterface  $app  The stand-in
     * @param   callable                                         $run  The code to run under it
     *
     * @return  mixed  What $run returned
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function withApplication(\Joomla\CMS\Application\CMSApplicationInterface $app, callable $run): mixed
    {
        $savedApp      = \Joomla\CMS\Factory::$application;
        $savedLanguage = \Joomla\CMS\Factory::$language;

        \Joomla\CMS\Factory::$language    = $savedApp->getLanguage();
        \Joomla\CMS\Factory::$application = $app;

        try {
            return $run();
        } finally {
            \Joomla\CMS\Factory::$application = $savedApp;
            \Joomla\CMS\Factory::$language    = $savedLanguage;
        }
    }

    /**
     * A real Mail whose send() records the call instead of connecting, or
     * fails as an unreachable SMTP server does, so a test can read what would
     * have gone out without sending anything.
     *
     * @param   bool  $succeed  Whether send() succeeds
     *
     * @return  \Joomla\CMS\Mail\Mail  With a public $sends count
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function recordingMailer(bool $succeed = true): \Joomla\CMS\Mail\Mail
    {
        return new class ($succeed) extends \Joomla\CMS\Mail\Mail {
            public int $sends = 0;

            public function __construct(private readonly bool $succeed)
            {
                parent::__construct(true);
            }

            public function Send()
            {
                $this->sends++;

                if (!$this->succeed) {
                    throw new \RuntimeException('SMTP Error: Could not connect to SMTP host.');
                }

                return true;
            }
        };
    }

    /**
     * A mailer factory that hands out the given mailer.
     *
     * @param   \Joomla\CMS\Mail\Mail  $mail  The mailer to hand out
     *
     * @return  \Joomla\CMS\Mail\MailerFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function mailerFactoryFor(\Joomla\CMS\Mail\Mail $mail): \Joomla\CMS\Mail\MailerFactoryInterface
    {
        $factory = $this->createStub(\Joomla\CMS\Mail\MailerFactoryInterface::class);
        $factory->method('createMailer')->willReturn($mail);

        return $factory;
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
