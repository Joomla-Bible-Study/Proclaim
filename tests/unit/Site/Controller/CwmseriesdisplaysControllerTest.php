<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmseriesdisplaysController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1502: cwmseriesdisplays.paginateAjax always 500'd.
 *
 * Root causes (both fixed):
 * 1. getModel()'s default config passed ignore_request => true, which sets
 *    __state_set = true in BaseModel::__construct() (see
 *    joomla-cms/libraries/src/MVC/Model/BaseModel.php), permanently
 *    skipping populateState() for any caller — like paginateAjax() — that
 *    fetches the model with fewer than 3 args. Fixed by dropping
 *    ignore_request from the default (see CwmseriesdisplaysModelTest for
 *    the companion populateState() fix).
 * 2. paginateAjax()'s catch was \Exception, which doesn't catch \TypeError
 *    (extends \Error, not \Exception) — so the null-params/template fallout
 *    from bug #1 produced an unhandled fatal instead of a JSON error
 *    response. Broadened to \Throwable regardless, since the endpoint's
 *    only consumer is fetch()-based JS expecting JSON either way.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmseriesdisplaysControllerTest extends ProclaimTestCase
{
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysController::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testGetModelDefaultConfigDoesNotIgnoreRequest(): void
    {
        $reflection  = new \ReflectionMethod(CwmseriesdisplaysController::class, 'getModel');
        $configParam = $reflection->getParameters()[2];

        $this->assertTrue($configParam->isDefaultValueAvailable());
        $this->assertSame(
            [],
            $configParam->getDefaultValue(),
            'getModel()\'s default $config must not set ignore_request — it silently skips populateState() ' .
                'for any caller (like paginateAjax()) using fewer than 3 args — see #1502'
        );
    }

    public function testPaginateAjaxCatchesThrowableNotJustException(): void
    {
        $body = self::methodBody('paginateAjax');

        $this->assertStringContainsString(
            'catch (\Throwable $e)',
            $body,
            'paginateAjax() must catch \Throwable — \TypeError (from the null-params bug this regression test ' .
                'guards) extends \Error, not \Exception, and previously escaped as an unhandled fatal — see #1502'
        );
    }
}
