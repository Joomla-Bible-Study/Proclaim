<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmepisodenumberHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The episode number a Save as Copy of a message stores.
 *
 * @since  10.7.4
 */
class CwmepisodenumberForCopyTest extends ProclaimTestCase
{
    /**
     * A copy that keeps the original's series and number has its number cleared, so it is
     * numbered next.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('A copy that keeps the original\'s series and number has the number cleared, to be numbered next')]
    public function testUnchangedNumberIsCleared(): void
    {
        $this->assertSame('', CwmepisodenumberHelper::forCopy('4', 42, '4', 42));
    }

    /**
     * Spaces around an unchanged number don't make it count as changed.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('Surrounding spaces do not make an unchanged number look changed')]
    public function testUnchangedNumberIsClearedDespiteSpaces(): void
    {
        $this->assertSame('', CwmepisodenumberHelper::forCopy(' 4 ', 42, '4', 42));
    }

    /**
     * A number the user changed is kept as it is.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('A number the user changed is kept, for check() to refuse if it is taken')]
    public function testChangedNumberIsKept(): void
    {
        $this->assertSame('7', CwmepisodenumberHelper::forCopy('7', 42, '4', 42));
    }

    /**
     * A copy moved to another series keeps its number.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('A copy moved to another series keeps its number, which is checked in that series')]
    public function testMovedToAnotherSeriesKeepsTheNumber(): void
    {
        $this->assertSame('4', CwmepisodenumberHelper::forCopy('4', 43, '4', 42));
    }

    /**
     * A message in no series keeps its number, even when unchanged.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('A message in no series keeps its number, since nothing numbers or checks it there')]
    public function testNumberOutsideASeriesIsKept(): void
    {
        $this->assertSame('12', CwmepisodenumberHelper::forCopy('12', 0, '12', 0));
    }
}
