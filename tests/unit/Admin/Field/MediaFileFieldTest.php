<?php

/**
 * Unit tests for MediaFileField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\MediaFileField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;

/**
 * Test class for MediaFileField's option-label logic.
 *
 * Regression test for #1459: getOptions() built its dropdown label with
 * `filename ? "id - mimetext" : filename` — backwards. When a filename
 * existed, the label showed "id - mimetype" (never the filename); when it
 * didn't, the label was blank.
 *
 * @since  __DEPLOY_VERSION__
 */
class MediaFileFieldTest extends ProclaimTestCase
{
    /**
     * Invoke the field's private static label builder.
     */
    private static function buildOptionLabel(int $id, Registry $params): string
    {
        $reflection = new \ReflectionMethod(MediaFileField::class, 'buildOptionLabel');

        return $reflection->invoke(null, $id, $params);
    }

    public function testLabelShowsFilenameWhenPresent(): void
    {
        $params = new Registry(['filename' => 'sermon.mp3', 'mimetext' => 'audio/mpeg']);

        $this->assertSame('sermon.mp3', self::buildOptionLabel(42, $params));
    }

    public function testLabelFallsBackToIdAndMimetextWhenFilenameMissing(): void
    {
        $params = new Registry(['mimetext' => 'audio/mpeg']);

        $this->assertSame('42 - audio/mpeg', self::buildOptionLabel(42, $params));
    }

    /**
     * Regression test for #1467: getOptions() interpolated an int-cast
     * study id directly into the query string instead of binding it, unlike
     * the equivalent lookup in MediaPlaylistsField.php. Not a security fix
     * (the value was already (int)-cast) -- a consistency fix so this file
     * matches the project's established query-building convention.
     */
    public function testGetOptionsBindsStudyIdInsteadOfInterpolating(): void
    {
        $reflection = new \ReflectionMethod(MediaFileField::class, 'getOptions');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/->bind\(.*ParameterType::INTEGER\)/s',
            $body,
            'getOptions() must bind the study id via ParameterType::INTEGER rather than interpolating it — see #1467'
        );
    }
}
