<?php

/**
 * Unit tests for the Proclaim webservices plugin route registration
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Plugin\WebServices\Proclaim\Extension\Proclaim;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for Proclaim webservices plugin
 *
 * @since  10.3.0
 */
class WebservicesPluginTest extends ProclaimTestCase
{
    public function testPluginClassExists(): void
    {
        $this->assertTrue(class_exists(Proclaim::class));
    }

    public function testCreateWriteRoutesMethodExists(): void
    {
        $ref = new \ReflectionClass(Proclaim::class);
        $this->assertTrue($ref->hasMethod('createWriteRoutes'));
    }

    /**
     * Write routes must always use public=false, regardless of api_access setting.
     */
    public function testWriteRoutesNeverPublic(): void
    {
        $ref    = new \ReflectionMethod(Proclaim::class, 'createWriteRoutes');
        $source = file_get_contents($ref->getFileName());

        // The createWriteRoutes method should not accept an $isPublic parameter
        $params     = $ref->getParameters();
        $paramNames = array_map(fn ($p) => $p->getName(), $params);
        $this->assertNotContains('isPublic', $paramNames, 'Write routes should not accept isPublic parameter');

        // Verify public is hardcoded to false in the method
        // Extract just the method body by line range
        $startLine    = $ref->getStartLine();
        $endLine      = $ref->getEndLine();
        $lines        = \array_slice(file($ref->getFileName()), $startLine - 1, $endLine - $startLine + 1);
        $methodSource = implode('', $lines);

        $this->assertStringContainsString("'public'    => false", $methodSource);
    }

    /**
     * Verify both read and write route methods exist.
     */
    public function testCreateReadOnlyRoutesMethodExists(): void
    {
        $ref = new \ReflectionClass(Proclaim::class);
        $this->assertTrue($ref->hasMethod('createReadOnlyRoutes'));
    }

    /**
     * Resources the API exposes, and whether each accepts writes.
     *
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function resourceProvider(): array
    {
        return [
            'sermons'      => ['sermons', true],
            'teachers'     => ['teachers', true],
            'series'       => ['series', true],
            'podcasts'     => ['podcasts', true],
            'media'        => ['media', true],
            'topics'       => ['topics', true],
            'locations'    => ['locations', true],
            'messagetypes' => ['messagetypes', true],
            'playlists'    => ['playlists', false],
            'comments'     => ['comments', false],
            'servers'      => ['servers', false],
            'templates'    => ['templates', false],
        ];
    }

    /**
     * Every expected resource is registered, with the expected write posture.
     *
     */
    #[DataProvider('resourceProvider')]
    public function testResourceRegisteredWithExpectedWritePosture(string $resource, bool $writable): void
    {
        $resources = (new \ReflectionClass(Proclaim::class))->getConstant('RESOURCES');

        $this->assertArrayHasKey($resource, $resources, "Resource '$resource' should be registered");
        $this->assertSame(
            $writable,
            $resources[$resource],
            "Resource '$resource' should " . ($writable ? '' : 'NOT ') . 'accept writes'
        );
    }

    /**
     * The registry must not grow silently — a new resource has to be a deliberate
     * change here, so nothing gets exposed without a matching decision on writes.
     */
    public function testNoUnexpectedResourcesRegistered(): void
    {
        $resources = (new \ReflectionClass(Proclaim::class))->getConstant('RESOURCES');

        $this->assertSame(
            array_keys(self::resourceProvider()),
            array_keys($resources),
            'Registered resources drifted from the expected set'
        );
    }

    /**
     * templatecodes must stay unexposed.
     *
     * CwmtemplatecodeTable::store() writes the `templatecode` column to a real
     * PHP file under components/com_proclaim/tmpl/, which the front end executes.
     * A write endpoint would let a caller put arbitrary PHP in the web root, so
     * this is a code-execution boundary rather than a content decision.
     *
     * Reads are separately unsafe: the column holds PHP source and the table has
     * no `access` column, so it cannot honour view levels. Adding that column
     * would fix the read side only — it would NOT make writes safe. Do not treat
     * this test as waiting on a schema change.
     */
    public function testTemplatecodesIsNotExposed(): void
    {
        $resources = (new \ReflectionClass(Proclaim::class))->getConstant('RESOURCES');

        $this->assertArrayNotHasKey(
            'templatecodes',
            $resources,
            'templatecodes cannot be ACL-segmented (no access column) and serves PHP source'
        );

        $this->assertFalse(
            class_exists('CWM\\Component\\Proclaim\\Api\\Controller\\TemplatecodesController'),
            'No API controller should exist for templatecodes'
        );
    }

    /**
     * Write routes are only created for writable resources.
     *
     * Guards the loop in onBeforeApiRoute: the createWriteRoutes() call must stay
     * behind the writable flag, otherwise every read-only resource silently gains
     * POST/PATCH/DELETE.
     */
    public function testWriteRoutesGatedOnWritableFlag(): void
    {
        $ref   = new \ReflectionMethod(Proclaim::class, 'onBeforeApiRoute');
        $lines = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = preg_replace('/\s+/', ' ', implode('', $lines));

        $this->assertMatchesRegularExpression(
            '/if \(\$writable\) \{ \$this->createWriteRoutes\(/',
            $source,
            'createWriteRoutes() must remain guarded by the $writable flag'
        );
    }

    /**
     * Unroutable write requests are explained, at DEBUG.
     *
     * A request the router cannot match gets a bare 404 with nothing saying
     * whether the endpoint was misspelled, absent, or read-only — the hardest
     * kind of API problem to diagnose from outside, and the answer is known in
     * the plugin. DEBUG keeps it free in production; CwmlogHelper::debug() is a
     * no-op unless debug mode is on.
     */
    public function testUnroutableWritesAreExplainedAtDebugLevel(): void
    {
        $ref    = new \ReflectionMethod(Proclaim::class, 'explainUnroutableRequest');
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        // DEBUG only — never info/warning/error, which would record in production.
        $this->assertStringContainsString('CwmlogHelper::debug(', $source);

        foreach (['info', 'warning', 'error'] as $louder) {
            $this->assertStringNotContainsString(
                'CwmlogHelper::' . $louder . '(',
                $source,
                "Route diagnostics must stay at DEBUG, not {$louder}"
            );
        }

        // Both failure modes are distinguished for the reader.
        $this->assertStringContainsString('no such Proclaim API resource', $source);
        $this->assertStringContainsString('read-only by design', $source);
    }

    /**
     * Only write verbs are explained. A GET that 404s is a missing record, which
     * the response already conveys.
     */
    public function testOnlyWriteVerbsAreExplained(): void
    {
        $ref    = new \ReflectionMethod(Proclaim::class, 'explainUnroutableRequest');
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        foreach (['POST', 'PATCH', 'PUT', 'DELETE'] as $verb) {
            $this->assertStringContainsString("'$verb'", $source, "$verb should be explained");
        }

        $this->assertStringNotContainsString("'GET'", $source, 'GET should not be explained');
    }

    /**
     * A writable resource must not be explained — its write route exists, so a
     * failure there is authentication or validation, not routing. Guards against
     * the diagnostic misattributing a 401 as a routing problem.
     */
    public function testWritableResourcesAreNotExplained(): void
    {
        $ref    = new \ReflectionMethod(Proclaim::class, 'explainUnroutableRequest');
        $lines  = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = preg_replace('/\s+/', ' ', implode('', $lines));

        $this->assertMatchesRegularExpression(
            '/self::RESOURCES\[\$resource\] === false/',
            $source,
            'Only resources flagged read-only should be explained'
        );
    }
}
