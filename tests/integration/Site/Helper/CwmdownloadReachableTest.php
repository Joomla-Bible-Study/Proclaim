<?php

/**
 * Integration tests for Cwmdownload::loadMedia() and reachable().
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmdownload;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #2047.
 *
 * Four endpoints record that a media file was played or downloaded, and all
 * four did so before anything decided whether the visitor could have it. Two
 * of them — playHit and playHitAjax — took a bare media id from the request
 * and incremented a counter with no check at all, so any message's play count
 * could be driven up by anyone, and playHitAjax transfers no file to do it.
 *
 * reachable() is the question those endpoints now ask first. A counter is a
 * statement that something happened, and a request that would have been
 * refused did not happen — it was turned away.
 *
 * ⚠️ loadMedia() is the load-bearing half: it carries the study and series
 * levels that isAccessible() needs. A row fetched without those joins produces
 * an object whose study_access is simply absent, and the chain check then finds
 * nothing to refuse — it fails open, silently, and every test asserting a
 * refusal would still pass if the refusal came from somewhere else.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmdownload::class)]
class CwmdownloadReachableTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private int $mediaAtStart = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        $this->mediaAtStart = $this->countMedia();

        // ⚠️ reachable() asks the application who is making the request, and a
        // test process arrives with no identity at all — getAuthorisedViewLevels()
        // then returns nothing and everything is refused. That refusal is an
        // artefact of the harness, and it would make every assertion in this
        // class pass for a reason unrelated to what it claims to test. Load a
        // real guest so the levels come from the site's own view-level rows.
        $guest = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        Factory::getApplication()->loadIdentity($guest);

        $this->assertNotEmpty(
            $guest->getAuthorisedViewLevels(),
            'The guest holds no view levels; nothing here would be distinguishing a refusal from an empty set.'
        );
    }

    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);

                $after = $this->countMedia();

                if ($after !== $this->mediaAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_mediafiles went from %d to %d rows.',
                        $this->mediaAtStart,
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
     * @return  int  Total rows in the media table.
     */
    private function countMedia(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_mediafiles'))
        )->loadResult();
    }

    /**
     * @param   array<string, int>  $state  Overrides for any flag.
     *
     * @return  int  The new media file's id.
     */
    private function makeChain(array $state = []): int
    {
        $state += [
            'series_access'   => 1,
            'study_access'    => 1,
            'media_published' => 1,
            'media_access'    => 1,
        ];

        $series = (object) [
            'series_text' => 'reachable fixture series',
            'published'   => 1,
            'access'      => $state['series_access'],
            'language'    => '*',
        ];
        $this->db->insertObject('#__bsms_series', $series, 'id');
        $seriesId = (int) $this->db->insertid();

        $study = (object) [
            'studytitle' => 'reachable fixture message',
            'series_id'  => $seriesId,
            'published'  => 1,
            'access'     => $state['study_access'],
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $studyId = (int) $this->db->insertid();

        $media = (object) [
            'study_id'  => $studyId,
            'server_id' => 0,
            'published' => $state['media_published'],
            'access'    => $state['media_access'],
            'params'    => '{"filename":"reachable-fixture.mp3"}',
            'metadata'  => '',
            'language'  => '*',
        ];
        $this->db->insertObject('#__bsms_mediafiles', $media, 'id');

        return (int) $this->db->insertid();
    }

    #[TestDox('The loaded row carries the levels the chain check needs')]
    public function testLoadMediaCarriesTheInheritedLevels(): void
    {
        $id    = $this->makeChain(['study_access' => 2, 'series_access' => 3]);
        $media = Cwmdownload::loadMedia($id);

        $this->assertNotNull($media, 'A published media file was not found.');

        // ⚠️ Without these the chain check has nothing to test and fails open.
        // Every refusal assertion elsewhere would still pass, for the wrong
        // reason, so this is asserted on its own.
        $this->assertObjectHasProperty('study_access', $media);
        $this->assertObjectHasProperty('series_access', $media);
        $this->assertSame(2, (int) $media->study_access);
        $this->assertSame(3, (int) $media->series_access);
    }

    #[TestDox('An unpublished media file is not loaded')]
    public function testUnpublishedMediaIsNotLoaded(): void
    {
        $id = $this->makeChain(['media_published' => 0]);

        $this->assertNull(Cwmdownload::loadMedia($id));
    }

    #[TestDox('A media id that does not exist is not loaded')]
    public function testUnknownMediaIsNotLoaded(): void
    {
        $this->assertNull(Cwmdownload::loadMedia(0));
    }

    #[TestDox('Nothing is reachable without a real, published media id')]
    public function testReachableRefusesMissingAndUnpublished(): void
    {
        // Independent of who is asking, so these hold whatever identity the
        // test process carries.
        $this->assertFalse(Cwmdownload::reachable(0));
        $this->assertFalse(Cwmdownload::reachable(-1));
        $this->assertFalse(Cwmdownload::reachable($this->makeChain(['media_published' => 0])));
    }

    #[TestDox('Each link in the chain puts the media out of a guest\'s reach on its own')]
    public function testEachLinkRefusesAGuest(): void
    {
        // The whole point of the gate. Before this, all four endpoints counted
        // a play or a download for media in exactly these states.
        foreach (['media_access', 'study_access', 'series_access'] as $field) {
            $this->assertFalse(
                Cwmdownload::reachable($this->makeChain([$field => 2])),
                "A guest reached media behind a restricted $field, so playing it would still be counted."
            );
        }
    }

    #[TestDox('A fully public chain is reachable by a guest')]
    public function testPublicChainIsReachable(): void
    {
        // Positive control. Without it, a reachable() broken for all input
        // would satisfy every refusal assertion above — and breaking it that
        // way stops every legitimate play and download being counted.
        $this->assertTrue(
            Cwmdownload::reachable($this->makeChain()),
            'A public media file was judged unreachable; no play or download would ever be recorded.'
        );
    }
}
