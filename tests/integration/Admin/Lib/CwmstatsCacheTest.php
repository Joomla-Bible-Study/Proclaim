<?php

/**
 * Integration tests for Cwmstats static cache behavior
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cwmstats::class)]
class CwmstatsCacheTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetStaticCache(Cwmstats::class, 'cache', []);
    }

    /**
     * Regression test for #1527.
     *
     * getTopScoreSite() was dead code: its only call site was a
     * commented-out `@todo not ready for live` stub in
     * site/src/View/Cwmsermons/HtmlView.php, itself removed in
     * ec2c9ec3b (2024-03-21) -- so the method never had a live caller.
     * It also diverged from every sibling in this class (no
     * CwmlocationHelper::applySecurityFilter() call, no LIMIT, no
     * caching). Removed rather than fixed, since nothing calls it.
     * getTopScore() -- the sibling actually used by
     * admin/tmpl/cwmcpanel/default.php -- is unaffected.
     */
    public function testGetTopScoreSiteIsRemoved(): void
    {
        $this->assertFalse(method_exists(Cwmstats::class, 'getTopScoreSite'));
    }

    public function testGetTopScoreStillExists(): void
    {
        $this->assertTrue(method_exists(Cwmstats::class, 'getTopScore'));
    }
}
