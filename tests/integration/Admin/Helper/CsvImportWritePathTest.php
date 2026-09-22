<?php

/**
 * Integration tests executing the CSV importer's write path.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmcsvimportHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The importer's INSERTs, UPDATEs and lookups are being moved from quote()d
 * literals to bound parameters, and until now nothing executed them — the
 * existing tests cover date parsing and a source-level rule. A conversion
 * mistake here would surface on the next real import of somebody's data,
 * which is the worst possible place.
 *
 * So the write path runs, against the real database, inside a rolled-back
 * transaction: a full row import, the duplicate-then-update branch, and each
 * resolve-or-create lookup both finding and creating. Values include quotes
 * and commas on purpose — the characters quoting exists for are the ones a
 * broken bind mangles first.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmcsvimportHelper::class)]
class CsvImportWritePathTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
        CwmcsvimportHelper::resetState();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        CwmcsvimportHelper::resetState();

        parent::tearDown();
    }

    /**
     * A row whose values carry the characters quoting exists for.
     *
     * @return  array<string, string>
     */
    private static function row(): array
    {
        return [
            'studytitle'  => "O'Brien's \"Test\", part 1",
            'studydate'   => '2030-07-04',
            'studyintro'  => "Intro with 'quotes', commas, and a\nnewline",
            'studytext'   => 'Body text',
            'studynumber' => '42',
            'series'      => "Importer's Fixture Series",
            'location'    => '',
            'messagetype' => '',
            'teacher'     => "D'Angelo Fixture",
            'language'    => '*',
        ];
    }

    #[TestDox('a new row imports, and every quoted character survives')]
    public function testInsertNewRow(): void
    {
        $result = CwmcsvimportHelper::importRow(self::row(), ['auto_create' => true, 'default_published' => 0]);

        $this->assertSame('imported', $result['status'] ?? '', json_encode($result));

        $stored = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName(['id', 'studytitle', 'studyintro', 'series_id']))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('studydate') . ' = ' . $this->db->quote('2030-07-04 00:00:00'))
        )->loadObject();

        $this->assertNotNull($stored, 'The imported study is not in the table.');
        $this->assertSame(self::row()['studytitle'], $stored->studytitle, 'Quoting mangled the title.');
        $this->assertStringContainsString("'quotes', commas", $stored->studyintro);
        $this->assertGreaterThan(0, (int) $stored->series_id, 'The auto-created series was not linked.');
    }

    #[TestDox('a duplicate row takes the update branch and the fields land')]
    public function testUpdateExistingRow(): void
    {
        CwmcsvimportHelper::importRow(self::row(), ['auto_create' => true, 'default_published' => 0]);

        $second               = self::row();
        $second['studyintro'] = "Updated intro, still with 'quotes'";

        // duplicate_handling defaults to skip; the update branch is opt-in,
        // and it reports 'imported' — the caller-facing outcome, not the path.
        $result = CwmcsvimportHelper::importRow(
            $second,
            ['auto_create' => true, 'default_published' => 0, 'duplicate_handling' => 'update']
        );

        $this->assertSame('imported', $result['status'] ?? '', json_encode($result));

        $intro = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('studyintro'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('studydate') . ' = ' . $this->db->quote('2030-07-04 00:00:00'))
        )->loadResult();

        $this->assertSame($second['studyintro'], $intro);
    }

    #[TestDox('each lookup creates once and then finds what it created')]
    public function testResolveOrCreateRoundTrips(): void
    {
        foreach (
            [
                'resolveOrCreateTeacher'     => "Rose O'Fixture",
                'resolveOrCreateSeries'      => "Series with, comma and 'quote'",
                'resolveOrCreateLocation'    => "St. Mary's Fixture Hall",
                'resolveOrCreateMessageType' => "Q&A 'Fixture'",
                'resolveOrCreateTopic'       => "Topic o' fixtures",
            ] as $method => $name
        ) {
            $created = CwmcsvimportHelper::$method($name, true);
            $this->assertGreaterThan(0, $created, "$method failed to create.");

            // ⚠️ The round trip is the quoting test: a LOWER() lookup whose
            // bind mangles the apostrophe creates a second row instead of
            // finding the first.
            $found = CwmcsvimportHelper::$method($name, false);
            $this->assertSame($created, $found, "$method did not find the row it just created.");
        }
    }
}
