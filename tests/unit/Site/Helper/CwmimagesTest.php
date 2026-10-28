<?php

/**
 * Unit tests for Cwmimages::renderPicture()
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for the picture element renderer
 *
 * The alt text comes straight from joined database columns, and a LEFT JOIN with
 * nothing on the other side yields NULL. A message with no teacher is a supported
 * configuration (the teachers field allows none), so the renderer has to take a
 * missing name rather than fatal on a public page.
 *
 * @since  10.7.3
 */
class CwmimagesTest extends ProclaimTestCase
{
    /**
     * An image object shaped like the ones getImagePath() returns
     *
     * @param   string  $webp  WebP variant path, or empty for none
     *
     * @return object
     *
     * @since  10.7.3
     */
    private function image(string $webp = ''): object
    {
        return (object) [
            'path'      => 'images/biblestudy/teachers/lead.jpg',
            'webp_path' => $webp,
            'width'     => 120,
            'height'    => 80,
        ];
    }

    /**
     * A missing alt (NULL from a join with no row) renders as an empty alt
     *
     * @return void
     *
     * @since  10.7.3
     */
    public function testNullAltRendersAsEmptyAlt(): void
    {
        $html = Cwmimages::renderPicture($this->image(), null);

        $this->assertStringContainsString(' alt=""', $html);
        $this->assertStringContainsString('images/biblestudy/teachers/lead.jpg', $html);
    }

    /**
     * Every call site that hands a possibly-NULL joined column straight through is covered
     *
     * @return void
     *
     * @since  10.7.3
     */
    public function testNullAltIsAcceptedWithAWebpVariantToo(): void
    {
        $html = Cwmimages::renderPicture($this->image('images/biblestudy/teachers/lead.webp'), null, 'card-img-top');

        $this->assertStringContainsString('<picture>', $html);
        $this->assertStringContainsString(' alt=""', $html);
        $this->assertStringContainsString('class="card-img-top"', $html);
    }

    /**
     * A real name is used as the alt text, and escaped
     *
     * @return void
     *
     * @since  10.7.3
     */
    public function testAltTextIsEscaped(): void
    {
        $html = Cwmimages::renderPicture($this->image(), 'Tom & "Jerry" <b>');

        $this->assertStringContainsString(' alt="Tom &amp; &quot;Jerry&quot; &lt;b&gt;"', $html);
    }

    /**
     * Omitting the alt keeps working and gives an empty one
     *
     * @return void
     *
     * @since  10.7.3
     */
    public function testOmittedAltRendersAsEmptyAlt(): void
    {
        $this->assertStringContainsString(' alt=""', Cwmimages::renderPicture($this->image()));
    }

    /**
     * An image with no path renders nothing, whatever the alt
     *
     * @return void
     *
     * @since  10.7.3
     */
    public function testNoPathRendersNothing(): void
    {
        $this->assertSame('', Cwmimages::renderPicture((object) ['path' => ''], null));
    }

    /**
     * A file with a space in its name, saved by the media field as %20, is
     * found on disk and keeps its stored URL as the output path.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetImagePathFindsFileStoredWithEncodedSpace(): void
    {
        $dir = 'tests/tmp-cwmimages-' . bin2hex(random_bytes(4));
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        mkdir(JPATH_ROOT . '/' . $dir, 0755, true);
        file_put_contents(JPATH_ROOT . '/' . $dir . '/my photo.png', $png);
        file_put_contents(JPATH_ROOT . '/' . $dir . '/my photo.webp', $png);

        try {
            // Guard: the file must exist where getImagePath() looks, or a null path proves nothing
            $this->assertFileExists(JPATH_ROOT . DIRECTORY_SEPARATOR . $dir . '/my photo.png');

            $image = Cwmimages::getImagePath(
                $dir . '/my%20photo.png#joomlaImage://local-images/my photo.png?width=1&height=1'
            );

            $this->assertSame($dir . '/my%20photo.png', $image->path);
            $this->assertSame(1, $image->width);
            $this->assertGreaterThan(0, $image->size);
            $this->assertSame($dir . '/my%20photo.webp', $image->webp_path);
        } finally {
            array_map('unlink', glob(JPATH_ROOT . '/' . $dir . '/*'));
            rmdir(JPATH_ROOT . '/' . $dir);
        }
    }
}
