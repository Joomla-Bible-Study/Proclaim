<?php

/**
 * Tests for the podcast validation report's artwork check
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The validation report checks podcast artwork as it is stored by the media
 * field: a URL with a #joomlaImage:// fragment and percent-escapes, not a bare
 * file name.
 *
 * @since 10.7.8
 */
class CwmpodcastImageValidationTest extends ProclaimTestCase
{
    /**
     * A directory under JPATH_ROOT for this test's files.
     *
     * @var    string
     * @since  10.7.8
     */
    private string $dir;

    /**
     * The language Text used before this test swapped in its own.
     *
     * @var    mixed
     * @since  10.7.8
     */
    private mixed $previousLanguage = null;

    /**
     * @return  void
     * @since   10.7.8
     */
    protected function setUp(): void
    {
        parent::setUp();

        // The report's messages are translated; untranslated, they are bare keys
        // that carry no path. A language of its own keeps the strings out of
        // the tests that run after this one.
        $this->previousLanguage = Factory::$language;
        $language               = new Language('en-GB');
        $language->load('com_proclaim', \dirname(__DIR__, 4) . '/admin', 'en-GB', true);
        Factory::$language = $language;

        $this->dir = 'tests/tmp-podcast-art-' . bin2hex(random_bytes(4));
        mkdir(JPATH_ROOT . '/' . $this->dir, 0755, true);
    }

    /**
     * @return  void
     * @since   10.7.8
     */
    protected function tearDown(): void
    {
        array_map('unlink', glob(JPATH_ROOT . '/' . $this->dir . '/*'));
        rmdir(JPATH_ROOT . '/' . $this->dir);

        Factory::$language = $this->previousLanguage;

        parent::tearDown();
    }

    /**
     * @return  void
     * @since   10.7.8
     */
    #[TestDox('Artwork picked with the media field is found and measured')]
    public function testMediaFieldArtworkIsFoundAndMeasured(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        file_put_contents(JPATH_ROOT . '/' . $this->dir . '/podcast art.png', $png);

        // Guard: the file must exist where the check looks, or a miss proves nothing
        $this->assertFileExists(JPATH_ROOT . DIRECTORY_SEPARATOR . $this->dir . '/podcast art.png');

        $result = $this->validate(
            $this->dir . '/podcast%20art.png#joomlaImage://local-images/podcast art.png?width=1&height=1'
        );

        // Measured means it was found: a missing file returns before the dimensions.
        $this->assertSame(['width' => 1, 'height' => 1], $result['dimensions'] ?? null);
        $this->assertSame([Text::sprintf('JBS_PDC_VALIDATE_IMAGE_TOO_SMALL', 1, 1)], $result['errors']);
    }

    /**
     * @return  void
     * @since   10.7.8
     */
    #[TestDox('Missing artwork is reported by its path, without the media field fragment')]
    public function testMissingArtworkIsReportedByItsPath(): void
    {
        $result   = $this->validate($this->dir . '/missing.png#joomlaImage://local-images/missing.png?width=1&height=1');
        $expected = Text::sprintf('JBS_PDC_VALIDATE_IMAGE_NOT_FOUND', $this->dir . '/missing.png');

        // Guard: a bare key carries no path, and the comparison would check nothing
        $this->assertStringContainsString($this->dir . '/missing.png', $expected);

        $this->assertFalse($result['valid']);
        $this->assertSame([$expected], $result['errors']);
    }

    /**
     * @param   string  $value  The stored podcastimage value
     *
     * @return  array  The artwork check's result
     *
     * @since   10.7.8
     */
    private function validate(string $value): array
    {
        $ref = new \ReflectionMethod(Cwmpodcast::class, 'validatePodcastImage');

        return $ref->invoke(new Cwmpodcast(), $value);
    }
}
