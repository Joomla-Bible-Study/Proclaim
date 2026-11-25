<?php

/**
 * Integration tests for description building against malformed params data.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmdescriptionHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1580.
 *
 * The JSON decodes here were wrapped in catch(\JsonException), so malformed
 * syntax could not crash description building. Valid JSON carrying the wrong
 * shape could: description_format as an object failed a string return type,
 * and chapters as a string satisfied !empty() and then failed an array return
 * type. Both are TypeErrors, which extend Error rather than Exception, so the
 * AJAX callers' catch(\Exception) did not see them either -- the request died
 * instead of falling back to no format and no chapters.
 *
 * @since  10.5.6
 */
#[CoversClass(CwmdescriptionHelper::class)]
class CwmdescriptionParamsTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back. tearDown() checks that the
     * rollback restored the media files.
     *
     * @var    DatabaseDriver|null
     * @since  10.5.6
     */
    private ?DatabaseDriver $db = null;

    /**
     * How many media files there were when setUp() ran.
     *
     * @var    int
     * @since  10.5.6
     */
    private int $rowsAtStart = 0;

    /**
     * The id of the study setUp() inserts, which the media files belong to.
     *
     * @var    int
     * @since  10.5.6
     */
    private int $studyId = 0;

    /**
     * Skip without a test database. Otherwise open a transaction, count the media files, and
     * insert a published study.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
        $this->rowsAtStart = $this->countMedia();

        $study = (object) [
            'published'  => 1,
            'studytitle' => 'cwm1580 fixture',
            'alias'      => $this->uniqueAlias('cwmdescriptionparams-study'),
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $this->studyId = (int) $this->db->insertid();
    }

    /**
     * Roll back the transaction setUp() opened, then fail the test if the media files don't
     * number what they did at setUp().
     *
     * @return  void
     *
     * @since   10.5.6
     */
    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
                $after = $this->countMedia();

                if ($after !== $this->rowsAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_mediafiles went from %d to %d rows.',
                        $this->rowsAtStart,
                        $after
                    );
                }
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();

        if ($leaked !== null) {
            $this->fail($leaked);
        }
    }

    /**
     * The number of media files.
     *
     * @return  int  Rows in the media table.
     *
     * @since   10.5.6
     */
    private function countMedia(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_mediafiles'))
        )->loadResult();
    }

    /**
     * Insert a media file carrying the given params JSON.
     *
     * @param   string  $paramsJson  Raw params column value
     *
     * @return  int  Media id
     *
     * @since   10.5.6
     */
    private function insertMedia(string $paramsJson): int
    {
        $row = (object) [
            'study_id'  => $this->studyId,
            'published' => 1,
            'params'    => $paramsJson,
            'ordering'  => 0,
            'language'  => '*',
            'metadata'  => '',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $row, 'id');

        return (int) $this->db->insertid();
    }

    /**
     * Params text the code does not expect: valid JSON of the wrong shape, malformed JSON, or an empty string.
     *
     * @return  array<string, array{0: string}>  Each case's params text, keyed by what is wrong with it
     *
     * @since   10.5.6
     */
    public static function malformedShapeProvider(): array
    {
        return [
            'format as an object'   => ['{"description_format":{"nested":true}}'],
            'format as a list'      => ['{"description_format":[1,2]}'],
            'format as a number'    => ['{"description_format":42}'],
            'format as a bool'      => ['{"description_format":true}'],
            'chapters as a string'  => ['{"chapters":"not-an-array"}'],
            'chapters as a number'  => ['{"chapters":5}'],
            'whole doc is a list'   => ['[1,2,3]'],
            'whole doc is a scalar' => ['5'],
            'whole doc is a string' => ['"just a string"'],
            'malformed syntax'      => ['{not valid json'],
            'empty'                 => [''],
        ];
    }

    /**
     * The contract the surrounding try/catch machinery was written to provide:
     * bad params data degrades description building, it does not crash it.
     *
     * @param   string  $paramsJson  Raw params value
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('malformedShapeProvider')]
    #[TestDox('Chapters lookup survives valid JSON with the wrong shape')]
    public function testChaptersLookupSurvivesBadShapes(string $paramsJson): void
    {
        $mediaId = $this->insertMedia($paramsJson);

        $ref = new \ReflectionMethod(CwmdescriptionHelper::class, 'getChaptersForDescription');

        try {
            $result = $ref->invoke(null, $this->studyId, $mediaId);
        } catch (\TypeError $e) {
            $this->fail(
                'A TypeError escapes the AJAX callers\' catch(\Exception) and kills the request — see #1580: '
                . $e->getMessage()
            );
        }

        $this->assertIsArray($result, 'Chapters must always come back as an array');
    }

    /**
     * getServerDescriptionFormat() returns a string, without a TypeError, for a media file
     * whose server holds params of the wrong shape, malformed or empty.
     *
     * @param   string  $paramsJson  Raw params value
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[DataProvider('malformedShapeProvider')]
    #[TestDox('Server description format survives valid JSON with the wrong shape')]
    public function testServerFormatSurvivesBadShapes(string $paramsJson): void
    {
        $server = (object) ['published' => 1, 'params' => $paramsJson, 'type' => 'legacy', 'media' => ''];
        $this->db->insertObject('#__bsms_servers', $server, 'id');
        $serverId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $this->studyId,
            'published' => 1,
            'server_id' => $serverId,
            'params'    => '{}',
            'ordering'  => 0,
            'language'  => '*',
            'metadata'  => '',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');
        $mediaId = (int) $this->db->insertid();

        $ref = new \ReflectionMethod(CwmdescriptionHelper::class, 'getServerDescriptionFormat');

        try {
            $result = $ref->invoke(null, $mediaId);
        } catch (\TypeError $e) {
            $this->fail(
                'A TypeError escapes the AJAX callers\' catch(\Exception) and kills the request — see #1580: '
                . $e->getMessage()
            );
        }

        $this->assertIsString($result, 'The format must always come back as a string');
    }

    // -------------------------------------------------------------------------
    // The well-formed cases still have to work
    // -------------------------------------------------------------------------

    /**
     * getChaptersForDescription() returns a media file's one well-formed chapter, with its
     * title.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[TestDox('A real chapters array is still returned')]
    public function testWellFormedChaptersAreReturned(): void
    {
        $mediaId = $this->insertMedia('{"chapters":[{"time":"00:00","title":"Intro"}]}');

        $ref    = new \ReflectionMethod(CwmdescriptionHelper::class, 'getChaptersForDescription');
        $result = $ref->invoke(null, $this->studyId, $mediaId);

        $this->assertCount(1, $result);
        $this->assertSame('Intro', $result[0]['title']);
    }

    /**
     * getServerDescriptionFormat() returns the description_format string a media file's
     * server holds.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[TestDox('A real description format string is still returned')]
    public function testWellFormedFormatIsReturned(): void
    {
        $server = (object) ['published' => 1, 'params' => '{"description_format":"{title} — {teacher}"}', 'type' => 'legacy', 'media' => ''];
        $this->db->insertObject('#__bsms_servers', $server, 'id');
        $serverId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $this->studyId,
            'published' => 1,
            'server_id' => $serverId,
            'params'    => '{}',
            'ordering'  => 0,
            'language'  => '*',
            'metadata'  => '',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');

        $ref = new \ReflectionMethod(CwmdescriptionHelper::class, 'getServerDescriptionFormat');

        $this->assertSame('{title} — {teacher}', $ref->invoke(null, (int) $this->db->insertid()));
    }

    /**
     * getServerDescriptionFormat() returns the format a server holds under the legacy
     * yt_description_format key.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    #[TestDox('The legacy yt_description_format key is still honoured')]
    public function testLegacyFormatKeyStillWorks(): void
    {
        $server = (object) ['published' => 1, 'params' => '{"yt_description_format":"legacy {title}"}', 'type' => 'legacy', 'media' => ''];
        $this->db->insertObject('#__bsms_servers', $server, 'id');
        $serverId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $this->studyId,
            'published' => 1,
            'server_id' => $serverId,
            'params'    => '{}',
            'ordering'  => 0,
            'language'  => '*',
            'metadata'  => '',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');

        $this->assertSame(
            'legacy {title}',
            (new \ReflectionMethod(CwmdescriptionHelper::class, 'getServerDescriptionFormat'))
                ->invoke(null, (int) $this->db->insertid())
        );
    }
}
