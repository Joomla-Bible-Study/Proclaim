<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmepisodeauditModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression test for #1505: the episode-audit report model delegates to
 * CwmepisodenumberHelper::findAllDuplicates() rather than duplicating the
 * query — see CwmepisodenumberHelperTest for the query's own real-DB coverage.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmepisodeauditModelTest extends ProclaimTestCase
{
    public function testGetDuplicatesReturnsKnownFixtureSeries(): void
    {
        $model = new CwmepisodeauditModel();
        $rows  = $model->getDuplicates();

        $match = array_filter(
            $rows,
            static fn ($row) => (int) $row->series_id === 42 && $row->studynumber === '1'
        );

        $this->assertNotEmpty($match, 'getDuplicates() must surface the known series 42 / studynumber 1 duplicate group');
    }
}
