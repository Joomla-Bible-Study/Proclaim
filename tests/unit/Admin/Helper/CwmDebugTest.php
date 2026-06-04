<?php

/**
 * Unit tests for CwmDebug::logQuery().
 *
 * Guards the production-critical property of the query logging now wired into
 * the main list models: it must be a true no-op when JBSMDEBUG is off, so the
 * instrumentation adds zero overhead in production.
 *
 * The "enabled" branch (buffer + 500-char truncation) is gated on the JBSMDEBUG
 * constant, which cannot be defined per-test without leaking to the rest of the
 * suite, and PHPUnit process isolation conflicts with the noisy test bootstrap.
 * That branch is exercised end-to-end through CwmsermonsModuleStateTest /
 * manual verification instead.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmDebug;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for CwmDebug.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmDebugTest extends ProclaimTestCase
{
    /**
     * Clear CwmDebug's static buffer before each test.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $ref  = new \ReflectionClass(CwmDebug::class);
        $prop = $ref->getProperty('buffer');
        $prop->setValue(null, []);
    }

    #[TestDox('Debug is off by default in the unit harness (no JBSMDEBUG)')]
    public function testDisabledByDefault(): void
    {
        $this->assertFalse(CwmDebug::isEnabled(), 'JBSMDEBUG must not be defined in the unit harness');
    }

    #[TestDox('logQuery is a no-op when JBSMDEBUG is off')]
    public function testLogQueryNoopWhenDisabled(): void
    {
        CwmDebug::logQuery('sermons.getListQuery', 'SELECT 1');

        $this->assertSame([], CwmDebug::getBuffer(), 'Disabled logQuery must not buffer anything');
    }

    #[TestDox('logQuery accepts any stringable query without error when disabled')]
    public function testLogQueryAcceptsQueryObjectWhenDisabled(): void
    {
        $queryObject = new class () {
            public function __toString(): string
            {
                return 'SELECT * FROM x';
            }
        };

        // Must not throw casting/handling a query object even on the no-op path.
        CwmDebug::logQuery('messages.getListQuery', $queryObject);

        $this->assertSame([], CwmDebug::getBuffer());
    }

    #[TestDox('log and error also no-op the buffer when disabled')]
    public function testLogAndTimerNoopWhenDisabled(): void
    {
        CwmDebug::log('hello', 'general');
        CwmDebug::startTimer('t');

        $this->assertSame(0.0, CwmDebug::stopTimer('t'), 'Timer returns 0.0 when disabled');
        $this->assertSame([], CwmDebug::getBuffer(), 'Nothing should buffer when disabled');
    }
}
