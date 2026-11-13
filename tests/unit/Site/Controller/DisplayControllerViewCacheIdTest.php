<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\DisplayController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Cache\Controller\ViewController;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * DisplayController removes a not-found page from the view cache by key, so
 * its key has to be the one Joomla stored the page under.
 *
 * @since  __DEPLOY_VERSION__
 */
class DisplayControllerViewCacheIdTest extends ProclaimTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The view-cache key matches the key ViewController stores a view under')]
    public function testKeyMatchesViewController(): void
    {
        $view = new \stdClass();

        $cache    = (new \ReflectionClass(ViewController::class))->newInstanceWithoutConstructor();
        $expected = (new \ReflectionMethod(ViewController::class, '_makeId'))->invoke($cache, $view, 'display');

        $actual = (new \ReflectionMethod(DisplayController::class, 'viewCacheId'))->invoke(null, $view);

        $this->assertSame($expected, $actual);
    }
}
