<?php

/**
 * Tests that a refused API request is recorded once, whichever layer refused it
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Plugin\System\Proclaim\Extension\Proclaim as SystemPlugin;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\ApiApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Event\ErrorEvent;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\User\User;
use Joomla\Input\Input;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * From Joomla 5.4.11 and 6.2.1 the API dispatcher refuses a request without
 * core.api before any Proclaim controller exists, so the system plugin's onError
 * handler records it. The controller still records the refusals it makes itself,
 * and each refusal is recorded once.
 *
 * Each test swaps in a fresh Log instance whose only logger captures the API
 * category, so nothing is written to a real log file. The harness defines
 * JPATH_ROOT as '.', and LogEntry replaces the root path in every message, so
 * a captured message is unreadable here: the tests count entries, and the line
 * itself is tested through refusalMessage().
 *
 * @since  10.7.9
 */
class ApiRefusalLoggingTest extends ProclaimTestCase
{
    /**
     * The entries the capturing logger received.
     *
     * @var    LogEntry[]
     * @since  10.7.9
     */
    private array $logged = [];

    /**
     * The Log instance in place before the test.
     *
     * @var    Log|null
     * @since  10.7.9
     */
    private ?Log $previousLog = null;

    /**
     * Whether CwmlogHelper had registered its file loggers before the test.
     *
     * @var    bool
     * @since  10.7.9
     */
    private bool $wasRegistered = false;

    /**
     * Swap in a fresh Log instance whose only logger captures the API category.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->previousLog = (new \ReflectionProperty(Log::class, 'instance'))->getValue();

        $registered          = new \ReflectionProperty(CwmlogHelper::class, 'registered');
        $this->wasRegistered = $registered->getValue();

        // Marked registered, so CwmlogHelper adds no file loggers to the fresh instance.
        $registered->setValue(null, true);

        Log::setInstance(null);
        Log::addLogger(
            [
                'logger'   => 'callback',
                'callback' => function (LogEntry $entry): void {
                    $this->logged[] = $entry;
                },
            ],
            Log::ALL,
            [CwmlogHelper::CATEGORY_API]
        );
    }

    /**
     * Put back the Log instance and CwmlogHelper's registration flag.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    protected function tearDown(): void
    {
        Log::setInstance($this->previousLog);
        (new \ReflectionProperty(CwmlogHelper::class, 'registered'))->setValue(null, $this->wasRegistered);

        parent::tearDown();
    }

    /**
     * A user, authenticated when given an id and a guest otherwise.
     *
     * @param   int  $id  The user id, or 0 for a guest.
     *
     * @return  User  The user
     *
     * @since   10.7.9
     */
    private static function user(int $id): User
    {
        $user = new User();

        if ($id > 0) {
            $user->id       = $id;
            $user->username = 'reader';
            $user->guest    = 0;
        }

        return $user;
    }

    /**
     * Dispatch onError to the system plugin, as Joomla does for an uncaught error.
     *
     * The request method and address come from $_SERVER, which the base test case
     * restores after each test.
     *
     * @param   class-string  $class     The application class.
     * @param   string        $client    The application's client name, such as api or site.
     * @param   array         $request   The request variables, such as option and controller.
     * @param   User          $identity  The user the request authenticated as.
     * @param   \Throwable    $error     The uncaught error.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    private function dispatchOnError(string $class, string $client, array $request, User $identity, \Throwable $error): void
    {
        /** @var CMSApplication $app */
        $app = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

        (new \ReflectionProperty(CMSApplication::class, 'name'))->setValue($app, $client);
        (new \ReflectionProperty(CMSApplication::class, 'identity'))->setValue($app, $identity);
        (new \ReflectionProperty(CMSApplication::class, 'input'))->setValue($app, new Input($request));

        $plugin = (new \ReflectionClass(SystemPlugin::class))->newInstanceWithoutConstructor();
        $plugin->setApplication($app);
        $plugin->onError(new ErrorEvent('onError', ['subject' => $error, 'application' => $app]));
    }

    /**
     * The log line names who was refused what, and from where.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testTheRefusalLineNamesWhoWhatAndWhere(): void
    {
        $this->assertSame(
            'Authenticated user reader (#42) was refused permission to read sermons #12 from 203.0.113.5',
            CwmlogHelper::refusalMessage('reader', 42, 'read', 'sermons #12', '203.0.113.5')
        );
    }

    /**
     * The shared recorder writes a refusal once, as a warning in the API log,
     * however many layers report it.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testEachRefusalIsRecordedOnce(): void
    {
        $refusal = new NotAllowed('refused', 403);

        $this->assertFalse(CwmlogHelper::refusalRecorded($refusal));
        $this->assertTrue(CwmlogHelper::apiRefusal($refusal, 'reader', 42, 'read', 'sermons', '203.0.113.5'));
        $this->assertTrue(CwmlogHelper::refusalRecorded($refusal));
        $this->assertFalse(CwmlogHelper::apiRefusal($refusal, 'reader', 42, 'read', 'sermons', '203.0.113.5'));

        $this->assertCount(1, $this->logged);
        $this->assertSame(Log::WARNING, $this->logged[0]->priority);
        $this->assertSame(CwmlogHelper::CATEGORY_API, $this->logged[0]->category);
    }

    /**
     * A second refusal with the same details is still recorded: it is keyed on
     * the refusal itself.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testEachDistinctRefusalIsRecorded(): void
    {
        $first  = new NotAllowed('refused', 403);
        $second = new NotAllowed('refused', 403);

        CwmlogHelper::apiRefusal($first, 'reader', 42, 'read', 'sermons', '203.0.113.5');
        CwmlogHelper::apiRefusal($second, 'reader', 42, 'read', 'sermons', '203.0.113.5');

        $this->assertCount(2, $this->logged);
    }

    /**
     * Each HTTP method and the verb its refusal is recorded with.
     *
     * @return  array<string, array{0: string, 1: string}>  The method, then its verb
     *
     * @since   10.7.9
     */
    public static function methodProvider(): array
    {
        return [
            'GET'    => ['GET', 'read'],
            'POST'   => ['POST', 'create'],
            'PATCH'  => ['PATCH', 'update'],
            'DELETE' => ['DELETE', 'delete'],
        ];
    }

    /**
     * A refused request is described by what it tried and which record it named.
     *
     * @param   string  $method  The HTTP method.
     * @param   string  $verb    The verb the refusal should be recorded with.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    #[DataProvider('methodProvider')]
    public function testARefusedRequestIsDescribedByItsVerbAndRecord(string $method, string $verb): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;

        $describe = new \ReflectionMethod(SystemPlugin::class, 'describeApiRequest');

        $this->assertSame([$verb, 'sermons #12'], $describe->invoke(null, new Input(['controller' => 'sermons', 'id' => 12])));
        $this->assertSame([$verb, 'sermons'], $describe->invoke(null, new Input(['controller' => 'sermons'])));
    }

    /**
     * An authenticated caller that Joomla refused before any Proclaim controller
     * ran is recorded.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    public function testOnErrorRecordsAProclaimApiRefusal(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $refusal = new NotAllowed('refused', 403);

        $this->dispatchOnError(
            ApiApplication::class,
            'api',
            ['option' => 'com_proclaim', 'controller' => 'sermons', 'id' => 12],
            self::user(42),
            $refusal
        );

        $this->assertCount(1, $this->logged);
        $this->assertTrue(CwmlogHelper::refusalRecorded($refusal));
    }

    /**
     * Requests the handler leaves alone: another component, the site instead of
     * the API, a guest, and an error that is not a refusal.
     *
     * @return  array<string, array{0: class-string, 1: string, 2: string, 3: int, 4: bool}>  The application class, client, option, user id, and whether the error is a refusal
     *
     * @since   10.7.9
     */
    public static function ignoredProvider(): array
    {
        return [
            'another component'     => [ApiApplication::class, 'api', 'com_content', 42, true],
            'the site, not the API' => [SiteApplication::class, 'site', 'com_proclaim', 42, true],
            'a guest'               => [ApiApplication::class, 'api', 'com_proclaim', 0, true],
            'not a refusal'         => [ApiApplication::class, 'api', 'com_proclaim', 42, false],
        ];
    }

    /**
     * Nothing is recorded for anything but an authenticated caller's refused
     * request to Proclaim's API.
     *
     * @param   class-string  $class    The application class.
     * @param   string        $client   The application's client name.
     * @param   string        $option   The component the request was for.
     * @param   int           $userId   The caller's user id, or 0 for a guest.
     * @param   bool          $refused  Whether the error is a refusal.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    #[DataProvider('ignoredProvider')]
    public function testOnErrorIgnoresAnythingElse(string $class, string $client, string $option, int $userId, bool $refused): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $error = $refused ? new NotAllowed('refused', 403) : new \RuntimeException('broken', 500);

        $this->dispatchOnError($class, $client, ['option' => $option, 'controller' => 'sermons'], self::user($userId), $error);

        $this->assertCount(0, $this->logged);
        $this->assertFalse(CwmlogHelper::refusalRecorded($error));
    }

    /**
     * A refusal the controller already recorded is not recorded again when the
     * same exception reaches onError.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    public function testOnErrorSkipsARefusalTheControllerRecorded(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $refusal = new NotAllowed('refused', 403);

        // The controller records it first, with the details it knows.
        CwmlogHelper::apiRefusal($refusal, 'reader', 42, 'create', 'sermons', '203.0.113.5');

        $this->dispatchOnError(
            ApiApplication::class,
            'api',
            ['option' => 'com_proclaim', 'controller' => 'sermons'],
            self::user(42),
            $refusal
        );

        $this->assertCount(1, $this->logged);
    }

    /**
     * The system plugin subscribes its handler to onError.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testTheSystemPluginListensForOnError(): void
    {
        $this->assertSame('onError', SystemPlugin::getSubscribedEvents()['onError'] ?? null);
    }
}
