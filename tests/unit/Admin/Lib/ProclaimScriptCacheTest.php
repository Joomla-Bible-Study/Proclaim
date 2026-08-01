<?php

/**
 * Tests for the update script's cache-clearing behaviour.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Both faults here were reported from a real update, and both are visible to
 * every administrator on every update: the external-cache notice named the same
 * plugin twice, and clearing the cache surfaced a red "Could not delete folder"
 * warning beside a successful update (#1407).
 *
 * The install script is a `return new class` file rather than a named class, so
 * these assert against its source. That is weaker than calling the methods, but
 * the alternative is instantiating an installer script outside an install.
 *
 * @since __DEPLOY_VERSION__
 */
class ProclaimScriptCacheTest extends ProclaimTestCase
{
    /**
     * The install script's source.
     *
     * @return  string
     */
    private function script(): string
    {
        return file_get_contents(\dirname(__DIR__, 4) . '/proclaim.script.php');
    }

    /**
     * The plugin list is deduplicated before it is printed.
     *
     * JCH Optimize registers more than one plugin under the element
     * `jchoptimize`, and the query filters on element but not folder, so the
     * name appeared twice in one sentence.
     *
     * @return  void
     */
    public function testExternalCacheNamesAreDeduplicated(): void
    {
        $this->assertMatchesRegularExpression(
            '/array_unique\s*\(\s*array_map/',
            $this->script(),
            'Names must be collapsed to distinct values before being joined'
        );
    }

    /**
     * Deduplication mirrors what the script does, pinned so the behaviour is
     * described rather than merely present.
     *
     * @return  void
     */
    public function testDeduplicationCollapsesRepeatedPlugins(): void
    {
        $known = [
            'jchoptimize'    => 'JCH Optimize',
            'litespeedcache' => 'LiteSpeed Cache',
            'cache'          => 'System - Page Cache',
        ];

        // What loadColumn() returns when a suite registers several plugins
        // under one element.
        $rows = ['jchoptimize', 'jchoptimize', 'cache'];

        $found = array_values(array_unique(array_map(
            static fn (string $element): string => $known[$element] ?? $element,
            $rows
        )));

        $this->assertSame(['JCH Optimize', 'System - Page Cache'], $found);
        $this->assertStringNotContainsString(
            'JCH Optimize, JCH Optimize',
            implode(', ', $found)
        );
    }

    /**
     * The folder-delete warning is filtered rather than left to alarm.
     *
     * It cannot be caught: FileStorage::_deleteFolder() logs to the `jerror`
     * category and returns false rather than throwing, so the try/catch around
     * clean() never sees it. Joomla routes that category into the message queue.
     *
     * @return  void
     */
    public function testCacheClearFiltersItsOwnHousekeepingWarning(): void
    {
        $source = $this->script();

        $this->assertStringContainsString(
            'withoutCacheHousekeepingNoise',
            $source,
            'The cache clean must run inside the message-queue filter'
        );
        $this->assertStringContainsString(
            'JLIB_FILESYSTEM_ERROR_FOLDER_DELETE',
            $source,
            'The filter should key on the specific message, not drop the queue'
        );
    }

    /**
     * Only that one message is dropped — anything else the operation queues,
     * including messages from plugins reacting to the cache events, is put back.
     *
     * @return  void
     */
    public function testFilterPreservesOtherMessages(): void
    {
        $source = $this->script();

        $this->assertMatchesRegularExpression(
            '/\$app->enqueueMessage\(\s*\$message\[.message.\]/',
            $source,
            'Messages that are not the folder-delete warning must be re-queued'
        );
        $this->assertStringContainsString(
            '$before = $app->getMessageQueue()',
            $source,
            'Pre-existing messages must be distinguished from ones the clean added'
        );
    }
}
