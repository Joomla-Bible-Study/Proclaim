<?php

/**
 * Tests that an API delete is logged only once the row is gone
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\AbstractWritableController;
use CWM\Component\Proclaim\Api\Controller\LocationsController;
use CWM\Component\Proclaim\Api\Controller\MediaController;
use CWM\Component\Proclaim\Api\Controller\MessagetypesController;
use CWM\Component\Proclaim\Api\Controller\PodcastsController;
use CWM\Component\Proclaim\Api\Controller\SeriesController;
use CWM\Component\Proclaim\Api\Controller\SermonsController;
use CWM\Component\Proclaim\Api\Controller\TeachersController;
use CWM\Component\Proclaim\Api\Controller\TopicsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Joomla 5.4 answers 204 when the model refuses a delete without setting an
 * error, and 6.0 to 6.2.0 do the same when a plugin vetoes it, so a delete that
 * returns is no proof the row is gone. The controller checks before logging the
 * deletion, and answers 409 when the row is still there.
 *
 * The controller under test is a real one, built without its constructor, with
 * doubles for its model factory and application, so the model lookup runs
 * through the controller's own getModel().
 *
 * @since  10.7.9
 */
class ApiDeleteOutcomeTest extends ProclaimTestCase
{
    /**
     * The reason Joomla 6.2.1 gives for refusing to delete a record that is not
     * trashed.
     *
     * @var    string
     * @since  10.7.9
     */
    private const string NOT_TRASHED = 'Resource not in state that can be deleted, must be trashed before it can be deleted';

    /**
     * A table double whose load() gives a fixed answer.
     *
     * @param   bool|\Throwable  $loads      What load() returns, or throws.
     * @param   int              $published  The record's published state.
     *
     * @return  object  The table double
     *
     * @since   10.7.9
     */
    private static function table(bool|\Throwable $loads, int $published = 1): object
    {
        return new class ($loads, $published) {
            /**
             * The record's published state, as a loaded table holds it.
             *
             * @var    int
             * @since  10.7.9
             */
            public int $published;

            /**
             * Keep what load() reports, and the published state.
             *
             * @param   bool|\Throwable  $loads      What load() returns, or throws.
             * @param   int              $published  The record's published state.
             *
             * @since   10.7.9
             */
            public function __construct(private readonly bool|\Throwable $loads, int $published)
            {
                $this->published = $published;
            }

            /**
             * Report whether the record was found, or throw.
             *
             * @return  bool  The fixed answer
             *
             * @since   10.7.9
             * @throws  \Throwable
             */
            public function load(): bool
            {
                if ($this->loads instanceof \Throwable) {
                    throw $this->loads;
                }

                return $this->loads;
            }
        };
    }

    /**
     * A model double whose getTable() returns the given table.
     *
     * @param   object  $table  The table double.
     *
     * @return  object  The model double
     *
     * @since   10.7.9
     */
    private static function model(object $table): object
    {
        return new class ($table) {
            /**
             * Keep the table getTable() returns.
             *
             * @param   object  $table  The table double.
             *
             * @since   10.7.9
             */
            public function __construct(private readonly object $table)
            {
            }

            /**
             * Accept the state the controller sets on every model it creates.
             *
             * @return  void
             *
             * @since   10.7.9
             */
            public function setState(): void
            {
            }

            /**
             * The table double.
             *
             * @return  object  The table double
             *
             * @since   10.7.9
             */
            public function getTable(): object
            {
                return $this->table;
            }
        };
    }

    /**
     * An API application double that records the response and throws on close().
     *
     * @return  object  The application double
     *
     * @since   10.7.9
     */
    private static function app(): object
    {
        return new class () {
            /**
             * The headers set, by name.
             *
             * @var    array<string, mixed>
             * @since  10.7.9
             */
            public array $headers = [];

            /**
             * Whether the headers were sent.
             *
             * @var    bool
             * @since  10.7.9
             */
            public bool $sent = false;

            /**
             * The application's client name.
             *
             * @return  string  Always api
             *
             * @since   10.7.9
             */
            public function getName(): string
            {
                return 'api';
            }

            /**
             * Whether this is the given client.
             *
             * @param   string  $identifier  The client name.
             *
             * @return  bool  True for api
             *
             * @since   10.7.9
             */
            public function isClient(string $identifier): bool
            {
                return $identifier === 'api';
            }

            /**
             * Record a header.
             *
             * @param   string  $name   The header name.
             * @param   mixed   $value  The header value.
             *
             * @return  static  This double
             *
             * @since   10.7.9
             */
            public function setHeader(string $name, mixed $value): static
            {
                $this->headers[$name] = $value;

                return $this;
            }

            /**
             * Record that the headers were sent.
             *
             * @return  static  This double
             *
             * @since   10.7.9
             */
            public function sendHeaders(): static
            {
                $this->sent = true;

                return $this;
            }

            /**
             * Stand in for ending the request, which would exit.
             *
             * @return  void
             *
             * @since   10.7.9
             * @throws  \LogicException
             */
            public function close(): void
            {
                throw new \LogicException('closed');
            }

            /**
             * An empty message queue, for the JSON response to read.
             *
             * @return  array  No messages
             *
             * @since   10.7.9
             */
            public function getMessageQueue(): array
            {
                return [];
            }
        };
    }

    /**
     * The media controller, built without its constructor, whose model factory
     * hands out the given model.
     *
     * @param   ?object  $model    What the factory creates, or null for no model.
     * @param   ?object  $app      The application, or null for a fresh double.
     * @param   string   $logType  The action-log type.
     *
     * @return  MediaController  The controller
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    private static function controller(?object $model, ?object $app = null, string $logType = 'mediafile'): MediaController
    {
        $controller = (new \ReflectionClass(MediaController::class))->newInstanceWithoutConstructor();

        $factory = new class ($model) {
            /**
             * Keep the model createModel() hands out.
             *
             * @param   ?object  $model  The model, or null for none.
             *
             * @since   10.7.9
             */
            public function __construct(private readonly ?object $model)
            {
            }

            /**
             * Hand out the model, whatever is asked for.
             *
             * @return  ?object  The model, or null for none
             *
             * @since   10.7.9
             */
            public function createModel(): ?object
            {
                return $this->model;
            }
        };

        (new \ReflectionProperty(BaseController::class, 'factory'))->setValue($controller, $factory);
        (new \ReflectionProperty(BaseController::class, 'app'))->setValue($controller, $app ?? self::app());
        (new \ReflectionProperty(AbstractWritableController::class, 'logType'))->setValue($controller, $logType);

        return $controller;
    }

    /**
     * Call one of AbstractWritableController's private methods.
     *
     * @param   AbstractWritableController  $controller  The controller.
     * @param   string                      $method      The method name.
     * @param   mixed                       ...$args     The arguments.
     *
     * @return  mixed  What the method returns
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    private static function call(AbstractWritableController $controller, string $method, mixed ...$args): mixed
    {
        return (new \ReflectionMethod(AbstractWritableController::class, $method))->invoke($controller, ...$args);
    }

    /**
     * Run sendDeleteConflict() against an application double, capturing what it
     * sends.
     *
     * @param   object  $record  The record still in the database.
     *
     * @return  array{0: object, 1: array}  The application double, and the decoded response body
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     * @throws  \JsonException
     */
    private function sendConflict(object $record): array
    {
        $app      = self::app();
        $previous = Factory::$application;

        // Built before the swap: building the language reads the running
        // application's configuration, which the double does not have.
        Factory::getLanguage();

        // The JSON response reads the message queue from the running application.
        Factory::$application = $app;
        ob_start();

        try {
            self::call(self::controller(null, $app), 'sendDeleteConflict', $record);
            $this->fail('sendDeleteConflict() should end the request');
        } catch (\LogicException $e) {
            $this->assertSame('closed', $e->getMessage());
        } finally {
            $body                 = (string) ob_get_clean();
            Factory::$application = $previous;
        }

        return [$app, (array) json_decode($body, true, 512, JSON_THROW_ON_ERROR)];
    }

    /**
     * A record the delete left in place is returned, so the caller can refuse.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    public function testARecordStillThereIsReturned(): void
    {
        $table = self::table(true);

        $this->assertSame($table, self::call(self::controller(self::model($table)), 'remainingRecord', 7));
    }

    /**
     * A record that is gone is not returned, so the deletion is logged.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    public function testARecordThatIsGoneIsNotReturned(): void
    {
        $this->assertNull(self::call(self::controller(self::model(self::table(false))), 'remainingRecord', 7));
    }

    /**
     * A record that cannot be looked up counts as gone, so the delete is logged
     * as it always was rather than refused on a guess.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    public function testARecordThatCannotBeLookedUpCountsAsGone(): void
    {
        $present = self::model(self::table(true));

        $this->assertNull(self::call(self::controller(null), 'remainingRecord', 7), 'no model');
        $this->assertNull(
            self::call(self::controller(self::model(self::table(new \RuntimeException('down')))), 'remainingRecord', 7),
            'load() throws'
        );
        $this->assertNull(self::call(self::controller($present), 'remainingRecord', 0), 'no record id');
        $this->assertNull(self::call(self::controller($present, null, ''), 'remainingRecord', 7), 'no log type');
    }

    /**
     * A record that is not trashed gets a 409 with Joomla 6.2.1's reason, and the
     * request ends.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     * @throws  \JsonException
     */
    public function testAnUntrashedRecordGetsTheConflictJoomlaSends(): void
    {
        [$app, $body] = $this->sendConflict(self::table(true));

        $this->assertSame(409, $app->headers['status'] ?? null);
        $this->assertSame('application/json', $app->headers['Content-Type'] ?? null);
        $this->assertTrue($app->sent, 'The headers should be sent before the body');
        $this->assertSame(
            ['status' => 'Conflict', 'code' => 409, 'message' => self::NOT_TRASHED],
            $body['data'] ?? null
        );
    }

    /**
     * A trashed record that still could not be deleted, after a plugin veto, gets
     * Joomla's generic delete failure instead.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     * @throws  \JsonException
     */
    public function testATrashedRecordGetsTheGenericReason(): void
    {
        [, $body] = $this->sendConflict(self::table(true, -2));

        $this->assertSame(Text::_('JLIB_APPLICATION_ERROR_DELETE'), $body['data']['message'] ?? null);
    }

    /**
     * delete() checks the row is gone before it logs the deletion, and answers
     * the conflict instead when it is not.
     *
     * @return  void
     *
     * @since   10.7.9
     */
    public function testDeleteLogsOnlyOnceTheRowIsGone(): void
    {
        $ref  = new \ReflectionMethod(AbstractWritableController::class, 'delete');
        $body = implode('', \array_slice(file($ref->getFileName()), $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $positions = [];

        foreach (['parent::delete(', '$this->remainingRecord(', '$this->sendDeleteConflict(', "\$this->logApiWrite('DELETED'"] as $call) {
            $position = strpos($body, $call);

            $this->assertNotFalse($position, "delete() no longer calls {$call}");
            $positions[] = $position;
        }

        $inOrder = $positions;
        sort($inOrder);

        $this->assertSame($inOrder, $positions, 'delete() must check the row is gone before it logs the deletion');
    }

    /**
     * The eight writable controllers.
     *
     * @return  array<string, array{0: class-string}>  Each controller's class, keyed by resource name
     *
     * @since   10.7.9
     */
    public static function writableControllerProvider(): array
    {
        return [
            'Sermons'      => [SermonsController::class],
            'Locations'    => [LocationsController::class],
            'Podcasts'     => [PodcastsController::class],
            'Media'        => [MediaController::class],
            'Topics'       => [TopicsController::class],
            'Teachers'     => [TeachersController::class],
            'Messagetypes' => [MessagetypesController::class],
            'Series'       => [SeriesController::class],
        ];
    }

    /**
     * Each writable controller's action-log type resolves to a real model, so the
     * title a log entry names, and the check that a delete happened, read the
     * record. The API app's model factory looks in the Administrator namespace
     * when the API has no model of that name.
     *
     * @param   class-string  $class  The controller class.
     *
     * @return  void
     *
     * @since   10.7.9
     * @throws  \ReflectionException
     */
    #[DataProvider('writableControllerProvider')]
    public function testLogTypeResolvesToARealModel(string $class): void
    {
        $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

        $factory = new class () {
            /**
             * The model names asked for, in order.
             *
             * @var    string[]
             * @since  10.7.9
             */
            public array $requested = [];

            /**
             * Record the model name asked for, and create nothing.
             *
             * @param   string  $name  The model name.
             *
             * @return  null  No model
             *
             * @since   10.7.9
             */
            public function createModel(string $name): null
            {
                $this->requested[] = $name;

                return null;
            }
        };

        (new \ReflectionProperty(BaseController::class, 'app'))->setValue($controller, self::app());
        (new \ReflectionProperty(BaseController::class, 'factory'))->setValue($controller, $factory);

        $logType = (new \ReflectionProperty($class, 'logType'))->getValue($controller);

        $controller->getModel($logType);

        $this->assertNotEmpty($factory->requested, "{$class}::getModel() asked the factory for nothing");

        $model = 'CWM\\Component\\Proclaim\\Administrator\\Model\\' . ucfirst($factory->requested[0]) . 'Model';

        $this->assertTrue(
            class_exists($model),
            "{$class} maps its log type \"{$logType}\" to {$factory->requested[0]}, but {$model} does not exist"
        );
    }
}
