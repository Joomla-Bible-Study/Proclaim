<?php

/**
 * Unit tests for CwmcommentController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmcommentController;
use CWM\Component\Proclaim\Administrator\Model\CwmcommentModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmcommentController
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmcommentControllerTest extends ProclaimTestCase
{
    /**
     * Regression test for #1439: batch() called $this->getModel('Comment', ...),
     * but there is no CommentModel — only CwmcommentModel, matching the
     * Cwm-prefixed naming every other controller in this codebase uses.
     *
     * @return void
     */
    public function testBatchResolvesRealModelName(): void
    {
        $reflection = new \ReflectionMethod(CwmcommentController::class, 'batch');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertStringNotContainsString(
            "getModel('Comment'",
            $body,
            "batch() must not reference the nonexistent 'Comment' model — see #1439"
        );

        $this->assertStringContainsString(
            "getModel('Cwmcomment'",
            $body,
            'batch() must reference the real CwmcommentModel class — see #1439'
        );

        $this->assertTrue(
            class_exists(CwmcommentModel::class),
            'CwmcommentModel must exist for batch() to resolve it correctly'
        );
    }
}
