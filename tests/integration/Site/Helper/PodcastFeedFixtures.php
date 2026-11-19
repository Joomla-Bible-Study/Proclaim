<?php

/**
 * Fixtures for integration tests that build real podcast feeds.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Helper;

use Joomla\Database\DatabaseDriver;

/**
 * Seeds the rows a feed is built from: a podcast, a message and a media file.
 *
 * The using test supplies $this->db, inside a transaction it rolls back, and
 * uniqueAlias() through IntegrationTestCase.
 *
 * @property  DatabaseDriver|null  $db
 * @method    string  uniqueAlias(string $label = 'row')
 *
 * @since  __DEPLOY_VERSION__
 */
trait PodcastFeedFixtures
{
    /**
     * @param   string    $filename  The feed's file name
     * @param   int|null  $limit     The podcast's episode limit; null leaves the column NULL (no limit)
     * @param   string    $title     The podcast's title
     *
     * @return  int  The new podcast id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertPodcast(string $filename, ?int $limit = null, string $title = 'Episode date podcast'): int
    {
        // The feed passes website and podcastimage to string parameters.
        // podcastlink is set because older dev databases hold it NOT NULL with no default.
        $row = (object) [
            'title'        => $title,
            'website'      => 'example.org',
            'filename'     => $filename,
            'podcastimage' => '',
            'podcastlink'  => '',
            'podcastlimit' => $limit,
            'published'    => 1,
            'access'       => 1,
            'language'     => '*',
        ];

        $this->db->insertObject('#__bsms_podcast', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   string|null  $studydate  The message's date
     *
     * @return  int  The new message id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertMessage(?string $studydate): int
    {
        $row = (object) [
            'studytitle'  => 'Episode date message',
            'alias'       => $this->uniqueAlias('episode-date-study'),
            'studydate'   => $studydate,
            'teacher_id'  => 0,
            'series_id'   => 0,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'hits'        => 0,
            'checked_out' => 0,
            'asset_id'    => 0,
            'created_by'  => 0,
            'modified_by' => 0,
        ];

        // insertObject() skips NULL properties, so the column keeps its NULL default
        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int          $studyId     The owning message
     * @param   int          $podcastId   The podcast, in the CSV podcast_id column
     * @param   string|null  $createdate  The media file's createdate
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertMediaFile(int $studyId, int $podcastId, ?string $createdate): void
    {
        $row = (object) [
            'study_id'   => $studyId,
            'podcast_id' => (string) $podcastId,
            'metadata'   => '',
            'params'     => '{"filename":"https://example.org/media/episode.mp3","size":"1000","mime_type":"audio/mpeg"}',
            'createdate' => $createdate,
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_mediafiles', $row);
    }
}
