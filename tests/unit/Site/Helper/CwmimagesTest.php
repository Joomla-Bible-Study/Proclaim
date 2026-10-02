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
}
