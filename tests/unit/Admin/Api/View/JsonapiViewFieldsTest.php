<?php

/**
 * Unit tests for the REST API JsonapiView field lists.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\View;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Field-exposure contract for the API's JSON views: heavy detail-only fields
 * stay out of list responses, core fields stay in, and internal bookkeeping
 * columns are never exposed at all.
 *
 * These assertions lived in tests/integration/Admin/Api/*DataTest.php, whose
 * other tests were removed in the 2026-08 test-correctness pass: those built
 * their own SQL predicates via a test-local queryItems() helper and asserted
 * MySQL honored them, with no Proclaim code (no Api model, no Table::check(),
 * no OutputFilter) anywhere in the path — structurally unable to fail from an
 * app-code regression, while real endpoint behavior is covered by the e2e API
 * acceptance suite (tests/e2e/api). The view field-list checks below were the
 * only assertions in those files that exercised app code, and they need no
 * DB, so they moved here as plain unit tests.
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiViewFieldsTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: class-string, 1: string[], 2: string[]}>
     */
    public static function viewProvider(): array
    {
        return [
            'Sermons' => [
                \CWM\Component\Proclaim\Api\View\Sermons\JsonapiView::class,
                ['studytext', 'params'],
                ['id', 'studytitle', 'alias', 'studydate', 'published', 'hits'],
            ],
            'Series' => [
                \CWM\Component\Proclaim\Api\View\Series\JsonapiView::class,
                ['params'],
                ['id', 'series_text', 'alias', 'published'],
            ],
            'Teachers' => [
                \CWM\Component\Proclaim\Api\View\Teachers\JsonapiView::class,
                ['description', 'params'],
                ['id', 'teachername', 'alias', 'published'],
            ],
        ];
    }

    /**
     * @param   class-string  $viewClass         The JsonapiView under test.
     * @param   string[]      $detailOnlyFields  Fields allowed in item view only.
     * @param   string[]      $coreListFields    Fields that must be in list view.
     */
    #[DataProvider('viewProvider')]
    public function testListFieldsExcludeDetailOnlyFields(string $viewClass, array $detailOnlyFields, array $coreListFields): void
    {
        $ref        = new \ReflectionClass($viewClass);
        $listFields = $ref->getProperty('fieldsToRenderList')->getDefaultValue();
        $itemFields = $ref->getProperty('fieldsToRenderItem')->getDefaultValue();

        foreach ($detailOnlyFields as $field) {
            $this->assertContains($field, $itemFields, "$viewClass item view should include $field");
            $this->assertNotContains($field, $listFields, "$viewClass list view should NOT include $field (too large for lists)");
        }

        foreach ($coreListFields as $field) {
            $this->assertContains($field, $listFields, "$viewClass list view should include $field");
        }
    }

    /**
     * @param   class-string  $viewClass         The JsonapiView under test.
     * @param   string[]      $detailOnlyFields  Unused here; shared provider shape.
     * @param   string[]      $coreListFields    Unused here; shared provider shape.
     */
    #[DataProvider('viewProvider')]
    public function testSensitiveFieldsExcludedFromView(string $viewClass, array $detailOnlyFields = [], array $coreListFields = []): void
    {
        $ref       = new \ReflectionClass($viewClass);
        $allFields = array_unique(array_merge(
            $ref->getProperty('fieldsToRenderList')->getDefaultValue(),
            $ref->getProperty('fieldsToRenderItem')->getDefaultValue(),
        ));

        foreach (['asset_id', 'checked_out', 'checked_out_time', 'created_by', 'modified_by'] as $field) {
            $this->assertNotContains($field, $allFields, "$viewClass must NOT expose $field");
        }
    }
}
