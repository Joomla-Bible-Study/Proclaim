<?php

/**
 * Characterization tests pinning today's scripture rendering output.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use CWM\Library\Scripture\Helper\ScriptureReference;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Groundwork for #1623 -- retiring the legacy flat columns on #__bsms_studies.
 *
 * Step 1 of that project converts the site read paths from the flat
 * booknumber, chapter, verse and "…2" columns to the #__bsms_study_scriptures
 * junction, and is described as behaviour-neutral. Nothing asserted the
 * rendered output, so "behaviour-neutral" could only be claimed, not shown.
 *
 * These tests pin what the flat path renders today, and -- the part that
 * actually de-risks the refactor -- assert that getAllScriptures()'s junction
 * branch produces the *same string* as the legacy branch for equivalent data.
 * That equivalence is the contract step 1 has to preserve.
 *
 * They are deliberately characterization tests: they record current behaviour
 * rather than argue it is correct. If a change here is intended, update them
 * knowingly.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmlisting::class)]
class CwmscriptureRenderingCharacterizationTest extends IntegrationTestCase
{
    /**
     * @var  Cwmlisting|null
     */
    private ?Cwmlisting $listing = null;

    /**
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        // Book names render through Text::_(), so the component's strings have
        // to be loaded or every reference comes out as a raw language key.
        Factory::getApplication()->getLanguage()->load(
            'com_proclaim',
            \dirname(__DIR__, 4) . '/admin'
        );

        $this->listing = new Cwmlisting();
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * Chapters-only is the default (show_verses = 0).
     *
     * @return  Registry
     */
    private function params(int $showVerses = 0): Registry
    {
        return new Registry(['show_verses' => $showVerses]);
    }

    /**
     * A row as the site queries build it today: flat columns plus the joined
     * book-name keys from the two #__bsms_books JOINs.
     *
     * @param   array  $overrides  Column overrides.
     *
     * @return  object
     */
    private function legacyRow(array $overrides = []): object
    {
        // Proclaim numbers books from 101 (Genesis) and keys them JBS_BBK_*;
        // John is 143, Romans 145, Ephesians 149.
        return (object) array_merge([
            'id'             => 1,
            'booknumber'     => 143,
            'chapter_begin'  => 3,
            'verse_begin'    => 16,
            'chapter_end'    => 3,
            'verse_end'      => 16,
            'bookname'       => 'JBS_BBK_JOHN',
            'bible_version'  => '',
            'booknumber2'    => 0,
            'chapter_begin2' => 0,
            'verse_begin2'   => 0,
            'chapter_end2'   => 0,
            'verse_end2'     => 0,
            'bookname2'      => '',
            'bible_version2' => '',
        ], $overrides);
    }

    /**
     * Joining two or more rendered parts goes through the `scripture.list`
     * layout, resolved against JPATH_SITE -- which on a test Joomla root has no
     * Proclaim installed into it, so LayoutHelper returns an empty string.
     *
     * Skipping is the honest outcome: comparing two empty strings would pass
     * while verifying nothing.
     *
     * @return  void
     */
    private function requireJoinLayout(): void
    {
        if (!is_dir(JPATH_SITE . '/components/com_proclaim/layouts')) {
            $this->markTestSkipped(
                'The scripture.list layout is not resolvable from JPATH_SITE in this harness, '
                . 'so a multi-part join renders empty and any comparison would be vacuous.'
            );
        }
    }

    #[TestDox('A single reference renders its book and chapter')]
    public function testSingleReferenceRenders(): void
    {
        $out = $this->listing->getScripture($this->params(), $this->legacyRow(), 0, 1);

        $this->assertSame('John 3', $out, 'Chapters-only mode renders "Book Chapter"');
    }

    /**
     * The fallback every caller relies on once the #__bsms_books JOIN is gone.
     *
     * A row that arrives without a bookname resolves the name from the book
     * number instead. Nothing exercised that path before — every fixture here
     * supplied a bookname, so the branch was invisible — and #1687 makes it the
     * normal case rather than the exception, since removing the JOIN is exactly
     * what stops rows carrying a name.
     *
     * @return  void
     */
    #[TestDox('A row with no bookname resolves the name from its book number')]
    public function testBookNameIsResolvedWhenTheRowCarriesNone(): void
    {
        $withName = $this->legacyRow();

        $withoutName = $this->legacyRow();
        unset($withoutName->bookname);

        $this->assertSame(
            $this->listing->getScripture($this->params(), $withName, 0, 1),
            $this->listing->getScripture($this->params(), $withoutName, 0, 1),
            'Dropping the JOIN must not change what renders — see #1687'
        );

        $this->assertSame(
            'John 3',
            $this->listing->getScripture($this->params(), $withoutName, 0, 1),
            'and it must be the right book, not an empty string that happens to match'
        );
    }

    #[TestDox('Chapters-only and with-verses modes render differently')]
    public function testShowVersesChangesTheOutput(): void
    {
        $row = $this->legacyRow(['chapter_begin' => 3, 'verse_begin' => 16, 'verse_end' => 18]);

        $chaptersOnly = $this->listing->getScripture($this->params(0), $row, 0, 1);
        $withVerses   = $this->listing->getScripture($this->params(1), $row, 0, 1);

        $this->assertNotSame(
            $chaptersOnly,
            $withVerses,
            'show_verses must still change what is rendered'
        );
        $this->assertStringContainsString('16', $withVerses, 'The verse must appear in verse mode');
    }

    #[TestDox('An empty second reference renders nothing')]
    public function testEmptySecondReferenceRendersNothing(): void
    {
        $this->assertSame('', $this->listing->getScripture($this->params(), $this->legacyRow(), 0, 2));
    }

    #[TestDox('A populated second reference renders on its own')]
    public function testSecondReferenceRenders(): void
    {
        $row = $this->legacyRow([
            'booknumber2'    => 145,
            'chapter_begin2' => 5,
            'chapter_end2'   => 5,
            'bookname2'      => 'JBS_BBK_ROMANS',
        ]);

        $out = $this->listing->getScripture($this->params(), $row, 0, 2);

        $this->assertSame('Romans 5', $out);
    }

    #[TestDox('A row with no book at all renders nothing')]
    public function testNoBookRendersNothing(): void
    {
        $row = $this->legacyRow(['booknumber' => 0, 'bookname' => '']);

        $this->assertSame('', $this->listing->getScripture($this->params(), $row, 0, 1));
    }

    #[TestDox('Both legacy references render individually, ready to be joined')]
    public function testBothLegacyReferencesRender(): void
    {
        $row = $this->legacyRow([
            'booknumber2'    => 145,
            'chapter_begin2' => 5,
            'chapter_end2'   => 5,
            'bookname2'      => 'JBS_BBK_ROMANS',
        ]);

        // Asserted per part rather than on the joined string: joining two or
        // more parts goes through the scripture.list layout under JPATH_SITE,
        // which a test Joomla root has no Proclaim installed into. See
        // requireJoinLayout().
        $this->assertSame('John 3', $this->listing->getScripture($this->params(), $row, 0, 1));
        $this->assertSame('Romans 5', $this->listing->getScripture($this->params(), $row, 0, 2));
    }

    /**
     * The contract #1623 step 1 has to preserve.
     *
     * getAllScriptures() already prefers the junction when $row->scriptures is
     * populated and falls back to the flat columns otherwise, so the seam the
     * refactor moves through exists today. If the two branches render the same
     * data differently, converting the read paths silently changes the site.
     */
    #[TestDox('The junction branch renders identically to the legacy branch for the same reference')]
    public function testJunctionAndLegacyBranchesAgree(): void
    {
        $legacy = $this->legacyRow();

        $viaLegacy = $this->listing->getAllScriptures($this->params(), $legacy);

        // Same reference, expressed the way the junction supplies it.
        $viaJunction = $this->listing->getAllScriptures(
            $this->params(),
            (object) [
                'id'         => 1,
                'scriptures' => [
                    new ScriptureReference(
                        booknumber: 143,
                        chapterBegin: 3,
                        verseBegin: 16,
                        chapterEnd: 3,
                        verseEnd: 16
                    ),
                ],
            ]
        );

        // Guard against the comparison passing because both sides are empty.
        $this->assertNotSame('', $viaLegacy, 'The legacy branch must render something to compare');
        $this->assertNotSame('', $viaJunction, 'The junction branch must render something to compare');
        $this->assertSame(
            $viaLegacy,
            $viaJunction,
            'Converting a read path to the junction must not change what the site shows — see #1623'
        );
    }

    #[TestDox('The junction branch joins multiple references the same way the legacy branch joins two')]
    public function testJunctionJoinsMultipleReferences(): void
    {
        $this->requireJoinLayout();

        $viaLegacy = $this->listing->getAllScriptures(
            $this->params(),
            $this->legacyRow([
                'booknumber2'    => 145,
                'chapter_begin2' => 5,
                'chapter_end2'   => 5,
                'bookname2'      => 'JBS_BBK_ROMANS',
            ])
        );

        $viaJunction = $this->listing->getAllScriptures(
            $this->params(),
            (object) [
                'id'         => 1,
                'scriptures' => [
                    new ScriptureReference(booknumber: 143, chapterBegin: 3, verseBegin: 16, chapterEnd: 3, verseEnd: 16),
                    new ScriptureReference(booknumber: 145, chapterBegin: 5, chapterEnd: 5),
                ],
            ]
        );

        $this->assertSame(
            $viaLegacy,
            $viaJunction,
            'Two references must render identically whichever store they came from — see #1623'
        );
    }

    #[TestDox('The junction branch can express more references than the flat columns can hold')]
    public function testJunctionCarriesMoreThanTwoReferences(): void
    {
        $this->requireJoinLayout();

        // The capability the flat columns cap at two, and the reason #1623
        // exists. Pinned so the refactor demonstrably gains it.
        $out = $this->listing->getAllScriptures(
            $this->params(),
            (object) [
                'id'         => 1,
                'scriptures' => [
                    new ScriptureReference(booknumber: 143, chapterBegin: 3, chapterEnd: 3),
                    new ScriptureReference(booknumber: 145, chapterBegin: 5, chapterEnd: 5),
                    new ScriptureReference(booknumber: 149, chapterBegin: 2, chapterEnd: 2),
                ],
            ]
        );

        $this->assertNotSame('', $out);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($out, ';'),
            'Three references should be joined, which the two flat slots cannot represent'
        );
    }

    #[TestDox('An unparsed junction reference falls back to its raw text')]
    public function testUnparsedJunctionReferenceUsesRawText(): void
    {
        $out = $this->listing->getAllScriptures(
            $this->params(),
            (object) [
                'id'         => 1,
                'scriptures' => [
                    new ScriptureReference(booknumber: 0, referenceText: 'The whole of Habakkuk'),
                ],
            ]
        );

        $this->assertStringContainsString('The whole of Habakkuk', $out);
    }
}
