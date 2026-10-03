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
     * {origin} is free text, translated at write time, and the action log only
     * filters by `extension` — so an API change must also carry a structural,
     * filterable signal, not just a human-readable one.
     */
    public function testLogUsesAFilterableContextNotAHardcodedExtension(): void
    {
        $helper = (string) file_get_contents(
            \dirname(__DIR__, 5) . '/admin/src/Helper/CwmactionlogHelper.php'
        );

        $ref  = new \ReflectionMethod(
            'CWM\\Component\\Proclaim\\Administrator\\Helper\\CwmactionlogHelper',
            'log'
        );
        $body = self::methodBody($helper, $ref);

        $this->assertStringContainsString(
            'self::context($app)',
            $body,
            "log() must pass the API-aware context to addLog(), not a hardcoded 'com_proclaim' literal"
        );
        $this->assertStringNotContainsString(
            "addLog([\$message], \$messageKey, 'com_proclaim',",
            $body,
            'The extension argument must vary by origin, not be a fixed string'
        );
    }

    /**
     * API writes get a dotted sub-extension so they can be isolated by a saved
     * search or SQL query; other origins keep the plain extension every
     * existing row already uses.
     */
    public function testApiContextUsesJoomlasDottedExtensionConvention(): void
    {
        $helper = (string) file_get_contents(
            \dirname(__DIR__, 5) . '/admin/src/Helper/CwmactionlogHelper.php'
        );

        $ref  = new \ReflectionMethod(
            'CWM\\Component\\Proclaim\\Administrator\\Helper\\CwmactionlogHelper',
            'context'
        );
        $body = self::methodBody($helper, $ref);

        $this->assertStringContainsString("isClient('api')", $body);
        $this->assertStringContainsString('com_proclaim.api', $body);
        $this->assertStringContainsString(
            "'com_proclaim'",
            $body,
            'Non-API origins must fall back to the plain extension, matching historical rows'
        );
    }

    /**
     * The control panel's "Recent Activity" link filters on the plain
     * extension — it must keep matching API rows via the list model's prefix
     * LIKE, or splitting the context would silently hide API activity from it.
     */
    public function testCpanelActivityLinkStillUsesThePlainExtension(): void
    {
        $tmpl = (string) file_get_contents(
            \dirname(__DIR__, 5) . '/admin/tmpl/cwmcpanel/default.php'
        );

        $this->assertStringContainsString(
            'filter[extension]=com_proclaim',
            $tmpl,
            "The cpanel link must stay on the plain 'com_proclaim' filter — "
                . "com_actionlogs' list model matches it via LIKE 'com_proclaim%', "
                . "which still catches 'com_proclaim.api' rows"
        );
    }

    /**
     * Slice one method body out of a source string by reflection line numbers.
     */
    private static function methodBody(string $source, \ReflectionMethod $ref): string
    {
        $lines = explode("\n", $source);

        return implode(
            "\n",
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );
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

    /**
     * Every write verb records an authenticated caller being refused.
     *
     * The narrow case this covers: a caller who authenticated successfully and
     * then attempted something their account is not permitted to do. Failed
     * authentication is deliberately NOT logged — an anonymous bad key is
     * unbounded noise and the web server access log already has it.
     */
    public function testDeniedActionsAreLogged(): void
    {
        // Scope every check to the individual verb's method body. The prior
        // whole-file regex with a non-greedy `.*?` could satisfy delete()'s
        // check by matching across into add()/edit()'s catch blocks, so one
        // verb could silently lose its denial logging while the counts and
        // cross-method matches kept everything green.
        foreach (['add', 'edit', 'delete'] as $verb) {
            $body = self::verbBody($verb);

            $this->assertStringContainsString(
                'catch (NotAllowed',
                $body,
                "{$verb}() should record a permission refusal"
            );
            $this->assertSame(
                1,
                substr_count($body, '$this->logDenied('),
                "{$verb}() should record exactly one refusal"
            );
        }
    }

    /**
     * A refusal must still reach the caller as a 403 — logging never swallows it.
     */
    public function testDeniedActionsRethrow(): void
    {
        foreach (['add', 'edit', 'delete'] as $verb) {
            $this->assertSame(
                1,
                substr_count(self::verbBody($verb), 'throw $e;'),
                "{$verb}()'s caught NotAllowed must be rethrown so the caller still gets a 403"
            );
        }
    }

    /**
     * Slice one write verb's method body out of AbstractWritableController.
     *
     * @param   string  $verb  Method name (add/edit/delete)
     *
     * @return  string
     */
    private static function verbBody(string $verb): string
    {
        $ref   = new \ReflectionMethod(
            'CWM\\Component\\Proclaim\\Api\\Controller\\AbstractWritableController',
            $verb
        );
        $lines = file($ref->getFileName());

        return implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );
    }

    /**
     * Refusals are operational logging, not audit entries — nothing changed, so
     * there is nothing to audit.
     */
    public function testDeniedActionsDoNotWriteToTheActionLog(): void
    {
        $ref    = new \ReflectionMethod(
            'CWM\\Component\\Proclaim\\Api\\Controller\\AbstractWritableController',
            'logDenied'
        );
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        $this->assertStringContainsString('CwmlogHelper::warning', $source);
        $this->assertStringNotContainsString(
            'CwmactionlogHelper',
            $source,
            'A refused action changed nothing, so it does not belong in the audit trail'
        );
    }
}
