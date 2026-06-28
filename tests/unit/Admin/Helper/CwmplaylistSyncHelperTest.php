<?php

/**
 * Unit tests for CwmplaylistSyncHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\CWMAddonYoutube;
use CWM\Component\Proclaim\Administrator\Helper\CwmplaylistSyncHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the playlist-sync reconciliation core.
 *
 * extractVideoMapFromRows() is the dependency-free matching index that lets bulk
 * import link a YouTube video to media we already have instead of duplicating
 * it. These tests pin its handling of the various ways a YouTube URL is stored.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmplaylistSyncHelper::class)]
class CwmplaylistSyncHelperTest extends ProclaimTestCase
{
    /**
     * It maps the stored URL forms (youtu.be, watch?v=, embed, live, bare id)
     * to their media-file IDs.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExtractVideoMapHandlesAllUrlForms(): void
    {
        $rows = [
            ['id' => 10, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs'])],
            ['id' => 11, 'params' => json_encode(['filename' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'])],
            ['id' => 12, 'params' => json_encode(['filename' => 'https://www.youtube.com/embed/AbCdEfGhIjK'])],
            ['id' => 13, 'params' => json_encode(['filename' => 'https://www.youtube.com/live/LiVeStReAm1'])],
            ['id' => 14, 'params' => json_encode(['filename' => 'JustABareId'])],
        ];

        $map = CwmplaylistSyncHelper::extractVideoMapFromRows($rows, [CWMAddonYoutube::class, 'extractMediaId']);

        $this->assertSame(10, $map['cXhKlo2nxPs']);
        $this->assertSame(11, $map['dQw4w9WgXcQ']);
        $this->assertSame(12, $map['AbCdEfGhIjK']);
        $this->assertSame(13, $map['LiVeStReAm1']);
        $this->assertSame(14, $map['JustABareId']);
    }

    /**
     * It skips rows that cannot yield a video ID and keeps the first match when
     * the same video appears on more than one media file.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExtractVideoMapSkipsJunkAndKeepsFirstMatch(): void
    {
        $rows = [
            ['id' => 1, 'params' => ''],
            ['id' => 2, 'params' => 'not-json'],
            ['id' => 3, 'params' => json_encode(['link_type' => '0'])], // no filename
            ['id' => 4, 'params' => json_encode(['filename' => 'https://example.com/audio.mp3'])], // not YouTube
            ['id' => 5, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs'])],
            ['id' => 6, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs'])], // dup -> ignored
        ];

        $map = CwmplaylistSyncHelper::extractVideoMapFromRows($rows, [CWMAddonYoutube::class, 'extractMediaId']);

        $this->assertCount(1, $map);
        $this->assertSame(5, $map['cXhKlo2nxPs']);
        $this->assertArrayNotHasKey('', $map);
    }

    /**
     * An empty row set yields an empty map.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExtractVideoMapEmpty(): void
    {
        $this->assertSame([], CwmplaylistSyncHelper::extractVideoMapFromRows([], [CWMAddonYoutube::class, 'extractMediaId']));
    }

    /**
     * Matching titles need no action regardless of edit/write-back state.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTitlePushDecisionNoneWhenTitlesMatch(): void
    {
        $this->assertSame('none', CwmplaylistSyncHelper::titlePushDecision('Same', 'Same', false, false));
        $this->assertSame('none', CwmplaylistSyncHelper::titlePushDecision('Same', 'Same', true, true));
    }

    /**
     * A remote change with no local edit is pulled in, write-back irrelevant.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTitlePushDecisionPullsRemoteWhenNotLocallyEdited(): void
    {
        $this->assertSame('pull', CwmplaylistSyncHelper::titlePushDecision('Local', 'Remote', false, false));
        $this->assertSame('pull', CwmplaylistSyncHelper::titlePushDecision('Local', 'Remote', false, true));
    }

    /**
     * A locally-edited divergence pushes when write-back is on.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTitlePushDecisionPushesWhenLocallyEditedAndWritebackOn(): void
    {
        $this->assertSame('push', CwmplaylistSyncHelper::titlePushDecision('Local', 'Remote', true, true));
    }

    /**
     * A locally-edited divergence is a conflict (keep local) when write-back is off.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTitlePushDecisionConflictWhenLocallyEditedAndWritebackOff(): void
    {
        $this->assertSame('conflict', CwmplaylistSyncHelper::titlePushDecision('Local', 'Remote', true, false));
    }
}
