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
     * Verify all 5 resources are registered in onBeforeApiRoute.
     */
    public function testAllResourcesRegistered(): void
    {
        $ref   = new \ReflectionMethod(Proclaim::class, 'onBeforeApiRoute');
        $lines = \array_slice(
            file($ref->getFileName()),
            $ref->getStartLine() - 1,
            $ref->getEndLine() - $ref->getStartLine() + 1
        );
        $source = implode('', $lines);

        foreach (['sermons', 'teachers', 'series', 'podcasts', 'media'] as $resource) {
            $this->assertStringContainsString($resource, $source, "Resource '$resource' should be registered");
        }
    }
}
