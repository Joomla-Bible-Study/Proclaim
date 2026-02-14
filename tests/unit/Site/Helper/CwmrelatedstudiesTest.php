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
     * Test scores property starts empty
     *
     * @return void
     */
    public function testScoresDefaultEmpty(): void
    {
        $helper = new Cwmrelatedstudies();

        $this->assertIsArray($helper->scores);
        $this->assertEmpty($helper->scores);
    }

    /**
     * Test scoring order is correct after arsort
     *
     * @return void
     */
    public function testScoreSortOrder(): void
    {
        $helper = new Cwmrelatedstudies();

        // Simulate different scoring dimensions
        $helper->addScore(10, 3); // series match
        $helper->addScore(20, 2); // teacher match
        $helper->addScore(30, 4); // topic: 2 overlaps
        $helper->addScore(10, 2); // teacher match (same study)

        arsort($helper->scores);
        $sorted = array_keys($helper->scores);

        // Study 10 has 5 points, study 30 has 4, study 20 has 2
        $this->assertSame(10, $sorted[0]);
        $this->assertSame(30, $sorted[1]);
        $this->assertSame(20, $sorted[2]);
    }

    /**
     * Test class existence and method signatures
     *
     * @return void
     */
    public function testClassStructure(): void
    {
        $helper = new Cwmrelatedstudies();

        $this->assertInstanceOf(Cwmrelatedstudies::class, $helper);
        $this->assertTrue(method_exists($helper, 'getRelated'));
        $this->assertTrue(method_exists($helper, 'addScore'));
    }
}
