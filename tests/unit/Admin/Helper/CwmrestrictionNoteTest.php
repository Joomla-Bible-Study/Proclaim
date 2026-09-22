<?php

/**
 * Unit tests for CwmrestrictionNote
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmrestrictionNote;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The naming rule behind the restricted list's row explanations.
 *
 * The case that motivated it is the one that must never regress: a media file
 * whose own level is Public under a message set to Special. The old list
 * showed such a row with nothing to say, because the restriction is inherited
 * and nothing named the inheriting link.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmrestrictionNote::class)]
class CwmrestrictionNoteTest extends ProclaimTestCase
{
    /**
     * A visitor holding Public (1) and the site-default Guest level (5).
     */
    private const VISITOR = [1, 5];

    #[TestDox('A fully public chain restricts nothing')]
    public function testPublicChainIsUnrestricted(): void
    {
        // ⚠️ Positive control: if the baseline chain were reported restricted,
        // every naming assertion below would pass while the list annotated
        // all 1,600 public rows with nonsense.
        $this->assertSame([], CwmrestrictionNote::restrictedBy(1, 1, 1, self::VISITOR));
    }

    #[TestDox('The motivating case: a Public file under a Special message names the message')]
    public function testInheritedRestrictionNamesTheMessage(): void
    {
        $this->assertSame(
            [['member' => 'message', 'level' => 3]],
            CwmrestrictionNote::restrictedBy(1, 3, null, self::VISITOR),
            'The media reads Public and the message is what refuses; the message must be the one named.'
        );
    }

    #[TestDox('Each link is named independently, and only the refusing ones')]
    public function testEveryCombination(): void
    {
        $this->assertSame(
            [['member' => 'media', 'level' => 2]],
            CwmrestrictionNote::restrictedBy(2, 1, 1, self::VISITOR)
        );
        $this->assertSame(
            [['member' => 'series', 'level' => 6]],
            CwmrestrictionNote::restrictedBy(1, 1, 6, self::VISITOR)
        );
        $this->assertSame(
            [
                ['member' => 'media', 'level' => 2],
                ['member' => 'message', 'level' => 3],
                ['member' => 'series', 'level' => 6],
            ],
            CwmrestrictionNote::restrictedBy(2, 3, 6, self::VISITOR),
            'All three refuse; all three are named, in chain order.'
        );
    }

    #[TestDox('An absent link constrains nothing')]
    public function testNullLinksAreSkipped(): void
    {
        // A media file with no message, or a message in no series — matching
        // Cwmdownload::isAccessible(), where null means absent, not level 0.
        $this->assertSame([], CwmrestrictionNote::restrictedBy(1, null, null, self::VISITOR));
        $this->assertSame(
            [['member' => 'media', 'level' => 4]],
            CwmrestrictionNote::restrictedBy(4, null, null, self::VISITOR)
        );
    }

    #[TestDox('A visitor holding the level is not refused by it')]
    public function testHeldLevelsSatisfy(): void
    {
        $this->assertSame([], CwmrestrictionNote::restrictedBy(3, 3, 3, [1, 3]));
    }
}
