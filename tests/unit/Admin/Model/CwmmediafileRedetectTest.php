<?php

/**
 * Tests for media metadata re-detection on save.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Detection is gated on the filename or server having changed, so clearing a
 * detected field and saving used to do nothing — the obvious way to ask for a
 * value to be worked out again was silently swallowed. metadataWasCleared()
 * reopens that path for the clearing save only, so a platform that can never
 * supply a value (YouTube has no file size) does not re-hit its API forever.
 *
 * @since __DEPLOY_VERSION__
 */
class CwmmediafileRedetectTest extends ProclaimTestCase
{
    /**
     * Old params, new params, and whether a re-detect should be triggered.
     *
     * @return  array<string, array{0: array, 1: array, 2: bool}>
     */
    public static function clearedProvider(): array
    {
        return [
            'size cleared to blank' => [
                ['size' => '52428800'],
                ['size' => ''],
                true,
            ],
            'size zeroed' => [
                ['size' => '52428800'],
                ['size' => '0'],
                true,
            ],
            'mime cleared' => [
                ['mime_type' => 'audio/mpeg'],
                ['mime_type' => ''],
                true,
            ],
            'duration cleared' => [
                ['media_hours' => '00', 'media_minutes' => '27', 'media_seconds' => '08'],
                ['media_hours' => '', 'media_minutes' => '', 'media_seconds' => ''],
                true,
            ],
            // A value that was never set must not re-trigger on every save —
            // this is the YouTube-has-no-filesize case.
            'size absent before and after' => [
                ['size' => ''],
                ['size' => ''],
                false,
            ],
            'size zero before and after' => [
                ['size' => '0'],
                ['size' => '0'],
                false,
            ],
            'nothing changed' => [
                ['size' => '52428800', 'mime_type' => 'audio/mpeg'],
                ['size' => '52428800', 'mime_type' => 'audio/mpeg'],
                false,
            ],
            'value edited, not cleared' => [
                ['size' => '52428800'],
                ['size' => '12345678'],
                false,
            ],
            // '00' is the stored form of "no duration", so it is already unset.
            'duration was already zero' => [
                ['media_hours' => '00', 'media_minutes' => '00', 'media_seconds' => '00'],
                ['media_hours' => '', 'media_minutes' => '', 'media_seconds' => ''],
                false,
            ],
            // mime_type is a string: (int) 'audio/mpeg' is 0, so it must not be
            // treated numerically or every save would look like a clear.
            'mime unchanged is not a clear' => [
                ['mime_type' => 'audio/mpeg'],
                ['mime_type' => 'audio/mpeg'],
                false,
            ],
        ];
    }

    /**
     * Only a populated → empty transition asks for re-detection.
     *
     * @param   array  $old       Params as stored before the save.
     * @param   array  $new       Params being saved.
     * @param   bool   $expected  Whether detection should re-run.
     *
     * @return  void
     */
    #[DataProvider('clearedProvider')]
    public function testClearingADetectedFieldRequestsRedetection(array $old, array $new, bool $expected): void
    {
        $ref = new \ReflectionMethod(CwmmediafileModel::class, 'metadataWasCleared');

        $model = (new \ReflectionClass(CwmmediafileModel::class))->newInstanceWithoutConstructor();

        $this->assertSame(
            $expected,
            $ref->invoke($model, new Registry($old), new Registry($new))
        );
    }
}
