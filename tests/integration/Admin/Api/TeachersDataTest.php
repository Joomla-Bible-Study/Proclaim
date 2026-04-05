<?php

/**
 * Integration tests for Teachers API data.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Api;

/**
 * Test teacher data filtering as used by the REST API.
 *
 * @since  10.3.0
 */
class TeachersDataTest extends ApiDataTestCase
{
    public function testPublishedFilterExcludesUnpublishedAndTrashed(): void
    {
        $this->insertTeacher('Published Teacher', 1);
        $this->insertTeacher('Unpublished Teacher', 0);
        $this->insertTeacher('Trashed Teacher', -2);

        $results = $this->queryItems('#__bsms_teachers', [
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $names = array_column($results, 'teachername');

        $this->assertContains('Published Teacher', $names);
        $this->assertNotContains('Unpublished Teacher', $names);
        $this->assertNotContains('Trashed Teacher', $names);
    }

    public function testSearchFilterMatchesName(): void
    {
        $this->insertTeacher('Pastor Smith');
        $this->insertTeacher('Reverend Jones');
        $this->insertTeacher('Elder Smith');

        $results = $this->queryItems('#__bsms_teachers', [
            $this->db->quoteName('teachername') . ' LIKE ' . $this->db->quote('%Smith%'),
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(2, $results);
    }

    public function testTeacherRecordHasExpectedFields(): void
    {
        $this->insertTeacher('Field Check Teacher');

        $results = $this->queryItems('#__bsms_teachers', [
            $this->db->quoteName('teachername') . ' = ' . $this->db->quote('Field Check Teacher'),
        ]);

        $this->assertCount(1, $results);
        $teacher = $results[0];

        $this->assertObjectHasProperty('id', $teacher);
        $this->assertObjectHasProperty('teachername', $teacher);
        $this->assertObjectHasProperty('alias', $teacher);
        $this->assertObjectHasProperty('published', $teacher);
        $this->assertObjectHasProperty('access', $teacher);
    }

    public function testReturnedValuesMatchInsertedData(): void
    {
        $id = $this->insertTeacher('Pastor Williams');

        $results = $this->queryItems('#__bsms_teachers', [
            $this->db->quoteName('id') . ' = ' . $id,
        ]);

        $teacher = $results[0];

        $this->assertEquals($id, (int) $teacher->id);
        $this->assertEquals('Pastor Williams', $teacher->teachername);
        $this->assertEquals('pastor-williams', $teacher->alias);
        $this->assertEquals(1, (int) $teacher->published);
    }

    public function testEmptyResultSetForNonMatchingSearch(): void
    {
        $this->insertTeacher('Actual Teacher');

        $results = $this->queryItems('#__bsms_teachers', [
            $this->db->quoteName('teachername') . ' LIKE ' . $this->db->quote('%Nonexistent%'),
            $this->db->quoteName('published') . ' IN (1, 2)',
        ]);

        $this->assertCount(0, $results);
    }

    public function testViewListFieldsExcludeDetailOnlyFields(): void
    {
        $ref        = new \ReflectionClass(\CWM\Component\Proclaim\Api\View\Teachers\JsonapiView::class);
        $listFields = $ref->getProperty('fieldsToRenderList')->getDefaultValue();
        $itemFields = $ref->getProperty('fieldsToRenderItem')->getDefaultValue();

        // Detail-only fields
        $this->assertContains('description', $itemFields);
        $this->assertNotContains('description', $listFields);
        $this->assertContains('params', $itemFields);
        $this->assertNotContains('params', $listFields);

        // Core list fields present
        foreach (['id', 'teachername', 'alias', 'published'] as $field) {
            $this->assertContains($field, $listFields, "List should include $field");
        }
    }

    public function testSensitiveFieldsExcludedFromView(): void
    {
        $ref       = new \ReflectionClass(\CWM\Component\Proclaim\Api\View\Teachers\JsonapiView::class);
        $allFields = array_unique(array_merge(
            $ref->getProperty('fieldsToRenderList')->getDefaultValue(),
            $ref->getProperty('fieldsToRenderItem')->getDefaultValue(),
        ));

        foreach (['asset_id', 'checked_out', 'checked_out_time', 'created_by', 'modified_by'] as $field) {
            $this->assertNotContains($field, $allFields, "API should NOT expose $field");
        }
    }
}
