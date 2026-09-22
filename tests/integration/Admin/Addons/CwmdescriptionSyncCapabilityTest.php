<?php

/**
 * Integration tests for the addon description-sync capability contract.
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
 * `supportsDescriptionSync()` decides which media files are offered a
 * description push, both in the analytics drill-down that builds the list and
 * in the controller that performs it.
 *
 * #1920 was that contract failing in both directions at once: the drill-down
 * carried a hardcoded `['vimeo', 'wistia']` roster, so YouTube — which
 * implements the push — was never offered it; and four addons returned true
 * with no implementation behind them, so anything trusting the flag cleared
 * the controller's capability guard and then hit the base stub, which reports
 * the operation unsupported immediately after the guard said otherwise.
 *
 * Both halves are pinned here: the roster, and the rule that a true flag must
 * have a real method behind it.
 *
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CWMAddon::class)]
class CwmdescriptionSyncCapabilityTest extends IntegrationTestCase
{
    /**
     * Every shipped server type and whether it can push a description back.
     *
     * Only platforms Proclaim can actually authenticate against and write to
     * belong here. Rumble, Facebook, Dailymotion and SoundCloud each returned
     * true before #1920 without implementing the push.
     *
     * @return  array<string, array{0: string, 1: bool}>
     */
    public static function serverTypeProvider(): array
    {
        return [
            'youtube'     => ['youtube', true],
            'vimeo'       => ['vimeo', true],
            'wistia'      => ['wistia', true],
            'dailymotion' => ['dailymotion', false],
            'facebook'    => ['facebook', false],
            'rumble'      => ['rumble', false],
            'soundcloud'  => ['soundcloud', false],
            'local'       => ['local', false],
            'direct'      => ['direct', false],
            'legacy'      => ['legacy', false],
            'embed'       => ['embed', false],
            'article'     => ['article', false],
            'docman'      => ['docman', false],
            'resi'        => ['resi', false],
            'googledrive' => ['googledrive', false],
            'virtuemart'  => ['virtuemart', false],
        ];
    }

    /**
     * Each addon reports the capability the description-sync system expects.
     *
     * @param   string  $type      Server type as stored in #__bsms_servers.
     * @param   bool    $expected  Whether the platform accepts a description push.
     *
     * @return  void
     */
    #[Test]
    #[DataProvider('serverTypeProvider')]
    public function addonReportsItsDescriptionSyncCapability(string $type, bool $expected): void
    {
        $this->assertSame(
            $expected,
            CWMAddon::getInstance($type)->supportsDescriptionSync(),
            \sprintf('Addon "%s" reported the wrong description-sync capability', $type)
        );
    }

    /**
     * A declared capability must have an implementation behind it.
     *
     * This is the check that catches #1920's second half. An addon may return
     * true only if it overrides `syncDescription()`; inheriting the base stub
     * means the guard in `CwmadminController::syncVideoDescriptionXHR()` lets
     * the request through to a method that reports the platform unsupported.
     *
     * @return  void
     */
    #[Test]
    public function claimingTheCapabilityRequiresImplementingIt(): void
    {
        foreach (self::serverTypeProvider() as [$type, $expected]) {
            $addon = CWMAddon::getInstance($type);

            if (!$addon->supportsDescriptionSync()) {
                continue;
            }

            $method = new \ReflectionMethod($addon, 'syncDescription');

            $this->assertNotSame(
                CWMAddon::class,
                $method->getDeclaringClass()->getName(),
                \sprintf(
                    'Addon "%s" returns true from supportsDescriptionSync() but inherits the base '
                    . 'syncDescription() stub, so the push reports "Not supported by this addon" '
                    . 'after the capability guard has already passed. Implement it or return false.',
                    $type
                )
            );
        }
    }

    /**
     * The capability is opt-in: the base class denies it, so a new addon
     * cannot pick up an offer it has no way to honour.
     *
     * @return  void
     */
    #[Test]
    public function capabilityIsOptInAtTheBaseClass(): void
    {
        $method = new \ReflectionMethod(CWMAddon::class, 'supportsDescriptionSync');

        $this->assertSame(
            CWMAddon::class,
            $method->getDeclaringClass()->getName(),
            'supportsDescriptionSync() must be declared on the base class'
        );

        // Read the body rather than instantiate: CWMAddon is abstract and its
        // constructor resolves addon paths off disk.
        $source = file($method->getFileName());
        $body   = implode('', \array_slice(
            $source,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertDoesNotMatchRegularExpression(
            '/return\s+true/',
            $body,
            'supportsDescriptionSync() must not have any true-returning branch in the base class'
        );
    }

    /**
     * Readiness is opt-in too, and denied by default.
     *
     * Capability says the platform could; readiness says this server is set
     * up to. An addon that has not thought about its own prerequisites must
     * not claim the second, or the drill-down offers a button that fails.
     *
     * @return  void
     */
    #[Test]
    public function readinessIsOptInAtTheBaseClass(): void
    {
        $method = new \ReflectionMethod(CWMAddon::class, 'isDescriptionSyncReady');

        $this->assertSame(
            CWMAddon::class,
            $method->getDeclaringClass()->getName(),
            'isDescriptionSyncReady() must be declared on the base class'
        );

        $source = file($method->getFileName());
        $body   = implode('', \array_slice(
            $source,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));

        $this->assertDoesNotMatchRegularExpression(
            '/return\s+true/',
            $body,
            'isDescriptionSyncReady() must not have any true-returning branch in the base class'
        );
    }

    /**
     * Every capable addon decides readiness for itself.
     *
     * Inheriting the base denial would mean a platform that implements the
     * push can never be offered it -- the mirror image of #1920's first half,
     * where YouTube could sync and was never asked.
     *
     * @return  void
     */
    #[Test]
    public function capableAddonsImplementTheirOwnReadinessCheck(): void
    {
        foreach (self::serverTypeProvider() as [$type, $expected]) {
            if (!$expected) {
                continue;
            }

            $method = new \ReflectionMethod(CWMAddon::getInstance($type), 'isDescriptionSyncReady');

            $this->assertNotSame(
                CWMAddon::class,
                $method->getDeclaringClass()->getName(),
                \sprintf(
                    'Addon "%s" can sync descriptions but inherits the base readiness denial, so it '
                    . 'would never be offered. Override isDescriptionSyncReady() with the same '
                    . 'preconditions syncDescription() enforces.',
                    $type
                )
            );
        }
    }
}
