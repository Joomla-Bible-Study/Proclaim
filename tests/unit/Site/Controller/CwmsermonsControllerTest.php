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

use CWM\Component\Proclaim\Site\Controller\CwmsermonsController;
use CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * The message list's AJAX filter endpoint takes no CSRF token.
 *
 * filterAjax() is a read-only GET of public listings. The page that calls it
 * can come from Joomla's view cache, which hands every visitor the token of
 * the session that filled it, so a token check there fails for everyone else:
 * every filter change, page link and search reports "Invalid token".
 *
 * The check has been removed once already and then restored by a cleanup that
 * read the token the view still sent as a check left out. These tests keep
 * both halves gone: the check in the controller and the token in the view's
 * cached script options.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmsermonsControllerTest extends ProclaimTestCase
{
    /**
     * @param   class-string  $class   The class holding the method
     * @param   string        $method  The method name
     *
     * @return  string  The method's source
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function methodBody(string $class, string $method): string
    {
        $reflection = new \ReflectionMethod($class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFilterAjaxChecksNoToken(): void
    {
        $body = self::methodBody(CwmsermonsController::class, 'filterAjax');

        $this->assertStringNotContainsString(
            'checkToken',
            $body,
            'filterAjax() must not check a CSRF token. It is a read-only GET, and the view cache gives every visitor '
                . 'the same token, so a check fails for all but one of them.'
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testListViewPutsNoFormTokenInItsCachedOptions(): void
    {
        $body = self::methodBody(HtmlView::class, 'display');

        $this->assertStringContainsString('com_proclaim.sermonFilters', $body, 'The filter options moved; update this test.');
        $this->assertStringNotContainsString(
            'getFormToken',
            $body,
            'The list view must not put a form token in its script options. They are cached with the view, so the '
                . 'token would be one visitor\'s, handed to everyone.'
        );
    }
}
