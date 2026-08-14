<?php

/**
 * Integration tests for the default view/controller a bare request resolves to.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * `index.php?option=com_proclaim`, with no view, 404'd on the front end:
 *
 *     404 Invalid controller class: DisplayController
 *
 * The site dispatcher's default was the string 'DisplayController' — a class
 * name where a controller *name* belongs. applyViewAndController() set both the
 * controller and the view to it, so Joomla looked for a
 * `DisplayControllerController` and found nothing.
 *
 * The two cannot be the same string on the site: the landing page has no
 * controller of its own, it is rendered by the generic display controller. So
 * the default view is now passed separately.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmproclaimHelper::class)]
class CwmproclaimHelperDefaultViewTest extends IntegrationTestCase
{
    /**
     * @var  array<string, mixed>
     */
    private array $saved = [];

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $input = Factory::getApplication()->getInput();

        foreach (['view', 'controller', 'task'] as $key) {
            $this->saved[$key] = $input->get($key);
        }
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        $input = Factory::getApplication()->getInput();

        foreach ($this->saved as $key => $value) {
            $input->set($key, $value);
        }

        parent::tearDown();
    }

    /**
     * The regression: a bare request must reach a controller that exists.
     */
    #[TestDox('a bare request resolves the landing view through the display controller')]
    public function testBareRequestResolvesSeparateViewAndController(): void
    {
        $input = $this->clearRequest();

        CwmproclaimHelper::applyViewAndController('display', 'cwmlandingpage');

        self::assertSame('display', $input->getCmd('controller'), 'The controller must be one that exists.');
        self::assertSame('cwmlandingpage', $input->getCmd('view'), 'The view must be the landing page.');
    }

    /**
     * The admin side passes one argument and must keep behaving as it did.
     */
    #[TestDox('with no separate view the controller name is still used for both')]
    public function testSingleArgumentKeepsTheOldBehaviour(): void
    {
        $input = $this->clearRequest();

        CwmproclaimHelper::applyViewAndController('cwmcpanel');

        self::assertSame('cwmcpanel', $input->getCmd('controller'));
        self::assertSame('cwmcpanel', $input->getCmd('view'), 'Unchanged for callers that pass one name.');
    }

    /**
     * An explicit controller still names the view, defaults untouched.
     */
    #[TestDox('an explicit controller with no view still supplies the view')]
    public function testExplicitControllerSuppliesTheView(): void
    {
        $input = $this->clearRequest();
        $input->set('controller', 'cwmsermon');

        CwmproclaimHelper::applyViewAndController('display', 'cwmlandingpage');

        self::assertSame('cwmsermon', $input->getCmd('controller'));
        self::assertSame('cwmsermon', $input->getCmd('view'), 'The default view must not override an explicit controller.');
    }

    /**
     * A controller.task command still splits, and the defaults stay out of it.
     */
    #[TestDox('a controller.task command is split and left alone')]
    public function testControllerTaskCommandIsUnaffected(): void
    {
        $input = $this->clearRequest();
        $input->set('task', 'cwmsermon.comment');

        CwmproclaimHelper::applyViewAndController('display', 'cwmlandingpage');

        self::assertSame('cwmsermon', $input->getCmd('controller'));
        self::assertSame('comment', $input->getCmd('task'));
    }

    /**
     * @return  \Joomla\Input\Input
     */
    private function clearRequest(): \Joomla\Input\Input
    {
        $input = Factory::getApplication()->getInput();

        $input->set('view', null);
        $input->set('controller', null);
        $input->set('task', null);

        return $input;
    }
}
