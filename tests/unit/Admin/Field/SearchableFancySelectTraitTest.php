<?php

/**
 * Unit test for the shared searchable fancy-select setup() logic
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Administrator\Field\MediaPlaylistsField;
use CWM\Component\Proclaim\Administrator\Field\PodcastsField;
use CWM\Component\Proclaim\Administrator\Field\TeacherListField;
use CWM\Component\Proclaim\Administrator\Field\Trait\SearchableFancySelectTrait;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Regression test: TeacherListField, MediaPlaylistsField and PodcastsField
 * each carried an identical setup() body switching to the fancy-select
 * (Choices.js) layout when searchable="true" -- three independent copies of
 * the same ~10 lines, including the WebAssetManager wiring for the
 * dropdown-clipping fix. Extracted to SearchableFancySelectTrait.
 *
 * @since  __DEPLOY_VERSION__
 */
class SearchableFancySelectTraitTest extends ProclaimTestCase
{
    /**
     * @return array<string, array{0: class-string}>
     */
    public static function fieldProvider(): array
    {
        return [
            'TeacherListField'    => [TeacherListField::class],
            'MediaPlaylistsField' => [MediaPlaylistsField::class],
            'PodcastsField'       => [PodcastsField::class],
        ];
    }

    #[DataProvider('fieldProvider')]
    public function testFieldUsesTheSharedTrait(string $fieldClass): void
    {
        $this->assertContains(
            SearchableFancySelectTrait::class,
            class_uses($fieldClass),
            "$fieldClass must use SearchableFancySelectTrait rather than duplicating its own copy"
        );
    }

    #[DataProvider('fieldProvider')]
    public function testSetupNoLongerDuplicatesTheLayoutSwitchingLogic(string $fieldClass): void
    {
        $ref   = new \ReflectionMethod($fieldClass, 'setup');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString('$this->applySearchableFancySelect($element)', $body);
        $this->assertStringNotContainsString(
            'list-fancy-select',
            $body,
            "$fieldClass::setup() must delegate to the trait, not set the layout itself"
        );
        $this->assertStringNotContainsString(
            'getWebAssetManager',
            $body,
            "$fieldClass::setup() must delegate to the trait, not wire the asset itself"
        );
    }

    public function testTraitAppliesTheLayoutOnlyWhenSearchableIsTrue(): void
    {
        $ref   = new \ReflectionMethod(SearchableFancySelectTrait::class, 'applySearchableFancySelect');
        $lines = file((string) $ref->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1)
        );

        $this->assertStringContainsString("element['searchable']", $body);
        $this->assertStringContainsString("'true'", $body);
        $this->assertStringContainsString('joomla.form.field.list-fancy-select', $body);
        $this->assertStringContainsString('topics-field', $body);
    }
}
