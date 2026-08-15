<?php

/**
 * The package retires the component's update sites only when it holds a
 * working one of its own.
 *
 * "The package has an update site" and "the package has a channel that can
 * deliver an update" look like the same test and are not. A package row on the
 * 9.x stream satisfies the first while announcing nothing, and the component's
 * rows would be deleted on the strength of it.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since  __DEPLOY_VERSION__
 */
class PackageUpdateSiteGuardTest extends ProclaimTestCase
{
    /**
     * @return  string  The package manifest script's source
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function script(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 4) . '/build/script.install.php');
    }

    /**
     * @return  string  Just the retirement method
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function retirementBody(): string
    {
        $source = self::script();
        $start  = strpos($source, 'private function removeComponentUpdateSites(): void');

        self::assertNotFalse($start, 'removeComponentUpdateSites() could not be found.');

        $end = strpos($source, "\n    }\n", $start);

        return substr($source, $start, $end - $start);
    }

    #[TestDox('The guard requires a package row on the current stream, not merely any row')]
    public function testGuardTestsForAWorkingChannel(): void
    {
        $body = self::retirementBody();

        $this->assertStringContainsString(
            'currentStreamSites($packageSites)',
            $body,
            'A package row that announces nothing must not license deleting the '
            . 'component rows that were working.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/if \(\$packageSites === \[\]\)/',
            $body,
            'Testing the row exists is the weaker check this replaced.'
        );
    }

    #[TestDox('The current stream is identified allowing for HTML-escaped locations')]
    public function testStreamMatchAllowsEscapedLocations(): void
    {
        $source = self::script();

        $this->assertStringContainsString(
            "str_replace('&amp;', '&'",
            $source,
            'Stored locations occur both plain and HTML-escaped; matching only the '
            . 'plain form reads an escaped current-stream row as the 9.x one.'
        );
    }

    #[TestDox('A site is never left without a channel')]
    public function testNothingIsDeletedWithoutAWorkingPackageRow(): void
    {
        $body = self::retirementBody();

        $guard  = strpos($body, 'currentStreamSites($packageSites)');
        $delete = strpos($body, '->delete(');

        $this->assertNotFalse($guard);
        $this->assertNotFalse($delete);
        $this->assertLessThan(
            $delete,
            $guard,
            'The guard has to come first, or rows are deleted before anything '
            . 'establishes the site still has a way to hear about updates.'
        );
    }
}
