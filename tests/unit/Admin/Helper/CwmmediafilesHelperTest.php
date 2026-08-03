<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmediafilesHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Regression test for #1448: CwmmediafilesController and CwmmessagesController
 * each implemented checkDeleteFiles() as a ~70-line near-verbatim duplicate
 * (same join query + Registry parsing loop), differing only in whether the
 * incoming ids are matched against mf.id or mf.study_id. Consolidated into
 * CwmmediafilesHelper::findFilesForDelete(), taking the differing column as
 * a parameter; confirmed byte-for-byte identical before extraction (`diff`
 * on the two original method bodies showed exactly one line differed).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmediafilesHelperTest extends ProclaimTestCase
{
    public function testFindFilesForDeleteReturnsEmptyArrayForEmptyIds(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $this->assertSame([], CwmmediafilesHelper::findFilesForDelete($db, [], 'id'));
    }

    public function testFindFilesForDeleteRejectsUnknownColumn(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        CwmmediafilesHelper::findFilesForDelete($db, [1], 'not_a_real_column');
    }

    /**
     * Real DB round-trip (not mocked) against the live query — no seeded
     * media file on this dev site has server params.delete_files set, so
     * this exercises the "no matching files" branch, but it does prove the
     * query itself is syntactically valid against the real schema (a typo'd
     * column/table name would fail here even though a mock could never
     * catch it).
     *
     * @return void
     */
    public function testFindFilesForDeleteRunsRealQueryById(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $result = CwmmediafilesHelper::findFilesForDelete($db, [-1], 'id');

        $this->assertSame([], $result);
    }

    public function testFindFilesForDeleteRunsRealQueryByStudyId(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $result = CwmmediafilesHelper::findFilesForDelete($db, [-1], 'study_id');

        $this->assertSame([], $result);
    }
}
