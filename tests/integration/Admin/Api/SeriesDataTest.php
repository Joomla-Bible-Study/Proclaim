<?php

/**
 * Integration tests for Series API data.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Api;

/**
 * Test series data filtering as used by the REST API.
 *
 * @since  10.3.0
 */
class SeriesDataTest extends ApiDataTestCase
{
    public function testPublishedFilterExcludesUnpublishedAndTrashed(): void
    {
        $this->insertSeries('Published Series', 1);
        $this->insertSeries('Archived Series', 2);
        $this->insertSeries('Unpublished Series', 0);

        $results = $this->queryItems('#__bsms_series', [
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $titles = array_column($results, 'series_text');

        $this->assertContains('Published Series', $titles);
        $this->assertContains('Archived Series', $titles);
        $this->assertNotContains('Unpublished Series', $titles);
    }

    public function testSeriesRecordHasExpectedFields(): void
    {
        $this->insertSeries('Field Check Series');

        $results = $this->queryItems('#__bsms_series', [
            $this->db->quoteName('series_text') . ' = ' . $this->db->quote('Field Check Series'),
        ]);

        $this->assertCount(1, $results);
        $series = $results[0];

        $this->assertObjectHasProperty('id', $series);
        $this->assertObjectHasProperty('series_text', $series);
        $this->assertObjectHasProperty('alias', $series);
        $this->assertObjectHasProperty('published', $series);
        $this->assertObjectHasProperty('access', $series);
    }

    public function testSermonsCanBeFilteredBySeries(): void
    {
        $seriesId = $this->insertSeries('Gospel of John');

        $this->insertSermon('John Chapter 1', 1, $seriesId);
        $this->insertSermon('John Chapter 2', 1, $seriesId);
        $this->insertSermon('Unrelated Sermon', 1, 0);

        $results = $this->queryItems('#__bsms_studies', [
            $this->db->quoteName('series_id') . ' = ' . $seriesId,
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(2, $results);
    }

    public function testReturnedValuesMatchInsertedData(): void
    {
        $id = $this->insertSeries('Ephesians Deep Dive');

        $results = $this->queryItems('#__bsms_series', [
            $this->db->quoteName('id') . ' = ' . $id,
        ]);

        $series = $results[0];

        $this->assertEquals($id, (int) $series->id);
        $this->assertEquals('Ephesians Deep Dive', $series->series_text);
        $this->assertEquals('ephesians-deep-dive', $series->alias);
        $this->assertEquals(1, (int) $series->published);
    }

    public function testViewListFieldsExcludeDetailOnlyFields(): void
    {
        $ref        = new \ReflectionClass(\CWM\Component\Proclaim\Api\View\Series\JsonapiView::class);
        $listFields = $ref->getProperty('fieldsToRenderList')->getDefaultValue();
        $itemFields = $ref->getProperty('fieldsToRenderItem')->getDefaultValue();

        $this->assertContains('params', $itemFields);
        $this->assertNotContains('params', $listFields);

        foreach (['id', 'series_text', 'alias', 'published'] as $field) {
            $this->assertContains($field, $listFields, "List should include $field");
        }
    }

    public function testSensitiveFieldsExcludedFromView(): void
    {
        $ref       = new \ReflectionClass(\CWM\Component\Proclaim\Api\View\Series\JsonapiView::class);
        $allFields = array_unique(array_merge(
            $ref->getProperty('fieldsToRenderList')->getDefaultValue(),
            $ref->getProperty('fieldsToRenderItem')->getDefaultValue(),
        ));

        foreach (['asset_id', 'checked_out', 'checked_out_time', 'created_by', 'modified_by'] as $field) {
            $this->assertNotContains($field, $allFields, "API should NOT expose $field");
        }
    }
}
