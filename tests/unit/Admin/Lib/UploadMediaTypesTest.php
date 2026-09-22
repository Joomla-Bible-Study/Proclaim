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
 * The install script's upload-policy normaliser.
 *
 * A migrated site keeps a stored upload allow-list that predates newer formats,
 * so `.jpeg` and `.webp` uploads are rejected while a fresh install accepts
 * them. The postflight repairs the stored value. The list transformation is
 * pure and is exercised here directly; the surrounding DB read/write is left to
 * the live install path.
 *
 * @since __DEPLOY_VERSION__
 */
class UploadMediaTypesTest extends ProclaimTestCase
{
    /**
     * Load the install script class so its private static helpers can be
     * reflected. The file is a plain named-class definition with no top-level
     * side effects, so requiring it only declares the class.
     *
     * @return  void
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
     * Invoke a private static helper on the install script.
     *
     * @param   string  $method
     * @param   mixed   ...$args
     *
     * @return  mixed
     */
    private static function call(string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionMethod('com_proclaimInstallerScript', $method);

        return $ref->invoke(null, ...$args);
    }

    #[TestDox('A list that allows jpg gains jpeg, webp and avif')]
    public function testJpgListGainsJpegWebpAvif(): void
    {
        $out = self::call('augmentImageExtensions', ['jpg', 'png', 'gif']);

        $this->assertContains('jpeg', $out, 'jpg and jpeg are one format.');
        $this->assertContains('webp', $out);
        $this->assertContains('avif', $out);
        $this->assertContains('jpg', $out, 'Nothing is removed.');
        $this->assertContains('png', $out);
    }

    #[TestDox('An upper-case-only list still gains the usable lower-case tokens')]
    public function testUpperCaseOnlyListGainsLowerCaseTokens(): void
    {
        // canUpload matches the lower-cased file extension, so 'JPEG' alone
        // matches nothing — the repair must add lower-case 'jpeg'.
        $out = self::call('augmentImageExtensions', ['JPG', 'JPEG', 'PNG']);

        $this->assertContains('jpg', $out);
        $this->assertContains('jpeg', $out);
        $this->assertContains('webp', $out);
    }

    #[TestDox('An audio/video-only list is left untouched')]
    public function testNonImageListUntouched(): void
    {
        $in  = ['mp3', 'mp4', 'mov'];
        $out = self::call('augmentImageExtensions', $in);

        $this->assertSame($in, $out, 'Image formats must not be forced onto a non-image policy.');
    }

    #[TestDox('An empty list is left empty so the built-in default applies')]
    public function testEmptyListStaysEmpty(): void
    {
        $this->assertSame([], self::call('augmentImageExtensions', []));
        $this->assertSame([], self::call('augmentImageMimes', []));
    }

    #[TestDox('A complete list is returned unchanged (idempotent)')]
    public function testCompleteListIsIdempotent(): void
    {
        $in  = ['bmp', 'gif', 'jpg', 'jpeg', 'png', 'webp', 'avif'];
        $this->assertSame($in, self::call('augmentImageExtensions', $in));

        $mimes = ['image/jpeg', 'image/webp', 'image/avif', 'audio/mpeg'];
        $this->assertSame($mimes, self::call('augmentImageMimes', $mimes));
    }

    #[TestDox('The MIME list gains the image types that match the extensions')]
    public function testMimeListGainsImageTypes(): void
    {
        $out = self::call('augmentImageMimes', ['image/jpeg', 'audio/mpeg', 'video/mp4']);

        $this->assertContains('image/webp', $out);
        $this->assertContains('image/avif', $out);
        $this->assertContains('audio/mpeg', $out, 'Existing media MIMEs are preserved.');
    }
}
