<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The install script's JW Player param sweep.
 *
 * JW Player is no longer bundled. The stored display settings still carry the
 * jwplayer_* params on every site; the front end read four of them
 * (image/mute/logo/logolink) with a player_* fallback, so those four are
 * carried across before every jwplayer_* key is dropped. The list
 * transformation is pure and is exercised here directly; the surrounding
 * #__bsms_admin read/write is left to the live install path.
 *
 * @since __DEPLOY_VERSION__
 */
class JwplayerParamsTest extends ProclaimTestCase
{
    /**
     * Load the install script class so its private static helpers can be
     * reflected. Besides declaring the class, the file only checks _JEXEC,
     * which this method defines first, so requiring it only declares the class.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!\defined('_JEXEC')) {
            \define('_JEXEC', 1);
        }

        if (!class_exists('com_proclaimInstallerScript', false)) {
            require_once \dirname(__DIR__, 4) . '/proclaim.script.php';
        }
    }

    /**
     * Invoke the private static sweep on the install script.
     *
     * @param   array<string, mixed>  $params  The display-settings params to sweep.
     *
     * @return  array<string, mixed>  The swept params.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    private static function strip(array $params): array
    {
        return (new \ReflectionMethod('com_proclaimInstallerScript', 'stripJwplayerParams'))
            ->invoke(null, $params);
    }

    /**
     * A jwplayer_mute value is copied onto an unset player_mute and its own key
     * removed, while an unrelated key keeps its value.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('A read jwplayer value is carried onto its player_* key when that is unset')]
    public function testCarriesReadValueWhenTargetUnset(): void
    {
        $out = self::strip(['jwplayer_mute' => 'true', 'controls' => '1']);

        $this->assertSame('true', $out['player_mute'], 'The customised value must survive on player_mute.');
        $this->assertArrayNotHasKey('jwplayer_mute', $out);
        $this->assertSame('1', $out['controls'], 'Unrelated keys are untouched.');
    }

    /**
     * An existing player_mute keeps its own value over jwplayer_mute's, and
     * jwplayer_mute is still removed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('An existing player_* value wins over the jwplayer_* value')]
    public function testDoesNotOverwriteExistingTarget(): void
    {
        $out = self::strip(['jwplayer_mute' => 'true', 'player_mute' => 'false']);

        $this->assertSame('false', $out['player_mute'], 'An explicit player_mute must not be clobbered.');
        $this->assertArrayNotHasKey('jwplayer_mute', $out);
    }

    /**
     * The jwplayer_ image, mute, logo and logolink values each land on the
     * matching player_ key.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('All four read keys are carried across')]
    public function testCarriesAllFourReadKeys(): void
    {
        $out = self::strip([
            'jwplayer_image'    => 'poster.png',
            'jwplayer_mute'     => 'true',
            'jwplayer_logo'     => 'logo.png',
            'jwplayer_logolink' => 'https://example.test',
        ]);

        $this->assertSame('poster.png', $out['player_image']);
        $this->assertSame('true', $out['player_mute']);
        $this->assertSame('logo.png', $out['player_logo']);
        $this->assertSame('https://example.test', $out['player_logolink']);
    }

    /**
     * Sweeping five jwplayer_ keys the front end never read leaves no jwplayer_
     * key behind, and the unrelated rtmp key keeps its value.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('Every jwplayer_* key is removed, including the ten the front end never read')]
    public function testRemovesAllJwplayerKeys(): void
    {
        $in = [
            'jwplayer_pro'          => '0',
            'jwplayer_key'          => '',
            'jwplayer_cdn'          => '',
            'jwplayer_advertising'  => '',
            'jwplayer_sitecatalyst' => 'Comming Soon',
            'rtmp'                  => 'Comming Soon',
        ];

        $out = self::strip($in);

        foreach (array_keys($out) as $key) {
            $this->assertStringStartsNotWith('jwplayer_', $key);
        }

        // A key that merely resembled the prefix is not swept, and unrelated
        // keys are left exactly as they were.
        $this->assertSame('Comming Soon', $out['rtmp']);
    }

    /**
     * Sweeping the params a second time returns them exactly as the first sweep
     * left them.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('Running the sweep twice changes nothing the second time')]
    public function testIdempotent(): void
    {
        $once  = self::strip(['jwplayer_mute' => 'true', 'controls' => '1']);
        $twice = self::strip($once);

        $this->assertSame($once, $twice);
    }

    /**
     * Params without a jwplayer_ key come back exactly as they went in.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('Params with no jwplayer_* keys are returned unchanged')]
    public function testNoJwplayerKeysIsUnchanged(): void
    {
        $in = ['controls' => '1', 'podcast' => ['-1'], 'player_mute' => 'false'];

        $this->assertSame($in, self::strip($in));
    }

    /**
     * An empty param array comes back empty.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \ReflectionException
     */
    #[TestDox('An empty param set stays empty')]
    public function testEmptyStaysEmpty(): void
    {
        $this->assertSame([], self::strip([]));
    }
}
