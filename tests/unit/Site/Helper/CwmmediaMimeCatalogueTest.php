<?php

/**
 * Tests that the MIME catalogue offered to administrators agrees with the
 * canonical detection map.
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmmime;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Two MIME maps exist for different jobs: Cwmmime::MAP detects a type from a
 * file, Cwmmedia::getMimetypes() is the catalogue an administrator picks from.
 * They drifted apart, and the dropdown held the wrong values — 'audio/mp3',
 * which is not IANA-registered, was the only MP3 option offered, which is how
 * production feeds came to declare it on every episode (#1391, #1397).
 *
 * Detection is the authority. Where both maps know an extension they must
 * agree, or the dropdown teaches administrators to store something the feed
 * will then override.
 *
 * @since __DEPLOY_VERSION__
 */
class CwmmediaMimeCatalogueTest extends ProclaimTestCase
{
    /**
     * Expand the catalogue's pipe-keyed groups into one entry per extension.
     *
     * @return  array<string, string>  extension => MIME type
     */
    private function catalogueByExtension(): array
    {
        $flat = [];

        foreach ((new Cwmmedia())->getMimetypes() as $extensions => $mime) {
            foreach (explode('|', $extensions) as $extension) {
                $flat[strtolower(trim($extension))] = $mime;
            }
        }

        return $flat;
    }

    /**
     * Read Cwmmime's private canonical map.
     *
     * @return  array<string, string>
     */
    private function detectionMap(): array
    {
        $constants = (new \ReflectionClass(Cwmmime::class))->getConstants();

        return $constants['MAP'];
    }

    /**
     * Every extension both maps know must resolve to the same type.
     *
     * @return  void
     */
    public function testCatalogueAgreesWithDetectionOnSharedExtensions(): void
    {
        $catalogue = $this->catalogueByExtension();
        $detection = $this->detectionMap();
        $shared    = array_intersect_key($detection, $catalogue);

        $this->assertNotEmpty($shared, 'The two maps should overlap; check the expansion logic');

        foreach ($shared as $extension => $detected) {
            $this->assertSame(
                $detected,
                $catalogue[$extension],
                \sprintf(
                    'Extension "%s": the picker offers %s but detection resolves %s',
                    $extension,
                    $catalogue[$extension],
                    $detected
                )
            );
        }
    }

    /**
     * The specific values that were wrong, pinned by name so a regression is
     * unambiguous rather than buried in the sweep above.
     *
     * @return  void
     */
    public function testMp3AndM4aUseRegisteredTypes(): void
    {
        $catalogue = $this->catalogueByExtension();

        $this->assertSame('audio/mpeg', $catalogue['mp3'], 'audio/mp3 is not IANA-registered');
        $this->assertSame('audio/mp4', $catalogue['m4a'], 'M4A is MPEG-4 audio, not MPEG audio');
        $this->assertSame('audio/mp4', $catalogue['m4b']);
    }

    /**
     * No unregistered type should be selectable at all.
     *
     * @return  void
     */
    public function testUnregisteredTypesAreNotOffered(): void
    {
        $offered = array_values($this->catalogueByExtension());

        foreach (['audio/mp3', 'audio/mpeg3', 'audio/x-mp3'] as $bogus) {
            $this->assertNotContains($bogus, $offered, \sprintf('%s is not a registered media type', $bogus));
        }
    }
}
