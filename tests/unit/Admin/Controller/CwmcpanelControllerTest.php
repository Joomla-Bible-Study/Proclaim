<?php

/**
 * Unit tests for CwmcpanelController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmcpanelController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * Regression test for #1449: CwmcpanelController extended FormController,
 * but CwmcpanelModel extends BaseModel, not FormModel -- and no
 * task=cwmcpanel.* routing exists anywhere (only view=cwmcpanel display
 * links/redirects). Now extends BaseController, matching DisplayController's
 * pattern for display-only controllers.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmcpanelControllerTest extends ProclaimTestCase
{
    public function testExtendsBaseControllerNotFormController(): void
    {
        $this->assertTrue(
            is_subclass_of(CwmcpanelController::class, BaseController::class),
            'CwmcpanelController must extend Joomla BaseController — see #1449'
        );

        $this->assertFalse(
            is_subclass_of(CwmcpanelController::class, FormController::class),
            'CwmcpanelController must not extend FormController — its model (CwmcpanelModel) extends BaseModel, not FormModel — see #1449'
        );
    }
}
