<?php

/**
 * A series with no teacher must render, not take the page down with it.
 *
 * `Cwmlisting::getLink()` types its $tid parameter as int. The series listing
 * copies `$item->teacher` into `$item->teacher_id` before calling it, and
 * #__bsms_series.teacher is `int NULL DEFAULT NULL` -- so one teacherless series
 * threw a TypeError that Joomla turned into an error page. Not the row: the
 * whole cwmseriesdisplays listing, every other series with it.
 *
 * ⚠️ The teacher field is not required. serie.xml declares it with no
 * `required`, offers "-1 = Select Teacher", and the listing LEFT JOINs teachers.
 * A teacherless series is meant to be valid, so this is a null-handling gap
 * rather than a case for tightening the form.
 *
 * Never seen because no series on any development site had a NULL teacher --
 * 40 on one, 42 on another, all populated. The seeder was the first thing to
 * make one.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(Cwmlisting::class)]
class CwmlistingNullTeacherTest extends IntegrationTestCase
{
    /**
     * A layout-editor element row, as getFluidRow() receives one.
     *
     * @return  \stdClass
     * @since __DEPLOY_VERSION__
     */
    private function elementRow(): \stdClass
    {
        return (object) [
            'linktype'    => 1,
            'colspan'     => 0,
            'element'     => 'series',
            'name'        => 'sseries',
            'custom'      => '',
            'row'         => 1,
            'col'         => 1,
            'linktypeurl' => '',
        ];
    }

    /**
     * A series as the listing query returns one, with no teacher.
     *
     * @return  \stdClass
     * @since __DEPLOY_VERSION__
     */
    private function teacherlessSeries(): \stdClass
    {
        return (object) [
            'id'               => 1,
            'sid'              => 1,
            'series_text'      => 'A series nobody was assigned to',
            'alias'            => 'a-series-nobody-was-assigned-to',
            'description'      => '',
            'series_thumbnail' => '',
            'teacher'          => null,
            'teachername'      => null,
            'teachertitle'     => null,
            'slug'             => '1:a-series-nobody-was-assigned-to',
            'studydate'        => '2026-01-01 00:00:00',
        ];
    }

    #[TestDox('a series with a null teacher renders instead of throwing')]
    public function testNullTeacherDoesNotThrow(): void
    {
        $listing = new Cwmlisting();
        $method  = new \ReflectionMethod($listing, 'getFluidData');

        $rendered = $method->invoke(
            $listing,
            $this->teacherlessSeries(),
            $this->elementRow(),
            new Registry(['series_id' => 1]),
            (object) ['id' => 1],
            0,
            'seriesdisplays'
        );

        $this->assertIsString(
            $rendered,
            'A null teacher raised instead of rendering. getLink() types $tid as int, and the TypeError '
            . 'does not stop at this row -- it takes the whole listing with it.'
        );
    }

    #[TestDox('a series with a teacher still renders')]
    public function testATeacherStillRenders(): void
    {
        $listing = new Cwmlisting();
        $method  = new \ReflectionMethod($listing, 'getFluidData');
        $series  = $this->teacherlessSeries();

        $series->teacher     = 2;
        $series->teachername = 'A Teacher';

        $this->assertIsString($method->invoke(
            $listing,
            $series,
            $this->elementRow(),
            new Registry(['series_id' => 1]),
            (object) ['id' => 1],
            0,
            'seriesdisplays'
        ));
    }
}
