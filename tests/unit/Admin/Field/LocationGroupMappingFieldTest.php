<?php

/**
 * Unit tests for LocationGroupMappingField
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\LocationGroupMappingField;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1466: getInput() ran the main location query (which
 * already computes a per-location message_count via a LEFT JOIN + COUNT +
 * GROUP BY), then called CwmlocationHelper::getLocationUsage($locId) inside
 * the per-row loop -- an extra COUNT(*) query per location re-deriving the
 * exact same number the main query already had.
 *
 * @since  __DEPLOY_VERSION__
 */
class LocationGroupMappingFieldTest extends ProclaimTestCase
{
    /**
     * @return string
     */
    private function getInputMethodBody(): string
    {
        $reflection = new \ReflectionMethod(LocationGroupMappingField::class, 'getInput');
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * @return void
     */
    public function testGetInputDoesNotRunPerRowUsageQuery(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/CwmlocationHelper::getLocationUsage/',
            $this->getInputMethodBody(),
            'getInput() must not re-query a count per location the main query already computed — see #1466'
        );
    }

    /**
     * @return void
     */
    public function testGetInputUsesMessageCountFromTheMainQuery(): void
    {
        $this->assertMatchesRegularExpression(
            '/\$location->message_count/',
            $this->getInputMethodBody(),
            'getInput() must read message_count from the already-computed main query result — see #1466'
        );
    }
}
