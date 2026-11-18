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
 * @since  __DEPLOY_VERSION__
 */
class CwmepisodenumberForCopyTest extends ProclaimTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A copy that keeps the original\'s series and number has the number cleared, to be numbered next')]
    public function testUnchangedNumberIsCleared(): void
    {
        $this->assertSame('', CwmepisodenumberHelper::forCopy('4', 42, '4', 42));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Surrounding spaces do not make an unchanged number look changed')]
    public function testUnchangedNumberIsClearedDespiteSpaces(): void
    {
        $this->assertSame('', CwmepisodenumberHelper::forCopy(' 4 ', 42, '4', 42));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A number the user changed is kept, for check() to refuse if it is taken')]
    public function testChangedNumberIsKept(): void
    {
        $this->assertSame('7', CwmepisodenumberHelper::forCopy('7', 42, '4', 42));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A copy moved to another series keeps its number, which is checked in that series')]
    public function testMovedToAnotherSeriesKeepsTheNumber(): void
    {
        $this->assertSame('4', CwmepisodenumberHelper::forCopy('4', 43, '4', 42));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A message in no series keeps its number, since nothing numbers or checks it there')]
    public function testNumberOutsideASeriesIsKept(): void
    {
        $this->assertSame('12', CwmepisodenumberHelper::forCopy('12', 0, '12', 0));
    }
}
