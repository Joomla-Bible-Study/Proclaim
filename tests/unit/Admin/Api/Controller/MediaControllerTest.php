<?php

/**
 * Unit tests for MediaController (REST API)
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\Controller;

use CWM\Component\Proclaim\Api\Controller\MediaController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for MediaController.
 *
 * Shared base-class boilerplate (extends ApiController, contentType/
 * default_view, displayList() visibility + published filter) is covered
 * once for all five entity controllers in ApiControllerBoilerplateTest.
 *
 * @since  10.3.0
 */
class MediaControllerTest extends ProclaimTestCase
{
    /**
     * getModel() maps the media content type to Cwmmediafiles.
     *
     * @return  void
     *
     * @since   10.3.2
     */
    public function testGetModelNameMapping(): void
    {
        $ref    = new \ReflectionMethod(MediaController::class, 'getModel');
        $source = file_get_contents($ref->getFileName());

        $this->assertStringContainsString("'media'      => 'Cwmmediafiles'", $source);
        $this->assertStringContainsString("'medium'     => 'Cwmmediafile'", $source);
        $this->assertStringContainsString("'mediafile'  => 'Cwmmediafile'", $source);
        $this->assertStringContainsString("'mediafiles' => 'Cwmmediafiles'", $source);
    }

    /**
     * preprocessSaveData() exists and is protected.
     *
     * @return  void
     *
     * @since   10.3.2
     */
    public function testPreprocessSaveDataExists(): void
    {
        $ref = new \ReflectionMethod(MediaController::class, 'preprocessSaveData');
        $this->assertTrue($ref->isProtected());
    }

    /**
     * preprocessSaveData() splits a comma-separated podcast_id into a list.
     *
     * @return  void
     *
     * @since   10.3.2
     */
    public function testPreprocessNormalizesPodcastId(): void
    {
        $source = file_get_contents((new \ReflectionClass(MediaController::class))->getFileName());

        $this->assertStringContainsString("explode(',', \$data['podcast_id'])", $source);
    }
}
