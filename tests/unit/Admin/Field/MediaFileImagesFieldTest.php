<?php

/**
 * Unit tests for MediaFileImagesField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\MediaFileImagesField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;

/**
 * Regression test for #1467: getOptions() built the option value as a JSON
 * blob via raw string concatenation ('{"media_button_text":"' . $text .
 * '"}'), so a media_button_text containing a double quote (or backslash)
 * produced invalid JSON that the client-side consumer can't parse.
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaFileImagesFieldTest extends ProclaimTestCase
{
    private function buildOptionValueJson(Registry $params, string $mediaImage = ''): string
    {
        $field = (new \ReflectionClass(MediaFileImagesField::class))->newInstanceWithoutConstructor();

        return (new \ReflectionMethod(MediaFileImagesField::class, 'buildOptionValueJson'))
            ->invoke($field, $params, $mediaImage);
    }

    public function testButtonTextContainingDoubleQuoteProducesValidJson(): void
    {
        $params = new Registry([
            'media_use_button_icon' => '1',
            'media_button_type'     => 'btn-primary',
            'media_button_text'     => 'Watch "Live"',
            'media_icon_type'       => '',
        ]);

        $json    = $this->buildOptionValueJson($params);
        $decoded = json_decode($json, true);

        $this->assertNotNull($decoded, 'Option value must be valid, parseable JSON even when media_button_text contains a double quote — see #1467');
        $this->assertSame('Watch "Live"', $decoded['media_button_text']);
    }

    public function testMediaImageIsEmbeddedWhenProvided(): void
    {
        $params = new Registry([
            'media_use_button_icon' => '0',
            'media_button_type'     => '',
            'media_button_text'     => '',
            'media_icon_type'       => '',
            'media_image'           => 'images/sermon.jpg',
        ]);

        $json    = $this->buildOptionValueJson($params, 'images/sermon.jpg');
        $decoded = json_decode($json, true);

        $this->assertSame('images/sermon.jpg', $decoded['media_image']);
    }
}
