<?php

/**
 * Unit tests for the cwmmediafile.xhr dispatch allow-list.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Controller\CwmmediafileController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression coverage for #1599.
 *
 * CwmmediafileController::xhr() took a `handler` name straight from the request
 * and invoked it on the resolved addon after only a method_exists() check, with
 * no permission check beyond the CSRF token. That made every public method on
 * every addon callable by name -- including CWMAddonYoutube::createLiveEvent()
 * and cancelLiveEvent(), which act on the site's connected YouTube account. Any
 * authenticated backend user who could reach com_proclaim could cancel the
 * organisation's live stream without holding any Proclaim edit permission.
 *
 * Dispatch is now restricted to handlers each addon opts into via
 * getXhrHandlers(), and the task requires core.edit or core.create.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmediafileXhrDispatchTest extends ProclaimTestCase
{
    /**
     * Every concrete addon class.
     *
     * @return array<string, array{0: class-string}>
     */
    public static function addonClassProvider(): array
    {
        $base    = \dirname(__DIR__, 4) . '/admin/src/Addons/Servers';
        $classes = [];

        foreach (glob($base . '/*/CWMAddon*.php') as $file) {
            $server = basename(\dirname($file));
            $short  = basename($file, '.php');

            $classes[$short] = [
                'CWM\\Component\\Proclaim\\Administrator\\Addons\\Servers\\' . $server . '\\' . $short,
            ];
        }

        return $classes;
    }

    /**
     * An addon exposes nothing unless it opts in.
     */
    public function testBaseAddonExposesNoHandlersByDefault(): void
    {
        $method = new \ReflectionMethod(CWMAddon::class, 'getXhrHandlers');

        $this->assertTrue($method->isPublic(), 'getXhrHandlers() must be callable by the controller');

        // CWMAddon is abstract, so assert on the declared body rather than an
        // instance: the default must be an empty list so an addon that says
        // nothing exposes nothing.
        $lines = file($method->getFileName());
        $body  = implode('', \array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));

        $this->assertMatchesRegularExpression(
            '/return\s*\[\s*\]\s*;/',
            $body,
            'The base addon must expose no xhr handlers — opting in has to be explicit — see #1599'
        );
    }

    /**
     * A listed handler that does not resolve to a real public method would 500
     * on a request the allow-list claims is valid.
     *
     * @param   class-string  $class  Addon under test
     */
    #[DataProvider('addonClassProvider')]
    public function testEveryListedHandlerResolvesToAPublicMethod(string $class): void
    {
        $addon    = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $handlers = $addon->getXhrHandlers();

        // Most addons legitimately expose nothing; assert the shape so the
        // test still carries an assertion rather than passing vacuously.
        $this->assertIsArray($handlers, $class . '::getXhrHandlers() must return an array');

        foreach ($handlers as $handler) {
            $this->assertTrue(
                method_exists($addon, $handler),
                $class . " lists xhr handler '{$handler}' but has no such method"
            );
            $this->assertTrue(
                (new \ReflectionMethod($class, $handler))->isPublic(),
                $class . " lists xhr handler '{$handler}' but the method is not public — xhr() could not call it"
            );
        }
    }

    /**
     * The exploit surface. These are invoked server-side (CwmmediafileModel and
     * CwmmediafileTable call them directly on the addon object with their own
     * constructed Input) and have no JS caller, so they must never be reachable
     * by request-supplied name.
     *
     * @param   class-string  $class  Addon under test
     */
    #[DataProvider('addonClassProvider')]
    public function testStateChangingAndInternalMethodsAreNeverExposed(string $class): void
    {
        $addon    = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $handlers = $addon->getXhrHandlers();

        $forbiddenNames = [
            // State-changing, act on the connected platform account.
            'createLiveEvent',
            'cancelLiveEvent',
            // Dispatchers/introspection — never meant to be request-callable.
            'handleAjaxAction',
            'getAjaxActions',
            'getXhrHandlers',
            'getXml',
        ];

        foreach ($forbiddenNames as $forbidden) {
            $this->assertNotContains(
                $forbidden,
                $handlers,
                $class . " must not expose '{$forbidden}' through the generic xhr dispatcher — see #1599"
            );
        }
    }

    /**
     * Handler names the shipped admin JS calls, paired with the addon that must
     * serve them. Derived from `handler=` in build/media_source/js — a new JS
     * caller without a matching allow-list entry would 404 at runtime.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function jsCallerProvider(): array
    {
        return [
            // addon-youtube-browser.es6.js
            'youtube fetchChannelPlaylists' => ['Youtube', 'fetchChannelPlaylists'],
            'youtube fetchChannelVideos'    => ['Youtube', 'fetchChannelVideos'],
            'youtube searchChannelVideos'   => ['Youtube', 'searchChannelVideos'],
            'youtube fetchPlaylistVideos'   => ['Youtube', 'fetchPlaylistVideos'],
            'youtube fetchLiveVideos'       => ['Youtube', 'fetchLiveVideos'],
            // addon-vimeo-browser.es6.js
            'vimeo fetchFolders' => ['Vimeo', 'fetchFolders'],
            'vimeo fetchVideos'  => ['Vimeo', 'fetchVideos'],
            'vimeo getMetadata'  => ['Vimeo', 'getMetadata'],
            // addon-wistia-browser.es6.js
            'wistia fetchProjects' => ['Wistia', 'fetchProjects'],
            'wistia fetchVideos'   => ['Wistia', 'fetchVideos'],
            'wistia getMetadata'   => ['Wistia', 'getMetadata'],
            // playlist-picker.es6.js (type is the server type, so both)
            'youtube fetchRemotePlaylists' => ['Youtube', 'fetchRemotePlaylists'],
            'vimeo fetchRemotePlaylists'   => ['Vimeo', 'fetchRemotePlaylists'],
        ];
    }

    /**
     * @param   string  $server   Addon directory/class suffix
     * @param   string  $handler  Handler the JS requests
     */
    #[DataProvider('jsCallerProvider')]
    public function testHandlersCalledByShippedJsRemainAllowed(string $server, string $handler): void
    {
        $class = 'CWM\\Component\\Proclaim\\Administrator\\Addons\\Servers\\'
            . $server . '\\CWMAddon' . $server;
        $addon = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

        $this->assertContains(
            $handler,
            $addon->getXhrHandlers(),
            "The admin JS calls handler={$handler} on type={$server}; removing it from the allow-list breaks that UI"
        );
    }

    /**
     * The upload path #1564 restored stays reachable for the three addons that
     * implement it.
     *
     * @param   string  $server  Addon serving uploads
     */
    #[DataProvider('uploadAddonProvider')]
    public function testUploadRemainsAllowedForRealUploadHandlers(string $server): void
    {
        $class = 'CWM\\Component\\Proclaim\\Administrator\\Addons\\Servers\\'
            . $server . '\\CWMAddon' . $server;
        $addon = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

        $this->assertContains('upload', $addon->getXhrHandlers(), $server . ' must still accept uploads — see #1564');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function uploadAddonProvider(): array
    {
        return ['Local' => ['Local'], 'Direct' => ['Direct'], 'Legacy' => ['Legacy']];
    }

    /**
     * xhr() must check permissions before it dispatches. A structural
     * assertion: a live permission test would need a user/group fixture for
     * what is a single authorise() call.
     */
    public function testXhrRequiresAnEditOrCreatePermission(): void
    {
        $ref   = new \ReflectionMethod(CwmmediafileController::class, 'xhr');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            "authorise('core.edit', 'com_proclaim')",
            $body,
            'xhr() must require core.edit — the CSRF token alone is not authorisation — see #1599'
        );
        $this->assertStringContainsString(
            "authorise('core.create', 'com_proclaim')",
            $body,
            'xhr() must also accept core.create — the browse fields render on create forms too'
        );

        // The permission check has to run before dispatch, not after.
        $this->assertLessThan(
            strpos($body, '$addon->$handler('),
            strpos($body, 'authorise('),
            'The permission check must precede dispatch'
        );
    }

    /**
     * The dispatcher must consult the allow-list, not just method_exists().
     */
    public function testXhrDispatchesOnlyThroughTheAllowList(): void
    {
        $ref   = new \ReflectionMethod(CwmmediafileController::class, 'xhr');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            'getXhrHandlers()',
            $body,
            'xhr() must validate the requested handler against the addon allow-list — see #1599'
        );
        $this->assertLessThan(
            strpos($body, '$addon->$handler('),
            strpos($body, 'getXhrHandlers()'),
            'The allow-list check must precede dispatch'
        );
    }
}
