<?php

/**
 * The scripture1 and scripture2 elements read the junction, not the flat pair.
 *
 * The flat columns are gone (#1623), but a row can still arrive carrying
 * properties that look like them -- from an older cache, a template override,
 * or a caller that built the object by hand. The junction is the only source.
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
use CWM\Library\Scripture\Helper\ScriptureReference;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(Cwmlisting::class)]
class CwmlistingScriptureSourceTest extends IntegrationTestCase
{
    /**
     * A row whose flat columns and junction references disagree.
     *
     * The flat pair says Genesis 1 and Exodus 2 -- what a stale row looks like.
     * The junction says Romans 8 and Titus 3.
     *
     * @param   bool  $withJunction  Whether to attach junction references
     *
     * @return  object
     *
     * @since __DEPLOY_VERSION__
     */
    private function row(bool $withJunction): object
    {
        $row = (object) [
            'id'             => 4242,
            'booknumber'     => 101,
            'chapter_begin'  => 1,
            'verse_begin'    => 1,
            'chapter_end'    => 1,
            'verse_end'      => 2,
            'bookname'       => 'JBS_BBK_GENESIS',
            'bible_version'  => 'kjv',
            'booknumber2'    => 102,
            'chapter_begin2' => 2,
            'verse_begin2'   => 1,
            'chapter_end2'   => 2,
            'verse_end2'     => 2,
            'bookname2'      => 'JBS_BBK_EXODUS',
            'bible_version2' => 'kjv',
        ];

        if ($withJunction) {
            $row->scriptures = [
                new ScriptureReference(booknumber: 145, chapterBegin: 8, verseBegin: 1, chapterEnd: 8, verseEnd: 4),
                new ScriptureReference(booknumber: 156, chapterBegin: 3, verseBegin: 1, chapterEnd: 3, verseEnd: 5),
            ];
        }

        return $row;
    }

    /**
     * ⚠️ Assertions here ignore case because the book name renders as its
     * language key ("JBS_BBK_TITUS") when this test runs alone and as the
     * translation ("Titus") when an earlier test in the suite has loaded the
     * language. Both contain the book, so matching case-insensitively is true
     * either way; asserting on one spelling passes or fails by suite order.
     *
     * @return  Registry  Params asking for chapters only, the default.
     * @since __DEPLOY_VERSION__
     */
    private function params(): Registry
    {
        return new Registry(['show_verses' => 0]);
    }

    #[TestDox('scripture1 renders the junction reference, not the flat column')]
    public function testFirstReferenceComesFromTheJunction(): void
    {
        $rendered = (new Cwmlisting())->getScripture($this->params(), $this->row(true), 0, 1);

        $this->assertStringContainsStringIgnoringCase(
            'ROMANS',
            $rendered,
            'scripture1 rendered the flat column. After the writes are removed that column is stale, '
            . 'so this would show the study\'s previous reference or Genesis.'
        );
        $this->assertStringNotContainsStringIgnoringCase('GENESIS', $rendered);
    }

    #[TestDox('scripture2 renders the junction reference, not the flat column')]
    public function testSecondReferenceComesFromTheJunction(): void
    {
        $rendered = (new Cwmlisting())->getScripture($this->params(), $this->row(true), 0, 2);

        $this->assertStringContainsStringIgnoringCase('TITUS', $rendered);
        $this->assertStringNotContainsStringIgnoringCase('EXODUS', $rendered);
    }

    #[TestDox('a row with no junction references renders nothing')]
    public function testRowsWithoutReferencesRenderNothing(): void
    {
        $this->assertSame(
            '',
            (new Cwmlisting())->getScripture($this->params(), $this->row(false), 0, 1),
            'The legacy columns are gone (#1623). A row carrying only their leftovers has no reference, '
            . 'and rendering one from stale properties is exactly what must not happen.'
        );
    }

    #[TestDox('hasScripture reports only what the junction holds')]
    public function testHasScriptureReadsTheJunctionOnly(): void
    {
        $this->assertTrue(Cwmlisting::hasScripture($this->row(true), 1));
        $this->assertTrue(Cwmlisting::hasScripture($this->row(true), 2));

        $this->assertFalse(
            Cwmlisting::hasScripture($this->row(false), 1),
            'The row carries legacy-looking properties and no junction reference. Trusting them is how a '
            . 'retired column reaches a page.'
        );
        $this->assertFalse(Cwmlisting::hasScripture((object) ['id' => 1], 1));
    }

    #[TestDox('a junction row with one reference has no second position')]
    public function testASingleJunctionReferenceHasNoSecond(): void
    {
        $row             = $this->row(false);
        $row->scriptures = [new ScriptureReference(booknumber: 145, chapterBegin: 8)];

        $this->assertTrue(Cwmlisting::hasScripture($row, 1));
        $this->assertFalse(
            Cwmlisting::hasScripture($row, 2),
            'The junction says one reference; booknumber2 is a stale leftover and must not resurrect a second.'
        );
    }
}
