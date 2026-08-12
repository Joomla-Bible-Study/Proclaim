<?php

/**
 * Moving rows out of the flat columns is the SQL update file's job now -- it has
 * to run before the DROP in the same file, because Joomla executes schema
 * updates before postflight. What is left for PHP is the display text SQL could
 * not produce, since translating a book name is not something SQL can do.
 *
 * The migration therefore fills empty reference_text values, and every caller
 * (postflight, Backup, Restore, control-panel data fixes) may run it repeatedly
 * (#1623).
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\CwmscriptureMigration;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CwmscriptureMigration::class)]
class CwmscriptureMigrationTest extends IntegrationTestCase
{
    /**
     * @var DatabaseDriver|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        try {
            $this->db?->transactionRollback(true);
        } catch (\Throwable) {
            // Connection lost; nothing to roll back.
        }

        parent::tearDown();
    }

    /**
     * A junction row with no display text, as the SQL backfill leaves it.
     *
     * @param   int  $book  Proclaim book number
     *
     * @return  int  The new reference's primary key
     *
     * @since __DEPLOY_VERSION__
     */
    private function referenceWithoutText(int $book = 143): int
    {
        $study = (object) ['studytitle' => 'cwm1623 migration fixture', 'published' => 0, 'language' => '*'];
        $this->db->insertObject('#__bsms_studies', $study, 'id');

        $row = (object) [
            'study_id'       => (int) $this->db->insertid(),
            'ordering'       => 0,
            'booknumber'     => $book,
            'chapter_begin'  => 3,
            'verse_begin'    => 16,
            'chapter_end'    => 3,
            'verse_end'      => 16,
            'bible_version'  => '',
            'reference_text' => '',
        ];
        $this->db->insertObject('#__bsms_study_scriptures', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int  $id  Reference primary key
     *
     * @return  string
     *
     * @since __DEPLOY_VERSION__
     */
    private function referenceText(int $id): string
    {
        return (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('reference_text'))
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadResult();
    }

    #[TestDox('a reference the SQL backfill left without display text is given one')]
    public function testEmptyReferenceTextIsFilled(): void
    {
        $id = $this->referenceWithoutText();

        CwmscriptureMigration::migrate();

        $this->assertNotSame(
            '',
            $this->referenceText($id),
            'The SQL backfill cannot translate a book name, so it writes an empty reference_text and '
            . 'leaves this to run afterwards.'
        );
    }

    #[TestDox('running it twice does not rewrite text that is already there')]
    public function testItIsIdempotent(): void
    {
        $id = $this->referenceWithoutText();

        CwmscriptureMigration::migrate();
        $first = $this->referenceText($id);

        $this->assertSame(
            0,
            CwmscriptureMigration::migrate(),
            'Nothing is left empty, so a second run has no work. Backup, Restore and the control panel '
            . 'data fixes all call this.'
        );
        $this->assertSame($first, $this->referenceText($id));
    }

    #[TestDox('a reference that already has text is left alone')]
    public function testExistingTextIsNotOverwritten(): void
    {
        $id = $this->referenceWithoutText();

        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__bsms_study_scriptures'))
                ->set($this->db->quoteName('reference_text') . ' = ' . $this->db->quote('Hand written'))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->execute();

        CwmscriptureMigration::migrate();

        $this->assertSame('Hand written', $this->referenceText($id));
    }

    #[TestDox('migrateStudy is a no-op now that there are no legacy columns')]
    public function testMigrateStudyDoesNothing(): void
    {
        // Kept only so an importer or older caller does not fatal.
        $this->assertSame(0, CwmscriptureMigration::migrateStudy(1));
    }
}
