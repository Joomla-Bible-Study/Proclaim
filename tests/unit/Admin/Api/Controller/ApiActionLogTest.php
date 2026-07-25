<?php

/**
 * Unit tests for API action logging.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\AbstractWritableController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @since  __DEPLOY_VERSION__
 */
class ApiActionLogTest extends ProclaimTestCase
{
    /**
     * Writable controllers and the entity type each logs under.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function writableControllerProvider(): array
    {
        return [
            'sermons'      => ['SermonsController', 'message'],
            'teachers'     => ['TeachersController', 'teacher'],
            'series'       => ['SeriesController', 'serie'],
            'media'        => ['MediaController', 'mediafile'],
            'podcasts'     => ['PodcastsController', 'podcast'],
            'topics'       => ['TopicsController', 'topic'],
            'locations'    => ['LocationsController', 'location'],
            'messagetypes' => ['MessagetypesController', 'messagetype'],
        ];
    }

    /**
     * Every writable resource logs its changes.
     *
     * A writable controller that does not extend the logging base is an audit
     * hole: changes land in the database with nothing recording who made them.
     */
    #[DataProvider('writableControllerProvider')]
    public function testWritableControllerLogsChanges(string $class, string $logType): void
    {
        $fqcn = 'CWM\\Component\\Proclaim\\Api\\Controller\\' . $class;

        $this->assertTrue(class_exists($fqcn), "$class should exist");
        $this->assertTrue(
            is_subclass_of($fqcn, AbstractWritableController::class),
            "$class must extend AbstractWritableController so its writes are logged"
        );

        $defaults = (new \ReflectionClass($fqcn))->getDefaultProperties();

        $this->assertSame(
            $logType,
            $defaults['logType'] ?? '',
            "$class should log under the '$logType' entity type"
        );
    }

    /**
     * Each logType must have a matching entity-name language key, or the log entry
     * renders a raw key instead of "topic".
     */
    #[DataProvider('writableControllerProvider')]
    public function testLogTypeHasLanguageKey(string $class, string $logType): void
    {
        $ini = file_get_contents(
            \dirname(__DIR__, 5) . '/admin/language/en-GB/en-GB.com_proclaim.ini'
        );

        $this->assertStringContainsString(
            'COM_PROCLAIM_ACTION_LOG_TYPE_' . strtoupper($logType) . '=',
            (string) $ini,
            "Missing entity label for logType '$logType'"
        );
    }

    /**
     * The API uses the same key family as the admin UI — one taxonomy, not a
     * parallel API one.
     */
    public function testUsesUnifiedKeyFamily(): void
    {
        $source = file_get_contents(
            \dirname(__DIR__, 5) . '/api/src/Controller/AbstractWritableController.php'
        );

        $this->assertStringContainsString('COM_PROCLAIM_ACTION_LOG_ITEM_', (string) $source);
        $this->assertStringNotContainsString(
            'COM_PROCLAIM_ACTION_LOG_API_',
            (string) $source,
            'API writes should use the shared ITEM_* keys, not a parallel API family'
        );
    }

    /**
     * Origin is derived centrally, never passed by a caller — otherwise a call
     * site could mislabel where a change came from.
     */
    public function testOriginIsDerivedNotPassed(): void
    {
        $helper = file_get_contents(
            \dirname(__DIR__, 5) . '/admin/src/Helper/CwmactionlogHelper.php'
        );

        $this->assertStringContainsString("'origin'", (string) $helper);
        $this->assertStringContainsString('originLabel', (string) $helper);

        foreach (['ADMIN', 'API', 'SITE', 'CLI'] as $origin) {
            $this->assertStringContainsString(
                'COM_PROCLAIM_ACTION_LOG_ORIGIN_' . $origin,
                (string) $helper,
                "originLabel() should cover the $origin entry point"
            );
        }
    }

    /**
     * Every message key and origin label the code references must exist.
     */
    public function testUnifiedKeysExistInLanguageFile(): void
    {
        $ini = (string) file_get_contents(
            \dirname(__DIR__, 5) . '/admin/language/en-GB/en-GB.com_proclaim.ini'
        );

        foreach (['ADDED', 'UPDATED', 'DELETED'] as $verb) {
            $this->assertStringContainsString('COM_PROCLAIM_ACTION_LOG_ITEM_' . $verb . '=', $ini);
        }

        foreach (['ADMIN', 'API', 'SITE', 'CLI'] as $origin) {
            $this->assertStringContainsString('COM_PROCLAIM_ACTION_LOG_ORIGIN_' . $origin . '=', $ini);
        }
    }

    /**
     * The shared messages must actually render the origin, or unifying the family
     * would have thrown away the ability to tell an API change from a UI one.
     */
    public function testUnifiedMessagesRenderOrigin(): void
    {
        $ini = (string) file_get_contents(
            \dirname(__DIR__, 5) . '/admin/language/en-GB/en-GB.com_proclaim.ini'
        );

        preg_match_all('/^COM_PROCLAIM_ACTION_LOG_ITEM_[A-Z]+="(.*)"$/m', $ini, $matches);

        $this->assertNotEmpty($matches[1], 'Expected ITEM_* messages in the language file');

        foreach ($matches[1] as $message) {
            $this->assertStringContainsString(
                '{origin}',
                $message,
                'Every shared action-log message must render {origin}'
            );
        }
    }

    /**
     * Content changes are logged once. The operational logger must not also record
     * successful writes, or an audit would show every change twice.
     */
    public function testOperationalLoggerDoesNotDuplicateContentChanges(): void
    {
        $source = (string) file_get_contents(
            \dirname(__DIR__, 5) . '/api/src/Controller/AbstractWritableController.php'
        );

        // The only action-log call is the single logApiWrite() helper.
        $this->assertSame(
            1,
            substr_count($source, 'CwmactionlogHelper::log('),
            'A write should produce exactly one action-log entry'
        );

        // Operational logging here is limited to diagnostics, never info/warning
        // records of a successful change.
        $this->assertSame(
            0,
            substr_count($source, 'CwmlogHelper::info('),
            'Successful writes belong to the action log only, not the operational log'
        );
    }
}
