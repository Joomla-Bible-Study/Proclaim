<?php

/**
 * Unit tests for the schemaorg/proclaim plugin's stored-item bridge.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Plugin\Schemaorg;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A sermon, teacher or series page must carry its stored item node exactly once.
 *
 * Joomla's system plugin looks the row up under the page's view context
 * (`com_proclaim.cwmsermon`), but the edit form saves it under the form context
 * (`com_proclaim.cwmmessage`), so core on its own never finds a saved row.
 * appendStoredItem() adds the form-context row, unless core already printed a
 * node for the item from a row stored under the view context.
 *
 * The plugin class is outside composer's autoload map, so it is loaded directly
 * and the private static method is reached through reflection.
 *
 * @since  __DEPLOY_VERSION__
 */
class ProclaimStoredItemTest extends ProclaimTestCase
{
    private const DOMAIN = 'https://example.org/';

    private const WEBPAGE_ID = 'https://example.org/#/schema/WebPage/base';

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\CWM\Plugin\Schemaorg\Proclaim\Extension\Proclaim::class)) {
            require_once \dirname(__DIR__, 4) . '/plugins/schemaorg/proclaim/src/Extension/Proclaim.php';
        }
    }

    /**
     * @param   array       $graph   The @graph core built
     * @param   array|null  $stored  The form-context row's schema
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function append(array $graph, ?array $stored): array
    {
        $method = new \ReflectionMethod(\CWM\Plugin\Schemaorg\Proclaim\Extension\Proclaim::class, 'appendStoredItem');

        return $method->invoke(null, $graph, 'com_proclaim.cwmsermon', 42, $stored, self::DOMAIN);
    }

    /**
     * The graph core builds for a page with no row of its own.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function coreGraph(): array
    {
        return [
            ['@type' => 'Organization', '@id' => 'https://example.org/#/schema/Organization/base'],
            ['@type' => 'WebSite', '@id' => 'https://example.org/#/schema/WebSite/base'],
            ['@type' => 'WebPage', '@id' => self::WEBPAGE_ID],
        ];
    }

    #[TestDox('a form-context row is added with the @id and isPartOf core would have given it')]
    public function testAppendsTheFormContextRow(): void
    {
        $graph = $this->append($this->coreGraph(), ['@type' => 'CreativeWork', 'name' => 'A sermon']);

        $this->assertCount(4, $graph);
        $this->assertSame('A sermon', $graph[3]['name']);
        $this->assertSame('https://example.org/#/schema/com_proclaim/cwmsermon/42', $graph[3]['@id']);
        $this->assertSame(['@id' => self::WEBPAGE_ID], $graph[3]['isPartOf']);
    }

    #[TestDox('nothing is added when core already printed a node for the item')]
    public function testDoesNotDuplicateANodeCoreFound(): void
    {
        $graph   = $this->coreGraph();
        $graph[] = [
            '@type' => 'CreativeWork',
            '@id'   => 'https://example.org/#/schema/com_proclaim/cwmsermon/42',
            'name'  => 'Stored under the view context',
        ];

        $result = $this->append($graph, ['@type' => 'CreativeWork', 'name' => 'Stored under the form context']);

        $this->assertSame($graph, $result);
    }

    #[TestDox('the graph is unchanged when the item has no form-context row')]
    public function testLeavesTheGraphAloneWithoutARow(): void
    {
        $this->assertSame($this->coreGraph(), $this->append($this->coreGraph(), null));
    }

    #[TestDox('the added node is sanitised whatever its @type, not only the three item types')]
    public function testSanitisesTheAddedNodeOfAnyType(): void
    {
        $graph = $this->append($this->coreGraph(), ['@type' => 'Event', 'name' => 'x</script><script>alert(1)</script>']);

        $this->assertStringNotContainsStringIgnoringCase('</script', $graph[3]['name']);
        $this->assertSame('Event', $graph[3]['@type']);
    }
}
