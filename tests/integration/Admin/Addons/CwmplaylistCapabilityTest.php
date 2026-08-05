<?php

/**
 * Integration tests for the addon playlist capability contract.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Addons;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * supportsPlaylists() is the single source of truth for which platforms the
 * playlist system reaches — the sync helper, the server picker and (since
 * #1392) the Choose Playlist(s) field on the media file edit form all consult
 * it instead of hardcoding "youtube".
 *
 * A platform silently flipping to true would put an unusable, permanently
 * empty picker back on every media file of that type, so the roster is pinned
 * here rather than left to the addon classes to agree among themselves.
 *
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CWMAddon::class)]
class CwmplaylistCapabilityTest extends IntegrationTestCase
{
    /**
     * Every shipped server type and whether it can host playlists.
     *
     * @return  array<string, array{0: string, 1: bool}>
     */
    public static function serverTypeProvider(): array
    {
        return [
            'youtube'     => ['youtube', true],
            'vimeo'       => ['vimeo', true],
            'local'       => ['local', false],
            'direct'      => ['direct', false],
            'legacy'      => ['legacy', false],
            'embed'       => ['embed', false],
            'article'     => ['article', false],
            'docman'      => ['docman', false],
            'facebook'    => ['facebook', false],
            'rumble'      => ['rumble', false],
            'soundcloud'  => ['soundcloud', false],
            'resi'        => ['resi', false],
            'wistia'      => ['wistia', false],
            'dailymotion' => ['dailymotion', false],
            'googledrive' => ['googledrive', false],
            'virtuemart'  => ['virtuemart', false],
        ];
    }

    /**
     * Each addon reports the capability the playlist system expects of it.
     *
     * @param   string  $type      Server type as stored in #__bsms_servers.
     * @param   bool    $expected  Whether the platform hosts playlists.
     *
     * @return  void
     */
    #[Test]
    #[DataProvider('serverTypeProvider')]
    public function addonReportsItsPlaylistCapability(string $type, bool $expected): void
    {
        $this->assertSame(
            $expected,
            CWMAddon::getInstance($type)->supportsPlaylists(),
            \sprintf('Addon "%s" reported the wrong playlist capability', $type)
        );
    }

    /**
     * The capability is opt-in: the base class denies it, so the fourteen
     * platforms above inherit false rather than each restating it, and a new
     * addon cannot pick up a picker it has no way to fill.
     *
     * @return  void
     */
    #[Test]
    public function capabilityIsOptInAtTheBaseClass(): void
    {
        foreach (['supportsPlaylists', 'supportsPlaylistWriteback'] as $name) {
            $method = new \ReflectionMethod(CWMAddon::class, $name);

            $this->assertSame(
                CWMAddon::class,
                $method->getDeclaringClass()->getName(),
                \sprintf('%s() must be declared on the base class', $name)
            );

            // The body is a bare `return false;` — read it rather than
            // instantiate, since CWMAddon is abstract and its constructor
            // resolves addon paths off disk.
            $source = file($method->getFileName());
            $body   = implode('', \array_slice(
                $source,
                $method->getStartLine() - 1,
                $method->getEndLine() - $method->getStartLine() + 1
            ));

            // Anchor on the whole body being a lone `return false;` -- a
            // bare /return\s+false\s*;/ also matches a conditionally-true
            // body like `if ($x) { return true; } return false;`, which is
            // exactly the "base class no longer defaults to false" regression
            // this guards.
            $this->assertDoesNotMatchRegularExpression(
                '/return\s+true/',
                $body,
                \sprintf('%s() must not have any true-returning branch in the base class', $name)
            );
            $this->assertMatchesRegularExpression(
                '/return\s+false\s*;/',
                $body,
                \sprintf('%s() must default to false', $name)
            );
        }
    }
}
