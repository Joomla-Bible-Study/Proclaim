<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The title and alias a Save as Copy of a message or series stores.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmaliasForCopyTest extends ProclaimTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An unchanged title gets a number, and the alias is cleared to be rebuilt')]
    public function testUnchangedTitleIsNumbered(): void
    {
        $this->assertSame(
            ['Romans Study (2)', ''],
            Cwmalias::forCopy('Romans Study', 'romans-study', 'Romans Study', 'romans-study')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A new title keeps it, and an unchanged alias is cleared to be rebuilt')]
    public function testChangedTitleClearsTheCopiedAlias(): void
    {
        $this->assertSame(
            ['Romans, Part Two', ''],
            Cwmalias::forCopy('Romans, Part Two', 'romans-study', 'Romans Study', 'romans-study')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An alias the user changed is kept, for check() to refuse if it is taken')]
    public function testChangedAliasIsKept(): void
    {
        $this->assertSame(
            ['Romans, Part Two', 'romans-part-two'],
            Cwmalias::forCopy('Romans, Part Two', 'romans-part-two', 'Romans Study', 'romans-study')
        );
    }
}
