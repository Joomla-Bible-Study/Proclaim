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

    /**
     * fieldPushDecision is field-agnostic: the same gate drives title and
     * description write-back. Covers all four outcomes for a description-shaped
     * value (multi-line text) so phase 6.3 shares the title gate's guarantees.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFieldPushDecisionCoversAllOutcomes(): void
    {
        $local  = "Local desc\nline two";
        $remote = "Remote desc\nline two";

        // Identical values → nothing to do, regardless of edit/write-back state.
        $this->assertSame('none', CwmplaylistSyncHelper::fieldPushDecision($local, $local, false, false));
        $this->assertSame('none', CwmplaylistSyncHelper::fieldPushDecision($local, $local, true, true));

        // Diverged, not locally edited → pull remote in (write-back irrelevant).
        $this->assertSame('pull', CwmplaylistSyncHelper::fieldPushDecision($local, $remote, false, false));
        $this->assertSame('pull', CwmplaylistSyncHelper::fieldPushDecision($local, $remote, false, true));

        // Diverged + locally edited → push when write-back on, conflict when off.
        $this->assertSame('push', CwmplaylistSyncHelper::fieldPushDecision($local, $remote, true, true));
        $this->assertSame('conflict', CwmplaylistSyncHelper::fieldPushDecision($local, $remote, true, false));
    }

    /**
     * titlePushDecision must stay a faithful alias of fieldPushDecision so the
     * shipped phase-6.1 behaviour is preserved after generalisation.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTitlePushDecisionDelegatesToFieldPushDecision(): void
    {
        foreach ([['A', 'A'], ['A', 'B']] as [$l, $r]) {
            foreach ([true, false] as $edited) {
                foreach ([true, false] as $writeback) {
                    $this->assertSame(
                        CwmplaylistSyncHelper::fieldPushDecision($l, $r, $edited, $writeback),
                        CwmplaylistSyncHelper::titlePushDecision($l, $r, $edited, $writeback)
                    );
                }
            }
        }
    }

    /**
     * membershipsToPush returns only videos that resolve to an ID and are not
     * already members, deduped, carrying mediafile id + title.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMembershipsToPushPlansOnlyMissingVideos(): void
    {
        $rows = [
            ['id' => 10, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs']), 'title' => 'Sermon A'],
            ['id' => 11, 'params' => json_encode(['filename' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']), 'title' => 'Sermon B'],
            ['id' => 12, 'params' => json_encode(['filename' => 'https://www.youtube.com/embed/AbCdEfGhIjK']), 'title' => 'Sermon C'],
        ];

        // dQw4w9WgXcQ is already a member, so only A and C should be planned.
        $plan = CwmplaylistSyncHelper::membershipsToPush($rows, [CWMAddonYoutube::class, 'extractMediaId'], ['dQw4w9WgXcQ']);

        $this->assertCount(2, $plan);
        $this->assertSame(['mediafileId' => 10, 'videoId' => 'cXhKlo2nxPs', 'title' => 'Sermon A'], $plan[0]);
        $this->assertSame(['mediafileId' => 12, 'videoId' => 'AbCdEfGhIjK', 'title' => 'Sermon C'], $plan[1]);
    }

    /**
     * membershipsToPush skips junk rows and dedups repeats within the candidate set.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMembershipsToPushSkipsJunkAndDedups(): void
    {
        $rows = [
            ['id' => 1, 'params' => ''],
            ['id' => 2, 'params' => 'not-json'],
            ['id' => 3, 'params' => json_encode(['link_type' => '0'])],
            ['id' => 4, 'params' => json_encode(['filename' => 'https://example.com/audio.mp3'])],
            ['id' => 5, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs']), 'title' => 'First'],
            ['id' => 6, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs']), 'title' => 'Dup'],
        ];

        $plan = CwmplaylistSyncHelper::membershipsToPush($rows, [CWMAddonYoutube::class, 'extractMediaId'], []);

        $this->assertCount(1, $plan);
        $this->assertSame(5, $plan[0]['mediafileId']);
        $this->assertSame('cXhKlo2nxPs', $plan[0]['videoId']);
    }

    /**
     * Everything already a member yields an empty plan.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMembershipsToPushEmptyWhenAllPresent(): void
    {
        $rows = [
            ['id' => 5, 'params' => json_encode(['filename' => 'https://youtu.be/cXhKlo2nxPs'])],
        ];

        $this->assertSame([], CwmplaylistSyncHelper::membershipsToPush($rows, [CWMAddonYoutube::class, 'extractMediaId'], ['cXhKlo2nxPs']));
    }

    /**
     * planPlaylistAssignments diffs current vs desired into add/remove lists
     * (phase 6.2b), the pure core of the media-file playlist reconcile.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPlanPlaylistAssignmentsDiffsCurrentAndDesired(): void
    {
        $plan = CwmplaylistSyncHelper::planPlaylistAssignments([1, 2, 3], [2, 3, 4]);

        $this->assertSame([4], $plan['add']);
        $this->assertSame([1], $plan['remove']);
    }

    /**
     * An empty desired set removes every current membership; an empty current set
     * adds every desired one.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPlanPlaylistAssignmentsHandlesEmptySides(): void
    {
        $cleared = CwmplaylistSyncHelper::planPlaylistAssignments([1, 2], []);
        $this->assertSame([], $cleared['add']);
        $this->assertSame([1, 2], $cleared['remove']);

        $fresh = CwmplaylistSyncHelper::planPlaylistAssignments([], [7, 8]);
        $this->assertSame([7, 8], $fresh['add']);
        $this->assertSame([], $fresh['remove']);
    }

    /**
     * Desired IDs are cast to int, de-duplicated, and non-positive/junk values
     * dropped — a form post is untrusted input.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPlanPlaylistAssignmentsSanitisesDesiredIds(): void
    {
        $plan = CwmplaylistSyncHelper::planPlaylistAssignments([5], ['5', '9', '9', 0, -1, 'x']);

        // '5' already present (no-op), '9' added once, junk/zero/negative ignored.
        $this->assertSame([9], $plan['add']);
        $this->assertSame([], $plan['remove']);
    }
}
