<?php

/**
 * Integration tests for Sermons API data — verifies correct records are
 * returned with real database queries.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Api;

/**
 * Test sermon data filtering as used by the REST API.
 *
 * @since  10.3.0
 */
class SermonsDataTest extends ApiDataTestCase
{
    /**
     * Test that only published (1) and archived (2) sermons are returned.
     */
    public function testPublishedFilterExcludesUnpublishedAndTrashed(): void
    {
        $this->insertSermon('Published Sermon', 1);
        $this->insertSermon('Archived Sermon', 2);
        $this->insertSermon('Unpublished Sermon', 0);
        $this->insertSermon('Trashed Sermon', -2);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $titles = array_column($results, 'studytitle');

        $this->assertContains('Published Sermon', $titles);
        $this->assertContains('Archived Sermon', $titles);
        $this->assertNotContains('Unpublished Sermon', $titles);
        $this->assertNotContains('Trashed Sermon', $titles);
    }

    /**
     * Test filtering sermons by series_id.
     */
    public function testFilterBySeriesId(): void
    {
        $seriesA = $this->insertSeries('Series A');
        $seriesB = $this->insertSeries('Series B');

        $this->insertSermon('Sermon in A', 1, $seriesA);
        $this->insertSermon('Sermon in B', 1, $seriesB);
        $this->insertSermon('Sermon in A again', 1, $seriesA);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('series_id') . ' = ' . $seriesA,
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(2, $results);

        foreach ($results as $row) {
            $this->assertEquals($seriesA, (int) $row->series_id);
        }
    }

    /**
     * Test filtering sermons by teacher_id.
     */
    public function testFilterByTeacherId(): void
    {
        $teacher = $this->insertTeacher('Pastor Smith');

        $this->insertSermon('By Smith', 1, 0, $teacher);
        $this->insertSermon('By Unknown', 1, 0, 0);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('teacher_id') . ' = ' . $teacher,
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('By Smith', $results[0]->studytitle);
    }

    /**
     * Test filtering sermons by year.
     */
    public function testFilterByYear(): void
    {
        $this->insertSermon('Sermon 2026', 1);

        // Insert a sermon with a different year
        $row = (object) [
            'studytitle'  => 'Sermon 2025',
            'alias'       => 'sermon-2025',
            'studydate'   => '2025-06-15 10:00:00',
            'teacher_id'  => 0,
            'series_id'   => 0,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'hits'        => 0,
            'checked_out' => 0,
            'asset_id'    => 0,
            'created_by'  => 0,
            'modified_by' => 0,
        ];
        $this->db->insertObject('#__bsms_studies', $row);

        $results = $this->queryItems('#__bsms_studies', [
            'YEAR(' . $this->db->quoteName('studydate') . ') = 2026',
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $titles = array_column($results, 'studytitle');
        $this->assertContains('Sermon 2026', $titles);
        $this->assertNotContains('Sermon 2025', $titles);
    }

    /**
     * Test that search filter matches on studytitle.
     */
    public function testSearchFilter(): void
    {
        $this->insertSermon('The Beatitudes', 1);
        $this->insertSermon('Parable of the Sower', 1);
        $this->insertSermon('Beatitudes Part Two', 1);

        $searchTerm = 'Beatitudes';
        $results    = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('studytitle') . ' LIKE ' . $this->db->quote('%' . $searchTerm . '%'),
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(2, $results);
    }

    /**
     * Test that response data includes expected fields.
     */
    public function testSermonRecordHasExpectedFields(): void
    {
        $this->insertSermon('Field Check Sermon', 1);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('studytitle') . ' = ' . $this->db->quote('Field Check Sermon'),
        ]);

        $this->assertCount(1, $results);
        $sermon = $results[0];

        // Verify API-exposed fields exist
        $this->assertObjectHasProperty('id', $sermon);
        $this->assertObjectHasProperty('studytitle', $sermon);
        $this->assertObjectHasProperty('alias', $sermon);
        $this->assertObjectHasProperty('studydate', $sermon);
        $this->assertObjectHasProperty('series_id', $sermon);
        $this->assertObjectHasProperty('published', $sermon);
        $this->assertObjectHasProperty('access', $sermon);
        $this->assertObjectHasProperty('hits', $sermon);
    }

    /**
     * Test that returned values match what was inserted.
     */
    public function testReturnedValuesMatchInsertedData(): void
    {
        $seriesId  = $this->insertSeries('Romans Study');
        $teacherId = $this->insertTeacher('Pastor Johnson');
        $sermonId  = $this->insertSermon('Grace and Faith', 1, $seriesId, $teacherId);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('id') . ' = ' . $sermonId,
        ]);

        $this->assertCount(1, $results);
        $sermon = $results[0];

        $this->assertEquals($sermonId, (int) $sermon->id);
        $this->assertEquals('Grace and Faith', $sermon->studytitle);
        $this->assertEquals('grace-and-faith', $sermon->alias);
        $this->assertEquals('2026-01-15 10:00:00', $sermon->studydate);
        $this->assertEquals($seriesId, (int) $sermon->series_id);
        $this->assertEquals($teacherId, (int) $sermon->teacher_id);
        $this->assertEquals(1, (int) $sermon->published);
        $this->assertEquals(1, (int) $sermon->access);
        $this->assertEquals(0, (int) $sermon->hits);
    }

    /**
     * Test data types are correct for numeric fields.
     */
    public function testNumericFieldsAreIntegers(): void
    {
        $sermonId = $this->insertSermon('Type Check Sermon', 1);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('id') . ' = ' . $sermonId,
        ]);

        $sermon = $results[0];

        $this->assertIsNumeric($sermon->id);
        $this->assertIsNumeric($sermon->published);
        $this->assertIsNumeric($sermon->access);
        $this->assertIsNumeric($sermon->hits);
        $this->assertIsNumeric($sermon->series_id);
    }

    /**
     * Test empty result set when no sermons match filter.
     */
    public function testEmptyResultSetForNonMatchingFilter(): void
    {
        $this->insertSermon('Only Sermon', 1, 0);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('series_id') . ' = 99999',
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(0, $results);
    }

    /**
     * Test that API view field lists are correct (unit-level check).
     */
    public function testSermonViewListFieldsExcludeDetailOnlyFields(): void
    {
        $ref        = new \ReflectionClass(\CWM\Component\Proclaim\Api\View\Sermons\JsonapiView::class);
        $listProp   = $ref->getProperty('fieldsToRenderList');
        $itemProp   = $ref->getProperty('fieldsToRenderItem');
        $listFields = $listProp->getDefaultValue();
        $itemFields = $itemProp->getDefaultValue();

        // studytext should be in item but NOT in list (too large for list view)
        $this->assertContains('studytext', $itemFields, 'Item view should include studytext');
        $this->assertNotContains('studytext', $listFields, 'List view should NOT include studytext');

        // params should be in item but NOT in list
        $this->assertContains('params', $itemFields, 'Item view should include params');
        $this->assertNotContains('params', $listFields, 'List view should NOT include params');

        // Core list fields should be present
        foreach (['id', 'studytitle', 'alias', 'studydate', 'published', 'hits'] as $field) {
            $this->assertContains($field, $listFields, "List view should include $field");
        }
    }

    /**
     * Test that sensitive internal fields are NOT in the API view field lists.
     */
    public function testSensitiveFieldsExcludedFromView(): void
    {
        $ref       = new \ReflectionClass(\CWM\Component\Proclaim\Api\View\Sermons\JsonapiView::class);
        $listProp  = $ref->getProperty('fieldsToRenderList');
        $itemProp  = $ref->getProperty('fieldsToRenderItem');
        $allFields = array_unique(array_merge($listProp->getDefaultValue(), $itemProp->getDefaultValue()));

        foreach (['asset_id', 'checked_out', 'checked_out_time', 'created_by', 'modified_by'] as $field) {
            $this->assertNotContains($field, $allFields, "API should NOT expose $field");
        }
    }
}
