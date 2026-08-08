<?php

/**
 * Integration tests for the batch widget helpers.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Field\MediaFileImagesField;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhtml;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1589.
 *
 * teacherList(), messageTypeList() and seriesList() initialised $options to
 * null, assigned it only inside the try, and had no return in the catch -- so a
 * failed query returned null. That null goes straight into
 * HTMLHelper::_('select.options', ...), which foreach()es it with no null
 * guard, so the batch dialog's markup got a TypeError warning written into the
 * middle of it wherever display_errors is on. locationList() already returned
 * [] on the same path.
 *
 * Six widgets also shared id="batch-client-lbl". Several appear on the same
 * page -- the Messages batch body renders teacher(), series() and
 * messageType() together -- which is invalid HTML, a duplicate-id
 * accessibility failure, and makes any getElementById() lookup ambiguous.
 * location(), added later, already used a unique id.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmhtml::class)]
class CwmhtmlBatchWidgetTest extends IntegrationTestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function listMethodProvider(): array
    {
        return [
            'teacherList'     => ['teacherList'],
            'messageTypeList' => ['messageTypeList'],
            'seriesList'      => ['seriesList'],
            'locationList'    => ['locationList'],
        ];
    }

    /**
     * @param   string  $method  List method name
     */
    #[DataProvider('listMethodProvider')]
    #[TestDox('A list method always returns an array, never null')]
    public function testListMethodsReturnArrays(string $method): void
    {
        $this->assertIsArray(
            Cwmhtml::$method(),
            $method . '() returning null corrupts the batch dialog markup — see #1589'
        );
    }

    /**
     * The declared type is the contract callers rely on; leaving it nullable
     * invites the same bug back.
     *
     * @param   string  $method  List method name
     */
    #[DataProvider('listMethodProvider')]
    #[TestDox('A list method does not declare a nullable return')]
    public function testListMethodsAreNotNullable(string $method): void
    {
        $type = (new \ReflectionMethod(Cwmhtml::class, $method))->getReturnType();

        $this->assertNotNull($type, $method . '() must declare a return type');
        $this->assertSame('array', (string) $type, $method . '() must return array, not ?array — see #1589');
    }

    // -------------------------------------------------------------------------
    // Duplicate label ids
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{0: string}>
     */
    public static function widgetProvider(): array
    {
        return [
            'linkType'    => ['linkType'],
            'popup'       => ['popup'],
            'mediaType'   => ['mediaType'],
            'teacher'     => ['teacher'],
            'messageType' => ['messageType'],
            'series'      => ['series'],
            'location'    => ['location'],
        ];
    }

    /**
     * @param   string  $widget  Widget method name
     */
    #[DataProvider('widgetProvider')]
    #[TestDox('A batch widget carries its own label id')]
    public function testWidgetLabelIdIsUnique(string $widget): void
    {
        $html = Cwmhtml::$widget();

        $this->assertStringNotContainsString(
            'id="batch-client-lbl"',
            $html,
            $widget . '() shared one id with five other widgets — see #1589'
        );
        $this->assertStringContainsString(
            'id="batch-' . $widget . '-lbl"',
            $html,
            $widget . '() must label itself distinctly'
        );
    }

    /**
     * The failure this prevents: several of these render into one page.
     */
    #[TestDox('Widgets rendered together produce no duplicate ids')]
    public function testWidgetsCombineWithoutDuplicateIds(): void
    {
        // What admin/tmpl/cwmmessages/default_batch_body.php combines.
        $html = Cwmhtml::teacher() . Cwmhtml::series() . Cwmhtml::messageType();

        preg_match_all('/id="([^"]+)"/', $html, $matches);

        $ids = $matches[1];

        $this->assertSame(
            \count($ids),
            \count(array_unique($ids)),
            'Duplicate ids on one page are invalid HTML and fail the accessibility scan — see #1589: '
            . implode(', ', array_diff_assoc($ids, array_unique($ids)))
        );
    }

    // -------------------------------------------------------------------------
    // The media-type widget offers whole display configurations
    // -------------------------------------------------------------------------

    /**
     * This widget was deliberately left empty while batchMediatype() wrote an
     * integer into media_image, which holds an image path -- offering options
     * would have exposed a one-click way to corrupt every selected file. The
     * handler now applies a whole display configuration, so the widget carries
     * the same choices as the Image Tools screen. See #1627.
     */
    #[TestDox('The media-type widget offers display configurations, each a complete parameter set')]
    public function testMediaTypeWidgetOffersDisplayConfigurations(): void
    {
        $html = Cwmhtml::mediaType();

        preg_match_all('/<option value="([^"]*)"/', $html, $matches);

        $this->assertGreaterThan(
            1,
            \count($matches[1]),
            'The widget must offer more than the "No change" sentinel now the handler is fixed'
        );

        // The first option is the sentinel; every other value must decode to a
        // configuration carrying each display key, or applying it would leave a
        // media file in a state the chosen label does not describe.
        foreach (\array_slice($matches[1], 1) as $value) {
            $decoded = json_decode(html_entity_decode($value, ENT_QUOTES), true);

            $this->assertIsArray($decoded, 'Each option value must be a display configuration');

            foreach (MediaFileImagesField::DISPLAY_KEYS as $key) {
                $this->assertArrayHasKey($key, $decoded, $key . ' missing from an option the batch can apply');
            }
        }
    }

    /**
     * A count is appended to the Image Tools labels to show how many files share
     * a configuration. In a batch modal that number describes the whole site
     * rather than the rows the administrator picked, so it is left off there.
     */
    #[TestDox('The batch widget omits the site-wide usage counts the Image Tools field shows')]
    public function testBatchWidgetOmitsSiteWideCounts(): void
    {
        foreach (MediaFileImagesField::buildDisplayOptions(false) as $option) {
            $this->assertDoesNotMatchRegularExpression(
                '/\(\d+\)$/',
                $option->text,
                'A site-wide count next to a selection of rows misleads: ' . $option->text
            );
        }
    }
}
