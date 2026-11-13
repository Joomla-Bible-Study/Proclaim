<?php

/**
 * Part of Proclaim Package
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Helper\HelperFactory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The YouTube module's status poll reaches its helper through com_ajax.
 *
 * The URL comes from the module's template and the helper namespace from its
 * service provider, and the helper is looked up exactly as com_ajax looks it
 * up. The poll went unanswered from 10.1.0 because the class name and the
 * name com_ajax derives from the URL did not match, and nothing noticed.
 *
 * @since  __DEPLOY_VERSION__
 */
class YoutubeStatusEndpointTest extends ProclaimTestCase
{
    private const string MODULE_DIR = '/modules/site/mod_proclaim_youtube';

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The poll URL in the template names a helper and method that com_ajax can call')]
    public function testPollUrlReachesTheHelper(): void
    {
        $root = \dirname(__DIR__, 4);

        $template = (string) file_get_contents($root . self::MODULE_DIR . '/tmpl/default.php');
        $url      = [];
        $this->assertSame(
            1,
            preg_match('/option=com_ajax&module=([\w-]+)&method=(\w+)/', $template, $url),
            'The template has no com_ajax poll URL'
        );
        $module = $url[1];
        $method = $url[2];

        $provider = (string) file_get_contents($root . self::MODULE_DIR . '/services/provider.php');
        $factory  = [];
        $this->assertSame(
            1,
            preg_match("/new HelperFactory\\('([^']+)'\\)/", $provider, $factory),
            'The service provider registers no helper factory'
        );
        $namespace = str_replace('\\\\', '\\', $factory[1]);

        $helpers = new HelperFactory($namespace);
        $helpers->setDatabase($this->createStub(DatabaseInterface::class));

        $helper = $helpers->getHelper($this->helperName($module));

        $this->assertNotNull($helper, 'com_ajax asks for ' . $this->helperName($module) . ' in ' . $namespace . ', which does not exist');
        $this->assertTrue(method_exists($helper, $method . 'Ajax'), 'The helper has no ' . $method . 'Ajax() for com_ajax to call');
        $this->assertFalse(
            (new \ReflectionMethod($helper, $method . 'Ajax'))->isStatic(),
            'com_ajax calls the method on an instance, as the Joomla manual describes'
        );
    }

    /**
     * The helper name com_ajax asks a module's helper factory for, built as
     * components/com_ajax/ajax.php builds it: "Mod" and each part of the
     * module name capitalised, then "Helper", less the leading "Mod".
     *
     * @param   string  $module  The module parameter, without "mod_"
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function helperName(string $module): string
    {
        if (str_contains($module, '_')) {
            $parts = explode('_', $module);
        } elseif (str_contains($module, '-')) {
            $parts = explode('-', $module);
        } else {
            $parts = [$module];
        }

        return substr('Mod' . implode('', array_map('ucfirst', $parts)) . 'Helper', 3);
    }
}
