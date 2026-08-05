<?php

/**
 * Unit tests for Cwmrelatedstudies Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmrelatedstudies;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmrelatedstudies helper
 *
 * @since  10.1.0
 */
class CwmrelatedstudiesTest extends ProclaimTestCase
{
    /**
     * Test addScore accumulates points correctly
     *
     * @return void
     */
    public function testAddScoreAccumulates(): void
    {
        $helper = new Cwmrelatedstudies();

        $helper->addScore(1, 3);
        $helper->addScore(2, 2);
        $helper->addScore(1, 2);

        $this->assertSame(5, $helper->scores[1]);
        $this->assertSame(2, $helper->scores[2]);
    }

    /**
     * Test addScore initializes at zero
     *
     * @return void
     */
    public function testAddScoreInitializesAtZero(): void
    {
        $helper = new Cwmrelatedstudies();

        $this->assertEmpty($helper->scores);

        $helper->addScore(10, 1);

        $this->assertSame(1, $helper->scores[10]);
    }

    /**
     * The production ranking sort lives inside buildCards()
     * (site/src/Helper/Cwmrelatedstudies.php), which needs a DB to run.
     * A prior version of this test called arsort() on the scores itself
     * and asserted the result -- which only verified PHP's arsort(), and
     * stayed green if buildCards() dropped its own sort. Until buildCards()
     * gets integration coverage, pin the sort's presence structurally.
     *
     * @return void
     */
    public function testBuildCardsRanksByScore(): void
    {
        $reflection = new \ReflectionMethod(Cwmrelatedstudies::class, 'buildCards');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertStringContainsString(
            'arsort($this->scores)',
            $body,
            'buildCards() must rank related studies by descending score before building cards'
        );
    }
}
