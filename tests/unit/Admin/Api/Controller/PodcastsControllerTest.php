<?php

/**
 * Unit tests for PodcastsController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\PodcastsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for PodcastsController.
 *
 * Shared base-class boilerplate (extends ApiController, contentType/
 * default_view, displayList() visibility + published filter) is covered
 * once for all five entity controllers in ApiControllerBoilerplateTest.
 *
 * @since  10.3.0
 */
class PodcastsControllerTest extends ProclaimTestCase
{
    public function testGetModelNameMapping(): void
    {
        $ref    = new \ReflectionMethod(PodcastsController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'podcasts' => 'Cwmpodcasts'", $source);
        $this->assertStringContainsString("'podcast'  => 'Cwmpodcast'", $source);
    }
}
