<?php

/**
 * Integration tests for the protected-podcast health check.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Health\Check\ProtectedPodcastCheck;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Driven against the real database because the defect this exists to hold
 * down lived between the SQL and the data: the params column is JSON, and
 * json_encode escapes slashes, so the stored path reads
 * `images\/biblestudy\/protected\/`. A LIKE on the plain path matched
 * nothing, and the check reported Ok over a live conflict — it shipped that
 * way to review and was caught by an end-to-end run, not by reading.
 *
 * ⚠️ The fixture therefore writes its params with json_encode's DEFAULT
 * escaping, exactly as the site does. A fixture built with unescaped slashes
 * would pass against the broken prefilter and guard nothing.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(ProtectedPodcastCheck::class)]
class ProtectedPodcastCheckTest extends IntegrationTestCase
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

        parent::tearDown();
    }

    /**
     * Insert a published media row, params encoded the way the site encodes them.
     *
     * @param   string  $filename   Site-relative filename.
     * @param   string  $podcastId  Raw podcast_id column value.
     *
     * @return  int
     */
    private function makeMedia(string $filename, string $podcastId): int
    {
        $media = (object) [
            'study_id'  => 0,
            'server_id' => 0,
            'published' => 1,
            'access'    => 1,
            // ⚠️ Default escaping on purpose — see the class docblock.
            'params'     => json_encode(['filename' => $filename, 'mime_type' => 'audio/mpeg']),
            'podcast_id' => $podcastId,
            'metadata'   => '',
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');

        return (int) $this->db->insertid();
    }

    #[TestDox('A clean site reports Ok')]
    public function testBaselineIsOk(): void
    {
        // ⚠️ Positive control for the Warning test: if the test database
        // already carried a conflict, a Warning there would prove nothing
        // about the fixture.
        $this->assertSame(HealthStatus::Ok, (new ProtectedPodcastCheck())->run()->status);
    }

    #[TestDox('A protected file a podcast references is reported by name')]
    public function testConflictIsReported(): void
    {
        $id     = $this->makeMedia('/images/biblestudy/protected/zz-conflict.mp3', '2');
        $result = (new ProtectedPodcastCheck())->run();

        $this->assertSame(
            HealthStatus::Warning,
            $result->status,
            'A podcast-referenced protected file was not reported. If the fingerprint below is also empty, '
            . 'suspect the SQL prefilter matching the raw JSON, where slashes are escaped.'
        );
        // The filename lives in the translated sentence, and this process has
        // no language loaded — Text::plural() hands back the bare key. The
        // id-bearing fingerprint is language-free, so it carries the by-name
        // assertion here; the rendered sentence is covered by the live panel.
        $this->assertStringContainsString((string) $id, $result->fingerprint);
        $this->assertNotSame('', $result->detail);
    }

    #[TestDox('A protected file no podcast references is not a finding')]
    public function testUnreferencedProtectedFileIsFine(): void
    {
        $this->makeMedia('/images/biblestudy/protected/zz-quiet.mp3', '0');
        $this->makeMedia('/images/biblestudy/protected/zz-quiet2.mp3', '-1');

        $this->assertSame(HealthStatus::Ok, (new ProtectedPodcastCheck())->run()->status);
    }

    #[TestDox('Podcast media outside protected storage is not a finding')]
    public function testOrdinaryPodcastMediaIsFine(): void
    {
        $this->makeMedia('/images/biblestudy/media/zz-normal.mp3', '2');

        $this->assertSame(HealthStatus::Ok, (new ProtectedPodcastCheck())->run()->status);
    }
}
